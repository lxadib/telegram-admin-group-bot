<?php

declare(strict_types=1);

namespace Platform\Contracts\Groups\Events;

use Platform\Contracts\Event\DomainEventInterface;

/**
 * Emitted when a Telegram chat is bound to a tenant.
 */
final readonly class GroupBound implements DomainEventInterface
{
    public function __construct(
        public string $groupId,
        public string $tenantId,
        public string $telegramChatId,
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable(),
    ) {
    }

    public function eventName(): string
    {
        return 'groups.bound';
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
