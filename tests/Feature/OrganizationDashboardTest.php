<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use App\Models\MailingList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrganizationDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_organizations_can_be_found_by_tag_from_organization_dashboard(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();

        $publicOrganization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Board Game Circle',
            'slug' => 'board-game-circle-'.Str::random(6),
            'summary' => 'Weekly tabletop events',
            'visibility' => 'public',
            'approval_status' => 'approved',
            'approved_at' => now(),
            'tags' => ['tabletop games', 'community'],
        ]);

        Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Private Board Group',
            'slug' => 'private-board-group-'.Str::random(6),
            'summary' => 'Hidden group',
            'visibility' => 'private',
            'approval_status' => 'approved',
            'approved_at' => now(),
            'tags' => ['tabletop games'],
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.organizations', ['tag' => 'tabletop games']))
            ->assertOk()
            ->assertSee($publicOrganization->name)
            ->assertDontSee('Private Board Group');
    }

    public function test_organization_creation_normalizes_tags(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('organizations.store'), [
            'name' => 'Music Makers',
            'summary' => 'Local music events',
            'visibility' => 'public',
            'tags' => ' Music, community, music ',
        ])->assertRedirect();

        $organization = Organization::query()->where('name', 'Music Makers')->firstOrFail();

        $this->assertSame(['music', 'community'], $organization->tags);
    }

    public function test_organization_creation_builds_default_mailing_list_for_owner(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('organizations.store'), [
            'name' => 'Garden Circle',
            'summary' => 'Local gardening group',
            'visibility' => 'public',
        ])->assertRedirect();

        $organization = Organization::query()->where('name', 'Garden Circle')->firstOrFail();
        $defaultList = $organization->defaultMailingList()->first();

        $this->assertNotNull($defaultList);
        $this->assertTrue((bool) $defaultList->is_default);
        $this->assertSame('Garden Circle updates', $defaultList->name);
        $this->assertTrue($user->fresh()->mailingLists->contains(fn (MailingList $list) => $list->id === $defaultList->id));
    }

    public function test_following_an_organization_subscribes_to_the_default_mailing_list(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Board Game Circle',
            'slug' => 'board-game-circle-'.Str::random(6),
            'summary' => 'Weekly tabletop events',
            'visibility' => 'public',
            'approval_status' => 'approved',
            'approved_at' => now(),
        ]);

        $organization->members()->attach($owner->id, ['role' => 'owner']);

        $this->actingAs($user)
            ->post(route('organizations.follow', $organization))
            ->assertRedirect(route('organizations.show', $organization));

        $defaultList = $organization->defaultMailingList()->firstOrFail();

        $this->assertTrue($user->fresh()->mailingLists->contains(fn (MailingList $list) => $list->id === $defaultList->id));
    }
}
