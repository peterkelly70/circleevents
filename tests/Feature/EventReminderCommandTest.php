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
}
