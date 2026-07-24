<?php

declare(strict_types=1);

namespace Platform\Kernel\Security;

use Platform\Contracts\Clock\ClockInterface;
use Platform\Contracts\Security\IdempotencyStoreInterface;

/**
 * Process-local idempotency guard. Redis adapter provides the distributed version.
 */
final class InMemoryIdempotencyStore implements IdempotencyStoreInterface
{
    /** @var array<string, int> key => expiry timestamp */
    private array $claims = [];

    public function __construct(
        private readonly ClockInterface $clock,
    ) {
    }

    public function claim(string $key, int $ttlSeconds): bool
    {
        $now = $this->clock->now()->getTimestamp();

        if (isset($this->claims[$key]) && $this->claims[$key] > $now) {
            return false;
        }

        $this->claims[$key] = $now + $ttlSeconds;

        return true;
    }
}
