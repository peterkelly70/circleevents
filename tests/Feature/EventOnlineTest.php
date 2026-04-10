<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventOnlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_publish_online_event_without_location_fields(): void
    {
        $organizer = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $organizer->id,
            'name' => 'Virtual Builders',
            'slug' => 'virtual-builders',
            'summary' => 'Remote workshops',
            'description' => 'Online events only',
            'visibility' => 'public',
            'approval_status' => 'approved',
        ]);

        $organization->members()->attach($organizer->id, ['role' => 'owner']);

        $response = $this->actingAs($organizer)->post(route('events.store'), [
            'organization_id' => $organization->id,
            'title' => 'Virtual Meetup',
            'summary' => 'Remote session',
            'description' => 'Bring your laptop.',
            'is_online' => '1',
            'online_url' => 'https://meet.example.test/virtual-meetup',
            'start_date' => now()->addDays(3)->format('Y-m-d'),
            'start_time' => '19:00',
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'end_time' => '21:00',
            'timezone' => 'Australia/Perth',
            'visibility' => 'public',
        ]);

        $response->assertRedirect();

        $event = $organization->events()->firstOrFail();

        $this->assertTrue($event->is_online);
        $this->assertSame('https://meet.example.test/virtual-meetup', $event->online_url);
        $this->assertNull($event->venue_name);
        $this->assertNull($event->venue_address);
        $this->assertNull($event->city);
        $this->assertNull($event->google_place_id);
        $this->assertNotNull($event->mailing_list_id);
    }
}
