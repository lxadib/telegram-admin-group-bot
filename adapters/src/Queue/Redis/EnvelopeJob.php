<?php

declare(strict_types=1);

namespace Platform\Adapters\Queue\Redis;

use Platform\Contracts\Queue\JobInterface;

/**
 * Concrete JobInterface rebuilt from a Redis envelope. Handlers read payload().
 */
final class EnvelopeJob implements JobInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private readonly string $jobName,
        private readonly string $queue,
        private readonly array $payload,
        private readonly int $priority = 100,
        private readonly int $maxAttempts = 3,
        private readonly int $delaySeconds = 0,
    ) {
    }

    public function queue(): string
    {
        return $this->queue;
    }

    public function priority(): int
    {
        return $this->priority;
    }

    public function maxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function delaySeconds(): int
    {
        return $this->delaySeconds;
    }

    public function payload(): array
    {
        return $this->payload;
    }

    public function jobName(): string
    {
        return $this->jobName;
    }
}
