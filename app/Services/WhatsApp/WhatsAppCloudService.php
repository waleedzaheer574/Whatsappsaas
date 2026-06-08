<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsAppAccount;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Http;

class WhatsAppCloudService
{
    public function sendText(WhatsAppAccount $account, string $to, string $body): array
    {
        $token = data_get($account->settings, 'access_token', config('services.whatsapp.token'));
        $phoneNumberId = data_get($account->settings, 'phone_number_id', config('services.whatsapp.phone_number_id'));

        if (! $token || ! $phoneNumberId) {
            return ['queued' => true, 'message' => 'WhatsApp credentials are not configured.'];
        }

        return Http::withToken($token)
            ->post("https://graph.facebook.com/v20.0/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => PhoneNumber::e164Digits($to),
                'type' => 'text',
                'text' => ['body' => $body],
            ])
            ->json();
    }
}
