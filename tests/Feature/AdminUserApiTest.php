<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminUserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_users_with_filters(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $matchingUser = User::factory()->create([
            'name' => 'Support Match',
            'role' => User::ROLE_SUPPORT_AGENT,
            'is_active' => true,
        ]);

        User::factory()->create([
            'name' => 'Inactive User',
            'role' => User::ROLE_USER,
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->getJson('/api/admin/users?search=Support&role=support_agent&is_active=true');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingUser->id);
    }

    public function test_admin_can_update_user_role_and_status(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->patchJson("/api/admin/users/{$user->id}", [
            'role' => User::ROLE_MANAGER,
            'is_active' => false,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.role', User::ROLE_MANAGER)
            ->assertJsonPath('data.is_active', false);
    }

    public function test_admin_can_trigger_password_reset_link(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);
        $user = User::factory()->create();

        $this->actingAs($admin)->postJson("/api/admin/users/{$user->id}/reset-password")
            ->assertOk();

        Notification::assertSentTo($user, ResetPassword::class);
    }
}