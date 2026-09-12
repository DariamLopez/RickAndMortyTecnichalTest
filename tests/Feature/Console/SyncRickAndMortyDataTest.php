<?php

namespace Tests\Feature\Console;

use App\Models\Character;
use App\Models\RawExternalPayload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncRickAndMortyDataTest extends TestCase
{
    use RefreshDatabase;

    private function fakeExternalApi(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/location*' => Http::response([
                'info' => ['count' => 2, 'pages' => 1, 'next' => null, 'prev' => null],
                'results' => [
                    ['id' => 1, 'name' => 'Earth (C-137)', 'type' => 'Planet', 'dimension' => 'Dimension C-137'],
                    ['id' => 20, 'name' => 'Earth (Replacement Dimension)', 'type' => 'Planet', 'dimension' => 'Replacement Dimension'],
                ],
            ], 200),
            'rickandmortyapi.com/api/episode*' => Http::response([
                'info' => ['count' => 2, 'pages' => 1, 'next' => null, 'prev' => null],
                'results' => [
                    ['id' => 1, 'name' => 'Pilot', 'air_date' => 'December 2, 2013', 'episode' => 'S01E01'],
                    ['id' => 2, 'name' => 'Lawnmower Dog', 'air_date' => 'December 9, 2013', 'episode' => 'S01E02'],
                ],
            ], 200),
            'rickandmortyapi.com/api/character*' => Http::response([
                'info' => ['count' => 1, 'pages' => 1, 'next' => null, 'prev' => null],
                'results' => [
                    [
                        'id' => 1,
                        'name' => 'Rick Sanchez',
                        'status' => 'Alive',
                        'species' => 'Human',
                        'type' => '',
                        'gender' => 'Male',
                        'image' => 'https://rickandmortyapi.com/api/character/avatar/1.jpeg',
                        'origin' => ['name' => 'Earth (C-137)', 'url' => 'https://rickandmortyapi.com/api/location/1'],
                        'location' => ['name' => 'Earth (Replacement Dimension)', 'url' => 'https://rickandmortyapi.com/api/location/20'],
                        'episode' => [
                            'https://rickandmortyapi.com/api/episode/1',
                            'https://rickandmortyapi.com/api/episode/2',
                        ],
                    ],
                ],
            ], 200),
        ]);
    }

    public function test_it_syncs_locations_episodes_and_characters(): void
    {
        $this->fakeExternalApi();

        $this->artisan('rickandmorty:sync')->assertExitCode(0);

        $this->assertDatabaseCount('locations', 2);
        $this->assertDatabaseCount('episodes', 2);
        $this->assertDatabaseCount('characters', 1);

        $character = Character::first();
        $this->assertSame('Rick Sanchez', $character->name);
        $this->assertSame('Earth (C-137)', $character->origin->name);
        $this->assertSame('Earth (Replacement Dimension)', $character->current->name);
        $this->assertCount(2, $character->episodes);
    }

    public function test_running_it_twice_does_not_duplicate_records_or_relations(): void
    {
        $this->fakeExternalApi();

        $this->artisan('rickandmorty:sync')->assertExitCode(0);
        $this->artisan('rickandmorty:sync')->assertExitCode(0);

        $this->assertDatabaseCount('locations', 2);
        $this->assertDatabaseCount('episodes', 2);
        $this->assertDatabaseCount('characters', 1);
        $this->assertDatabaseCount('character_episode', 2);
    }

    public function test_it_stores_the_raw_payload_before_transforming(): void
    {
        $this->fakeExternalApi();

        $this->artisan('rickandmorty:sync')->assertExitCode(0);

        $this->assertDatabaseHas('raw_external_payloads', [
            'entity_type' => 'character',
            'external_id' => 1,
        ]);

        $payload = RawExternalPayload::where('entity_type', 'character')->first();
        $this->assertSame('Rick Sanchez', $payload->payload['name']);
    }

    public function test_it_continues_with_other_entities_when_one_fails(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/location*' => Http::response([
                'info' => ['count' => 1, 'pages' => 1, 'next' => null, 'prev' => null],
                'results' => [
                    ['id' => 1, 'name' => 'Earth (C-137)', 'type' => 'Planet', 'dimension' => 'Dimension C-137'],
                ],
            ], 200),
            'rickandmortyapi.com/api/episode*' => Http::response('Internal Server Error', 500),
            'rickandmortyapi.com/api/character*' => Http::response([
                'info' => ['count' => 1, 'pages' => 1, 'next' => null, 'prev' => null],
                'results' => [
                    [
                        'id' => 1,
                        'name' => 'Rick Sanchez',
                        'status' => 'Alive',
                        'species' => 'Human',
                        'gender' => 'Male',
                        'origin' => ['name' => 'unknown', 'url' => ''],
                        'location' => ['name' => 'unknown', 'url' => ''],
                        'episode' => [],
                    ],
                ],
            ], 200),
        ]);

        $this->artisan('rickandmorty:sync')->assertExitCode(1);

        // Episodes failed entirely, but locations and characters still synced.
        $this->assertDatabaseCount('locations', 1);
        $this->assertDatabaseCount('episodes', 0);
        $this->assertDatabaseCount('characters', 1);
    }

    public function test_it_follows_pagination_across_multiple_pages(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/location*' => Http::response([
                'info' => ['count' => 0, 'pages' => 1, 'next' => null, 'prev' => null],
                'results' => [],
            ], 200),
            'rickandmortyapi.com/api/episode*' => Http::response([
                'info' => ['count' => 0, 'pages' => 1, 'next' => null, 'prev' => null],
                'results' => [],
            ], 200),
            'rickandmortyapi.com/api/character*' => Http::sequence()
                ->push([
                    'info' => ['count' => 2, 'pages' => 2, 'next' => 'https://rickandmortyapi.com/api/character?page=2', 'prev' => null],
                    'results' => [
                        ['id' => 1, 'name' => 'Rick Sanchez', 'status' => 'Alive', 'species' => 'Human', 'gender' => 'Male', 'origin' => ['name' => 'unknown', 'url' => ''], 'location' => ['name' => 'unknown', 'url' => ''], 'episode' => []],
                    ],
                ], 200)
                ->push([
                    'info' => ['count' => 2, 'pages' => 2, 'next' => null, 'prev' => 'https://rickandmortyapi.com/api/character?page=1'],
                    'results' => [
                        ['id' => 2, 'name' => 'Morty Smith', 'status' => 'Alive', 'species' => 'Human', 'gender' => 'Male', 'origin' => ['name' => 'unknown', 'url' => ''], 'location' => ['name' => 'unknown', 'url' => ''], 'episode' => []],
                    ],
                ], 200),
        ]);

        $this->artisan('rickandmorty:sync')->assertExitCode(0);

        $this->assertDatabaseCount('characters', 2);
    }
}
