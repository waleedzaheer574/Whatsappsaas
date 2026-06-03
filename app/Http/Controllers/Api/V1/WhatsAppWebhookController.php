<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\InboundWhatsAppMessageData;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessIncomingWhatsAppMessage;
use App\Models\WhatsAppAccount;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WhatsAppWebhookController extends Controller
{
    use ApiResponse;

    public function verify(Request $request, WhatsAppAccount $account)
    {
        $verifyToken = data_get($account->settings, 'verify_token', config('services.whatsapp.verify_token'));

        if ($request->query('hub_verify_token') === $verifyToken) {
            return response($request->query('hub_challenge'));
        }

        return $this->error('Invalid webhook verification token.', status: 403);
    }

    public function receive(Request $request, WhatsAppAccount $account)
    {
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
                DB::table('messages')
                    ->where('metadata->provider_message_id', $providerMessageId)
                    ->update(['status' => $mappedStatus, 'updated_at' => now()]);
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
}
