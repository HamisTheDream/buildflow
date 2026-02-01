<?php

namespace App\Services;

use App\Models\Organization;

class SubscriptionLifecycle
{
    // Change once, affects everything
    public int $graceDays = 7;

    public function moveActiveToPastDueIfExpired(Organization $org): bool
    {
        if ($org->subscription_status !== 'active') return false;
        if (!$org->paid_until) return false;

        if (now()->lessThanOrEqualTo($org->paid_until)) return false;

        $org->subscription_status = 'past_due';
        $org->past_due_at = $org->past_due_at ?? now();
        $org->grace_ends_at = $org->grace_ends_at ?? now()->addDays($this->graceDays);
        $org->save();

        return true;
    }

    public function expireTrialIfNeeded(Organization $org): bool
    {
        if ($org->subscription_status !== 'trial') return false;
        if (!$org->trial_ends_at) return false;

        if (now()->lessThanOrEqualTo($org->trial_ends_at)) return false;

        // Lean decision: trial-expired goes to free (no hard lock)
        $org->subscription_status = 'free';
        $org->trial_ends_at = null;
        $org->save();

        return true;
    }

    public function clearDunning(Organization $org): void
    {
        $org->dunning_stage = 0;
        $org->last_dunning_sent_at = null;
        $org->past_due_at = null;
        $org->grace_ends_at = null;
        $org->last_payment_failed_at = null;
        $org->save();
    }

    public function markPaymentFailed(Organization $org): void
    {
        $org->last_payment_failed_at = now();

        // If not active, ensure it's past_due with grace
        if ($org->subscription_status !== 'active') {
            $org->subscription_status = 'past_due';
            $org->past_due_at = $org->past_due_at ?? now();
            $org->grace_ends_at = $org->grace_ends_at ?? now()->addDays($this->graceDays);
        }

        $org->save();
    }

    /**
     * Extend paid_until from max(now, paid_until) by $days.
     */
    public function applySuccessfulPayment(Organization $org, int $days, ?int $planId = null): void
    {
        if ($planId) $org->plan_id = $planId;

        $start = ($org->paid_until && now()->lessThanOrEqualTo($org->paid_until))
            ? $org->paid_until
            : now();

        $org->subscription_status = 'active';
        $org->trial_ends_at = null;
        $org->paid_until = $start->copy()->addDays($days);
        $org->save();

        $this->clearDunning($org);
    }
}
