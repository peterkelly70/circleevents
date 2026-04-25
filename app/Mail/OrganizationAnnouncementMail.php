<?php

namespace App\Mail;

use App\Models\OrganizationMessage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrganizationAnnouncementMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public OrganizationMessage $messageRecord,
        public User $recipient,
        public string $optOutToken,
    ) {
    }

    public function build(): self
    {
        return $this->subject($this->messageRecord->subject)
            ->view('emails.organization-announcement');
    }
}
