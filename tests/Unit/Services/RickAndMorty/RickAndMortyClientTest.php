<?php

namespace Tests\Unit\Services\RickAndMorty;

use App\Services\RickAndMorty\Exceptions\RickAndMortyApiException;
use App\Services\RickAndMorty\Exceptions\RickAndMortyValidationException;
use App\Services\RickAndMorty\RickAndMortyClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RickAndMortyClientTest extends TestCase
{
    private function client(): RickAndMortyClient
    {
        return new RickAndMortyClient(
            baseUri: 'https://rickandmortyapi.com/api',
            timeout: 5,
            retryTimes: 2,
            retryDelayMs: 1,
        );
    }

    public function test_characters_are_mapped_into_dtos(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/character*' => Http::response([
                'info' => ['count' => 2, 'pages' => 1, 'next' => null, 'prev' => null],
                'results' => [
                    [
                        'id' => 1,
                        'name' => 'Rick Sanchez',
                        'status' => 'Alive',
                        'species' => 'Human',
                        'type' => '',
                        'gender' => 'Male',
                        'image' => 'https://rickandmortyapi.com/api/character/avatar/1.jpeg',
                        'origin' => ['name' => 'Earth', 'url' => 'https://rickandmortyapi.com/api/location/1'],
                        'location' => ['name' => 'Earth', 'url' => 'https://rickandmortyapi.com/api/location/20'],
                        'episode' => [
                            'https://rickandmortyapi.com/api/episode/1',
                            'https://rickandmortyapi.com/api/episode/2',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $page = $this->client()->characters();
        dump($page);
        $this->assertCount(1, $page->items);
        $this->assertFalse($page->hasMorePages);

        $character = $page->items[0];
        $this->assertSame(1, $character->externalId);
        $this->assertSame('Rick Sanchez', $character->name);
        $this->assertSame(1, $character->originExternalId);
        $this->assertSame(20, $character->currentExternalId);
        $this->assertSame([1, 2], $character->episodeExternalIds);
    }

    public function test_it_reports_more_pages_when_the_api_has_a_next_link(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/character*' => Http::response([
                'info' => ['count' => 826, 'pages' => 42, 'next' => 'https://rickandmortyapi.com/api/character?page=2', 'prev' => null],
                'results' => [],
            ], 200),
        ]);

        $page = $this->client()->characters();

        $this->assertTrue($page->hasMorePages);
        $this->assertSame(42, $page->totalPages);
    }

    public function test_it_defaults_missing_or_unknown_relations_to_null(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/character*' => Http::response([
                'info' => ['count' => 1, 'pages' => 1, 'next' => null, 'prev' => null],
                'results' => [
                    [
                        'id' => 2,
                        'name' => 'Morty',
                        'status' => 'unknown',
                        'species' => 'Human',
                        'gender' => 'Male',
                        'origin' => ['name' => 'unknown', 'url' => ''],
                        'location' => ['name' => 'unknown', 'url' => ''],
                        'episode' => [],
                    ],
                ],
            ], 200),
        ]);

        $character = $this->client()->characters()->items[0];

        $this->assertNull($character->originExternalId);
        $this->assertNull($character->currentExternalId);
        $this->assertSame('', $character->type);
    }

    public function test_it_throws_an_api_exception_on_connection_failure(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection timed out');
        });

        $this->expectException(RickAndMortyApiException::class);

        $this->client()->characters();
    }

    public function test_it_throws_an_api_exception_after_exhausting_retries_on_server_error(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/character*' => Http::response('Internal Server Error', 500),
        ]);

        $this->expectException(RickAndMortyApiException::class);

        $this->client()->characters();

        Http::assertSentCount(3); // initial attempt + 2 retries
    }

    public function test_it_does_not_retry_on_client_errors(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/character*' => Http::response(['error' => 'Not found'], 404),
        ]);

        $this->expectException(RickAndMortyApiException::class);

        $this->client()->characters();

        Http::assertSentCount(1); // no retries on a 4xx
    }

    public function test_it_throws_a_validation_exception_on_malformed_response(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/character*' => Http::response(['unexpected' => 'shape'], 200),
        ]);

        $this->expectException(RickAndMortyValidationException::class);

        $this->client()->characters();
    }

    public function test_episodes_are_mapped_into_dtos(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/episode*' => Http::response([
                'info' => ['count' => 1, 'pages' => 1, 'next' => null, 'prev' => null],
                'results' => [
                    ['id' => 1, 'name' => 'Pilot', 'air_date' => 'December 2, 2013', 'episode' => 'S01E01'],
                ],
            ], 200),
        ]);

        $episode = $this->client()->episodes()->items[0];

        $this->assertSame('S01E01', $episode->episodeCode);
        $this->assertSame('Pilot', $episode->name);
    }

    public function test_locations_are_mapped_into_dtos(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/location*' => Http::response([
                'info' => ['count' => 1, 'pages' => 1, 'next' => null, 'prev' => null],
                'results' => [
                    ['id' => 3, 'name' => 'Citadel of Ricks', 'type' => 'Space station', 'dimension' => 'unknown'],
                ],
            ], 200),
        ]);

        $location = $this->client()->locations()->items[0];

        $this->assertSame(3, $location->externalId);
        $this->assertSame('Citadel of Ricks', $location->name);
    }
}
