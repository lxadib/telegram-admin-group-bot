<?php

declare(strict_types=1);

namespace Platform\Contracts\Event;

/**
 * Listener for a single event class. Isolation is enforced by the Kernel event bus.
 */
interface EventListenerInterface
{
    /**
     * @return class-string
     */
    public function eventClass(): string;

    /**
     * Owning module id for boundary / circuit tracking.
     */
    public function moduleId(): string;

    public function handle(object $event): void;
}
