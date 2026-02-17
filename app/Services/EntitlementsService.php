<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Plan;

class EntitlementsService
{
    public function __construct(private UsageService $usage) {}

    public function forOrg(?Organization $org): array
    {
        if (!$org) {
            return [
                'plan' => null,
                'usage' => null,
                'limits' => null,
                'can' => [
                    'create_project' => false,
                    'invite_member' => false,
                    'upload' => false,
                    'share_reports' => false,
                    'password_protect_reports' => false,
                ],
            ];
        }

        $plan = $org->effectivePlan(); // you already added this

        if (!$plan) {
            return [
                'plan' => null,
                'usage' => $this->usage->getUsage($org),
                'limits' => null,
                'can' => [
                    'create_project' => true,
                    'invite_member' => true,
                    'upload' => true,
                    'share_reports' => false,
                    'password_protect_reports' => false,
                ],
                'subscription' => [
                    'status' => $org->subscription_status,
                    'trial_ends_at' => $org->trial_ends_at?->toDateTimeString(),
                ],
            ];
        }

        $gate = $this->usage->withinLimits($org); // returns usage/limits/can

        return [
            'plan' => [
                'key' => $plan->key,
                'name' => $plan->name,
                'price_monthly_cents' => $plan->price_monthly_cents,
                'features' => [
                    'share_reports' => (bool)$plan->can_share_reports,
                    'password_protect_reports' => (bool)$plan->can_password_protect_reports,
                ],
            ],
            'usage' => $gate['usage'],
            'limits' => $gate['limits'],
            // IMPORTANT: keep can keys stable for frontend
            'can' => [
                'create_project' => (bool)$gate['can']['create_project'],
                'invite_member' => (bool)$gate['can']['invite_member'],
                'upload' => (bool)$gate['can']['upload'],
                'share_reports' => (bool)$plan->can_share_reports,
                'password_protect_reports' => (bool)$gate['can']['password_protect_reports'],
            ],
            'subscription' => [
                'status' => $org->subscription_status,
                'trial_ends_at' => $org->trial_ends_at?->toDateTimeString(),
            ],
        ];
    }
}
