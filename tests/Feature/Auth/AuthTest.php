<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Rick Sanchez',
            'email' => 'rick@citadel.test',
            'password' => 'wubbalubba123',
            'password_confirmation' => 'wubbalubba123',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.user.email', 'rick@citadel.test');
        $this->assertNotEmpty($response->json('data.token'));
        $this->assertDatabaseHas('users', ['email' => 'rick@citadel.test']);
    }

    public function test_registration_fails_with_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'rick@citadel.test']);

        $response = $this->postJson('/api/register', [
            'name' => 'Rick Sanchez',
            'email' => 'rick@citadel.test',
            'password' => 'wubbalubba123',
            'password_confirmation' => 'wubbalubba123',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('email');
    }

    public function test_a_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create(['password' => Hash::make('wubbalubba123')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wubbalubba123',
        ]);

        $response->assertOk();
        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_login_fails_with_an_incorrect_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('wubbalubba123')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('email');
    }

    public function test_a_guest_cannot_access_a_protected_route(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_an_authenticated_user_can_access_a_protected_route(): void
    {
        $user = User::factory()->create();
        [, $token] = $user->issueToken('test');

        $response = $this->withToken($token)->getJson('/api/me');

        $response->assertOk();
        $response->assertJsonPath('data.id', $user->id);
    }

    public function test_a_request_with_an_invalid_token_is_rejected(): void
    {
        $this->withToken('not-a-real-token')->getJson('/api/me')->assertUnauthorized();
    }

    public function test_a_request_with_an_expired_token_is_rejected(): void
    {
        $user = User::factory()->create();
        [, $token] = $user->issueToken('test', now()->subMinute());

        $this->withToken($token)->getJson('/api/me')->assertUnauthorized();
    }

    public function test_logout_revokes_the_token(): void
    {
        $user = User::factory()->create();
        [, $token] = $user->issueToken('test');

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->withToken($token)->getJson('/api/me')->assertUnauthorized();
    }
}
