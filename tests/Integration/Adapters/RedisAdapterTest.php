<?php

declare(strict_types=1);

namespace Platform\Tests\Integration\Adapters;

use PHPUnit\Framework\TestCase;
use Platform\Adapters\Cache\Redis\RedisCache;
use Platform\Adapters\Cache\Redis\RedisConnectionFactory;
use Platform\Adapters\Cache\Redis\RedisIdempotencyStore;
use Platform\Adapters\Cache\Redis\RedisRateLimiter;
use Platform\Adapters\Queue\Redis\RedisJobBus;
use Platform\Adapters\Queue\Redis\RedisJobConsumer;
use Platform\Kernel\Clock\SystemClock;
use Platform\Tests\Integration\Adapters\Fixtures\RecordingJob;
use Platform\Tests\Integration\Adapters\Fixtures\RecordingJobHandler;
use Predis\Client;

/**
 * Exercises the Redis adapters against a live server.
 * Skips automatically when REDIS_URL is absent or unreachable.
 */
final class RedisAdapterTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $url = getenv('REDIS_URL');
        if (!is_string($url) || $url === '') {
            self::markTestSkipped('REDIS_URL not set; skipping Redis integration test.');
        }

        $this->client = RedisConnectionFactory::fromUrl($url);

        try {
            $this->client->flushdb();
        } catch (\Throwable $e) {
            self::markTestSkipped('Redis unreachable: ' . $e->getMessage());
        }
    }

    public function testCacheRoundTrip(): void
    {
        $cache = new RedisCache($this->client);

        self::assertNull($cache->get('missing'));
        $cache->set('user', ['id' => 7, 'name' => 'Ada']);

        self::assertTrue($cache->has('user'));
        self::assertSame(['id' => 7, 'name' => 'Ada'], $cache->get('user'));

        $cache->delete('user');
        self::assertFalse($cache->has('user'));
    }

    public function testRateLimiterFixedWindow(): void
    {
        $limiter = new RedisRateLimiter($this->client);

        self::assertTrue($limiter->attempt('chat:1', 2, 60));
        self::assertTrue($limiter->attempt('chat:1', 2, 60));
        self::assertFalse($limiter->attempt('chat:1', 2, 60));
        self::assertSame(0, $limiter->remaining('chat:1', 2, 60));

        $limiter->reset('chat:1');
        self::assertTrue($limiter->attempt('chat:1', 2, 60));
    }

    public function testIdempotencyStoreClaimsOnce(): void
    {
        $store = new RedisIdempotencyStore($this->client);

        self::assertTrue($store->claim('op:1', 30));
        self::assertFalse($store->claim('op:1', 30));
    }

    public function testJobBusDispatchAndConsume(): void
    {
        $clock = new SystemClock();
        $bus = new RedisJobBus($this->client, $clock);
        $handler = new RecordingJobHandler();

        $consumer = new RedisJobConsumer($this->client, $bus, $clock);
        $consumer->register($handler);

        $bus->dispatch(new RecordingJob(['x' => 1]));
        $bus->dispatch(new RecordingJob(['x' => 2]));

        self::assertSame(2, $consumer->drain('default'));
        self::assertSame([['x' => 1], ['x' => 2]], $handler->handled);
    }

    public function testJobRetriesThenSucceeds(): void
    {
        $clock = new SystemClock();
        $bus = new RedisJobBus($this->client, $clock);
        $handler = new RecordingJobHandler(failFirst: true);

        $consumer = new RedisJobConsumer($this->client, $bus, $clock);
        $consumer->register($handler);

        $bus->dispatch(new RecordingJob(['x' => 42]));

        // First pass fails and requeues; second pass succeeds.
        $consumer->drain('default');

        self::assertSame([['x' => 42]], $handler->handled);
    }
}
