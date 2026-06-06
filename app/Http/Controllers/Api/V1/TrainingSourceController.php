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
        return $this->success(AiTrainingSource::query()->where('workspace_id', $request->attributes->get('workspace_id'))->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $source = AiTrainingSource::query()->create($request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:document,url,faq'],
            'content' => ['nullable', 'string', 'max:20000'],
            'source_url' => ['nullable', 'url', 'max:500'],
            'status' => ['nullable', 'string', 'max:60'],
        ]) + ['workspace_id' => $request->attributes->get('workspace_id')]);

        return $this->success($source, 'Training source created successfully', status: 201);
    }

    public function show(Request $request, AiTrainingSource $trainingSource)
    {
        abort_unless($trainingSource->workspace_id === (int) $request->attributes->get('workspace_id'), 404);

        return $this->success($trainingSource);
    }

    public function update(Request $request, AiTrainingSource $trainingSource)
    {
        abort_unless($trainingSource->workspace_id === (int) $request->attributes->get('workspace_id'), 404);

        $trainingSource->update($request->only(['title', 'type', 'content', 'source_url', 'status', 'chunks_count', 'trained_at', 'metadata']));

        return $this->success($trainingSource->fresh(), 'Training source updated successfully');
    }

    public function destroy(Request $request, AiTrainingSource $trainingSource)
    {
        abort_unless($trainingSource->workspace_id === (int) $request->attributes->get('workspace_id'), 404);

        $trainingSource->delete();

        return $this->success([], 'Training source deleted successfully');
    }
}
