<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\InboundWhatsAppMessageData;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessIncomingWhatsAppMessage;
use App\Models\WhatsAppAccount;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    use ApiResponse;

    public function verify(Request $request, string $account)
    {
        $account = $this->resolveAccount($account, $request);
        $verifyToken = data_get($account->settings, 'verify_token', config('services.whatsapp.verify_token'));

        if ($request->query('hub_verify_token') === $verifyToken) {
            return response($request->query('hub_challenge'));
        }

        return $this->error('Invalid webhook verification token.', status: 403);
    }

    public function receive(Request $request, string $account)
    {
        $account = $this->resolveAccount($account, $request);
        $status = data_get($request->all(), 'entry.0.changes.0.value.statuses.0');
        if ($status) {
            $providerMessageId = data_get($status, 'id');
            $mappedStatus = match (data_get($status, 'status')) {
                'delivered' => 'delivered',
                'read' => 'read',
                'failed' => 'failed',
                default => 'sent',
            };

            if ($providerMessageId) {
                $updated = DB::table('messages')
                    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.provider_message_id')) = ?", [$providerMessageId])
                    ->update(['status' => $mappedStatus, 'updated_at' => now()]);

                if (! $updated) {
                    Log::warning('WhatsApp status webhook did not match any message.', [
                        'account_id' => $account->id,
                        'provider_message_id' => $providerMessageId,
                        'status' => data_get($status, 'status'),
                    ]);
                }
            }

            return $this->success([], 'WhatsApp status webhook processed.');
        }

        $message = data_get($request->all(), 'entry.0.changes.0.value.messages.0', []);
        $contact = data_get($request->all(), 'entry.0.changes.0.value.contacts.0', []);

        if (! $message) {
            return $this->success([], 'Webhook received without message payload.');
        }

        ProcessIncomingWhatsAppMessage::dispatch(new InboundWhatsAppMessageData(
            whatsappAccountId: $account->id,
            from: data_get($message, 'from'),
            name: data_get($contact, 'profile.name', data_get($message, 'from')),
            body: data_get($message, 'text.body', ''),
            messageId: data_get($message, 'id', uniqid('wa_', true)),
            type: data_get($message, 'type', 'text'),
            metadata: $request->all(),
        ));

        return $this->success([], 'WhatsApp webhook queued successfully.');
    }

    private function resolveAccount(string $account, Request $request): WhatsAppAccount
    {
        $record = WhatsAppAccount::query()->find($account);
        if ($record) {
            return $record;
        }

        $phoneNumberId = data_get($request->all(), 'entry.0.changes.0.value.metadata.phone_number_id');
        if ($phoneNumberId) {
            $record = WhatsAppAccount::query()
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(settings, '$.phone_number_id')) = ?", [$phoneNumberId])
                ->first();

            if ($record) {
                return $record;
            }
        }

        $verifyToken = $request->query('hub_verify_token');
        if ($verifyToken) {
            $record = WhatsAppAccount::query()
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(settings, '$.verify_token')) = ?", [$verifyToken])
                ->first();

            if ($record) {
                return $record;
            }
        }

        abort(404);
    }
}
