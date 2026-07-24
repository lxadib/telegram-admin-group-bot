<?php

declare(strict_types=1);

namespace Platform\Adapters\Queue\Redis;

use Platform\Contracts\Clock\ClockInterface;
use Platform\Contracts\Queue\JobBusInterface;
use Platform\Contracts\Queue\JobInterface;
use Predis\Client;

/**
 * Redis-backed job bus. Ready jobs live on a per-queue list; delayed jobs live on
 * a sorted set keyed by availability timestamp until a consumer promotes them.
 */
final class RedisJobBus implements JobBusInterface
{
    public function __construct(
        private readonly Client $client,
        private readonly ClockInterface $clock,
        private readonly string $prefix = 'queue:',
    ) {
    }

    public function dispatch(JobInterface $job): void
    {
        $envelope = JobEnvelope::encode($job);

        if ($job->delaySeconds() > 0) {
            $availableAt = $this->clock->now()->getTimestamp() + $job->delaySeconds();
            $this->client->zadd($this->delayedKey($job->queue()), [$envelope => $availableAt]);

            return;
        }

        $this->client->rpush($this->readyKey($job->queue()), [$envelope]);
    }

    public function dispatchAll(iterable $jobs): void
    {
        foreach ($jobs as $job) {
            $this->dispatch($job);
        }
    }

    public function readyKey(string $queue): string
    {
        return $this->prefix . $queue . ':ready';
    }

    public function delayedKey(string $queue): string
    {
        return $this->prefix . $queue . ':delayed';
    }

    public function deadLetterKey(string $queue): string
    {
        return $this->prefix . $queue . ':dead';
    }
}
