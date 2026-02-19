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
        // Skip for console, API, or static assets
        if ($request->is('storage/*', 'build/*', 'vendor/*', 'api/*')) {
            return $next($request);
        }

        // Only track GET requests to avoid noise
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        // Skip Inertia partial/prefetch requests — these are AJAX navigations
        // that already tracked the initial page load
        if ($request->header('X-Inertia') || $request->header('X-Inertia-Partial-Data')) {
            return $next($request);
        }

        // Rate-limit: only log once per URL per session every 5 minutes
        $cacheKey = 'visitor_tracked:' . md5($request->ip() . '|' . $request->fullUrl());
        if (cache()->has($cacheKey)) {
            return $next($request);
        }

        try {
            $log = VisitorLog::create([
                'ip_address' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'user_id' => $request->user()?->id,
                'referer' => $request->header('referer'),
                'visit_time' => now(),
                'session_id' => session()->getId(),
            ]);

            // Dispatch job to enrich data (async)
            \App\Jobs\EnrichVisitorData::dispatch($log->id)->afterResponse();

            // Mark as tracked for 5 minutes
            cache()->put($cacheKey, true, 300);
        } catch (\Exception $e) {
            // Silently fail to not disrupt user experience
        }

        return $next($request);
    }
}
