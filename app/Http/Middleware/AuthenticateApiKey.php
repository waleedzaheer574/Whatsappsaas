<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?: $request->header('X-API-Key');

        if (! $token) {
            return response()->json(['success' => false, 'message' => 'API key is required.'], 401);
        }

        $key = DB::table('api_keys')->where('token_hash', hash('sha256', $token))->first();

        if (! $key || ($key->expires_at && now()->greaterThan($key->expires_at))) {
            return response()->json(['success' => false, 'message' => 'Invalid API key.'], 401);
        }

        DB::table('api_keys')->where('id', $key->id)->update(['last_used_at' => now()]);
        $request->attributes->set('workspace_id', (int) $key->workspace_id);
        $request->attributes->set('api_key_id', (int) $key->id);

        return $next($request);
    }
}
