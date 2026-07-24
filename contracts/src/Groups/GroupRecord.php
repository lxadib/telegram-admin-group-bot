<?php

declare(strict_types=1);

namespace Platform\Contracts\Groups;

/**
 * Bound Telegram group within a tenant. Owned by the Groups module.
 */
final readonly class GroupRecord
{
    public function __construct(
        public string $groupId,
        public string $tenantId,
        public ?string $telegramChatId = null,
        public ?string $title = null,
        public bool $active = true,
        public ?\DateTimeImmutable $boundAt = null,
    ) {
        if ($this->groupId === '' || $this->tenantId === '') {
            throw new \InvalidArgumentException('GroupRecord requires groupId and tenantId.');
        }
    }
}
