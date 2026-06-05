<?php

namespace App\Repositories;

use App\Models\Contact;

class ContactRepository
{
    public function upsertFromWhatsApp(int $workspaceId, string $phone, string $name): Contact
    {
        $phone = $this->normalizePhone($phone);
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

    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        if ($phone === '') {
            return $phone;
        }

        return str_starts_with($phone, '+') ? $phone : '+'.preg_replace('/\D+/', '', $phone);
    }
}
