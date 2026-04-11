<?php

namespace Tests\Feature;

use App\Mail\EventPublishedMail;
use App\Models\Event;
use App\Models\MailingList;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EventPublicationMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishing_an_event_emails_subscribed_mailing_list_members_once(): void
    {
        Mail::fake();

        $owner = User::factory()->create();
        $subscriber = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Perth Board Games',
            'slug' => 'perth-board-games',
            'summary' => 'Weekly games',
            'description' => 'Board game group',
            'visibility' => 'public',
        ]);

        $organization->members()->attach($owner->id, ['role' => 'owner']);

        $firstList = MailingList::create([
            'organization_id' => $organization->id,
            'name' => 'General updates',
            'slug' => 'general-updates',
            'description' => 'General updates',
            'audience' => 'all-members',
        ]);

        $secondList = MailingList::create([
            'organization_id' => $organization->id,
            'name' => 'Volunteers',
            'slug' => 'volunteers',
            'description' => 'Volunteer updates',
            'audience' => 'volunteers',
        ]);

        $subscriber->mailingLists()->attach($firstList->id, [
            'status' => 'subscribed',
            'subscribed_at' => now(),
        ]);

        $subscriber->mailingLists()->attach($secondList->id, [
            'status' => 'subscribed',
            'subscribed_at' => now(),
        ]);

        $this->actingAs($owner)->post(route('events.store'), [
            'organization_id' => $organization->id,
            'title' => 'Friday Night Games',
            'summary' => 'Bring your favorite game',
            'description' => 'Open tables and social games.',
            'venue_name' => 'Community Hall',
            'venue_address' => '123 Main Street',
            'city' => 'Perth',
            'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addWeek()->addHours(3)->format('Y-m-d H:i:s'),
            'timezone' => 'Australia/Perth',
            'capacity' => 40,
            'visibility' => 'public',
        ])->assertRedirect();

        Mail::assertSent(EventPublishedMail::class, 1);
        Mail::assertSent(EventPublishedMail::class, function (EventPublishedMail $mail) use ($subscriber) {
            return $mail->hasTo($subscriber->email) && $mail->recipient->is($subscriber);
        });
    }

    public function test_publishing_an_event_creates_a_linked_event_update_mailing_list(): void
    {
        Mail::fake();

        $owner = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Perth Creators',
            'slug' => 'perth-creators',
            'summary' => 'Creative community',
            'description' => 'Workshops and meetups',
            'visibility' => 'public',
        ]);

        $organization->members()->attach($owner->id, ['role' => 'owner']);

        $this->actingAs($owner)->post(route('events.store'), [
            'organization_id' => $organization->id,
            'title' => 'Open Studio Night',
            'summary' => 'Show and tell',
            'description' => 'Bring work in progress.',
            'venue_name' => 'Studio 3',
            'venue_address' => '45 Market Street',
            'city' => 'Perth',
            'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addWeek()->addHours(2)->format('Y-m-d H:i:s'),
            'timezone' => 'Australia/Perth',
            'capacity' => 25,
            'visibility' => 'public',
        ])->assertRedirect();

        $event = Event::query()->with('mailingList')->firstOrFail();

        $this->assertNotNull($event->mailing_list_id);
        $this->assertNotNull($event->mailingList);
        $this->assertStringStartsWith('Open Studio Night updates', $event->mailingList->name);
        $this->assertSame($organization->id, $event->mailingList->organization_id);
    }

    public function test_publishing_a_repeating_event_creates_future_occurrences(): void
    {
        Mail::fake();

        $owner = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Perth Creators',
            'slug' => 'perth-creators',
            'summary' => 'Creative community',
            'description' => 'Workshops and meetups',
            'visibility' => 'public',
        ]);

        $organization->members()->attach($owner->id, ['role' => 'owner']);

        $startsAt = now()->addWeek()->startOfHour();

        $this->actingAs($owner)->post(route('events.store'), [
            'organization_id' => $organization->id,
            'title' => 'Weekly Studio',
            'summary' => 'Shared making night',
            'description' => 'Bring your work.',
            'venue_name' => 'Studio Hall',
            'venue_address' => '45 Market Street',
            'city' => 'Perth',
            'starts_at' => $startsAt->format('Y-m-d H:i:s'),
            'ends_at' => $startsAt->copy()->addHours(2)->format('Y-m-d H:i:s'),
            'timezone' => 'Australia/Perth',
            'capacity' => 25,
            'visibility' => 'public',
            'repeat_frequency' => 'weekly',
            'repeat_until' => $startsAt->copy()->addWeeks(2)->format('Y-m-d H:i:s'),
        ])->assertRedirect();

        $this->assertSame(3, Event::count());
        $this->assertSame(3, MailingList::count());
        $this->assertNotNull(Event::query()->first()->recurrence_group);
    }

    public function test_private_events_are_not_emailed_to_mailing_lists(): void
    {
        Mail::fake();

        $owner = User::factory()->create();
        $subscriber = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Secret Circle',
            'slug' => 'secret-circle',
            'summary' => 'Private club',
            'description' => 'Invite only',
            'visibility' => 'private',
        ]);

        $organization->members()->attach($owner->id, ['role' => 'owner']);

        $list = MailingList::create([
            'organization_id' => $organization->id,
            'name' => 'Inner circle',
            'slug' => 'inner-circle',
            'description' => 'Private notices',
            'audience' => 'all-members',
        ]);

        $subscriber->mailingLists()->attach($list->id, [
            'status' => 'subscribed',
            'subscribed_at' => now(),
        ]);

        $this->actingAs($owner)->post(route('events.store'), [
            'organization_id' => $organization->id,
            'title' => 'Closed Session',
            'summary' => 'Private planning',
            'description' => 'Closed planning meeting.',
            'venue_name' => 'Private venue',
            'venue_address' => 'Undisclosed',
            'city' => 'Perth',
            'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addWeek()->addHours(2)->format('Y-m-d H:i:s'),
            'timezone' => 'Australia/Perth',
            'capacity' => 10,
            'visibility' => 'private',
        ])->assertRedirect();

        Mail::assertNothingSent();
    }

    public function test_publishing_an_event_can_post_to_discord_when_configured(): void
    {
        Mail::fake();
        Http::fake([
            'https://discord.example/webhook' => Http::response(['id' => '123'], 204),
        ]);

        $owner = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Perth Makers',
            'slug' => 'perth-makers',
            'summary' => 'Build nights',
            'description' => 'Workshop community',
            'visibility' => 'public',
            'discord_webhook_url' => 'https://discord.example/webhook',
            'auto_post_discord_events' => true,
        ]);

        $organization->members()->attach($owner->id, ['role' => 'owner']);

        $this->actingAs($owner)->post(route('events.store'), [
            'organization_id' => $organization->id,
            'title' => 'Laser Cutter Night',
            'summary' => 'Open workshop session',
            'description' => 'Bring material stock.',
            'venue_name' => 'Maker Shed',
            'venue_address' => '55 Foundry Lane',
            'city' => 'Perth',
            'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addWeek()->addHours(2)->format('Y-m-d H:i:s'),
            'timezone' => 'Australia/Perth',
            'capacity' => 20,
            'visibility' => 'public',
        ])->assertRedirect();

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->url() === 'https://discord.example/webhook');
        $this->assertNotNull(Event::query()->firstOrFail()->discord_posted_at);
    }

    public function test_manager_can_manually_post_public_event_to_discord(): void
    {
        Http::fake([
            'https://discord.example/webhook' => Http::response(['id' => '123'], 204),
        ]);

        $owner = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Discord Club',
            'slug' => 'discord-club',
            'summary' => 'Discord events',
            'description' => 'Posts manually',
            'visibility' => 'public',
            'discord_webhook_url' => 'https://discord.example/webhook',
            'auto_post_discord_events' => false,
        ]);

        $organization->members()->attach($owner->id, ['role' => 'owner']);

        $event = Event::create([
            'organization_id' => $organization->id,
            'creator_id' => $owner->id,
            'title' => 'Manual Discord Post',
            'slug' => 'manual-discord-post',
            'summary' => 'Post this manually',
            'description' => 'Manual cross-post test.',
            'venue_name' => 'Community Hall',
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(2),
            'timezone' => 'Australia/Perth',
            'visibility' => 'public',
            'is_published' => true,
        ]);

        $this->actingAs($owner)
            ->post(route('events.discord', $event))
            ->assertRedirect(route('events.show', $event))
            ->assertSessionHas('status', 'Event posted to Discord.');

        Http::assertSent(fn ($request) => $request->url() === 'https://discord.example/webhook');
        $this->assertNotNull($event->fresh()->discord_posted_at);
    }

    public function test_private_events_are_not_manually_posted_to_discord(): void
    {
        Http::fake();

        $owner = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Private Discord Club',
            'slug' => 'private-discord-club',
            'summary' => 'Private events',
            'description' => 'Do not leak these',
            'visibility' => 'public',
            'discord_webhook_url' => 'https://discord.example/webhook',
            'auto_post_discord_events' => true,
        ]);

        $organization->members()->attach($owner->id, ['role' => 'owner']);

        $event = Event::create([
            'organization_id' => $organization->id,
            'creator_id' => $owner->id,
            'title' => 'Private Discord Post',
            'slug' => 'private-discord-post',
            'summary' => 'Private event',
            'description' => 'Should not post.',
            'venue_name' => 'Private Room',
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(2),
            'timezone' => 'Australia/Perth',
            'visibility' => 'private',
            'is_published' => true,
        ]);

        $this->actingAs($owner)
            ->post(route('events.discord', $event))
            ->assertRedirect(route('events.show', $event))
            ->assertSessionHas('status', 'Private events are not posted to Discord. Change the event to public or unlisted first.');

        Http::assertNothingSent();
        $this->assertNull($event->fresh()->discord_posted_at);
    }

    public function test_publishing_an_event_can_post_to_facebook_when_configured(): void
    {
        Mail::fake();
        Http::fake([
            'https://graph.facebook.com/*' => Http::response(['id' => 'fb-post-id'], 200),
        ]);

        $owner = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Perth Makers',
            'slug' => 'perth-makers',
            'summary' => 'Build nights',
            'description' => 'Workshop community',
            'visibility' => 'public',
            'facebook_page_id' => '123456789',
            'facebook_page_access_token' => 'page-token',
            'auto_post_facebook_events' => true,
        ]);

        $organization->members()->attach($owner->id, ['role' => 'owner']);

        $this->actingAs($owner)->post(route('events.store'), [
            'organization_id' => $organization->id,
            'title' => 'Laser Cutter Night',
            'summary' => 'Open workshop session',
            'description' => 'Bring material stock.',
            'venue_name' => 'Maker Shed',
            'venue_address' => '55 Foundry Lane',
            'city' => 'Perth',
            'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addWeek()->addHours(2)->format('Y-m-d H:i:s'),
            'timezone' => 'Australia/Perth',
            'capacity' => 20,
            'visibility' => 'public',
        ])->assertRedirect();

        Http::assertSent(fn ($request) => str_contains($request->url(), 'graph.facebook.com/v23.0/123456789/feed'));
        $this->assertNotNull(Event::query()->firstOrFail()->facebook_posted_at);
    }
}
