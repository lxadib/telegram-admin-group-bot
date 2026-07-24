<?php

declare(strict_types=1);

namespace Platform\Kernel\Clock;

use Platform\Contracts\Clock\ClockInterface;

final class SystemClock implements ClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now');
    }
}
