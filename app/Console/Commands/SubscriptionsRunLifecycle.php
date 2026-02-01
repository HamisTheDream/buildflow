<?php

namespace App\Console\Commands;

use App\Mail\DunningReminderMail;
use App\Models\Organization;
use App\Services\SubscriptionLifecycle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SubscriptionsRunLifecycle extends Command
{
    protected $signature = 'subscriptions:run-lifecycle';
    protected $description = 'Apply subscription lifecycle transitions and send dunning emails';

    public function handle(SubscriptionLifecycle $lifecycle): int
    {
        $now = now();

        $orgs = Organization::query()
            ->whereIn('subscription_status', ['trial','active','past_due'])
            ->get();

        $sent = 0;
        $moved = 0;
        $expired = 0;

        foreach ($orgs as $org) {
            // 1) Trial expiration -> free
            if ($lifecycle->expireTrialIfNeeded($org)) {
                $expired++;
            }

            // 2) Active expiration -> past_due + grace
            if ($lifecycle->moveActiveToPastDueIfExpired($org)) {
                $moved++;
            }

            // reload after possible changes
            $org->refresh();

            // 3) Dunning for past_due
            if ($org->subscription_status === 'past_due' && $org->grace_ends_at) {
                $email = method_exists($org, 'billingEmail') ? $org->billingEmail() : null;
                if (!$email) continue;

                // Stages: day 0, day 3, day 6 from past_due_at
                $pastDueAt = $org->past_due_at ?? $org->paid_until ?? $now;
                $days = $pastDueAt->diffInDays($now);

                $targetStage = 0;
                if ($days >= 0) $targetStage = 1;
                if ($days >= 3) $targetStage = 2;
                if ($days >= 6) $targetStage = 3;

                // Send only when stage increases
                if ($targetStage > (int)$org->dunning_stage) {
                    Mail::to($email)->send(new DunningReminderMail($org, $targetStage));

                    $org->dunning_stage = $targetStage;
                    $org->last_dunning_sent_at = $now;
                    $org->save();

                    $sent++;
                }
            }
        }

        $this->info("Lifecycle complete: moved={$moved}, trial_expired={$expired}, dunning_sent={$sent}");

        return self::SUCCESS;
    }
}
