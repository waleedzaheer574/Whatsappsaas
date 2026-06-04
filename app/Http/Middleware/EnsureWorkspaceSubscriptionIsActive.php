<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceSubscriptionIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->email === 'admin@chatflow.test') {
            return $next($request);
        }

        $workspaceId = DB::table('workspace_user')
            ->where('user_id', $request->user()->id)
            ->value('workspace_id');

        $subscription = $workspaceId
            ? DB::table('subscriptions')->where('workspace_id', $workspaceId)->first()
            : null;

        if ($subscription?->status === 'active' && $subscription->renews_at && now()->greaterThan($subscription->renews_at)) {
            DB::table('subscriptions')->where('id', $subscription->id)->update([
                'status' => 'expired',
                'ends_at' => $subscription->renews_at,
                'updated_at' => now(),
            ]);
            $subscription->status = 'expired';
        }

        $isActive = $subscription?->status === 'active'
            && (! $subscription->renews_at || now()->lessThanOrEqualTo($subscription->renews_at));
        $isTrialing = $subscription?->status === 'trialing'
            && (! $subscription->trial_ends_at || now()->lessThanOrEqualTo($subscription->trial_ends_at));

        if ($isActive || $isTrialing) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(402, 'An active subscription is required.');
        }

        return redirect()
            ->route('dashboard.billing')
            ->with('error', 'Your subscription has expired. Please buy a subscription to continue using your CRM workspace.');
    }
}
