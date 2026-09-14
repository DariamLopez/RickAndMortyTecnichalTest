<?php

namespace App\Services\RickAndMorty\DTOs;

use App\Services\RickAndMorty\Exceptions\RickAndMortyValidationException;

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
        if (! isset($data['id']) || ! is_numeric($data['id'])) {
            throw new RickAndMortyValidationException('Location item is missing a valid "id" field.');
        }

        return new self(
            externalId: (int) $data['id'],
            name: (string) ($data['name'] ?? 'unknown'),
            type: (string) ($data['type'] ?? 'unknown'),
            dimension: (string) ($data['dimension'] ?? 'unknown'),
        );
    }
}
