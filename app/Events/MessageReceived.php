<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReceived implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Message $message)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('workspace.'.$this->message->conversation->workspace_id);
    }
}
