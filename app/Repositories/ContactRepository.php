<?php

namespace App\Repositories;

use App\Models\Contact;
use App\Support\PhoneNumber;

class ContactRepository
{
    public function upsertFromWhatsApp(int $workspaceId, string $phone, string $name): Contact
    {
        $phone = $this->normalizePhone($phone);
        $digits = preg_replace('/\D+/', '', $phone);
        $existing = Contact::query()
            ->where('workspace_id', $workspaceId)
            ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone_number, '+', ''), ' ', ''), '-', ''), '(', ''), ')', '') LIKE ?", ['%'.$digits])
            ->first();

        if ($existing) {
            $existing->update([
                'name' => $existing->name ?: ($name ?: $phone),
                'phone_number' => $phone,
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
        return PhoneNumber::normalize($phone);
    }
}
