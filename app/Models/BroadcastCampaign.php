<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BroadcastCampaign extends Model
{
    protected $fillable = ['workspace_id', 'name', 'body', 'audience_filter', 'status', 'audience_count', 'sent_count', 'delivered_count', 'replied_count', 'scheduled_at', 'started_at', 'completed_at'];

    protected function casts(): array
    {
        return ['audience_filter' => 'array', 'scheduled_at' => 'datetime', 'started_at' => 'datetime', 'completed_at' => 'datetime'];
    }
}
