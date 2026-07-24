<?php

declare(strict_types=1);

namespace Platform\Contracts\Users;

/**
 * Looks up and registers platform users. Bound by the Users module.
 */
interface UserDirectoryInterface
{
    public function get(string $userId): ?UserRecord;

    public function findByTelegramId(string $telegramUserId): ?UserRecord;

    /**
     * Create a user if absent; return the existing or new record.
     *
     * @param array{displayName?: string|null, locale?: string|null, tenantId?: string|null} $attributes
     */
    public function ensure(string $userId, array $attributes = []): UserRecord;
}
