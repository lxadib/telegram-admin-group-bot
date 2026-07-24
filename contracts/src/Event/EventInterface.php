<?php

declare(strict_types=1);

namespace Platform\Contracts\Event;

/**
 * Marker for platform events (domain, application, system, plugin, telegram-normalized).
 */
interface EventInterface
{
    /**
     * Stable dotted name, e.g. "licenses.activated".
     */
    public function eventName(): string;

    public function occurredAt(): \DateTimeImmutable;
}
