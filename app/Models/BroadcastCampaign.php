<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BroadcastCampaign extends Model
{
    protected $fillable = ['workspace_id', 'name', 'status', 'audience_count', 'sent_count', 'delivered_count', 'replied_count', 'scheduled_at'];

    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime'];
    }
}
