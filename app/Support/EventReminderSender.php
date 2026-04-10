<?php

namespace App\Support;

use App\Mail\EventReminderMail;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EventReminderSender
{
    public function send(): int
    {
        $sent = 0;

        foreach ($this->slots() as $slot => $config) {
            $sent += $this->sendFollowerRemindersForSlot($slot, $config);
            $sent += $this->sendAttendeeRemindersForSlot($slot, $config);
        }

        return $sent;
    }

    protected function slots(): array
    {
        return [
            'one_week' => [
                'lead' => '1 week',
                'window_start' => now()->addDays(7)->subHour(),
                'window_end' => now()->addDays(7)->addHour(),
                'event_toggle' => 'notify_followers_one_week_before',
                'event_sent_at' => 'follower_reminder_one_week_sent_at',
                'rsvp_toggle' => 'remind_one_week_before',
                'rsvp_sent_at' => 'reminder_one_week_sent_at',
            ],
            'one_day' => [
                'lead' => '1 day',
                'window_start' => now()->addDay()->subHour(),
                'window_end' => now()->addDay()->addHour(),
                'event_toggle' => 'notify_followers_one_day_before',
                'event_sent_at' => 'follower_reminder_one_day_sent_at',
                'rsvp_toggle' => 'remind_one_day_before',
                'rsvp_sent_at' => 'reminder_one_day_sent_at',
            ],
            'one_hour' => [
                'lead' => '1 hour',
                'window_start' => now()->addHour()->subMinutes(30),
                'window_end' => now()->addHour()->addMinutes(30),
                'event_toggle' => 'notify_followers_one_hour_before',
                'event_sent_at' => 'follower_reminder_one_hour_sent_at',
                'rsvp_toggle' => 'remind_one_hour_before',
                'rsvp_sent_at' => 'reminder_one_hour_sent_at',
            ],
        ];
    }

    protected function sendFollowerRemindersForSlot(string $slot, array $config): int
    {
        $events = Event::query()
            ->with(['organization.members', 'organization.mailingLists.subscribers', 'mailingList.subscribers'])
            ->where('is_published', true)
            ->where($config['event_toggle'], true)
            ->whereNull($config['event_sent_at'])
            ->whereBetween('starts_at', [$config['window_start'], $config['window_end']])
            ->get();

        $sent = 0;

        foreach ($events as $event) {
            $recipients = $this->followerRecipients($event);

            foreach ($recipients as $recipient) {
                Mail::to($recipient->email)->send(new EventReminderMail(
                    $event,
                    $recipient,
                    $config['lead'],
                    $event->organization->name.' asked us to remind followers about this upcoming event.',
                ));
                $sent++;
            }

            $event->forceFill([$config['event_sent_at'] => now()])->save();
        }

        return $sent;
    }

    protected function sendAttendeeRemindersForSlot(string $slot, array $config): int
    {
        $rsvps = EventRsvp::query()
            ->with(['event.organization', 'user'])
            ->where('status', 'going')
            ->whereNull($config['rsvp_sent_at'])
            ->where(function ($query) use ($slot, $config) {
                $query->where($config['rsvp_toggle'], true);

                if ($slot === 'one_day') {
                    $query->orWhere(function ($legacyQuery) {
                        $legacyQuery
                            ->where('remind_one_week_before', false)
                            ->where('remind_one_day_before', false)
                            ->where('remind_one_hour_before', false)
                            ->whereNull('reminder_sent_at');
                    });
                }
            })
            ->whereHas('event', fn ($query) => $query
                ->where('is_published', true)
                ->whereBetween('starts_at', [$config['window_start'], $config['window_end']]))
            ->get();

        foreach ($rsvps as $rsvp) {
            Mail::to($rsvp->user->email)->send(new EventReminderMail(
                $rsvp->event,
                $rsvp->user,
                $config['lead'],
                'This is your reminder for an event you marked as going.',
            ));

            $updates = [$config['rsvp_sent_at'] => now()];

            if ($slot === 'one_day' && $rsvp->reminder_sent_at === null) {
                $updates['reminder_sent_at'] = now();
            }

            $rsvp->forceFill($updates)->save();
        }

        return $rsvps->count();
    }

    protected function followerRecipients(Event $event): Collection
    {
        $memberRecipients = $event->organization->members
            ->filter(fn (User $member) => blank($member->pivot->email_opt_out_at))
            ->map(fn (User $member) => ['key' => Str::lower($member->email), 'user' => $member]);

        $mailingListRecipients = collect();

        if ($event->visibility !== 'private') {
            $mailingListRecipients = $event->organization->mailingLists
                ->flatMap->subscribers
                ->merge($event->mailingList?->subscribers ?? collect())
                ->filter(fn (User $subscriber) => $subscriber->pivot->status === 'subscribed')
                ->map(fn (User $subscriber) => ['key' => Str::lower($subscriber->email), 'user' => $subscriber]);
        }

        return $memberRecipients
            ->merge($mailingListRecipients)
            ->unique('key')
            ->pluck('user')
            ->values();
    }
}
