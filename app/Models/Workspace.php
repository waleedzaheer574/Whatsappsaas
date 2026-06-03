<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    protected $fillable = ['name', 'slug', 'plan', 'timezone', 'settings'];

    protected function casts(): array
    {
        return ['settings' => 'array'];
    }

    public function whatsappAccounts(): HasMany
    {
        return $this->hasMany(WhatsAppAccount::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }
}
