<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $workspaces = DB::table('workspaces')->select('id')->get();

        foreach ($workspaces as $workspace) {
            $content = <<<TEXT
A1 Rides WhatsApp AI assistant knowledge.
Website: https://a1rides.com.au
Business context: A1 Rides handles ride/transport booking enquiries in Australia.
AI reply rules:
- Be friendly, concise, and professional.
- Do not invent fixed prices, discounts, availability, or guarantees.
- For a new booking enquiry, collect: pickup location, drop-off location, date, time, passenger count, luggage count, child seat requirement, flight number if airport related, customer name, and best contact number.
- If the customer asks for fare/availability and the exact answer is not in training data, say the team will confirm after checking details.
- If the customer sends incomplete details, ask only for the missing details.
- If the customer wants urgent help, ask them to share pickup/drop-off and preferred time first.
- Keep replies suitable for WhatsApp: short paragraphs, no markdown tables.
TEXT;

            DB::table('ai_training_sources')->updateOrInsert(
                ['workspace_id' => $workspace->id, 'title' => 'A1 Rides Website Auto Reply Knowledge'],
                [
                    'type' => 'url',
                    'content' => $content,
                    'source_url' => 'https://a1rides.com.au',
                    'status' => 'indexed',
                    'chunks_count' => 1,
                    'trained_at' => now(),
                    'metadata' => json_encode(['seeded' => true, 'purpose' => 'whatsapp_ai_autoreply']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('ai_prompts')->updateOrInsert(
                ['workspace_id' => $workspace->id, 'type' => 'reply'],
                [
                    'name' => 'A1 Rides WhatsApp AI Assistant',
                    'tone' => 'friendly',
                    'system_prompt' => "You are the WhatsApp AI assistant for A1 Rides.\n\n".$content,
                    'is_active' => true,
                    'settings' => json_encode(['generated_from_training' => true, 'brand' => 'A1 Rides']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('ai_automations')->updateOrInsert(
                ['workspace_id' => $workspace->id, 'name' => 'A1 Rides AI Auto Reply'],
                [
                    'trigger' => '*',
                    'status' => 'active',
                    'flow' => json_encode([
                        'match' => 'all_messages',
                        'stop_after_match' => true,
                        'actions' => [
                            ['type' => 'send_ai_reply'],
                            ['type' => 'update_status', 'update_contact_status' => 'interested'],
                        ],
                    ]),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('ai_automations')->where('name', 'A1 Rides AI Auto Reply')->delete();
        DB::table('ai_training_sources')->where('title', 'A1 Rides Website Auto Reply Knowledge')->delete();
        DB::table('ai_prompts')->where('name', 'A1 Rides WhatsApp AI Assistant')->delete();
    }
};
