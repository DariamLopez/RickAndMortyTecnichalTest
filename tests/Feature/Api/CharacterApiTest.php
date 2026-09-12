<?php

namespace Tests\Feature\Api;

use App\Models\Character;
use App\Models\Episode;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacterApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_characters_with_pagination(): void
    {
        Character::factory()->count(3)->create();

        $response = $this->getJson('/api/characters?per_page=2');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('meta.total', 3);
    }

    public function test_it_filters_characters_by_status(): void
    {
        Character::factory()->create(['name' => 'Rick Sanchez', 'status' => 'Alive']);
        Character::factory()->create(['name' => 'Some Dead Guy', 'status' => 'Dead']);

        $response = $this->getJson('/api/characters?status=Alive');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Rick Sanchez');
    }

    public function test_it_filters_characters_by_name(): void
    {
        Character::factory()->create(['name' => 'Rick Sanchez']);
        Character::factory()->create(['name' => 'Morty Smith']);

        $response = $this->getJson('/api/characters?name=rick');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_it_rejects_an_invalid_status_filter(): void
    {
        $response = $this->getJson('/api/characters?status=not-a-real-status');

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('status');
    }

    public function test_it_shows_a_character_with_its_episodes_and_locations(): void
    {
        $origin = Location::factory()->create(['name' => 'Earth (C-137)']);
        $current = Location::factory()->create(['name' => 'Citadel of Ricks']);
        $episode = Episode::factory()->create(['episode_code' => 'S01E01']);

        $character = Character::factory()->create([
            'origin_location_id' => $origin->id,
            'current_location_id' => $current->id,
        ]);
        $character->episodes()->attach($episode);

        $response = $this->getJson("/api/characters/{$character->id}");

        $response->assertOk();
        $response->assertJsonPath('data.origin.name', 'Earth (C-137)');
        $response->assertJsonPath('data.current_location.name', 'Citadel of Ricks');
        $response->assertJsonPath('data.episodes.0.episode_code', 'S01E01');
    }

    public function test_it_returns_a_homogeneous_404_for_a_nonexistent_character(): void
    {
        $response = $this->getJson('/api/characters/999999');

        $response->assertNotFound();
        $response->assertJsonPath('message', 'Resource not found.');
    }
}
