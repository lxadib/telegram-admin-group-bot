<?php

declare(strict_types=1);

namespace Platform\Contracts\Moderation\Events;

use Platform\Contracts\Event\DomainEventInterface;
use Platform\Contracts\Moderation\ModerationAction;

/**
 * Emitted after a moderation action is applied to a member.
 */
final readonly class MemberModerated implements DomainEventInterface
{
    public function __construct(
        public string $tenantId,
        public string $groupId,
        public string $targetUserId,
        public ModerationAction $action,
        public string $actorId,
        public string $reason = '',
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable(),
    ) {
    }

    public function eventName(): string
    {
        return 'moderation.member_moderated';
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
