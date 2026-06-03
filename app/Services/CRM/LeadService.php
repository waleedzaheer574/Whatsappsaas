<?php

namespace App\Services\CRM;

use App\Models\Contact;
use Illuminate\Support\Facades\DB;

class LeadService
{
    public function capture(Contact $contact, string $title = 'WhatsApp Lead'): void
    {
        DB::table('leads')->updateOrInsert(
            ['workspace_id' => $contact->workspace_id, 'contact_id' => $contact->id, 'title' => $title],
            ['stage' => 'new', 'value' => $contact->deal_value ?? 0, 'score' => 70, 'updated_at' => now(), 'created_at' => now()]
        );
    }
}
