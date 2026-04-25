<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrganizationMessageDiscordTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_announcement_can_post_to_discord_when_requested(): void
    {
        Mail::fake();
        Http::fake([
            'https://discord.example/webhook' => Http::response(['id' => '123'], 204),
        ]);

        $owner = User::factory()->create();
        $member = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Perth Campers',
            'slug' => 'perth-campers',
            'summary' => 'Camping club',
            'description' => 'Weekend trips',
            'visibility' => 'public',
            'discord_webhook_url' => 'https://discord.example/webhook',
            'auto_post_discord_announcements' => false,
        ]);

        $organization->members()->attach($owner->id, ['role' => 'owner', 'email_opt_out_token' => 'owner-token']);
        $organization->members()->attach($member->id, ['role' => 'follower', 'email_opt_out_token' => 'member-token']);

        $this->actingAs($owner)->post(route('organizations.messages.store', $organization), [
            'subject' => 'Track change',
            'body' => 'Bring extra water.',
            'post_to_discord' => 1,
        ])->assertRedirect(route('organizations.show', $organization));

        Http::assertSentCount(1);
        $this->assertNotNull(OrganizationMessage::query()->firstOrFail()->discord_posted_at);
    }

    public function test_invalid_member_message_attachment_does_not_trigger_event_form_error(): void
    {
        $owner = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Boardgame Brigade',
            'slug' => 'boardgame-brigade',
            'summary' => 'Board games',
            'description' => 'Weekly games',
            'visibility' => 'public',
        ]);

        $organization->members()->attach($owner->id, ['role' => 'owner', 'email_opt_out_token' => 'owner-token']);

        $this->actingAs($owner)
            ->followingRedirects()
            ->from(route('organizations.show', $organization))
            ->post(route('organizations.messages.store', $organization), [
                'subject' => 'Tonight',
                'body' => 'Bring snacks.',
                'image' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
            ])
            ->assertOk()
            ->assertSee('Fix the highlighted member message details and try sending again.')
            ->assertDontSee('Fix the highlighted event details and try publishing again.');
    }
}
