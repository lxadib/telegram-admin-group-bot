<?php

declare(strict_types=1);

namespace Platform\Kernel\Event;

use Platform\Contracts\Event\EventInterface;
use Platform\Contracts\Event\EventOutboxInterface;

/**
 * Process-local outbox used until a durable adapter is wired in Phase 4.
 */
final class InMemoryEventOutbox implements EventOutboxInterface
{
    /** @var list<EventInterface> */
    private array $recorded = [];

    public function record(EventInterface $event): void
    {
        $this->recorded[] = $event;
    }

    public function recordAll(iterable $events): void
    {
        foreach ($events as $event) {
            $this->record($event);
        }
    }

    /**
     * @return list<EventInterface>
     */
    public function all(): array
    {
        return $this->recorded;
    }

    public function flush(): void
    {
        $this->recorded = [];
    }
}
