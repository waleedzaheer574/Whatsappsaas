<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'company' => ['required', 'string', 'max:255'],
            'password' => ['required', Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $workspaceId = DB::table('workspaces')->insertGetId([
                'name' => $data['company'],
                'slug' => str($data['company'])->slug().'-'.str()->random(6),
                'plan' => 'starter',
                'timezone' => config('app.timezone'),
                'settings' => json_encode(['onboarding' => true]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('workspace_user')->insert([
                'workspace_id' => $workspaceId,
                'user_id' => $user->id,
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('activity_logs')->insert([
                'workspace_id' => $workspaceId,
                'type' => 'workspace.created',
                'description' => 'Workspace created. Subscription required to unlock CRM tools.',
                'properties' => json_encode(['user_id' => $user->id]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/app/billing')->with('error', 'Choose a subscription plan to unlock your dashboard.');
    }
}
