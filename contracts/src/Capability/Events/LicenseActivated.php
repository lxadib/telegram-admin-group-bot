<?php

declare(strict_types=1);

namespace Platform\Contracts\Capability\Events;

use Platform\Contracts\Event\DomainEventInterface;

/**
 * Emitted after a license becomes usable (trial/active/grace).
 */
final readonly class LicenseActivated implements DomainEventInterface
{
    public function __construct(
        public string $tenantId,
        public string $licenseId,
        public string $state,
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable(),
    ) {
    }

    public function eventName(): string
    {
        return 'licenses.activated';
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
