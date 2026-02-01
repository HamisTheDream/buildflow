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
        Vite::prefetch(concurrency: 3);

        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(ProjectTask::class, ProjectTaskPolicy::class);
        Gate::policy(ProjectIssue::class, ProjectIssuePolicy::class);
        Gate::policy(ProjectCost::class, ProjectCostPolicy::class);
        Gate::policy(ProjectMedia::class, ProjectMediaPolicy::class);
        Gate::policy(ProjectReport::class, ProjectReportPolicy::class);
        Gate::policy(\App\Models\Attachment::class, \App\Policies\AttachmentPolicy::class);
    }
}
