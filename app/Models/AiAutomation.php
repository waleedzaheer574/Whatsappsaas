<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiAutomation extends Model
{
    protected $fillable = ['workspace_id', 'name', 'trigger', 'status', 'runs_count', 'success_rate', 'flow'];

    protected function casts(): array
    {
        return ['flow' => 'array', 'success_rate' => 'decimal:2'];
    }
}
