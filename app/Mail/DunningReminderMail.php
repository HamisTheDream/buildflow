<?php

namespace App\Mail;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DunningReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Organization $org,
        public int $stage,
    ) {}

    public function build()
    {
        $subject = match ($this->stage) {
            1 => "Action required: subscription renewal for {$this->org->name}",
            2 => "Reminder: renew BuildFlow subscription for {$this->org->name}",
            3 => "Final notice: BuildFlow access will be paused soon",
            default => "BuildFlow subscription reminder",
        };

        return $this->subject($subject)
            ->view('emails.dunning')
            ->with([
                'org' => $this->org,
                'stage' => $this->stage,
                'billingUrl' => url('/app/billing'),
                'graceEndsAt' => $this->org->grace_ends_at,
            ]);
    }
}
