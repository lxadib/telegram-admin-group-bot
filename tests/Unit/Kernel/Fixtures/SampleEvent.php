<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Kernel\Fixtures;

use Platform\Contracts\Event\EventInterface;

final class SampleEvent implements EventInterface
{
    public function __construct(
        public readonly string $message,
        private readonly \DateTimeImmutable $occurredAt = new \DateTimeImmutable(),
    ) {
    }

    public function eventName(): string
    {
        return 'test.sample';
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
