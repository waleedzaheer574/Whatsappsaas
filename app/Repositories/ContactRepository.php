<?php

namespace App\Repositories;

use App\Models\Contact;

class ContactRepository
{
    public function upsertFromWhatsApp(int $workspaceId, string $phone, string $name): Contact
    {
        $existing = Contact::query()
            ->where('workspace_id', $workspaceId)
            ->where('phone_number', $phone)
            ->first();

        if ($existing) {
            $existing->update([
                'name' => $name ?: $phone,
                'source' => 'whatsapp',
            ]);

            return $existing->fresh();
        }

        return Contact::query()->updateOrCreate(
            ['workspace_id' => $workspaceId, 'phone_number' => $phone],
            ['name' => $name ?: $phone, 'source' => 'whatsapp', 'status' => 'new_lead']
        );
    }
}
