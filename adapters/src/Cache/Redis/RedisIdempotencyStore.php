<?php

declare(strict_types=1);

namespace Platform\Adapters\Cache\Redis;

use Platform\Contracts\Security\IdempotencyStoreInterface;
use Predis\Client;

/**
 * Distributed idempotency guard using SET key value NX EX ttl.
 */
final class RedisIdempotencyStore implements IdempotencyStoreInterface
{
    public function __construct(
        private readonly Client $client,
        private readonly string $prefix = 'idem:',
    ) {
    }

    public function claim(string $key, int $ttlSeconds): bool
    {
        $ttl = max(1, $ttlSeconds);
        $response = $this->client->set($this->prefix . $key, '1', 'EX', $ttl, 'NX');

        return (string) $response === 'OK';
    }
}
