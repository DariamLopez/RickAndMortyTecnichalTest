<?php

namespace App\Services\RickAndMorty\DTOs;

use App\Services\RickAndMorty\Exceptions\RickAndMortyValidationException;

final readonly class EpisodeData
{
    public function __construct(
        public int $externalId,
        public string $name,
        public string $airDate,
        public string $episodeCode,
    ) {
    }

    public static function fromArray(array $data): self
    {
        if (! isset($data['id']) || ! is_numeric($data['id'])) {
            throw new RickAndMortyValidationException('Episode item is missing a valid "id" field.');
        }

        return new self(
            externalId: (int) $data['id'],
            name: (string) ($data['name'] ?? 'unknown'),
            airDate: (string) ($data['air_date'] ?? ''),
            episodeCode: (string) ($data['episode'] ?? ''),
        );
    }
}
