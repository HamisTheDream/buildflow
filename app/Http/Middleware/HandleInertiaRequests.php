<?php

namespace App\Http\Middleware;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware as InertiaMiddleware;

class HandleInertiaRequests extends InertiaMiddleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $shared = parent::share($request);

        $user = $request->user();

        // Only resolve org for actual Users, not Admins
        $isUser = $user instanceof \App\Models\User;
        $org = $isUser ? \App\Support\CurrentOrg::forUser($user) : null;

        // Compute org role once to avoid duplicate queries
        $orgRole = ($isUser && $user && $org) ? $user->orgRole($org->id) : null;

        // Only resolve owner admin on owner routes — skip for user routes
        $isOwnerRoute = str_starts_with($request->path(), 'owner');
        $ownerAdmin = $isOwnerRoute
            ? \Illuminate\Support\Facades\Auth::guard('owner')->user()
            : null;

        // Cache site settings to avoid DB query on every request
        $siteSettings = cache()->remember('site_settings', 3600, function () {
            return \App\Models\Setting::whereIn('key', ['site_title', 'site_description', 'site_keywords', 'site_icon'])->pluck('value', 'key');
        });

        return array_merge($shared, [
            'csrf_token' => csrf_token(),
            'auth' => [
                'user' => ($isUser && $user) ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar_path' => $user->avatar_path,
                    'avatar_url' => $user->avatar_path ? asset('storage/' . $user->avatar_path) : null,
                    'is_invited_only' => $user->is_invited_only,
                    'current_organization_id' => $user->current_organization_id,
                ] : null,
                'organization' => $org ? [
                    'id' => $org->id,
                    'name' => $org->name,
                    'type' => $org->type,
                    'currency' => $org->currency,
                    'plan' => $org->effectivePlan()?->key,
                    'subscription' => [
                        'status' => $org->subscription_status,
                        'paid_until' => $org->paid_until?->toDateTimeString(),
                        'grace_ends_at' => $org->grace_ends_at?->toDateTimeString(),
                        'dunning_stage' => (int)$org->dunning_stage,
                    ],
                ] : null,
                'orgRole' => $orgRole,
                'modules' => $orgRole ? $this->getAccessibleModulesFromRole($orgRole) : [],
            ],
            'ownerAuth' => [
                'admin' => $ownerAdmin ? [
                    'id' => $ownerAdmin->id,
                    'name' => $ownerAdmin->name,
                    'email' => $ownerAdmin->email,
                    'avatar_path' => $ownerAdmin->avatar_path,
                    'avatar_url' => $ownerAdmin->avatar_path
                        ? asset('storage/' . $ownerAdmin->avatar_path)
                        : null,
                    'is_super' => (bool) $ownerAdmin->is_super,
                ] : null,
            ],
            // Lazy-load entitlements — only computed when the component needs them
            'entitlements' => Inertia::lazy(function () use ($isUser, $user, $org) {
                return ($isUser && $user)
                    ? app(\App\Services\EntitlementsService::class)->forOrg($org)
                    : null;
            }),
            // Active announcements for this user's org/plan
            'onboarding_stats' => function () use ($isUser, $org) {
                if (!$isUser || !$org) return [];
                return cache()->remember('onboarding_stats:' . $org->id, 60, function () use ($org) {
                    $projectIds = $org->projects()->select('id');
                    return [
                        'projects_count' => $org->projects()->count(),
                        'members_count' => $org->users()->count(),
                        // Add other necessary counts for onboarding steps
                        'recent_activity_count' => \App\Models\ProjectLog::whereIn('project_id', $projectIds)->count(),
                        'tasks_count' => \App\Models\ProjectTask::whereIn('project_id', $projectIds)->count(),
                        'reports_count' => \App\Models\ProjectReport::whereIn('project_id', $projectIds)->count(),
                        'has_plan' => $org->plan_id !== null,
                    ];
                });
            },
            'announcements' => function () use ($isUser, $org) {
                if (!$isUser || !$org) return [];

                $cacheKey = 'announcements:org:' . $org->id;
                return cache()->remember($cacheKey, 300, function () use ($org) {
                    $planId = $org->effectivePlan()?->id;

                    return Announcement::where('is_active', true)
                        ->where(function ($q) {
                            $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                        })
                        ->where(function ($q) {
                            $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                        })
                        ->where(function ($q) use ($org, $planId) {
                            $q->where('is_global', true)
                                ->orWhere('organization_id', $org->id)
                                ->when($planId, fn($q2) => $q2->orWhere('plan_id', $planId));
                        })
                        ->orderByDesc('id')
                        ->limit(5)
                        ->get(['id', 'title', 'body', 'tone', 'cta_text', 'cta_url'])
                        ->toArray();
                });
            },
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
            ],
            'site_settings' => $siteSettings,
        ]);
    }


    private function getAccessibleModulesFromRole(string $role): array
    {
        $allowedModules = config("erp.roles.{$role}.modules", []);
        $allModules = config('erp.modules', []);

        $accessible = [];
        foreach ($allowedModules as $moduleKey) {
            if (isset($allModules[$moduleKey])) {
                $accessible[$moduleKey] = $allModules[$moduleKey];
            }
        }
        return $accessible;
    }
}
