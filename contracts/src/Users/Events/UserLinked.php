<?php

declare(strict_types=1);

namespace Platform\Contracts\Users\Events;

use Platform\Contracts\Event\DomainEventInterface;

/**
 * Emitted when a Telegram identity is linked to a platform user.
 */
final readonly class UserLinked implements DomainEventInterface
{
    public function __construct(
        public string $userId,
        public string $telegramUserId,
        public ?string $tenantId = null,
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable(),
    ) {
    }

    public function eventName(): string
    {
        return 'users.linked';
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
