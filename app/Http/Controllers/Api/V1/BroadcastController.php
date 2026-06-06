<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BroadcastCampaign;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        return $this->success(BroadcastCampaign::query()->where('workspace_id', $request->attributes->get('workspace_id'))->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $campaign = BroadcastCampaign::query()->create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
            'status' => ['nullable', 'string', 'max:60'],
            'audience_filter' => ['nullable', 'array'],
            'audience_count' => ['nullable', 'integer', 'min:0'],
            'scheduled_at' => ['nullable', 'date'],
        ]) + ['workspace_id' => $request->attributes->get('workspace_id')]);

        return $this->success($campaign, 'Broadcast campaign created successfully', status: 201);
    }

    public function show(Request $request, BroadcastCampaign $broadcast)
    {
        abort_unless($broadcast->workspace_id === (int) $request->attributes->get('workspace_id'), 404);

        return $this->success($broadcast);
    }

    public function update(Request $request, BroadcastCampaign $broadcast)
    {
        abort_unless($broadcast->workspace_id === (int) $request->attributes->get('workspace_id'), 404);

        $broadcast->update($request->only(['name', 'body', 'status', 'audience_filter', 'audience_count', 'scheduled_at']));

        return $this->success($broadcast->fresh(), 'Broadcast campaign updated successfully');
    }

    public function destroy(Request $request, BroadcastCampaign $broadcast)
    {
        abort_unless($broadcast->workspace_id === (int) $request->attributes->get('workspace_id'), 404);

        $broadcast->delete();

        return $this->success([], 'Broadcast campaign deleted successfully');
    }
}
