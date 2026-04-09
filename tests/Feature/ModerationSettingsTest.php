<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModerationSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_moderated_user_registration_creates_a_pending_account(): void
    {
        SiteSetting::setValue('user_registration_mode', 'moderated');

        $response = $this->post('/register', [
            'name' => 'Pending User',
            'email' => 'pending@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'accepted_usage_terms' => '1',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'email' => 'pending@example.com',
            'registration_status' => 'pending',
        ]);
    }

    public function test_pending_users_cannot_log_in_until_an_admin_approves_them(): void
    {
        $user = User::factory()->create([
            'registration_status' => 'pending',
            'approved_at' => null,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.users.approve', $user))
            ->assertRedirect();

        $this->post('/logout');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_moderated_organization_creation_stays_pending_until_approved(): void
    {
        SiteSetting::setValue('organization_registration_mode', 'moderated');

        $owner = User::factory()->create();

        $this->actingAs($owner)->post(route('organizations.store'), [
            'name' => 'Pending Org',
            'summary' => 'Awaiting review',
            'description' => 'Needs approval first',
            'visibility' => 'public',
        ])->assertRedirect();

        $organization = Organization::query()->firstOrFail();

        $this->assertSame('pending', $organization->approval_status);

        $this->get(route('organizations.show', $organization))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('organizations.show', $organization))->assertForbidden();

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.organizations.approve', $organization))
            ->assertRedirect();

        $this->get(route('organizations.show', $organization->fresh()))->assertOk();
    }

    public function test_organization_creation_stores_the_selected_theme(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->post(route('organizations.store'), [
            'name' => 'Theme Guild',
            'summary' => 'Theme test',
            'description' => 'Theme details',
            'visibility' => 'public',
            'theme_key' => 'midnight',
        ])->assertRedirect();

        $this->assertDatabaseHas('organizations', [
            'name' => 'Theme Guild',
            'theme_key' => 'midnight',
        ]);
    }

    public function test_suspended_users_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'registration_status' => 'suspended',
            'approved_at' => null,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_suspended_organizations_are_hidden_from_public_view(): void
    {
        $owner = User::factory()->create();

        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Suspended Org',
            'slug' => 'suspended-org',
            'summary' => 'No longer public',
            'description' => 'Hidden after moderation',
            'visibility' => 'public',
            'approval_status' => 'suspended',
            'approved_at' => null,
        ]);

        $organization->members()->attach($owner->id, ['role' => 'owner']);

        $this->get(route('organizations.show', $organization))->assertForbidden();
        $this->actingAs($owner)->get(route('organizations.show', $organization))->assertOk();
    }
}
