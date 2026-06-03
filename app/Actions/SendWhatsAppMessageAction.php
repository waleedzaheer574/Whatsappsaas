<?php

namespace App\Actions;

use App\DTOs\OutboundMessageData;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\WhatsApp\WhatsAppCloudService;

class SendWhatsAppMessageAction
{
    public function __construct(private readonly WhatsAppCloudService $whatsApp)
    {
    }

    public function execute(OutboundMessageData $data): Message
    {
        $conversation = Conversation::query()->with(['contact', 'whatsappAccount'])->findOrFail($data->conversationId);

        $providerResponse = $this->whatsApp->sendText($conversation->whatsappAccount, $conversation->contact->phone_number, $data->body);

        $message = Message::query()->create([
            'conversation_id' => $conversation->id,
            'direction' => 'outbound',
            'sender_type' => $data->senderType,
            'body' => $data->body,
            'message_type' => 'text',
            'status' => 'sent',
            'ai_generated' => $data->aiGenerated,
            'metadata' => ['provider_response' => $providerResponse],
            'sent_at' => now(),
        ]);

        $conversation->update(['last_message_at' => now()]);
        event(new MessageSent($message));

        return $message;
    }
}
