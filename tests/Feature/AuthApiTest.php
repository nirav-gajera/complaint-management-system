<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_bootstrap_user_endpoint_returns_null_user(): void
    {
        $this->getJson('/api/auth/user')
            ->assertOk()
            ->assertJson(['user' => null]);
    }

    public function test_user_can_register_via_api(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '1234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.email', 'jane@example.com');

        Notification::assertSentTo(User::first(), VerifyEmail::class);
    }

    public function test_user_can_login_via_api(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.email', 'login@example.com');
    }

    public function test_authenticated_user_can_logout_via_api(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/auth/logout');

        $response->assertOk()
            ->assertJson(['message' => 'Logout successful.']);
    }

    public function test_authenticated_user_can_update_profile_via_api(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patchJson('/api/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '9999999999',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.email', 'updated@example.com');
    }

    public function test_authenticated_user_cannot_update_profile_with_missing_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patchJson('/api/profile', [
            'name' => null,
            'email' => null,
            'phone' => '9999999999',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_authenticated_user_can_update_password_via_api(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/profile/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Password updated successfully.']);

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_authenticated_user_cannot_update_password_with_incorrect_current_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/profile/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    }
}
