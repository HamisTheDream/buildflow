<?php

namespace App\Providers;

use App\Models\Project;
use App\Policies\ProjectPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Project::class => ProjectPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Optional explicit gate for team management
        Gate::define('project.manageTeam', [ProjectPolicy::class, 'manageTeam']);
    }
}
