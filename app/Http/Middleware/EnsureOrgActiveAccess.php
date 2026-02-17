<?php

namespace App\Http\Middleware;

use App\Support\CurrentOrg;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user's organization has active subscription access.
 * Similar to EnsureOrgHasAccess but specifically for premium modules (Finance, HR).
 */
class EnsureOrgActiveAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) return $next($request);

        $org = CurrentOrg::forUser($user);
        if (!$org) {
            return redirect('/app/dashboard')
                ->with('error', 'Please set up your organization first.');
        }

        if (!$org->hasAppAccess()) {
            return redirect('/app/billing')
                ->with('error', 'Your subscription has expired. Renew to continue using BuildFlow.');
        }

        return $next($request);
    }
}
