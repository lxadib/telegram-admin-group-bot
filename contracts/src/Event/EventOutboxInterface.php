<?php

declare(strict_types=1);

namespace Platform\Contracts\Event;

/**
 * Reliable async hand-off for domain events (transactional outbox).
 */
interface EventOutboxInterface
{
    public function record(EventInterface $event): void;

    /**
     * @param iterable<EventInterface> $events
     */
    public function recordAll(iterable $events): void;
}
