<?php

namespace App\Services\RickAndMorty\DTOs;

final readonly class PageResult
{
    public function __construct(
        public array $items,
        public array $rawItems,
        public int $currentPage,
        public int $totalPages,
        public int $totalCount,
        public bool $hasMorePages,
    ) {
    }
}
