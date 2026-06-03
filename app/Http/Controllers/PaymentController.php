<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    private array $plans = [
        'starter' => ['name' => 'Starter', 'amount' => 1900, 'limits' => ['whatsapp_accounts' => 1, 'messages' => 1000, 'team_members' => 2]],
        'pro' => ['name' => 'Pro', 'amount' => 4900, 'limits' => ['whatsapp_accounts' => 3, 'messages' => 10000, 'team_members' => 10]],
        'agency' => ['name' => 'Agency', 'amount' => 9900, 'limits' => ['whatsapp_accounts' => 10, 'messages' => 100000, 'team_members' => 50]],
    ];

    public function checkout(Request $request): RedirectResponse
    {
        $data = $request->validate(['plan' => ['required', 'in:starter,pro,agency']]);
        $workspaceId = $this->workspaceId($request);
        $plan = $this->plans[$data['plan']];

        if (! config('services.stripe.secret')) {
            $gatewayId = 'demo_'.Str::random(16);
            DB::table('invoices')->insert([
                'workspace_id' => $workspaceId,
                'stripe_invoice_id' => $gatewayId,
                'number' => strtoupper(Str::random(10)),
                'amount_due' => $plan['amount'] / 100,
                'amount_paid' => 0,
                'currency' => 'usd',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->activateSubscription($workspaceId, $data['plan'], $gatewayId);

            return redirect('/app/dashboard')->with('success', 'Demo payment completed. Your workspace is unlocked.');
        }

        $invoiceId = DB::table('invoices')->insertGetId([
            'workspace_id' => $workspaceId,
            'stripe_invoice_id' => 'pending_'.Str::random(24),
            'number' => strtoupper(Str::random(10)),
            'amount_due' => $plan['amount'] / 100,
            'amount_paid' => 0,
            'currency' => 'usd',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = Http::asForm()
            ->withBasicAuth(config('services.stripe.secret'), '')
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'subscription',
                'success_url' => route('dashboard.billing.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('dashboard.billing.cancel'),
                'customer_email' => $request->user()->email,
                'client_reference_id' => $workspaceId,
                'metadata[workspace_id]' => $workspaceId,
                'metadata[user_id]' => $request->user()->id,
                'metadata[plan]' => $data['plan'],
                'metadata[invoice_id]' => $invoiceId,
                'line_items[0][quantity]' => 1,
                'line_items[0][price_data][currency]' => 'usd',
                'line_items[0][price_data][unit_amount]' => $plan['amount'],
                'line_items[0][price_data][product_data][name]' => 'ChatFlow AI '.$plan['name'].' Plan',
                'line_items[0][price_data][recurring][interval]' => 'month',
            ]);

        if (! $response->successful()) {
            DB::table('invoices')->where('id', $invoiceId)->update(['status' => 'failed', 'updated_at' => now()]);

            return back()->with('error', 'Payment gateway error. Please check Stripe keys and try again.');
        }

        $session = $response->json();
        DB::table('invoices')->where('id', $invoiceId)->update([
            'stripe_invoice_id' => $session['id'],
            'hosted_url' => $session['url'] ?? null,
            'updated_at' => now(),
        ]);

        return redirect()->away($session['url']);
    }

    public function success(Request $request): RedirectResponse
    {
        $sessionId = $request->query('session_id');
        $invoice = $sessionId ? DB::table('invoices')->where('stripe_invoice_id', $sessionId)->first() : null;

        if (! $invoice) {
            return redirect('/app/billing')->with('error', 'Payment session not found yet.');
        }

        $plan = 'pro';
        if (config('services.stripe.secret')) {
            $response = Http::withBasicAuth(config('services.stripe.secret'), '')
                ->get('https://api.stripe.com/v1/checkout/sessions/'.$sessionId);

            if (! $response->successful() || $response->json('payment_status') !== 'paid') {
                return redirect('/app/billing')->with('error', 'Payment is not confirmed yet.');
            }

            $plan = $response->json('metadata.plan', 'pro');
        }

        $this->activateSubscription((int) $invoice->workspace_id, $plan, $sessionId);

        return redirect('/app/dashboard')->with('success', 'Payment successful. Your CRM workspace is unlocked.');
    }

    public function cancel(): RedirectResponse
    {
        return redirect('/app/billing')->with('error', 'Payment was canceled. Choose a plan to unlock your workspace.');
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        if (! $this->validStripeSignature($payload, $signature)) {
            abort(400, 'Invalid signature.');
        }

        $event = json_decode($payload, true);
        if (($event['type'] ?? null) === 'checkout.session.completed') {
            $session = $event['data']['object'];
            if (($session['payment_status'] ?? null) === 'paid') {
                $workspaceId = (int) ($session['metadata']['workspace_id'] ?? 0);
                $plan = $session['metadata']['plan'] ?? 'pro';
                if ($workspaceId) {
                    $this->activateSubscription($workspaceId, $plan, $session['id']);
                }
            }
        }

        return response()->json(['received' => true]);
    }

    private function activateSubscription(int $workspaceId, string $plan, string $gatewayId): void
    {
        $selected = $this->plans[$plan] ?? $this->plans['pro'];

        DB::table('subscriptions')->updateOrInsert(
            ['workspace_id' => $workspaceId],
            [
                'stripe_id' => $gatewayId,
                'plan' => $plan,
                'status' => 'active',
                'limits' => json_encode($selected['limits']),
                'trial_ends_at' => null,
                'renews_at' => now()->addMonth(),
                'ends_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('workspaces')->where('id', $workspaceId)->update(['plan' => $plan, 'updated_at' => now()]);
        DB::table('invoices')->where('stripe_invoice_id', $gatewayId)->update([
            'amount_paid' => $selected['amount'] / 100,
            'status' => 'paid',
            'paid_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('activity_logs')->insert([
            'workspace_id' => $workspaceId,
            'type' => 'subscription.activated',
            'description' => 'Subscription activated for '.$selected['name'].' plan',
            'properties' => json_encode(['gateway_id' => $gatewayId, 'plan' => $plan]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function validStripeSignature(string $payload, ?string $signature): bool
    {
        $secret = config('services.stripe.webhook_secret');
        if (! $secret) return true;
        if (! $signature) return false;

        $parts = collect(explode(',', $signature))->mapWithKeys(function (string $part) {
            [$key, $value] = array_pad(explode('=', $part, 2), 2, null);
            return [$key => $value];
        });

        $timestamp = $parts->get('t');
        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        return hash_equals($expected, (string) $parts->get('v1'));
    }

    private function workspaceId(Request $request): int
    {
        return (int) DB::table('workspace_user')
            ->where('user_id', $request->user()->id)
            ->value('workspace_id');
    }
}
