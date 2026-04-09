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
        $this->assertSame(2, $invitation->fresh()->use_count);
        $this->assertDatabaseCount('invitation_audits', 3);
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

        $this->assertDatabaseHas('invitation_audits', [
            'invitation_type' => EventInvitation::class,
            'invitation_id' => $invitation->id,
            'action' => 'blocked-expired',
        ]);
    }

    public function test_share_event_invites_respect_max_uses_and_can_be_revoked(): void
    {
        $owner = User::factory()->create();
        $firstGuest = User::factory()->create();
        $secondGuest = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Trail Camp',
            'slug' => 'trail-camp',
            'summary' => 'Camp group',
            'description' => 'Weekend camps',
            'visibility' => 'public',
        ]);

        $organization->members()->attach($owner->id, ['role' => 'owner']);

        $event = Event::create([
            'organization_id' => $organization->id,
            'creator_id' => $owner->id,
            'title' => 'Bush Walk',
            'slug' => 'bush-walk',
            'summary' => 'Morning walk',
            'description' => 'Bring water.',
            'venue_name' => 'State forest',
            'venue_address' => 'Somewhere',
            'city' => 'Perth',
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(2),
            'timezone' => 'Australia/Perth',
            'visibility' => 'public',
            'is_published' => true,
        ]);

        $this->actingAs($owner)->post(route('events.invitations.store', $event), [
            'delivery' => 'share',
            'name' => 'Single use code',
            'max_uses' => 1,
        ])->assertRedirect(route('events.show', $event));

        $invitation = EventInvitation::query()->firstOrFail();

        $this->actingAs($firstGuest)
            ->get(route('event-invitations.accept-code', $invitation->share_code))
            ->assertRedirect(route('events.show', $event));

        $this->actingAs($secondGuest)
            ->get(route('event-invitations.accept-code', $invitation->share_code))
            ->assertRedirect(route('events.show', $event));

        $this->assertDatabaseHas('event_rsvps', [
            'event_id' => $event->id,
            'user_id' => $firstGuest->id,
        ]);
        $this->assertDatabaseMissing('event_rsvps', [
            'event_id' => $event->id,
            'user_id' => $secondGuest->id,
        ]);
        $this->assertSame(1, $invitation->fresh()->use_count);
        $this->assertDatabaseHas('invitation_audits', [
            'invitation_type' => EventInvitation::class,
            'invitation_id' => $invitation->id,
            'action' => 'blocked-max-uses',
        ]);

        $this->actingAs($owner)
            ->post(route('events.invitations.revoke', [$event, $invitation]))
            ->assertRedirect(route('events.show', $event));

        $this->assertNotNull($invitation->fresh()->revoked_at);
        $this->assertDatabaseHas('invitation_audits', [
            'invitation_type' => EventInvitation::class,
            'invitation_id' => $invitation->id,
            'action' => 'revoked',
        ]);
    }
}
