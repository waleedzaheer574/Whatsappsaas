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
            $trainingContext = DB::table('ai_training_sources')
                ->where('workspace_id', $conversation->workspace_id)
                ->where('status', 'indexed')
                ->latest('trained_at')
                ->limit(8)
                ->get()
                ->map(fn ($source) => "Source: {$source->title}\n".str((string) ($source->content ?: $source->source_url))->squish()->limit(1400))
                ->implode("\n\n");

            if (! $apiKey) {
                return $this->fallbackReply($message, $trainingContext);
            }

            $response = Http::withToken($apiKey)
                ->timeout(20)
                ->connectTimeout(10)
                ->withOptions(['proxy' => ''])
                ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'temperature' => 0.3,
                'max_tokens' => 220,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt."\n\nBusiness knowledge:\n".$trainingContext."\n\nImportant: Never invent prices, availability, or policies. If details are missing, ask for the missing details only."],
                    ['role' => 'user', 'content' => $message],
                ],
            ]);

            return data_get($response->json(), 'choices.0.message.content', 'I can help with that. Please provide more information.');
        });
    }

    private function fallbackReply(string $message, string $trainingContext): string
    {
        $lower = strtolower($message);

        if (str_contains($lower, 'price') || str_contains($lower, 'fare') || str_contains($lower, 'cost') || str_contains($lower, 'quote')) {
            return 'Thanks for contacting A1 Rides. To confirm fare and availability, please share pickup location, drop-off location, date, time, passenger count, and luggage details.';
        }

        if (str_contains($lower, 'airport') || str_contains($lower, 'flight')) {
            return 'Sure, we can help with an airport ride enquiry. Please share pickup/drop-off, travel date and time, flight number, passengers, and luggage count so our team can confirm.';
        }

        if (str_contains($lower, 'book') || str_contains($lower, 'ride') || str_contains($lower, 'cab') || str_contains($lower, 'taxi')) {
            return 'Thanks for your booking enquiry with A1 Rides. Please send pickup location, drop-off location, date, time, passengers, luggage, and your name so we can confirm the ride.';
        }

        if ($trainingContext) {
            return 'Thanks for messaging A1 Rides. I can help with ride booking enquiries. Please share pickup, drop-off, date, time, passengers, and luggage details.';
        }

        return 'Thanks for your message. Please share pickup location, drop-off location, date, time, passengers, and luggage details so we can help.';
    }
}
