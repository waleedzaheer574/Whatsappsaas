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
        return $this->success(DB::table('connected_integrations')->where('workspace_id', $request->query('workspace_id', 1))->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'workspace_id' => ['required', 'exists:workspaces,id'],
            'provider' => ['required', 'in:shopify,woocommerce,zapier,stripe,telegram,slack,google_sheets'],
            'status' => ['nullable', 'string'],
            'settings' => ['nullable', 'array'],
        ]);

        $id = DB::table('connected_integrations')->insertGetId([
            ...$data,
            'settings' => json_encode($data['settings'] ?? []),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->success(DB::table('connected_integrations')->find($id), 'Integration connected successfully', status: 201);
    }

    public function show(string $id)
    {
        return $this->success(DB::table('connected_integrations')->find($id));
    }

    public function update(Request $request, string $id)
    {
        DB::table('connected_integrations')->where('id', $id)->update([
            ...$request->only(['status']),
            'settings' => $request->has('settings') ? json_encode($request->input('settings')) : DB::raw('settings'),
            'updated_at' => now(),
        ]);

        return $this->success(DB::table('connected_integrations')->find($id), 'Integration updated successfully');
    }

    public function destroy(string $id)
    {
        DB::table('connected_integrations')->where('id', $id)->delete();

        return $this->success([], 'Integration disconnected successfully');
    }
}
