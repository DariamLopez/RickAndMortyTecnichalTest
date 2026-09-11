<?php

namespace App\Services\RickAndMorty\DTOs;

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
        return new self(
            externalId: (int) $data['id'] ?? 0,
            name: (string) ($data['name'] ?? 'unknown'),
            airDate: (string) ($data['air_date'] ?? ''),
            episodeCode: (string) ($data['episode'] ?? ''),
        );
    }
}
