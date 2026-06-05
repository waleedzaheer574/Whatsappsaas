<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AiAutomation;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AutomationController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        return $this->success(AiAutomation::query()->where('workspace_id', $request->attributes->get('workspace_id'))->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $automation = AiAutomation::query()->create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'trigger' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,paused,draft'],
            'flow' => ['nullable', 'array'],
        ]) + ['workspace_id' => $request->attributes->get('workspace_id')]);

        return $this->success($automation, 'Automation created successfully', status: 201);
    }

    public function show(Request $request, AiAutomation $automation)
    {
        abort_unless($automation->workspace_id === (int) $request->attributes->get('workspace_id'), 404);

        return $this->success($automation);
    }

    public function update(Request $request, AiAutomation $automation)
    {
        abort_unless($automation->workspace_id === (int) $request->attributes->get('workspace_id'), 404);

        $automation->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'trigger' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'in:active,paused,draft'],
            'flow' => ['nullable', 'array'],
        ]));

        return $this->success($automation->fresh(), 'Automation updated successfully');
    }

    public function destroy(Request $request, AiAutomation $automation)
    {
        abort_unless($automation->workspace_id === (int) $request->attributes->get('workspace_id'), 404);

        $automation->delete();

        return $this->success([], 'Automation deleted successfully');
    }
}
