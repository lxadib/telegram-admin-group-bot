<?php

declare(strict_types=1);

namespace Platform\Tests\Integration\Adapters\Fixtures;

use Platform\Contracts\Queue\JobInterface;

final class RecordingJob implements JobInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private readonly array $payload = [],
        private readonly int $delaySeconds = 0,
        private readonly int $maxAttempts = 3,
    ) {
    }

    public function queue(): string
    {
        return 'default';
    }

    public function priority(): int
    {
        return 100;
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
        return self::class;
    }
}
