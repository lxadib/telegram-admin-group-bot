<?php

declare(strict_types=1);

namespace Platform\Contracts\Event;

/**
 * Typed listener for a single event class.
 *
 * @template T of object
 */
interface EventListenerInterface
{
    /**
     * @return class-string<T>
     */
    public function eventClass(): string;

    /**
     * Owning module id for boundary / circuit tracking.
     */
    public function moduleId(): string;

    /**
     * @param T $event
     */
    public function handle(object $event): void;
}
