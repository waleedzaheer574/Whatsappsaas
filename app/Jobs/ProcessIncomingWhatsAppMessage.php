<?php

namespace App\Jobs;

use App\Actions\ProcessIncomingWebhookAction;
use App\DTOs\InboundWhatsAppMessageData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessIncomingWhatsAppMessage implements ShouldQueue
{
    use Queueable;

    public function __construct(public InboundWhatsAppMessageData $data)
    {
    }

    public function handle(ProcessIncomingWebhookAction $action): void
    {
        $action->execute($this->data);
    }
}
