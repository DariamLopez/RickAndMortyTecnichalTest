<?php

namespace App\Services\RickAndMorty\DTOs;

use App\Services\RickAndMorty\Support\ExternalId;

final readonly class CharacterData
{
    public function __construct(
        public int $externalId,
        public string $name,
        public string $status,
        public string $species,
        public string $type,
        public string $gender,
        public string $image,
        public ?int $originExternalId,
        public ?int $currentExternalId,
        public array $episodeExternalIds,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            externalId: (int) $data['id'] ?? 0,
            name: (string) ($data['name'] ?? 'unknown'),
            status: (string) ($data['status'] ?? 'unknown'),
            species: (string) ($data['species'] ?? 'unknown'),
            type: (string) ($data['type'] ?? ''),
            gender: (string) ($data['gender'] ?? 'unknown'),
            image: (string) ($data['image'] ?? ''),
            originExternalId: ExternalId::fromUrl($data['origin']['url'] ?? null),
            currentExternalId: ExternalId::fromUrl($data['location']['url'] ?? null),
            episodeExternalIds: array_values(array_filter(array_map(
                static fn($url) => ExternalId::fromUrl($url),
                $data['episode'] ?? [],
            ))),
        );
    }
}
