<?php

declare(strict_types=1);

namespace Platform\Adapters\Cache\Redis;

use Predis\Client;

/**
 * Creates configured Predis clients from a redis:// URL.
 */
final class RedisConnectionFactory
{
    public static function fromUrl(string $url): Client
    {
        return new Client($url);
    }

    /**
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     */
    public static function fromParameters(array $parameters, array $options = []): Client
    {
        return new Client($parameters, $options);
    }
}
