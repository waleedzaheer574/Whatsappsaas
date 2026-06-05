<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IntegrationController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        return $this->success(DB::table('connected_integrations')->where('workspace_id', $request->attributes->get('workspace_id'))->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'provider' => ['required', 'in:shopify,woocommerce,zapier,stripe,telegram,slack,google_sheets'],
            'status' => ['nullable', 'string'],
            'settings' => ['nullable', 'array'],
        ]);

        $id = DB::table('connected_integrations')->insertGetId([
            ...$data,
            'workspace_id' => $request->attributes->get('workspace_id'),
            'settings' => json_encode($data['settings'] ?? []),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->success(DB::table('connected_integrations')->find($id), 'Integration connected successfully', status: 201);
    }

    public function show(Request $request, string $id)
    {
        $record = DB::table('connected_integrations')
            ->where('workspace_id', $request->attributes->get('workspace_id'))
            ->where('id', $id)
            ->first();

        abort_unless($record, 404);

        return $this->success($record);
    }

    public function update(Request $request, string $id)
    {
        $updated = DB::table('connected_integrations')
            ->where('workspace_id', $request->attributes->get('workspace_id'))
            ->where('id', $id)
            ->update([
            ...$request->only(['status']),
            'settings' => $request->has('settings') ? json_encode($request->input('settings')) : DB::raw('settings'),
            'updated_at' => now(),
        ]);

        abort_unless($updated, 404);

        return $this->success(DB::table('connected_integrations')->where('workspace_id', $request->attributes->get('workspace_id'))->where('id', $id)->first(), 'Integration updated successfully');
    }

    public function destroy(Request $request, string $id)
    {
        $deleted = DB::table('connected_integrations')
            ->where('workspace_id', $request->attributes->get('workspace_id'))
            ->where('id', $id)
            ->delete();

        abort_unless($deleted, 404);

        return $this->success([], 'Integration disconnected successfully');
    }
}
