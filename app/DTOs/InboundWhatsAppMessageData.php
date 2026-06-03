<?php

namespace App\DTOs;

final readonly class InboundWhatsAppMessageData
{
    public function __construct(
        public int $whatsappAccountId,
        public string $from,
        public string $name,
        public string $body,
        public string $messageId,
        public string $type = 'text',
        public array $metadata = [],
    ) {
    }
}
