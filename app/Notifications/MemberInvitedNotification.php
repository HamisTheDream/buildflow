<?php

namespace App\Notifications;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberInvitedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Organization $organization,
        public string $invitedByName = 'Someone',
        public string $role = 'member'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/app/dashboard');

        return (new MailMessage)
            ->subject("You've been invited to {$this->organization->name}")
            ->greeting("Hi {$notifiable->name},")
            ->line("{$this->invitedByName} has invited you to join **{$this->organization->name}** on BuildFlow as a **{$this->role}**.")
            ->line("Start collaborating on projects, track progress, and manage your work — all in one place.")
            ->action('Open Dashboard', $url)
            ->line('Welcome aboard!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'member_invited',
            'title' => "You've been added to {$this->organization->name}",
            'body' => "{$this->invitedByName} invited you as {$this->role}",
            'data' => [
                'organization_id' => $this->organization->id,
                'organization_name' => $this->organization->name,
                'role' => $this->role,
            ],
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
