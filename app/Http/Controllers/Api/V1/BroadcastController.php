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
        return $this->success(BroadcastCampaign::query()->where('workspace_id', $request->query('workspace_id', 1))->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $campaign = BroadcastCampaign::query()->create($request->validate([
            'workspace_id' => ['required', 'exists:workspaces,id'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:60'],
            'audience_count' => ['nullable', 'integer', 'min:0'],
            'scheduled_at' => ['nullable', 'date'],
        ]));

        return $this->success($campaign, 'Broadcast campaign created successfully', status: 201);
    }

    public function show(BroadcastCampaign $broadcast)
    {
        return $this->success($broadcast);
    }

    public function update(Request $request, BroadcastCampaign $broadcast)
    {
        $broadcast->update($request->only(['name', 'status', 'audience_count', 'scheduled_at']));

        return $this->success($broadcast->fresh(), 'Broadcast campaign updated successfully');
    }

    public function destroy(BroadcastCampaign $broadcast)
    {
        $broadcast->delete();

        return $this->success([], 'Broadcast campaign deleted successfully');
    }
}
