<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrganizationMessageFacebookTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_announcement_can_post_to_facebook_when_requested(): void
    {
        Mail::fake();
        Http::fake([
            'https://graph.facebook.com/*' => Http::response(['id' => 'fb-post-id'], 200),
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
            'facebook_page_id' => '123456789',
            'facebook_page_access_token' => 'page-token',
            'auto_post_facebook_announcements' => false,
        ]);

        $organization->members()->attach($owner->id, ['role' => 'owner', 'email_opt_out_token' => 'owner-token']);
        $organization->members()->attach($member->id, ['role' => 'follower', 'email_opt_out_token' => 'member-token']);

        $this->actingAs($owner)->post(route('organizations.messages.store', $organization), [
            'subject' => 'Track change',
            'body' => 'Bring extra water.',
            'post_to_facebook' => 1,
        ])->assertRedirect(route('organizations.show', $organization));

        Http::assertSent(fn ($request) => str_contains($request->url(), 'graph.facebook.com/v23.0/123456789/feed'));
        $this->assertNotNull(OrganizationMessage::query()->firstOrFail()->facebook_posted_at);
    }
}
