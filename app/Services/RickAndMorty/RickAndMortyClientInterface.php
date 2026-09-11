<?php

namespace App\Services\RickAndMorty;

use App\Services\RickAndMorty\DTOs\PageResult;

interface RickAndMortyClientInterface
{
    public function characters(int $page = 1): PageResult;

    public function episodes(int $page = 1): PageResult;

    public function locations(int $page = 1): PageResult;
}
