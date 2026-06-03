<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'workspace_id' => ['required', 'integer', 'exists:workspaces,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email'],
            'status' => ['nullable', 'string', 'max:60'],
            'source' => ['nullable', 'string', 'max:60'],
            'deal_value' => ['nullable', 'numeric', 'min:0'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array'],
        ];
    }
}
