<?php

namespace App\Mail;

use App\Models\EventInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public EventInvitation $invitation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You are invited to '.$this->invitation->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-invitation',
        );
    }
}
