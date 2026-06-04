<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ChatMessageController extends Controller
{
    public function store(Request $request)
    {
        $workspaceId = (int) $request->attributes->get('workspace_id');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:40'],
            'message' => ['required', 'string', 'max:2000'],
            'direction' => ['nullable', Rule::in(['inbound', 'outbound'])],
            'status' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email'],
        ]);

        $accountId = DB::table('whatsapp_accounts')->where('workspace_id', $workspaceId)->value('id');
        if (! $accountId) {
            $accountId = DB::table('whatsapp_accounts')->insertGetId([
                'workspace_id' => $workspaceId,
                'name' => 'API Inbox',
                'phone_number' => 'api',
                'provider' => 'api',
                'status' => 'connected',
                'quality_rating' => 'high',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $phoneNumber = $this->normalizePhone($data['phone_number']);
        $contactRecord = DB::table('contacts')
            ->where('workspace_id', $workspaceId)
            ->where('phone_number', $phoneNumber)
            ->first();
        $contactId = $contactRecord?->id;

        if (! $contactId) {
            $contactId = DB::table('contacts')->insertGetId([
                'workspace_id' => $workspaceId,
                'name' => $data['name'],
                'phone_number' => $phoneNumber,
                'email' => $data['email'] ?? null,
                'status' => 'new_lead',
                'source' => 'api',
                'owner_name' => 'API',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('leads')->insert([
                'workspace_id' => $workspaceId,
                'contact_id' => $contactId,
                'title' => $data['name'].' Deal',
                'stage' => 'new_lead',
                'value' => 0,
                'score' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            if ($contactRecord->status === 'blocked') {
                return response()->json([
                    'success' => false,
                    'message' => 'This contact is blocked. Message was not stored.',
                ], 423);
            }

            DB::table('contacts')->where('id', $contactId)->update([
                'name' => $data['name'],
                'email' => $data['email'] ?? DB::raw('email'),
                'updated_at' => now(),
            ]);
        }

        $conversationId = DB::table('conversations')
            ->where('workspace_id', $workspaceId)
            ->where('contact_id', $contactId)
            ->value('id');

        if (! $conversationId) {
            $conversationId = DB::table('conversations')->insertGetId([
                'workspace_id' => $workspaceId,
                'whatsapp_account_id' => $accountId,
                'contact_id' => $contactId,
                'status' => 'open',
                'priority' => 'normal',
                'unread_count' => 0,
                'last_message_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $direction = $data['direction'] ?? 'inbound';
        $messageId = DB::table('messages')->insertGetId([
            'conversation_id' => $conversationId,
            'direction' => $direction,
            'sender_type' => $direction === 'inbound' ? 'contact' : 'agent',
            'body' => $data['message'],
            'message_type' => 'text',
            'status' => $data['status'] ?? ($direction === 'inbound' ? 'received' : 'sent'),
            'ai_generated' => false,
            'metadata' => json_encode(['source' => 'external_api']),
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('conversations')->where('id', $conversationId)->update([
            'unread_count' => $direction === 'inbound' ? DB::raw('unread_count + 1') : DB::raw('unread_count'),
            'last_message_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Chat message added successfully.',
            'data' => [
                'contact_id' => $contactId,
                'conversation_id' => $conversationId,
                'message_id' => $messageId,
            ],
        ], 201);
    }

    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        if (str_starts_with($phone, '+')) {
            return '+'.preg_replace('/\D+/', '', $phone);
        }

        return preg_replace('/\D+/', '', $phone);
    }
}
