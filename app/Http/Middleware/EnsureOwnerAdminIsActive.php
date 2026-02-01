<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnerAdminIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $admin = Auth::guard('owner')->user();

        if ($admin && property_exists($admin, 'is_active') && !$admin->is_active) {
            Auth::guard('owner')->logout();
            return redirect('/owner/login')->with('error', 'Your admin account is deactivated.');
        }

        return $next($request);
    }
}
