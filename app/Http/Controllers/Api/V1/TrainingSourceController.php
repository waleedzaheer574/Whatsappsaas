<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AiTrainingSource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class TrainingSourceController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        return $this->success(AiTrainingSource::query()->where('workspace_id', $request->query('workspace_id', 1))->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $source = AiTrainingSource::query()->create($request->validate([
            'workspace_id' => ['required', 'exists:workspaces,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:60'],
            'status' => ['nullable', 'string', 'max:60'],
        ]));

        return $this->success($source, 'Training source created successfully', status: 201);
    }

    public function show(AiTrainingSource $trainingSource)
    {
        return $this->success($trainingSource);
    }

    public function update(Request $request, AiTrainingSource $trainingSource)
    {
        $trainingSource->update($request->only(['title', 'type', 'status', 'chunks_count', 'trained_at']));

        return $this->success($trainingSource->fresh(), 'Training source updated successfully');
    }

    public function destroy(AiTrainingSource $trainingSource)
    {
        $trainingSource->delete();

        return $this->success([], 'Training source deleted successfully');
    }
}
