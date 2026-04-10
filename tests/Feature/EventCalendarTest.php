<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_event_can_be_downloaded_as_ics(): void
    {
        $organizer = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $organizer->id,
            'name' => 'Calendar Club',
            'slug' => 'calendar-club',
            'summary' => 'Calendar tests',
            'description' => 'Testing export',
            'visibility' => 'public',
            'approval_status' => 'approved',
        ]);

        $event = Event::create([
            'organization_id' => $organization->id,
            'creator_id' => $organizer->id,
            'title' => 'Exported Event',
            'slug' => 'exported-event',
            'summary' => 'Calendar save',
            'description' => 'Remember this.',
            'venue_name' => 'Main Hall',
            'venue_address' => '123 Example St',
            'city' => 'Perth',
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(2),
            'timezone' => 'Australia/Perth',
            'visibility' => 'public',
            'is_published' => true,
        ]);

        $response = $this->get(route('events.calendar', $event));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/calendar; charset=UTF-8');
        $response->assertSee('BEGIN:VCALENDAR', false);
        $response->assertSee('SUMMARY:Exported Event', false);
    }

    public function test_online_event_calendar_export_uses_online_location(): void
    {
        $organizer = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $organizer->id,
            'name' => 'Remote Club',
            'slug' => 'remote-club',
            'summary' => 'Online events',
            'description' => 'Remote sessions',
            'visibility' => 'public',
            'approval_status' => 'approved',
        ]);

        $event = Event::create([
            'organization_id' => $organization->id,
            'creator_id' => $organizer->id,
            'title' => 'Remote Gathering',
            'slug' => 'remote-gathering',
            'summary' => 'Join online',
            'description' => 'Bring your webcam.',
            'is_online' => true,
            'online_url' => 'https://meet.example.test/remote-gathering',
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(2),
            'timezone' => 'Australia/Perth',
            'visibility' => 'public',
            'is_published' => true,
        ]);

        $response = $this->get(route('events.calendar', $event));

        $response->assertOk();
        $response->assertSee('LOCATION:https://meet.example.test/remote-gathering', false);
    }
}
