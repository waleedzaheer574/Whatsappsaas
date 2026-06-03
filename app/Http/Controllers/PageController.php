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
        $totalMessages = DB::table('messages')->join('conversations', 'conversations.id', '=', 'messages.conversation_id')->where('conversations.workspace_id', $workspaceId)->count();
        $aiReplies = DB::table('messages')->join('conversations', 'conversations.id', '=', 'messages.conversation_id')->where('conversations.workspace_id', $workspaceId)->where('messages.ai_generated', true)->count();
        $leadsCount = DB::table('contacts')->where('workspace_id', $workspaceId)->count();
        $responseRate = $totalMessages > 0 ? round(($aiReplies / $totalMessages) * 100, 1) : 0;
        $firstConversationId = DB::table('conversations')
            ->where('workspace_id', $workspaceId)
            ->latest('last_message_at')
            ->value('id') ?? 0;

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
