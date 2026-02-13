<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendDripEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Day 1: Welcome / Getting Started
        $day1Users = User::whereDate('created_at', now()->subDays(1))
            ->whereNull('email_verified_at') // Example condition, or maybe all
            ->get();

        foreach ($day1Users as $user) {
            // Mail::to($user)->send(new \App\Mail\Drip\Day1Welcome($user));
        }

        // Day 3: Feature Highlight
        $day3Users = User::whereDate('created_at', now()->subDays(3))->get();
        foreach ($day3Users as $user) {
            // Mail::to($user)->send(new \App\Mail\Drip\Day3Features($user));
        }

        // Day 25: Trial Expiry Warning (assuming 30 day trial)
        // We look for org owners whose trial expires in 5 days
        $expiringOrgs = \App\Models\Organization::where('subscription_status', 'trial')
            ->whereDate('trial_ends_at', now()->addDays(5))
            ->with('users') // simpler than finding "owner" manually if we just email all admins
            ->get();

        foreach ($expiringOrgs as $org) {
            // Email the owner
            // Mail::to($org->ownerUser())->send(new \App\Mail\Drip\TrialEndingSoon($org));
        }
    }
}
