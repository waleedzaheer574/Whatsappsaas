<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceSubscriptionIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $workspaceId = DB::table('workspace_user')
            ->where('user_id', $request->user()->id)
            ->value('workspace_id');

        $subscription = $workspaceId
            ? DB::table('subscriptions')->where('workspace_id', $workspaceId)->first()
            : null;

        $isActive = $subscription?->status === 'active';
        $isTrialing = $subscription?->status === 'trialing'
            && (! $subscription->trial_ends_at || now()->lessThanOrEqualTo($subscription->trial_ends_at));

        if ($isActive || $isTrialing) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(402, 'An active subscription is required.');
        }

        return redirect()
            ->route('dashboard.billing')
            ->with('error', 'Please choose a subscription plan to unlock your CRM workspace.');
    }
}
