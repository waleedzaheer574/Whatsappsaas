<?php

namespace App\Repositories;

use App\Models\Contact;
use App\Models\Conversation;
use App\Models\WhatsAppAccount;

class ConversationRepository
{
    public function listForWorkspace(int $workspaceId)
    {
        return Conversation::query()
            ->with(['contact', 'whatsappAccount', 'messages' => fn ($query) => $query->latest()->limit(1)])
            ->where('workspace_id', $workspaceId)
            ->latest('last_message_at')
            ->paginate(20);
    }

    public function findOrCreateForContact(WhatsAppAccount $account, Contact $contact): Conversation
    {
        return Conversation::query()->firstOrCreate(
            ['workspace_id' => $account->workspace_id, 'whatsapp_account_id' => $account->id, 'contact_id' => $contact->id],
            ['status' => 'open', 'priority' => 'normal', 'last_message_at' => now()]
        );
    }
}
