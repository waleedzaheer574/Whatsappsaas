<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'direction', 'sender_type', 'body', 'message_type', 'status', 'ai_generated', 'metadata', 'sent_at'];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'ai_generated' => 'boolean', 'sent_at' => 'datetime'];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
