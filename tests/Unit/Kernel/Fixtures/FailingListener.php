<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Kernel\Fixtures;

use Platform\Contracts\Event\EventListenerInterface;

final class FailingListener implements EventListenerInterface
{
    public function eventClass(): string
    {
        return SampleEvent::class;
    }

    public function moduleId(): string
    {
        return 'broken';
    }

    public function handle(object $event): void
    {
        throw new \RuntimeException('listener boom');
    }
}
