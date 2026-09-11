<?php

namespace App\Services\RickAndMorty\Support;

class ExternalId
{
    public static function fromUrl(?string $url)
    {
        if (!$url || !preg_match('#/(\d+)/?$#', $url, $matches))
            {
                return null;
            }
        return (int) $matches[1];
    }
}
