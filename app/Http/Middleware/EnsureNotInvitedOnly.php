<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureNotInvitedOnly
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->is_invited_only) {
            return redirect()->route('app.dashboard')
                ->with('error', 'This account is restricted to an organization workspace.');
        }

        return $next($request);
    }
}
