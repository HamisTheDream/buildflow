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
            ->whereNotNull('email_verified_at')
            ->get();

        foreach ($day1Users as $user) {
            Mail::to($user)->send(new \App\Mail\Day1Welcome($user));
        }

        // Day 3: Feature Highlight
        $day3Users = User::whereDate('created_at', now()->subDays(3))->get();
        foreach ($day3Users as $user) {
            Mail::to($user)->send(new \App\Mail\Day3Features($user));
        }

        // Trial Expiry Warning (14 day trial, email 3 days before expiry)
        $expiringOrgs = \App\Models\Organization::where('subscription_status', 'trial')
            ->whereDate('trial_ends_at', now()->addDays(3))
            ->with(['users' => function ($q) {
                $q->wherePivot('role', 'owner');
            }])
            ->get();

        foreach ($expiringOrgs as $org) {
            $owner = $org->users->first();
            if ($owner) {
                Mail::to($owner)->send(new \App\Mail\TrialEndingSoon($org));
            }
        }
    }
}
