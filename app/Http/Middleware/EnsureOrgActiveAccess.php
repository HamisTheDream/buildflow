<?php

namespace App\Http\Middleware;

use App\Support\CurrentOrg;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrgActiveAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) return $next($request);

        $org = CurrentOrg::forUser($user);
        if (!$org) return $next($request);

        // Always allow these paths even if subscription expired
        $path = '/' . ltrim($request->path(), '/');

        $allowedPrefixes = [
            '/app/billing',
            '/app/billing/upgrade',
            '/app/billing/paystack/callback',
            '/app/profile',
            '/logout',
        ];

        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $next($request);
            }
        }

        if (!$org->isActiveAccess()) {
            return redirect('/app/billing')
                ->with('error', 'Your subscription has expired. Renew to continue using BuildFlow.');
        }

        return $next($request);
    }
}
