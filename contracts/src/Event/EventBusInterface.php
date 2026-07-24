<?php

declare(strict_types=1);

namespace Platform\Contracts\Event;

/**
 * Dispatches events to registered listeners.
 * Implementations must isolate listener failures (ModuleBoundary).
 */
interface EventBusInterface
{
    public function dispatch(object $event): void;

    /**
     * @param iterable<object> $events
     */
    public function dispatchAll(iterable $events): void;

    /**
     * Register a listener. Implementations must isolate failures per ModuleBoundary.
     */
    public function subscribe(EventListenerInterface $listener): void;
}
