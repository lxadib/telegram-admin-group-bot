<?php

declare(strict_types=1);

namespace Platform\Contracts\Clock;

/**
 * Platform clock port. Prefer this over `new DateTimeImmutable()` in domain code.
 * Implementations may wrap PSR-20 ClockInterface.
 */
interface ClockInterface
{
    public function now(): \DateTimeImmutable;
}
