<?php

declare(strict_types=1);

namespace Platform\Kernel\Security;

use Platform\Contracts\Clock\ClockInterface;
use Platform\Contracts\Security\RateLimiterInterface;

/**
 * Process-local sliding-window rate limiter. Default binding for single-process use.
 * Redis adapter provides the distributed implementation.
 */
final class InMemoryRateLimiter implements RateLimiterInterface
{
    /** @var array<string, list<int>> */
    private array $hits = [];

    public function __construct(
        private readonly ClockInterface $clock,
    ) {
    }

    public function attempt(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $now = $this->clock->now()->getTimestamp();
        $this->prune($key, $now, $windowSeconds);

        if (count($this->hits[$key] ?? []) >= $maxAttempts) {
            return false;
        }

        $this->hits[$key][] = $now;

        return true;
    }

    public function remaining(string $key, int $maxAttempts, int $windowSeconds): int
    {
        $now = $this->clock->now()->getTimestamp();
        $this->prune($key, $now, $windowSeconds);

        return max(0, $maxAttempts - count($this->hits[$key] ?? []));
    }

    public function reset(string $key): void
    {
        unset($this->hits[$key]);
    }

    private function prune(string $key, int $now, int $windowSeconds): void
    {
        if (!isset($this->hits[$key])) {
            return;
        }

        $threshold = $now - $windowSeconds;
        $this->hits[$key] = array_values(array_filter(
            $this->hits[$key],
            static fn (int $ts): bool => $ts > $threshold,
        ));
    }
}
