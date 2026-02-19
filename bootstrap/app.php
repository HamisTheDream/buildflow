<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\TrackVisitors::class,
            \App\Http\Middleware\BlockBots::class,
            // \App\Http\Middleware\ContentSecurityPolicy::class, // Temporarily disabled for debugging
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/paystack',
        ]);

        // Configure redirects for multi-guard setup
        $middleware->redirectGuestsTo(function ($request) {
            // Check if the request is for an owner route
            if (str_starts_with($request->path(), 'owner')) {
                return route('owner.login');
            }
            return route('login');
        });

        $middleware->redirectUsersTo(function ($request) {
            // Redirect authenticated owner admins away from guest-only routes
            if (str_starts_with($request->path(), 'owner') && auth('owner')->check()) {
                return '/owner/dashboard';
            }
            return '/dashboard';
        });

        $middleware->alias([
            'notInvitedOnly' => \App\Http\Middleware\EnsureNotInvitedOnly::class,
            'org.access' => \App\Http\Middleware\EnsureOrgHasAccess::class,
            'owner.active' => \App\Http\Middleware\EnsureOwnerAdminIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
