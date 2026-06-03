<?php

namespace App\Jobs;

use App\Actions\SendWhatsAppMessageAction;
use App\DTOs\OutboundMessageData;
use App\Models\Conversation;
use App\Services\AI\AiReplyService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateAiReply implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $conversationId, public string $message)
    {
    }

    public function handle(AiReplyService $ai, SendWhatsAppMessageAction $send): void
    {
        $conversation = Conversation::query()->findOrFail($this->conversationId);
        $reply = $ai->suggestReply($conversation, $this->message);

        $send->execute(new OutboundMessageData($conversation->id, $reply, 'ai', true));
    }
}
