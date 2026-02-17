<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only apply CSP to web responses, not API or file downloads
        if (
            !$response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse
            && !$request->expectsJson()
        ) {

            $csp = $this->buildCspHeader();
            $response->headers->set('Content-Security-Policy', $csp);

            // Additional security headers
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

            // Permissions Policy (formerly Feature-Policy)
            $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        }

        return $response;
    }

    /**
     * Build the Content-Security-Policy header value.
     */
    protected function buildCspHeader(): string
    {
        $isDev = !app()->isProduction();
        $viteDevSources = $isDev ? ' http://localhost:5173 http://127.0.0.1:5173' : '';
        $viteWsSources = $isDev ? ' http://localhost:5173 ws://localhost:5173 http://127.0.0.1:5173 ws://127.0.0.1:5173' : '';

        // Allow unsafe-eval in production for now to prevent WSOD with some Vue/Vite configs
        $unsafeEval = " 'unsafe-eval'";

        $directives = [
            // Default: only allow same origin
            "default-src 'self'",

            // Scripts: self, inline (needed for Vite/Inertia), eval
            "script-src 'self' 'unsafe-inline'{$unsafeEval} https:{$viteDevSources}",

            // Styles: self and inline (needed for Tailwind and dynamic styles)
            "style-src 'self' 'unsafe-inline' https:{$viteDevSources}",

            // Images: self, data URIs (for inline images), and blob (for file previews)
            "img-src 'self' data: blob: https:",

            // Fonts: self and Google Fonts
            "font-src 'self' https://fonts.gstatic.com data:{$viteDevSources}",

            // Connect: self for API calls, Vite HMR websocket in dev
            "connect-src 'self' wss: ws:{$viteWsSources} https://stats.pusher.com wss://ws-mt1.pusher.com",

            // Media: self for uploaded media
            "media-src 'self' blob:",

            // Objects: none (no Flash/plugins)
            "object-src 'none'",

            // Base URI: self only
            "base-uri 'self'",

            // Form actions: self only
            "form-action 'self'",

            // Frame ancestors: self only (clickjacking protection)
            "frame-ancestors 'self'",
        ];

        return implode('; ', $directives);
    }
}
