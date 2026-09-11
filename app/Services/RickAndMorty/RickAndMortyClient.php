<?php

namespace App\Services\RickAndMorty;

use App\Services\RickAndMorty\DTOs\CharacterData;
use App\Services\RickAndMorty\DTOs\EpisodeData;
use App\Services\RickAndMorty\DTOs\LocationData;
use App\Services\RickAndMorty\DTOs\PageResult;
use App\Services\RickAndMorty\Exceptions\RickAndMortyApiException;
use App\Services\RickAndMorty\Exceptions\RickAndMortyValidationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Throwable;

final class RickAndMortyClient implements RickAndMortyClientInterface
{
    public function __construct(
        private readonly string $baseUri,
        private readonly int $timeout = 5,
        private readonly int $retryTimes = 3,
        private readonly int $retryDelayMs = 200,
    ) {}

    public function characters(int $page = 1): PageResult
    {
        return $this->fetchPage('character', $page, CharacterData::class);
    }

    public function episodes(int $page = 1): PageResult
    {
        return $this->fetchPage('episode', $page, EpisodeData::class);
    }

    public function locations(int $page = 1): PageResult
    {
        return $this->fetchPage('location', $page, LocationData::class);
    }

    private function fetchPage(string $endpoint, int $page, string $dtoClass): PageResult
    {
        $data = $this->request($endpoint, $page);

        $items = array_map(
            static fn(array $item) => $dtoClass::fromArray($item),
            $data['results']
        );

        $info = $data['info'];

        return new PageResult(
            items: $items,
            currentPage: $page,
            totalPages: (int) ($info['pages'] ?? $page),
            totalCount: (int) ($info['count'] ?? count($items)),
            hasMorePages: !empty($info['next']),
        );
    }

    private function request(string $endpoint, int $page): array
    {
        try {
            $url = $this->baseUri . '/' . $endpoint . '?page=' . $page;
            $response = Http::baseUrl($this->baseUri)
                ->timeout($this->timeout)
                ->retry($this->retryTimes, $this->retryDelayMs, function (Throwable $exception) {
                    return $exception instanceof ConnectionException
                        || ($exception instanceof RequestException && $exception->response->serverError());
                })
                ->throw()
                ->get($endpoint, ['page' => $page]);
        } catch (ConnectionException $exception) {
            throw new RickAndMortyApiException(
                "Could not connect to the Rick and Morty API ({$endpoint}, page {$page}).",
                previous: $exception,
            );
        } catch (RequestException $exception) {
            $status = $exception->response->status();

            throw new RickAndMortyApiException(
                "The Rick and Morty API responded with status {$status} on {$endpoint}, page {$page}.",
                previous: $exception,
            );
        }
        $data = $response->json();
        if (
            ! is_array($data)
            || ! isset($data['results'], $data['info'])
            || ! is_array($data['results'])
            || ! is_array($data['info'])
        ) {
            throw new RickAndMortyValidationException(
                "Unexpected response shape from the Rick and Morty API on {$endpoint}, page {$page}.",
            );
        }

        return $data;
    }
}
