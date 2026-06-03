<?php

namespace App\Actions;

use App\DTOs\InboundWhatsAppMessageData;
use App\Events\MessageReceived;
use App\Models\Message;
use App\Models\WhatsAppAccount;
use App\Repositories\ContactRepository;
use App\Repositories\ConversationRepository;
use App\Services\Automation\WorkflowEngine;
use App\Services\CRM\LeadService;

class ProcessIncomingWebhookAction
{
    public function __construct(
        private readonly ContactRepository $contacts,
        private readonly ConversationRepository $conversations,
        private readonly WorkflowEngine $workflows,
        private readonly LeadService $leads,
    ) {
    }

    public function execute(InboundWhatsAppMessageData $data): Message
    {
        $account = WhatsAppAccount::query()->findOrFail($data->whatsappAccountId);
        $contact = $this->contacts->upsertFromWhatsApp($account->workspace_id, $data->from, $data->name);
        $conversation = $this->conversations->findOrCreateForContact($account, $contact);

        $message = Message::query()->create([
            'conversation_id' => $conversation->id,
            'direction' => 'inbound',
            'sender_type' => 'contact',
            'body' => $data->body,
            'message_type' => $data->type,
            'status' => 'received',
            'metadata' => ['provider_message_id' => $data->messageId] + $data->metadata,
            'sent_at' => now(),
        ]);

        $conversation->update(['last_message_at' => now(), 'unread_count' => $conversation->unread_count + 1]);
        $this->leads->capture($contact);
        $this->workflows->runForMessage($conversation, $data->body);

        event(new MessageReceived($message));

        return $message;
    }
}
