<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
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
}
