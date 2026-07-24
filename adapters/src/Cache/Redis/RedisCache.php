<?php

declare(strict_types=1);

namespace Platform\Adapters\Cache\Redis;

use Predis\Client;
use Psr\SimpleCache\CacheInterface;

/**
 * PSR-16 cache over Redis. Values are JSON-serialized under a key prefix.
 */
final class RedisCache implements CacheInterface
{
    public function __construct(
        private readonly Client $client,
        private readonly string $prefix = 'cache:',
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->assertKey($key);
        $raw = $this->client->get($this->prefix . $key);

        if (!is_string($raw)) {
            return $default;
        }

        return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    }

    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        $this->assertKey($key);
        $payload = json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $seconds = $this->ttlToSeconds($ttl);
        $prefixed = $this->prefix . $key;

        if ($seconds === null) {
            $this->client->set($prefixed, $payload);
        } else {
            if ($seconds <= 0) {
                $this->client->del($prefixed);

                return true;
            }
            $this->client->setex($prefixed, $seconds, $payload);
        }

        return true;
    }

    public function delete(string $key): bool
    {
        $this->assertKey($key);
        $this->client->del($this->prefix . $key);

        return true;
    }

    public function clear(): bool
    {
        $this->client->flushdb();

        return true;
    }

    /**
     * @param iterable<string> $keys
     * @return iterable<string, mixed>
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * @param iterable<mixed, mixed> $values
     */
    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set((string) $key, $value, $ttl);
        }

        return true;
    }

    /**
     * @param iterable<string> $keys
     */
    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    public function has(string $key): bool
    {
        $this->assertKey($key);

        return $this->client->exists($this->prefix . $key) > 0;
    }

    private function ttlToSeconds(\DateInterval|int|null $ttl): ?int
    {
        if ($ttl === null) {
            return null;
        }

        if (is_int($ttl)) {
            return $ttl;
        }

        $reference = new \DateTimeImmutable();

        return $reference->add($ttl)->getTimestamp() - $reference->getTimestamp();
    }

    private function assertKey(string $key): void
    {
        if ($key === '' || preg_match('#[{}()/\\\\@:]#', $key) === 1) {
            throw new InvalidCacheKey(sprintf('Invalid PSR-16 cache key "%s".', $key));
        }
    }
}
