<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        if (! Auth::attempt($request->only('email', 'password'), (bool) ($credentials['remember'] ?? false))) {
            throw ValidationException::withMessages([
                'email' => 'The provided email or password is incorrect.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended('/app/dashboard')->with('success', 'Welcome back. You are logged in successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/auth/login')->with('success', 'You have been logged out successfully.');
    }
}
