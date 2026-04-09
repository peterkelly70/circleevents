<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventInvitation;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShareInviteTest extends TestCase
{
    use RefreshDatabase;

    public function test_share_organization_invites_can_be_created_and_reused(): void
    {
        $owner = User::factory()->create();
        $firstGuest = User::factory()->create();
        $secondGuest = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Perth Kayakers',
            'slug' => 'perth-kayakers',
            'summary' => 'River paddles',
            'description' => 'Weekend launches',
            'visibility' => 'public',
        ]);

        $organization->members()->attach($owner->id, ['role' => 'owner']);

        $this->actingAs($owner)->post(route('organizations.invitations.store', $organization), [
            'delivery' => 'share',
            'role' => 'follower',
            'name' => 'Landing page code',
        ])->assertRedirect(route('organizations.show', $organization));

        $invitation = OrganizationInvitation::query()->firstOrFail();

        $this->assertNotNull($invitation->share_code);
        $this->assertNull($invitation->email);

        $this->actingAs($firstGuest)
            ->get(route('organizations.invitations.accept-code', $invitation->share_code))
            ->assertRedirect(route('organizations.show', $organization));

        $this->actingAs($secondGuest)
            ->get(route('organizations.invitations.accept-code', $invitation->share_code))
            ->assertRedirect(route('organizations.show', $organization));

        $this->assertTrue($firstGuest->fresh()->isMemberOf($organization));
        $this->assertTrue($secondGuest->fresh()->isMemberOf($organization));
        $this->assertNull($invitation->fresh()->accepted_at);
    }

    public function test_expired_event_share_invites_are_rejected(): void
    {
        $owner = User::factory()->create();
        $guest = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Night Riders',
            'slug' => 'night-riders',
            'summary' => 'Late rides',
            'description' => 'Evening sessions',
            'visibility' => 'public',
        ]);

        $organization->members()->attach($owner->id, ['role' => 'owner']);

        $event = Event::create([
            'organization_id' => $organization->id,
            'creator_id' => $owner->id,
            'title' => 'Moonlight Roll',
            'slug' => 'moonlight-roll',
            'summary' => 'Twilight skate',
            'description' => 'Bring lights.',
            'venue_name' => 'River path',
            'venue_address' => 'South Perth',
            'city' => 'Perth',
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(2),
            'timezone' => 'Australia/Perth',
            'visibility' => 'public',
            'is_published' => true,
        ]);

        $invitation = EventInvitation::create([
            'event_id' => $event->id,
            'invited_by_user_id' => $owner->id,
            'name' => 'Expired code',
            'token' => 'expired-token',
            'share_code' => 'EXPIRED1',
            'expires_at' => now()->subMinute(),
        ]);

        $this->actingAs($guest)
            ->get(route('event-invitations.accept-code', $invitation->share_code))
            ->assertRedirect(route('events.show', $event));

        $this->assertDatabaseMissing('event_rsvps', [
            'event_id' => $event->id,
            'user_id' => $guest->id,
        ]);
    }
}
