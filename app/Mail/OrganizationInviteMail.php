<?php

namespace App\Mail;

use App\Models\OrganizationInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// NOTE: intentionally NOT implementing ShouldQueue. Koyeb runs no queue
// worker, so a queued invite mail would sit in the jobs table forever and
// never send. The controller already try/catches Mail::send() and falls back
// to a copyable invite link. Revisit if a worker service is added.
class OrganizationInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public OrganizationInvite $invite) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You are invited to join ' . $this->invite->organization->name . ' on BuildFlow'
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.organization-invite',
            with: [
                'orgName' => $this->invite->organization->name,
                'role' => $this->invite->role,
                'inviteUrl' => route('invites.show', $this->invite->token),
                'expiresAt' => $this->invite->expires_at?->toDayDateTimeString(),
            ]
        );
    }
}
