<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureOrgHasAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) return $next($request);

        // using currentOrg binding if available, or resolving it manually
        // Assuming 'currentOrg' is bound in a previous middleware or service provider
        // If not, we can try to resolve it from the user's current context or session
        $org = app()->has('currentOrg') ? app('currentOrg') : null;
        
        // If not bound, try to get it from user context if possible (optional fallback)
        if (!$org && $user->current_organization_id) {
             $org = \App\Models\Organization::find($user->current_organization_id);
        }

        if (!$org) return $next($request);

        // Allow billing routes even when locked
        if ($request->is('app/billing*')) {
            return $next($request);
        }

        // Allow auth/profile routes to prevent lockouts
        if ($request->is('app/profile*') || $request->is('app/logout')) {
            return $next($request);
        }

        // Lock if org has no access
        if (method_exists($org, 'hasAppAccess') && !$org->hasAppAccess()) {
            return redirect('/app/billing')
                ->with('error', 'Your subscription needs attention. Please renew to continue.');
        }

        return $next($request);
    }
}
