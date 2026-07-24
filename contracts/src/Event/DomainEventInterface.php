<?php

declare(strict_types=1);

namespace Platform\Contracts\Event;

/**
 * Domain events emitted by aggregates / application services.
 * Prefer async delivery via outbox in Kernel/infrastructure.
 */
interface DomainEventInterface extends EventInterface
{
}
