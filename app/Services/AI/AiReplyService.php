<?php

namespace App\Services\AI;

use App\Models\Conversation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AiReplyService
{
    public function suggestReply(Conversation $conversation, string $message): string
    {
        return Cache::remember('ai-reply:'.sha1($conversation->id.$message), now()->addMinutes(10), function () use ($message) {
            $apiKey = config('services.openai.key');

            if (! $apiKey) {
                return "Thanks for your message. I can help with that. Please share a few more details.";
            }

            $response = Http::withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'messages' => [
                    ['role' => 'system', 'content' => 'You are ChatFlow AI, a concise WhatsApp support and sales assistant.'],
                    ['role' => 'user', 'content' => $message],
                ],
            ]);

            return data_get($response->json(), 'choices.0.message.content', 'I can help with that. Please provide more information.');
        });
    }
}
