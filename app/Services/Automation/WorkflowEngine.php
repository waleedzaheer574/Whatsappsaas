<?php

namespace App\Services\Automation;

use App\Models\AiAutomation;
use App\Models\Conversation;
use App\Services\AI\AiReplyService;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WorkflowEngine
{
    public function __construct(private readonly AiReplyService $aiReplies)
    {
    }

    public function runForMessage(Conversation $conversation, string $message): void
    {
        $automations = AiAutomation::query()
            ->where('workspace_id', $conversation->workspace_id)
            ->where('status', 'active')
            ->get();

        foreach ($automations as $automation) {
            $flow = is_array($automation->flow) ? $automation->flow : [];
            $matched = $this->matchesAutomation($automation, $message, $flow);
            $status = $matched ? 'matched' : 'skipped';
            $error = null;

            if ($matched) {
                try {
                    $this->executeActions($conversation, $automation, $message);
                    $automation->increment('runs_count');
                    $automation->update(['success_rate' => 100, 'updated_at' => now()]);
                    $status = 'completed';
                } catch (\Throwable $exception) {
                    $status = 'failed';
                    $error = $exception->getMessage();
                }
            }

            DB::table('automation_logs')->insert([
                'automation_id' => $automation->id,
                'conversation_id' => $conversation->id,
                'status' => $status,
                'context' => json_encode(['message' => $message, 'trigger' => $automation->trigger]),
                'error' => $error,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($matched && ($flow['stop_after_match'] ?? false)) {
                break;
            }
        }
    }

    private function matchesAutomation(AiAutomation $automation, string $message, array $flow): bool
    {
        $trigger = trim(strtolower((string) $automation->trigger));
        $match = $flow['match'] ?? 'contains';

        if (in_array($trigger, ['*', 'all', 'any'], true) || $match === 'all_messages') {
            return trim($message) !== '';
        }

        return str_contains(strtolower($message), $trigger);
    }

    private function executeActions(Conversation $conversation, AiAutomation $automation, string $message): void
    {
        $flow = is_array($automation->flow) ? $automation->flow : [];
        $actions = $flow['actions'] ?? [['type' => 'send_ai_reply']];

        foreach ($actions as $action) {
            $type = $action['type'] ?? 'send_ai_reply';
            match ($type) {
                'send_template' => $this->sendReply($conversation, (string) ($action['reply_template'] ?: 'Thanks for reaching out. Our team will help you shortly.'), $automation),
                'send_ai_reply' => $this->sendReply($conversation, $this->aiReplies->suggestReply($conversation, $message), $automation),
                'update_status' => $this->updateContactStatus($conversation, (string) ($action['update_contact_status'] ?? 'interested')),
                'add_note' => $this->addContactNote($conversation, (string) ($action['reply_template'] ?: 'Automation matched: '.$automation->name)),
                default => null,
            };
        }
    }

    private function sendReply(Conversation $conversation, string $body, AiAutomation $automation): void
    {
        $messageId = DB::table('messages')->insertGetId([
            'conversation_id' => $conversation->id,
            'direction' => 'outbound',
            'sender_type' => 'ai',
            'body' => $body,
            'message_type' => 'text',
            'status' => 'sent',
            'ai_generated' => true,
            'metadata' => json_encode(['source' => 'automation', 'automation_id' => $automation->id]),
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $record = DB::table('conversations')
            ->join('contacts', 'contacts.id', '=', 'conversations.contact_id')
            ->join('whatsapp_accounts', 'whatsapp_accounts.id', '=', 'conversations.whatsapp_account_id')
            ->where('conversations.id', $conversation->id)
            ->select('contacts.phone_number as contact_phone', 'whatsapp_accounts.settings as account_settings')
            ->first();
        $settings = json_decode($record->account_settings ?? '{}', true) ?: [];
        $token = $settings['access_token'] ?? null;
        $phoneNumberId = $settings['phone_number_id'] ?? null;

        if ($token && $phoneNumberId) {
            $response = Http::withToken($token)
                ->timeout(15)
                ->connectTimeout(10)
                ->withOptions(['proxy' => ''])
                ->post("https://graph.facebook.com/v20.0/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => PhoneNumber::e164Digits((string) $record->contact_phone),
                    'type' => 'text',
                    'text' => ['body' => $body],
                ]);

            DB::table('messages')->where('id', $messageId)->update([
                'status' => $response->successful() ? 'sent' : 'failed',
                'metadata' => json_encode([
                    'source' => 'automation',
                    'automation_id' => $automation->id,
                    'provider' => 'meta',
                    'provider_message_id' => $response->json('messages.0.id'),
                    'error' => $response->successful() ? null : $response->json(),
                ]),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('messages')->where('id', $messageId)->update([
                'status' => 'failed',
                'metadata' => json_encode(['source' => 'automation', 'automation_id' => $automation->id, 'error' => 'WhatsApp account is not connected.']),
                'updated_at' => now(),
            ]);
        }

        $conversation->update(['last_message_at' => now()]);
    }

    private function updateContactStatus(Conversation $conversation, string $status): void
    {
        $allowed = ['new_lead', 'interested', 'follow_up', 'won', 'lost', 'blocked'];
        $status = in_array($status, $allowed, true) ? $status : 'interested';

        DB::table('contacts')->where('id', $conversation->contact_id)->where('workspace_id', $conversation->workspace_id)->update([
            'status' => $status,
            'updated_at' => now(),
        ]);
        DB::table('leads')->where('contact_id', $conversation->contact_id)->where('workspace_id', $conversation->workspace_id)->update([
            'stage' => $status,
            'updated_at' => now(),
        ]);
    }

    private function addContactNote(Conversation $conversation, string $body): void
    {
        DB::table('contact_notes')->insert([
            'contact_id' => $conversation->contact_id,
            'body' => Str::limit($body, 2000),
            'is_internal' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
