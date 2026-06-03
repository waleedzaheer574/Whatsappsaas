<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'admin@chatflow.test'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password'),
            ]
        );

        $workspaceId = DB::table('workspaces')->updateOrInsert(
            ['slug' => 'chatflow-main'],
            [
                'name' => 'ChatFlow AI Demo',
                'plan' => 'pro',
                'timezone' => 'Asia/Karachi',
                'settings' => json_encode(['theme' => 'system', 'ai_reply_mode' => 'assistive']),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $workspace = DB::table('workspaces')->where('slug', 'chatflow-main')->first();
        DB::table('workspace_user')->updateOrInsert(
            ['workspace_id' => $workspace->id, 'user_id' => $user->id],
            ['role' => 'admin', 'created_at' => now(), 'updated_at' => now()]
        );

        $accounts = [
            ['name' => 'Main Business', 'phone_number' => '+1 (556) 123-4567'],
            ['name' => 'Support Line', 'phone_number' => '+1 (556) 987-6543'],
        ];

        foreach ($accounts as $account) {
            DB::table('whatsapp_accounts')->updateOrInsert(
                ['workspace_id' => $workspace->id, 'phone_number' => $account['phone_number']],
                [
                    'name' => $account['name'],
                    'provider' => 'meta',
                    'status' => 'connected',
                    'quality_rating' => 'high',
                    'last_synced_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $mainAccount = DB::table('whatsapp_accounts')->where('workspace_id', $workspace->id)->first();
        $contacts = [
            ['name' => 'Emily Johnson', 'phone_number' => '+1 (556) 123-4567', 'status' => 'interested', 'deal_value' => 2400],
            ['name' => 'Michael Smith', 'phone_number' => '+1 (865) 987-6543', 'status' => 'new_lead', 'deal_value' => 5800],
            ['name' => 'Sarah Wilson', 'phone_number' => '+1 (555) 456-7890', 'status' => 'follow_up', 'deal_value' => 1200],
            ['name' => 'James Brown', 'phone_number' => '+1 (555) 874-9012', 'status' => 'support', 'deal_value' => 400],
            ['name' => 'David Lee', 'phone_number' => '+1 (555) 321-8811', 'status' => 'payment_issue', 'deal_value' => 950],
        ];

        foreach ($contacts as $contact) {
            DB::table('contacts')->updateOrInsert(
                ['workspace_id' => $workspace->id, 'phone_number' => $contact['phone_number']],
                [
                    'name' => $contact['name'],
                    'status' => $contact['status'],
                    'source' => 'whatsapp',
                    'deal_value' => $contact['deal_value'],
                    'owner_name' => 'John Doe',
                    'tags' => json_encode(['demo', 'whatsapp']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        foreach (DB::table('contacts')->where('workspace_id', $workspace->id)->get() as $contact) {
            DB::table('conversations')->updateOrInsert(
                ['workspace_id' => $workspace->id, 'contact_id' => $contact->id],
                [
                    'whatsapp_account_id' => $mainAccount->id,
                    'status' => 'open',
                    'priority' => $contact->name === 'Emily Johnson' ? 'high' : 'normal',
                    'assigned_to' => 'John Doe',
                    'unread_count' => $contact->name === 'Emily Johnson' ? 2 : 1,
                    'last_message_at' => now()->subMinutes(rand(2, 75)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $conversation = DB::table('conversations')->join('contacts', 'contacts.id', '=', 'conversations.contact_id')->where('contacts.name', 'Emily Johnson')->select('conversations.id')->first();
        if ($conversation) {
            DB::table('messages')->insert([
                ['conversation_id' => $conversation->id, 'direction' => 'inbound', 'sender_type' => 'contact', 'body' => 'Hi, I need help with my order status.', 'status' => 'read', 'ai_generated' => false, 'sent_at' => now()->subMinutes(12), 'created_at' => now(), 'updated_at' => now()],
                ['conversation_id' => $conversation->id, 'direction' => 'outbound', 'sender_type' => 'ai', 'body' => 'Sure! I can help you with that. Please provide your order number.', 'status' => 'delivered', 'ai_generated' => true, 'sent_at' => now()->subMinutes(10), 'created_at' => now(), 'updated_at' => now()],
                ['conversation_id' => $conversation->id, 'direction' => 'inbound', 'sender_type' => 'contact', 'body' => 'My order number is #4567', 'status' => 'read', 'ai_generated' => false, 'sent_at' => now()->subMinutes(8), 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        DB::table('ai_automations')->insert([
            ['workspace_id' => $workspace->id, 'name' => 'Order Status Assistant', 'trigger' => 'order_status_question', 'status' => 'active', 'runs_count' => 1542, 'success_rate' => 92.6, 'created_at' => now(), 'updated_at' => now()],
            ['workspace_id' => $workspace->id, 'name' => 'Lead Qualification', 'trigger' => 'new_inbound_message', 'status' => 'active', 'runs_count' => 872, 'success_rate' => 88.4, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('ai_training_sources')->insert([
            ['workspace_id' => $workspace->id, 'title' => 'Product FAQ', 'type' => 'document', 'status' => 'indexed', 'chunks_count' => 128, 'trained_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['workspace_id' => $workspace->id, 'title' => 'Shipping Policy', 'type' => 'url', 'status' => 'indexed', 'chunks_count' => 42, 'trained_at' => now(), 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('broadcast_campaigns')->insert([
            ['workspace_id' => $workspace->id, 'name' => 'May Promo Blast', 'status' => 'sent', 'audience_count' => 3500, 'sent_count' => 3500, 'delivered_count' => 3290, 'replied_count' => 412, 'scheduled_at' => now()->subDay(), 'created_at' => now(), 'updated_at' => now()],
        ]);

        foreach (range(0, 6) as $day) {
            DB::table('analytics_daily_snapshots')->updateOrInsert(
                ['workspace_id' => $workspace->id, 'date' => now()->subDays(6 - $day)->toDateString()],
                [
                    'received_messages' => [850, 3200, 1850, 3500, 2450, 1680, 2300][$day],
                    'sent_messages' => [420, 1800, 900, 1720, 980, 1430, 650][$day],
                    'ai_replies' => [350, 1550, 760, 1490, 820, 1210, 530][$day],
                    'leads_captured' => [44, 82, 63, 104, 76, 58, 71][$day],
                    'response_rate' => 92.6,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        foreach (['New lead captured from WhatsApp', 'AI training data updated', 'Broadcast sent successfully', 'New team member added', 'Payment received from Pro Plan'] as $description) {
            DB::table('activity_logs')->insert([
                'workspace_id' => $workspace->id,
                'type' => 'system',
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (['Super Admin', 'Workspace Owner', 'Manager', 'Agent', 'Viewer'] as $role) {
            DB::table('roles')->updateOrInsert(
                ['workspace_id' => $workspace->id, 'slug' => str($role)->slug()],
                ['name' => $role, 'is_system' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        DB::table('subscriptions')->updateOrInsert(
            ['workspace_id' => $workspace->id],
            [
                'plan' => 'pro',
                'status' => 'active',
                'trial_ends_at' => now()->addDays(14),
                'renews_at' => now()->addMonth(),
                'limits' => json_encode(['whatsapp_accounts' => 3, 'messages' => 10000, 'team_members' => 10]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('invoices')->updateOrInsert(
            ['workspace_id' => $workspace->id, 'number' => 'INV-CHATFLOW-001'],
            [
                'amount_due' => 49,
                'amount_paid' => 49,
                'currency' => 'usd',
                'status' => 'paid',
                'paid_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
