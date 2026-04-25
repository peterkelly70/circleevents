<?php

namespace Tests\Feature;

use App\Mail\EventReminderMail;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EventReminderCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminder_command_emails_going_attendees_for_upcoming_events(): void
    {
        Mail::fake();

        $organizer = User::factory()->create();
        $attendee = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $organizer->id,
            'name' => 'River Makers',
            'slug' => 'river-makers',
            'summary' => 'Workshop group',
            'description' => 'Creative workshop group',
            'visibility' => 'public',
        ]);

        $event = Event::create([
            'organization_id' => $organization->id,
            'creator_id' => $organizer->id,
            'title' => 'Morning Workshop',
            'slug' => 'morning-workshop',
            'summary' => 'A practical workshop',
            'description' => 'Bring your tools.',
            'venue_name' => 'Studio Hall',
            'venue_address' => '10 River Road',
            'city' => 'Perth',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHours(2),
            'timezone' => 'Australia/Perth',
            'visibility' => 'public',
            'is_published' => true,
        ]);

        $rsvp = EventRsvp::create([
            'event_id' => $event->id,
            'user_id' => $attendee->id,
            'status' => 'going',
        ]);

        Artisan::call('events:send-reminders');

        Mail::assertSent(EventReminderMail::class, function (EventReminderMail $mail) use ($attendee) {
            return $mail->hasTo($attendee->email) && $mail->recipient->is($attendee);
        });

        $this->assertNotNull($rsvp->fresh()->reminder_sent_at);
    }

    public function test_reminder_command_skips_non_going_or_already_sent_rsvps(): void
    {
        Mail::fake();

        $organizer = User::factory()->create();
        $attendee = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $organizer->id,
            'name' => 'Quiet Club',
            'slug' => 'quiet-club',
            'summary' => 'Low key group',
            'description' => 'Quiet events',
            'visibility' => 'public',
        ]);

        $event = Event::create([
            'organization_id' => $organization->id,
            'creator_id' => $organizer->id,
            'title' => 'Discussion Circle',
            'slug' => 'discussion-circle',
            'summary' => 'Chat night',
            'description' => 'Short discussion.',
            'venue_name' => 'Library Room',
            'venue_address' => '22 Main Road',
            'city' => 'Perth',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'timezone' => 'Australia/Perth',
            'visibility' => 'public',
            'is_published' => true,
        ]);

        EventRsvp::create([
            'event_id' => $event->id,
            'user_id' => $attendee->id,
            'status' => 'interested',
        ]);

        EventRsvp::create([
            'event_id' => $event->id,
            'user_id' => User::factory()->create()->id,
            'status' => 'going',
            'reminder_sent_at' => now(),
        ]);

        Artisan::call('events:send-reminders');

        Mail::assertNothingSent();
    }

    public function test_reminder_command_emails_followers_when_event_reminders_are_enabled(): void
    {
        Mail::fake();

        $organizer = User::factory()->create();
        $follower = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $organizer->id,
            'name' => 'Forest Circle',
            'slug' => 'forest-circle',
            'summary' => 'Outdoor meetups',
            'description' => 'Forest walks',
            'visibility' => 'public',
        ]);

        $organization->members()->attach($organizer->id, ['role' => 'owner']);
        $organization->members()->attach($follower->id, ['role' => 'follower']);

        $event = Event::create([
            'organization_id' => $organization->id,
            'creator_id' => $organizer->id,
            'title' => 'Forest Gathering',
            'slug' => 'forest-gathering',
            'summary' => 'Bring walking shoes',
            'description' => 'Meet at the trailhead.',
            'venue_name' => 'Northern trail',
            'venue_address' => 'Reserve entrance',
            'city' => 'Perth',
            'starts_at' => now()->addHour(),
            'ends_at' => now()->addHours(3),
            'timezone' => 'Australia/Perth',
            'visibility' => 'public',
            'is_published' => true,
            'notify_followers_one_hour_before' => true,
        ]);

        Artisan::call('events:send-reminders');

        Mail::assertSent(EventReminderMail::class, function (EventReminderMail $mail) use ($follower) {
            return $mail->hasTo($follower->email)
                && $mail->recipient->is($follower)
                && $mail->leadTime === '1 hour';
        });

        $this->assertNotNull($event->fresh()->follower_reminder_one_hour_sent_at);
    }

    public function test_reminder_command_uses_attendee_selected_reminder_windows(): void
    {
        Mail::fake();

        $organizer = User::factory()->create();
        $attendee = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $organizer->id,
            'name' => 'Midnight Club',
            'slug' => 'midnight-club',
            'summary' => 'Late events',
            'description' => 'After dark gatherings',
            'visibility' => 'public',
        ]);

        $event = Event::create([
            'organization_id' => $organization->id,
            'creator_id' => $organizer->id,
            'title' => 'Late Session',
            'slug' => 'late-session',
            'summary' => 'Night meetup',
            'description' => 'Arrive early.',
            'venue_name' => 'Studio',
            'venue_address' => 'Perth',
            'city' => 'Perth',
            'starts_at' => now()->addHour(),
            'ends_at' => now()->addHours(2),
            'timezone' => 'Australia/Perth',
            'visibility' => 'public',
            'is_published' => true,
        ]);

        $rsvp = EventRsvp::create([
            'event_id' => $event->id,
            'user_id' => $attendee->id,
            'status' => 'going',
            'remind_one_hour_before' => true,
        ]);

        Artisan::call('events:send-reminders');

        Mail::assertSent(EventReminderMail::class, function (EventReminderMail $mail) use ($attendee) {
            return $mail->hasTo($attendee->email)
                && $mail->recipient->is($attendee)
                && $mail->leadTime === '1 hour';
        });

        $this->assertNotNull($rsvp->fresh()->reminder_one_hour_sent_at);
    }

    public function test_reminder_command_does_not_double_email_someone_who_is_both_follower_and_attendee(): void
    {
        Mail::fake();

        $organizer = User::factory()->create();
        $attendeeFollower = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $organizer->id,
            'name' => 'Harbor Circle',
            'slug' => 'harbor-circle',
            'summary' => 'Waterfront events',
            'description' => 'Harbor meetups',
            'visibility' => 'public',
        ]);

        $organization->members()->attach($organizer->id, ['role' => 'owner']);
        $organization->members()->attach($attendeeFollower->id, ['role' => 'follower']);

        $event = Event::create([
            'organization_id' => $organization->id,
            'creator_id' => $organizer->id,
            'title' => 'Harbor Walk',
            'slug' => 'harbor-walk',
            'summary' => 'Tomorrow evening',
            'description' => 'Meet by the pier.',
            'venue_name' => 'Pier entrance',
            'venue_address' => 'Perth',
            'city' => 'Perth',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHours(2),
            'timezone' => 'Australia/Perth',
            'visibility' => 'public',
            'is_published' => true,
            'notify_followers_one_day_before' => true,
        ]);

        EventRsvp::create([
            'event_id' => $event->id,
            'user_id' => $attendeeFollower->id,
            'status' => 'going',
            'remind_one_day_before' => true,
        ]);

        Artisan::call('events:send-reminders');

        Mail::assertSent(EventReminderMail::class, 1);
        Mail::assertSent(EventReminderMail::class, function (EventReminderMail $mail) use ($attendeeFollower) {
            return $mail->hasTo($attendeeFollower->email)
                && $mail->recipient->is($attendeeFollower)
                && $mail->intro === 'This is your reminder for an event you marked as going.';
        });
    }
}
