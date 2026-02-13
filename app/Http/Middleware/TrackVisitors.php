<?php

namespace App\Http\Middleware;

use App\Models\VisitorLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for console, API, or static assets if needed, though 'web' group handles most
        if ($request->is('storage/*', 'build/*', 'vendor/*', 'api/*')) {
            return $next($request);
        }

        // Only track GET requests primarily to avoid noise
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        try {
            VisitorLog::create([
                'ip_address' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'user_id' => $request->user()?->id,
                'referer' => $request->header('referer'),
                'visit_time' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail to not disrupt user experience
            // Log::error('Visitor tracking failed: ' . $e->getMessage());
        }

        return $next($request);
    }
}
