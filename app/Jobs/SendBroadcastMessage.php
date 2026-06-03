<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class SendBroadcastMessage implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $broadcastId)
    {
    }

    public function handle(): void
    {
        DB::table('broadcasts')->where('id', $this->broadcastId)->update([
            'status' => 'sent',
            'sent_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
