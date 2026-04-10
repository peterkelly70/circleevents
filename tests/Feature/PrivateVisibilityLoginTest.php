<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivateVisibilityLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_viewing_private_event_is_prompted_to_log_in_and_returned_after_login(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create([
            'email' => 'member@example.test',
            'password' => 'password',
        ]);

        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Private Circle',
            'slug' => 'private-circle',
            'summary' => 'Members only',
            'description' => 'Private group',
            'visibility' => 'private',
            'approval_status' => 'approved',
        ]);

        $organization->members()->attach($owner->id, ['role' => 'owner']);
        $organization->members()->attach($member->id, ['role' => 'follower']);

        $event = Event::create([
            'organization_id' => $organization->id,
            'creator_id' => $owner->id,
            'title' => 'Members Event',
            'slug' => 'members-event',
            'summary' => 'Private session',
            'description' => 'Only members can see this.',
            'venue_name' => 'Private Room',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHours(2),
            'timezone' => 'Australia/Perth',
            'visibility' => 'private',
            'is_published' => true,
        ]);

        $this->get(route('events.show', $event))
            ->assertRedirect(route('login'));

        $this->post(route('login'), [
            'email' => 'member@example.test',
            'password' => 'password',
        ])->assertRedirect(route('events.show', $event));
    }

    public function test_guest_viewing_private_organization_is_prompted_to_log_in(): void
    {
        $owner = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Hidden Circle',
            'slug' => 'hidden-circle',
            'summary' => 'Members only',
            'description' => 'Private group',
            'visibility' => 'private',
            'approval_status' => 'approved',
        ]);

        $this->get(route('organizations.show', $organization))
            ->assertRedirect(route('login'));
    }
}
