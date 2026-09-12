<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\Episode;
use App\Models\Location;
use App\Models\RawExternalPayload;
use App\Services\RickAndMorty\DTOs\CharacterData;
use App\Services\RickAndMorty\DTOs\EpisodeData;
use App\Services\RickAndMorty\DTOs\LocationData;
use App\Services\RickAndMorty\Exceptions\RickAndMortyApiException;
use App\Services\RickAndMorty\Exceptions\RickAndMortyValidationException;
use App\Services\RickAndMorty\RickAndMortyClientInterface;
use Closure;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncRickAndMortyData extends Command
{

    protected $signature = 'rickandmorty:sync';

    protected $description = 'Sync locations, episodes and characters from the Rick and Morty API into the local database.';

    private int $synced = 0;

    private int $failed = 0;

    public function __construct(private readonly RickAndMortyClientInterface $client)
    {
        parent::__construct();
    }

    public function handle()
    {
        // Location and episodes must exist before characters, which reference them.
        $this->components->info('Syncing locations...');
        $this->syncEntity('location', fn(int $page) => $this->client->locations($page), $this->storeLocation(...));

        $this->components->info('Syncing episodes...');
        $this->syncEntity('episode', fn(int $page) => $this->client->episodes($page), $this->storeEpisodes(...));

        $this->components->info('Syncing characters...');
        $this->syncEntity('character', fn(int $page) => $this->client->characters($page), $this->storeCharacter(...));

        $this->components->info("Done. Synced: {$this->synced}. Failed: {$this->failed}.");

        return $this->failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function syncEntity(string $entityType, Closure $fetch, Closure $store): void
    {
        $page = 1;
        $hasMorePage = true;

        while ($hasMorePage) {
            try {
                $result = $fetch($page);
            } catch (RickAndMortyApiException | RickAndMortyValidationException $exception) {
                $this->failed++;
                $this->components->error("Failed to fetch {$entityType} page {$page}: {$exception->getMessage()}");
                Log::warning('rick-and-morty-sync: failed to fetch page', [
                    'entity_type' => $entityType,
                    'page' => $page,
                    'exception' => $exception->getMessage(),
                ]);
                return;
            }
            foreach ($result->items as $index => $dto) {
                try {
                    $store($dto, $result->rawItems[$index]);
                    $this->synced++;
                } catch (\Throwable $exception) {
                    $this->failed++;
                    Log::warning('rick-and-morty-sync: failed to store record', [
                        'entity_type' => $entityType,
                        'external_id' => $dto->externalId,
                        'exception' => $exception->getMessage(),
                    ]);
                }
            }
            $hasMorePage = $result->hasMorePages;
            $page++;
        }
    }

    private function storeLocation(LocationData $dto, array $raw): void
    {
        $this->storeRawPayload('location', $dto->externalId, $raw);

        Location::updateOrCreate(
            ['external_id' => $dto->externalId],
            [
                'name' => $dto->name,
                'type' => $dto->type,
                'dimension' => $dto->dimension,
            ],
        );
    }

    private function storeEpisodes(EpisodeData $dto, array $raw): void
    {
        $this->storeRawPayload('episode', $dto->externalId, $raw);

        Episode::updateOrCreate(
            ['external_id' => $dto->externalId],
            [
                'name' => $dto->name,
                'air_date' => $dto->airDate,
                'episode_code' => $dto->episodeCode,
            ]
        );
    }

    private function storeCharacter(CharacterData $dto, array $raw): void
    {
        $this->storeRawPayload('character', $dto->externalId, $raw);

        $originalId = $dto->externalId
            ? Location::where('external_id', $dto->originExternalId)->value('id')
            : null;

        $currentLocationId = $dto->currentExternalId
            ? Location::where('external_id', $dto->currentExternalId)->value('id')
            : null;

        $character = Character::updateOrCreate(
            ['external_id' => $dto->externalId],
            [
                'name' => $dto->name,
                'status' => $dto->status,
                'species' => $dto->species,
                'type' => $dto->type,
                'gender' => $dto->gender,
                'image' => $dto->image,
                'origin_location_id' => $originalId,
                'current_location_id' => $currentLocationId,
            ]
        );
        $episodesIds = Episode::whereIn('external_id', $dto->episodeExternalIds)->pluck('id');
        $character->episodes()->sync($episodesIds);

    }

    private function storeRawPayload(string $entityType, int $externalId, array $payload): void
    {
        RawExternalPayload::updateOrCreate(
            ['entity_type' => $entityType, 'external_id' => $externalId],
            ['payload' => $payload, 'fetched_at' => now()]
        );
    }
}
