<?php

declare(strict_types=1);

namespace Platform\Adapters\Cache\Redis;

use Platform\Contracts\Security\RateLimiterInterface;
use Predis\Client;

/**
 * Distributed fixed-window rate limiter. Increment + expire happen atomically
 * via a Lua script so concurrent workers observe a consistent counter.
 */
final class RedisRateLimiter implements RateLimiterInterface
{
    private const INCR_SCRIPT = <<<'LUA'
        local current = redis.call('INCR', KEYS[1])
        if current == 1 then
            redis.call('EXPIRE', KEYS[1], ARGV[1])
        end
        return current
        LUA;

    public function __construct(
        private readonly Client $client,
        private readonly string $prefix = 'rl:',
    ) {
    }

    public function attempt(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $count = (int) $this->client->eval(self::INCR_SCRIPT, 1, $this->prefix . $key, (string) $windowSeconds);

        return $count <= $maxAttempts;
    }

    public function remaining(string $key, int $maxAttempts, int $windowSeconds): int
    {
        $raw = $this->client->get($this->prefix . $key);
        $used = is_string($raw) ? (int) $raw : 0;

        return max(0, $maxAttempts - $used);
    }

    public function reset(string $key): void
    {
        $this->client->del($this->prefix . $key);
    }
}
