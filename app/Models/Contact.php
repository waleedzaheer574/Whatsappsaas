<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    protected $fillable = ['workspace_id', 'name', 'phone_number', 'email', 'avatar', 'status', 'source', 'deal_value', 'owner_name', 'tags'];

    protected function casts(): array
    {
        return ['tags' => 'array', 'deal_value' => 'decimal:2'];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
