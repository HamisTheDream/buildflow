<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
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

        // Only compute for logged-in users
        $entitlements = ($isUser && $user)
            ? app(\App\Services\EntitlementsService::class)->forOrg($org)
            : null;

        // Resolve owner admin once instead of 6+ Auth::guard() calls
        $ownerAdmin = \Illuminate\Support\Facades\Auth::guard('owner')->user();

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
                    'plan' => $org->effectivePlan()->key,
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
            'entitlements' => $entitlements,
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
