<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppAccount;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class WhatsAppAccountController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        return $this->success(WhatsAppAccount::query()->where('workspace_id', $request->query('workspace_id', 1))->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'workspace_id' => ['required', 'exists:workspaces,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:40'],
            'provider' => ['nullable', 'string', 'max:60'],
            'settings' => ['nullable', 'array'],
        ]);

        return $this->success(WhatsAppAccount::query()->create($data), 'WhatsApp account connected successfully', status: 201);
    }

    public function show(WhatsAppAccount $whatsappAccount)
    {
        return $this->success($whatsappAccount->load('conversations.contact'));
    }

    public function update(Request $request, WhatsAppAccount $whatsappAccount)
    {
        $whatsappAccount->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:60'],
            'quality_rating' => ['sometimes', 'string', 'max:60'],
            'settings' => ['nullable', 'array'],
        ]));

        return $this->success($whatsappAccount->fresh(), 'WhatsApp account updated successfully');
    }
}
