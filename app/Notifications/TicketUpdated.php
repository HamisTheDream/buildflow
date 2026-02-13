<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\SupportTicket;

class TicketUpdated extends Notification
{
    use Queueable;

    public function __construct(public SupportTicket $ticket, public string $action, public string $message = '') {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->action) {
            'replied' => "New reply on ticket #{$this->ticket->id}: {$this->ticket->subject}",
            'status_updated' => "Status updated for ticket #{$this->ticket->id}",
            default => "Update on ticket #{$this->ticket->id}"
        };

        $line = match ($this->action) {
            'replied' => "An admin has replied to your support ticket.",
            'status_updated' => "Your support ticket status has been updated to: " . ucfirst($this->ticket->status),
            default => "Your support ticket has been updated."
        };

        return (new MailMessage)
            ->subject($subject)
            ->line($line)
            ->line($this->message)
            ->action('View Ticket', url("/app/support/{$this->ticket->id}"))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'subject' => $this->ticket->subject,
            'action' => $this->action,
            'status' => $this->ticket->status,
            'message' => $this->message,
        ];
    }
}
