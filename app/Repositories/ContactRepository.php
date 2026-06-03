<?php

namespace App\Repositories;

use App\Models\Contact;

class ContactRepository
{
    public function upsertFromWhatsApp(int $workspaceId, string $phone, string $name): Contact
    {
        return Contact::query()->updateOrCreate(
            ['workspace_id' => $workspaceId, 'phone_number' => $phone],
            ['name' => $name ?: $phone, 'source' => 'whatsapp', 'status' => 'new_lead']
        );
    }
}
