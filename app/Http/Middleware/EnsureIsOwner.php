<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Simple MVP Check: User ID 1 is the Super Admin
        if ($request->user() && $request->user()->id === 1) {
            return $next($request);
        }

        abort(403, 'Unauthorized access to Owner Backend.');
    }
}
