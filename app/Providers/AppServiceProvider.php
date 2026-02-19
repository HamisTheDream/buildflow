<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectIssue;
use App\Models\ProjectCost;
use App\Models\ProjectMedia;
use App\Models\ProjectReport;

use App\Policies\ProjectPolicy;
use App\Policies\ProjectTaskPolicy;
use App\Policies\ProjectIssuePolicy;
use App\Policies\ProjectCostPolicy;
use App\Policies\ProjectMediaPolicy;
use App\Policies\ProjectReportPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->isProduction()) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Vite::prefetch(concurrency: 3);

        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(ProjectTask::class, ProjectTaskPolicy::class);
        Gate::policy(ProjectIssue::class, ProjectIssuePolicy::class);
        Gate::policy(ProjectCost::class, ProjectCostPolicy::class);
        Gate::policy(ProjectMedia::class, ProjectMediaPolicy::class);
        Gate::policy(ProjectReport::class, ProjectReportPolicy::class);
        Gate::policy(\App\Models\Attachment::class, \App\Policies\AttachmentPolicy::class);
        Gate::policy(\App\Models\OrganizationNote::class, \App\Policies\Owner\OrganizationNotePolicy::class);
        Gate::policy(\App\Models\OrganizationTask::class, \App\Policies\Owner\OrganizationTaskPolicy::class);

        // Register humanized validation messages
        \Illuminate\Support\Facades\Validator::replacer('required', fn($m, $a, $r, $p) => "Please enter {$a}.");

        // Register event listeners
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Registered::class,
            \App\Listeners\SendWelcomeEmail::class,
        );

        // Configure rate limiters
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        $limiter = app(\Illuminate\Cache\RateLimiter::class);

        // General API rate limiting: 60 requests per minute
        $limiter->for('api', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });

        // Stricter auth rate limiting: 5 attempts per minute
        $limiter->for('auth', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)
                ->by($request->ip());
        });

        // Webhook rate limiting: 100 per minute (for payment provider callbacks)
        $limiter->for('webhooks', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(100)
                ->by($request->ip());
        });

        // Heavy operations rate limiting: 10 per minute
        $limiter->for('heavy', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(10)
                ->by($request->user()?->id ?: $request->ip());
        });

        // File uploads rate limiting: 30 per minute
        $limiter->for('uploads', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(30)
                ->by($request->user()?->id ?: $request->ip());
        });
    }
}
