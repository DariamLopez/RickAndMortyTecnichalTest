<?php

namespace Tests\Feature\Api;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_cannot_manage_favorites(): void
    {
        $character = Character::factory()->create();

        $this->getJson('/api/favorites')->assertUnauthorized();
        $this->postJson("/api/favorites/{$character->id}")->assertUnauthorized();
    }

    public function test_an_authenticated_user_can_add_and_list_a_favorite(): void
    {
        $user = User::factory()->create();
        [, $token] = $user->issueToken('test');
        $character = Character::factory()->create();

        $this->withToken($token)
            ->postJson("/api/favorites/{$character->id}")
            ->assertCreated();

        $response = $this->withToken($token)->getJson('/api/favorites');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $character->id);
    }

    public function test_adding_the_same_favorite_twice_does_not_duplicate_it(): void
    {
        $user = User::factory()->create();
        [, $token] = $user->issueToken('test');
        $character = Character::factory()->create();

        $this->withToken($token)->postJson("/api/favorites/{$character->id}")->assertCreated();
        $this->withToken($token)->postJson("/api/favorites/{$character->id}")->assertCreated();

        $this->assertDatabaseCount('favorites', 1);
    }

    public function test_an_authenticated_user_can_remove_a_favorite(): void
    {
        $user = User::factory()->create();
        [, $token] = $user->issueToken('test');
        $character = Character::factory()->create();

        $user->favoriteCharacters()->attach($character);

        $this->withToken($token)
            ->deleteJson("/api/favorites/{$character->id}")
            ->assertNoContent();

        $this->assertDatabaseCount('favorites', 0);
    }
}
