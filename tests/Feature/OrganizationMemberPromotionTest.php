<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationMemberPromotionTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_page_make_manager_form_posts_user_ids_array(): void
    {
        [$owner, $follower] = User::factory()->count(2)->create();

        $organization = $this->createOrganizationWithMembers($owner, $follower);

        $this->actingAs($owner)
            ->get(route('organizations.show', $organization))
            ->assertOk()
            ->assertSee('name="user_ids[]"', false)
            ->assertDontSee('name="user_id"', false);
    }

    public function test_member_promotion_validation_errors_do_not_show_event_error_banner(): void
    {
        [$owner, $follower] = User::factory()->count(2)->create();

        $organization = $this->createOrganizationWithMembers($owner, $follower);

        $this->followingRedirects()
            ->actingAs($owner)
            ->post(route('organizations.members.promote', $organization), [])
            ->assertOk()
            ->assertDontSee('Fix the highlighted event details and try publishing again.');
    }

    private function createOrganizationWithMembers(User $owner, User $follower): Organization
    {
        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Perth Makers',
            'slug' => 'perth-makers',
            'summary' => 'Shared workshops',
            'description' => 'Community build nights',
            'visibility' => 'public',
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by_user_id' => $owner->id,
        ]);

        $organization->members()->attach($owner->id, ['role' => 'owner']);
        $organization->members()->attach($follower->id, ['role' => 'follower']);

        return $organization;
    }
}
