<?php

declare(strict_types=1);

namespace Platform\Contracts\Users;

/**
 * Links Telegram identities to platform users.
 */
interface IdentityLinkerInterface
{
    /**
     * Link a Telegram user id to a platform user. Creates the user when missing.
     *
     * @param array{displayName?: string|null, locale?: string|null, tenantId?: string|null} $attributes
     */
    public function linkTelegram(string $telegramUserId, ?string $userId = null, array $attributes = []): UserRecord;

    public function unlinkTelegram(string $telegramUserId): void;
}
