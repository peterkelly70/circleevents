<?php

namespace App\Support;

use App\Mail\EventReminderMail;
use App\Models\EventRsvp;
use Illuminate\Support\Facades\Mail;

class EventReminderSender
{
    public function send(): int
    {
        $windowStart = now()->addHours(23);
        $windowEnd = now()->addHours(25);

        $rsvps = EventRsvp::query()
            ->with(['event.organization', 'user'])
            ->where('status', 'going')
            ->whereNull('reminder_sent_at')
            ->whereHas('event', fn ($query) => $query
                ->where('is_published', true)
                ->whereBetween('starts_at', [$windowStart, $windowEnd]))
            ->get();

        foreach ($rsvps as $rsvp) {
            Mail::to($rsvp->user->email)->send(new EventReminderMail($rsvp->event, $rsvp->user));
            $rsvp->forceFill(['reminder_sent_at' => now()])->save();
        }

        return $rsvps->count();
    }
}
