<?php

namespace App\Services\AI;

use App\Models\Conversation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AiReplyService
{
    public function suggestReply(Conversation $conversation, string $message): string
    {
        return Cache::remember('ai-reply:'.sha1($conversation->id.$message.$conversation->workspace_id), now()->addMinutes(10), function () use ($conversation, $message) {
            $apiKey = config('services.openai.key');
            $systemPrompt = DB::table('ai_prompts')
                ->where('workspace_id', $conversation->workspace_id)
                ->where('type', 'reply')
                ->where('is_active', true)
                ->latest()
                ->value('system_prompt')
                ?: 'You are ChatFlow AI, a concise WhatsApp support and sales assistant.';

            if (! $apiKey) {
                $knowledge = DB::table('ai_training_sources')
                    ->where('workspace_id', $conversation->workspace_id)
                    ->where('status', 'indexed')
                    ->latest('trained_at')
                    ->value('content');

                if ($knowledge) {
                    return 'Thanks for your message. Based on our business info: '.str($knowledge)->squish()->limit(180);
                }

                return 'Thanks for your message. I can help with that. Please share a few more details.';
            }

            $response = Http::withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $message],
                ],
            ]);

            return data_get($response->json(), 'choices.0.message.content', 'I can help with that. Please provide more information.');
        });
    }
}
