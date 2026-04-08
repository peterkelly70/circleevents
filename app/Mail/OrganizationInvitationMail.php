<?php

namespace App\Mail;

use App\Models\OrganizationInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrganizationInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public OrganizationInvitation $invitation)
    {
    }

    public function build(): self
    {
        return $this->subject('You are invited to follow '.$this->invitation->organization->name)
            ->view('emails.organization-invitation');
    }
}
