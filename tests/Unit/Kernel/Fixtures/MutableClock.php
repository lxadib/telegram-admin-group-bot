<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Kernel\Fixtures;

use Platform\Contracts\Clock\ClockInterface;

/**
 * Mutable clock for deterministic time-based assertions.
 */
final class MutableClock implements ClockInterface
{
    public function __construct(
        private \DateTimeImmutable $now = new \DateTimeImmutable('2026-01-01T00:00:00+00:00'),
    ) {
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }

    public function advance(int $seconds): void
    {
        $this->now = $this->now->modify(sprintf('+%d seconds', $seconds));
    }
}
