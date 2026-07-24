<?php

declare(strict_types=1);

namespace Platform\Contracts\Users;

/**
 * Read model for a platform user. Owned by the Users module.
 */
final readonly class UserRecord
{
    public function __construct(
        public string $userId,
        public ?string $telegramUserId = null,
        public ?string $displayName = null,
        public ?string $locale = null,
        public ?string $tenantId = null,
        public ?\DateTimeImmutable $createdAt = null,
    ) {
        if ($this->userId === '') {
            throw new \InvalidArgumentException('UserRecord requires userId.');
        }
    }
}
