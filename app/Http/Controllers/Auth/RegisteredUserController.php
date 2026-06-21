<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

        $code = (string) random_int(100000, 999999);
        $request->session()->put('pending_registration', [
            'name' => $data['name'],
            'email' => $data['email'],
            'company' => $data['company'],
            'password' => Hash::make($data['password']),
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10)->toIso8601String(),
        ]);

        $this->sendVerificationCode($data['email'], $data['name'], $code);

        $message = 'Verification code sent to '.$data['email'].'. Enter the code to create your account.';
        if (config('app.env') === 'local') {
            $message .= ' (Local Dev Code: '.$code.')';
        }

        return back()->with('success', $message);
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $pending = $request->session()->get('pending_registration');

        if (! $pending) {
            throw ValidationException::withMessages([
                'code' => 'Verification session expired. Please submit the registration form again.',
            ]);
        }

        if (now()->greaterThan(\Illuminate\Support\Carbon::parse($pending['expires_at']))) {
            $request->session()->forget('pending_registration');

            throw ValidationException::withMessages([
                'code' => 'Verification code expired. Please submit the registration form again.',
            ]);
        }

        if (User::query()->where('email', $pending['email'])->exists()) {
            $request->session()->forget('pending_registration');

            throw ValidationException::withMessages([
                'code' => 'An account with this email already exists. Please log in instead.',
            ]);
        }

        if (! Hash::check($data['code'], $pending['code_hash'])) {
            throw ValidationException::withMessages([
                'code' => 'The verification code is incorrect.',
            ]);
        }

        $user = DB::transaction(function () use ($pending) {
            $user = User::query()->create([
                'name' => $pending['name'],
                'email' => $pending['email'],
                'email_verified_at' => now(),
                'password' => $pending['password'],
            ]);

            $workspaceId = DB::table('workspaces')->insertGetId([
                'name' => $pending['company'],
                'slug' => str($pending['company'])->slug().'-'.str()->random(6),
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

        $request->session()->forget('pending_registration');
        $this->sendAccountCreatedEmail($user->email, $user->name);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/app/billing')->with('success', 'Account successfully created. A confirmation email has been sent.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('pending_registration');

        if (! $pending) {
            throw ValidationException::withMessages([
                'code' => 'Verification session expired. Please submit the registration form again.',
            ]);
        }

        if (User::query()->where('email', $pending['email'])->exists()) {
            $request->session()->forget('pending_registration');

            throw ValidationException::withMessages([
                'code' => 'An account with this email already exists. Please log in instead.',
            ]);
        }

        $code = (string) random_int(100000, 999999);
        $pending['code_hash'] = Hash::make($code);
        $pending['expires_at'] = now()->addMinutes(10)->toIso8601String();

        $request->session()->put('pending_registration', $pending);
        $this->sendVerificationCode($pending['email'], $pending['name'], $code);

        $message = 'A new verification code has been sent to '.$pending['email'].'.';
        if (config('app.env') === 'local') {
            $message .= ' (Local Dev Code: '.$code.')';
        }

        return back()->with('success', $message);
    }

    private function sendVerificationCode(string $email, string $name, string $code): void
    {
        try {
            Mail::raw(
                "Hi {$name},\n\nYour ChatFlow AI verification code is: {$code}\n\nThis code will expire in 10 minutes.\n\nIf you did not request this, please ignore this email.",
                fn ($message) => $message
                    ->to($email)
                    ->subject('Your ChatFlow AI verification code')
            );
        } catch (\Throwable $e) {
            report($e);
            if (config('app.env') !== 'local') {
                throw $e;
            }
        }
    }

    private function sendAccountCreatedEmail(string $email, string $name): void
    {
        Mail::raw(
            "Hi {$name},\n\nYour ChatFlow AI account has been successfully created.\n\nYou can now log in and continue setting up your workspace.\n\nThank you,\nChatFlow AI",
            fn ($message) => $message
                ->to($email)
                ->subject('Your ChatFlow AI account is ready')
        );
    }
}
