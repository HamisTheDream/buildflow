<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Jenssegers\Agent\Agent;
use Stevebauman\Location\Facades\Location;
use App\Jobs\LogPageVisit;
use Illuminate\Support\Facades\Cookie;

class TrackPageVisits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Execute request first so it doesn't block
        $response = $next($request);

        // GDPR Privacy: Only track if cookie consent is given
        if ($request->cookie('buildflow_cookie_consent') !== 'accepted') {
            return $response;
        }

        try {
            $agent = new Agent();

            // Skip bots and automated scripts
            if ($agent->isRobot()) {
                return $response;
            }

            // Skip API routes or static assets
            if ($request->is('api/*') || $request->is('_debugbar/*')) {
                return $response;
            }

            $ip = $request->ip();
            $location = Location::get($ip);

            // Determine device type
            $deviceType = 'Desktop';
            if ($agent->isTablet()) $deviceType = 'Tablet';
            elseif ($agent->isMobile()) $deviceType = 'Mobile';

            $user = $request->user();

            $visitData = [
                'user_id' => $user?->id,
                'organization_id' => $user ? $user->primaryOrganization()?->id : null,
                'url' => substr($request->fullUrl(), 0, 2048),
                'path' => substr($request->path(), 0, 1024),
                'method' => $request->method(),
                'user_agent' => substr($request->userAgent(), 0, 1024),
                'browser' => $agent->browser(),
                'device_type' => $deviceType,
                'ip_address' => $ip,
                'country' => $location ? $location->countryName : null,
                'city' => $location ? $location->cityName : null,
                'session_id' => $request->session()->getId(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Dispatch to queue so we don't slow down the response
            LogPageVisit::dispatch($visitData);
        } catch (\Exception $e) {
            // Fail silently so tracking errors don't crash the app
            \Illuminate\Support\Facades\Log::warning('Tracking error: ' . $e->getMessage());
        }

        return $response;
    }
}
