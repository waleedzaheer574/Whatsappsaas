<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function landing(): Response
    {
        return Inertia::render('Public/Landing');
    }

    public function features(): Response
    {
        return Inertia::render('Public/Features');
    }

    public function pricing(): Response
    {
        return Inertia::render('Public/Pricing');
    }

    public function contact(): Response
    {
        return Inertia::render('Public/Contact');
    }

    public function docs(): Response
    {
        return Inertia::render('Public/Docs');
    }

    public function blog(): Response
    {
        return Inertia::render('Public/Blog');
    }

    public function terms(): Response
    {
        return Inertia::render('Public/Legal', ['kind' => 'Terms']);
    }

    public function privacy(): Response
    {
        return Inertia::render('Public/Legal', ['kind' => 'Privacy']);
    }

    public function login(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function register(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function forgot(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function verify(): Response
    {
        return Inertia::render('Auth/VerifyEmail');
    }

    public function dashboard(Request $request): Response
    {
        $workspace = DB::table('workspaces')
            ->when($request->user(), fn ($query) => $query
                ->join('workspace_user', 'workspace_user.workspace_id', '=', 'workspaces.id')
                ->where('workspace_user.user_id', $request->user()->id)
                ->select('workspaces.*'))
            ->first();
        $workspaceId = $workspace->id ?? 1;
        $chartPeriod = in_array($request->query('chart_period'), ['week', 'month', 'quarter'], true)
            ? $request->query('chart_period')
            : 'week';
        $chartDays = match ($chartPeriod) {
            'month' => 30,
            'quarter' => 90,
            default => 7,
        };
        $chartStart = now()->subDays($chartDays - 1)->startOfDay();
        $totalMessages = DB::table('messages')->join('conversations', 'conversations.id', '=', 'messages.conversation_id')->where('conversations.workspace_id', $workspaceId)->count();
        $aiReplies = DB::table('messages')->join('conversations', 'conversations.id', '=', 'messages.conversation_id')->where('conversations.workspace_id', $workspaceId)->where('messages.ai_generated', true)->count();
        $leadsCount = DB::table('contacts')->where('workspace_id', $workspaceId)->count();
        $responseRate = $totalMessages > 0 ? round(($aiReplies / $totalMessages) * 100, 1) : 0;
        $firstConversationId = DB::table('conversations')
            ->where('workspace_id', $workspaceId)
            ->latest('last_message_at')
            ->value('id') ?? 0;
        $subscription = DB::table('subscriptions')->where('workspace_id', $workspaceId)->latest()->first();
        if ($subscription?->status === 'active' && $subscription->renews_at && now()->greaterThan($subscription->renews_at)) {
            DB::table('subscriptions')->where('id', $subscription->id)->update([
                'status' => 'expired',
                'ends_at' => $subscription->renews_at,
                'updated_at' => now(),
            ]);
            $subscription = DB::table('subscriptions')->where('id', $subscription->id)->first();
        }

        $subscriptionNotice = null;
        if ($subscription?->status === 'active' && $subscription->renews_at) {
            $renewsAt = \Illuminate\Support\Carbon::parse($subscription->renews_at);
            if ($renewsAt->isFuture() && $renewsAt->lessThanOrEqualTo(now()->addDays(2))) {
                $subscriptionNotice = [
                    'id' => 'subscription-renewal',
                    'title' => 'Subscription expiring soon',
                    'text' => 'Your subscription will expire on '.$renewsAt->format('M d, Y').'. Please renew or buy a subscription.',
                    'count' => 1,
                    'initial' => '!',
                    'created_at' => now(),
                ];
            }
        } elseif ($subscription?->status === 'expired') {
            $subscriptionNotice = [
                'id' => 'subscription-expired',
                'title' => 'Subscription expired',
                'text' => 'Your subscription has expired. Please buy a subscription to continue.',
                'count' => 1,
                'initial' => '!',
                'created_at' => now(),
            ];
        }

        $unreadNotifications = (int) DB::table('conversations')
            ->where('workspace_id', $workspaceId)
            ->sum('unread_count');
        $notifications = DB::table('conversations')
            ->join('contacts', 'contacts.id', '=', 'conversations.contact_id')
            ->where('conversations.workspace_id', $workspaceId)
            ->where('conversations.unread_count', '>', 0)
            ->select('conversations.id', 'conversations.unread_count as count', 'conversations.last_message_at', 'contacts.name', 'contacts.phone_number')
            ->latest('conversations.last_message_at')
            ->limit(8)
            ->get()
            ->map(fn ($conversation) => [
                'id' => $conversation->id,
                'title' => $conversation->name,
                'text' => $conversation->phone_number.' sent new message',
                'count' => $conversation->count,
                'initial' => strtoupper(substr($conversation->name ?? 'C', 0, 1)),
                'created_at' => $conversation->last_message_at,
            ]);
        if ($subscriptionNotice) {
            $notifications->prepend($subscriptionNotice);
            $unreadNotifications++;
        }
        $messageSeriesRows = DB::table('messages')
            ->join('conversations', 'conversations.id', '=', 'messages.conversation_id')
            ->where('conversations.workspace_id', $workspaceId)
            ->where('messages.sent_at', '>=', $chartStart)
            ->selectRaw('DATE(messages.sent_at) as bucket, messages.direction, COUNT(*) as total')
            ->groupBy('bucket', 'messages.direction')
            ->get()
            ->groupBy(fn ($row) => $row->bucket.'|'.$row->direction);

        $messageSeries = collect(range(0, $chartDays - 1))->map(function (int $offset) use ($chartStart, $messageSeriesRows) {
            $date = $chartStart->copy()->addDays($offset);
            $bucket = $date->toDateString();

            return [
                'label' => $date->format('M j'),
                'received' => (int) ($messageSeriesRows->get($bucket.'|inbound')?->first()?->total ?? 0),
                'sent' => (int) ($messageSeriesRows->get($bucket.'|outbound')?->first()?->total ?? 0),
            ];
        })->values();

        $channelRows = DB::table('contacts')
            ->where('workspace_id', $workspaceId)
            ->selectRaw("COALESCE(NULLIF(source, ''), 'whatsapp') as source, COUNT(*) as total")
            ->groupBy('source')
            ->orderByDesc('total')
            ->limit(4)
            ->get();
        if ($channelRows->isEmpty()) {
            $channelRows = collect([(object) ['source' => 'whatsapp', 'total' => $totalMessages]]);
        }
        $channelTotal = max(1, (int) $channelRows->sum('total'));
        $channelPalette = [
            ['class' => 'bg-violet-600', 'hex' => '#7c3aed'],
            ['class' => 'bg-teal-500', 'hex' => '#14b8a6'],
            ['class' => 'bg-sky-500', 'hex' => '#38bdf8'],
            ['class' => 'bg-pink-400', 'hex' => '#f472b6'],
        ];
        $channels = $channelRows->values()->map(function ($channel, int $index) use ($channelTotal, $channelPalette) {
            $palette = $channelPalette[$index] ?? $channelPalette[0];
            $percent = round(((int) $channel->total / $channelTotal) * 100);

            return [
                'name' => ucfirst(str_replace('_', ' ', $channel->source)),
                'value' => number_format((int) $channel->total),
                'raw' => (int) $channel->total,
                'width' => max(6, $percent).'%',
                'percent' => $percent,
                'color' => $palette['class'],
                'hex' => $palette['hex'],
            ];
        });

        $screen = $request->route('screen') ?? 'Dashboard Overview';

        return Inertia::render('Dashboard/Workspace', [
            'screen' => $screen,
            'workspace' => $workspace,
            'dashboard' => [
                'stats' => [
                    ['label' => 'Total Messages', 'value' => number_format($totalMessages), 'change' => '0', 'key' => 'messages'],
                    ['label' => 'AI Replies Sent', 'value' => number_format($aiReplies), 'change' => '0', 'key' => 'ai'],
                    ['label' => 'Leads Captured', 'value' => number_format($leadsCount), 'change' => '0', 'key' => 'leads'],
                    ['label' => 'Response Rate', 'value' => $responseRate.'%', 'change' => '6.3', 'key' => 'rate'],
                ],
                'accounts' => DB::table('whatsapp_accounts')->where('workspace_id', $workspaceId)->latest()->limit(3)->get(),
                'activities' => DB::table('activity_logs')->where('workspace_id', $workspaceId)->latest()->limit(5)->get(),
                'chartPeriod' => $chartPeriod,
                'messageSeries' => $messageSeries,
                'channels' => $channels,
                'unreadNotifications' => $unreadNotifications,
                'notifications' => $notifications,
                'subscriptionNotice' => $subscriptionNotice,
                'leads' => DB::table('contacts')->where('workspace_id', $workspaceId)->latest()->limit(4)->get(),
                'conversations' => DB::table('conversations')
                    ->join('contacts', 'contacts.id', '=', 'conversations.contact_id')
                    ->where('conversations.workspace_id', $workspaceId)
                    ->select('conversations.*', 'contacts.name', 'contacts.phone_number')
                    ->latest('conversations.last_message_at')
                    ->limit(6)
                    ->get(),
                'messages' => DB::table('messages')
                    ->join('conversations', 'conversations.id', '=', 'messages.conversation_id')
                    ->leftJoin('message_media', 'message_media.message_id', '=', 'messages.id')
                    ->where('conversations.workspace_id', $workspaceId)
                    ->select('messages.*', 'message_media.path as media_path', 'message_media.mime_type as media_mime_type', 'message_media.size as media_size', 'message_media.metadata as media_metadata')
                    ->orderBy('messages.sent_at')
                    ->limit(100)
                    ->get(),
            ],
            'module' => [
                'contacts' => DB::table('contacts')->where('workspace_id', $workspaceId)->latest()->limit(50)->get(),
                'leads' => DB::table('leads')->where('workspace_id', $workspaceId)->latest()->limit(50)->get(),
                'notes' => DB::table('contact_notes')
                    ->join('contacts', 'contacts.id', '=', 'contact_notes.contact_id')
                    ->where('contacts.workspace_id', $workspaceId)
                    ->select('contact_notes.*', 'contacts.name as contact_name')
                    ->latest('contact_notes.created_at')
                    ->limit(30)
                    ->get(),
                'subscriptions' => DB::table('subscriptions')->where('workspace_id', $workspaceId)->latest()->limit(10)->get(),
                'invoices' => DB::table('invoices')->where('workspace_id', $workspaceId)->latest()->limit(20)->get(),
                'paymentPlans' => [
                    ['key' => 'starter', 'name' => 'Starter', 'price' => 19, 'features' => ['1 WhatsApp account', '1,000 messages/month', '2 team members']],
                    ['key' => 'pro', 'name' => 'Pro', 'price' => 49, 'features' => ['3 WhatsApp accounts', '10,000 messages/month', '10 team members']],
                    ['key' => 'agency', 'name' => 'Agency', 'price' => 99, 'features' => ['10 WhatsApp accounts', '100,000 messages/month', '50 team members']],
                ],
                'paymentGateway' => config('services.stripe.secret') ? 'stripe' : 'demo',
                'automations' => DB::table('ai_automations')->where('workspace_id', $workspaceId)->latest()->limit(50)->get(),
                'broadcasts' => DB::table('broadcast_campaigns')->where('workspace_id', $workspaceId)->latest()->limit(50)->get(),
                'training' => DB::table('ai_training_sources')->where('workspace_id', $workspaceId)->latest()->limit(50)->get(),
                'team' => DB::table('workspace_user')->join('users', 'users.id', '=', 'workspace_user.user_id')->where('workspace_user.workspace_id', $workspaceId)->select('users.name', 'users.email', 'workspace_user.role', 'workspace_user.created_at')->latest('workspace_user.created_at')->limit(50)->get(),
                'integrations' => DB::table('connected_integrations')->where('workspace_id', $workspaceId)->latest()->limit(50)->get(),
                'apiKeys' => DB::table('api_keys')->where('workspace_id', $workspaceId)->latest()->limit(50)->get(),
                'activity' => DB::table('activity_logs')->where('workspace_id', $workspaceId)->latest()->limit(80)->get(),
                'accounts' => DB::table('whatsapp_accounts')->where('workspace_id', $workspaceId)->latest()->limit(20)->get(),
            ],
        ]);
    }
}
