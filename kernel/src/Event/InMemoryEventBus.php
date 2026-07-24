<?php

declare(strict_types=1);

namespace Platform\Kernel\Event;

use Platform\Contracts\Event\EventBusInterface;
use Platform\Contracts\Event\EventListenerInterface;
use Platform\Kernel\Boundary\ModuleBoundary;

/**
 * Synchronous in-process event bus with per-listener isolation.
 */
final class InMemoryEventBus implements EventBusInterface
{
    /** @var list<EventListenerInterface> */
    private array $listeners = [];

    public function __construct(
        private readonly ModuleBoundary $boundary,
    ) {
    }

    public function subscribe(EventListenerInterface $listener): void
    {
        $this->listeners[] = $listener;
    }

    public function dispatch(object $event): void
    {
        foreach ($this->listeners as $listener) {
            if (!is_a($event, $listener->eventClass(), false)) {
                continue;
            }

            $this->boundary->run($listener->moduleId(), function () use ($listener, $event): void {
                $listener->handle($event);
            });
        }
    }

    public function dispatchAll(iterable $events): void
    {
        foreach ($events as $event) {
            $this->dispatch($event);
        }
    }

    /**
     * @return list<EventListenerInterface>
     */
    public function listeners(): array
    {
        return $this->listeners;
    }
}
