<?php

namespace App\DTOs;

final readonly class OutboundMessageData
{
    public function __construct(
        public int $conversationId,
        public string $body,
        public string $senderType = 'agent',
        public bool $aiGenerated = false,
    ) {
    }
}
