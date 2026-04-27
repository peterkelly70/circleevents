<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminImpersonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_impersonation_logs_in_as_the_target_user(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.impersonate'), [
            'user_id' => $user->id,
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
        $response->assertSessionHas('impersonator_user_id', $admin->id);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('You are viewing this account as another user')
            ->assertDontSee('Admin moderation');
    }

    public function test_logging_out_ends_the_impersonated_session(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.impersonate'), [
            'user_id' => $user->id,
        ]);

        $response = $this->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
