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

        // Only compute for logged-in users
        $entitlements = ($isUser && $user)
            ? app(\App\Services\EntitlementsService::class)->forOrg($org)
            : null;

        return array_merge($shared, [
            'csrf_token' => csrf_token(),
            'auth' => [
                'user' => ($isUser && $user) ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar_path' => $user->avatar_path,
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
            ],
            'ownerAuth' => [
                'admin' => \Illuminate\Support\Facades\Auth::guard('owner')->check()
                    ? [
                        'id' => \Illuminate\Support\Facades\Auth::guard('owner')->user()->id,
                        'name' => \Illuminate\Support\Facades\Auth::guard('owner')->user()->name,
                        'email' => \Illuminate\Support\Facades\Auth::guard('owner')->user()->email,
                        'is_super' => (bool) \Illuminate\Support\Facades\Auth::guard('owner')->user()->is_super,
                    ]
                    : null,
            ],
            'entitlements' => $entitlements,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}
