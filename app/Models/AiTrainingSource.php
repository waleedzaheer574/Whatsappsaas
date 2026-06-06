<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiTrainingSource extends Model
{
    protected $fillable = ['workspace_id', 'title', 'type', 'content', 'source_url', 'status', 'chunks_count', 'trained_at', 'metadata'];

    protected function casts(): array
    {
        return ['trained_at' => 'datetime', 'metadata' => 'array'];
    }
}
