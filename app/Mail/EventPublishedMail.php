<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventPublishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public User $recipient,
    ) {
    }

    public function build(): self
    {
        return $this->subject('New event: '.$this->event->title)
            ->view('emails.event-published');
    }
}
