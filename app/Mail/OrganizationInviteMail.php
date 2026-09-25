<?php

namespace App\Mail;

use App\Models\OrganizationInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrganizationInviteMail extends Mailable implements ShouldQueue
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
