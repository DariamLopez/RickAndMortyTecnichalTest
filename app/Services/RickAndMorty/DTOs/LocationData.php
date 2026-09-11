<?php

namespace App\Services\RickAndMorty\DTOs;


final readonly class LocationData
{
    public function __construct(
        public int $externalId,
        public string $name,
        public string $type,
        public string $dimension,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            externalId: (int) $data['id'] ?? 0,
            name: (string) $data['name'] ?? 'unknown',
            type: (string) $data['type'] ?? 'unknown',
            dimension: (string) $data['dimension'] ?? 'unknown',
        );
    }
}
