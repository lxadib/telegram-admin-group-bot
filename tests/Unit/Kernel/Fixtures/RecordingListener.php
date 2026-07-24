<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Kernel\Fixtures;

use Platform\Contracts\Event\EventListenerInterface;

final class RecordingListener implements EventListenerInterface
{
    /** @var list<SampleEvent> */
    public array $seen = [];

    public function eventClass(): string
    {
        return SampleEvent::class;
    }

    public function moduleId(): string
    {
        return 'alpha';
    }

    public function handle(object $event): void
    {
        if ($event instanceof SampleEvent) {
            $this->seen[] = $event;
        }
    }
}
