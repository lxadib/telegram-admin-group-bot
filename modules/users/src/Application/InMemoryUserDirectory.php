<?php

declare(strict_types=1);

namespace Platform\Modules\Users\Application;

use Platform\Contracts\Clock\ClockInterface;
use Platform\Contracts\Users\UserDirectoryInterface;
use Platform\Contracts\Users\UserRecord;

/**
 * Process-local user directory. Durable adapter ships later.
 */
final class InMemoryUserDirectory implements UserDirectoryInterface
{
    /** @var array<string, UserRecord> */
    private array $byId = [];

    /** @var array<string, string> telegramUserId => userId */
    private array $telegramIndex = [];

    public function __construct(
        private readonly ClockInterface $clock,
    ) {
    }

    public function get(string $userId): ?UserRecord
    {
        return $this->byId[$userId] ?? null;
    }

    public function findByTelegramId(string $telegramUserId): ?UserRecord
    {
        $userId = $this->telegramIndex[$telegramUserId] ?? null;

        return $userId === null ? null : ($this->byId[$userId] ?? null);
    }

    public function ensure(string $userId, array $attributes = []): UserRecord
    {
        if (isset($this->byId[$userId])) {
            return $this->byId[$userId];
        }

        $record = new UserRecord(
            userId: $userId,
            displayName: $attributes['displayName'] ?? null,
            locale: $attributes['locale'] ?? null,
            tenantId: $attributes['tenantId'] ?? null,
            createdAt: $this->clock->now(),
        );

        $this->byId[$userId] = $record;

        return $record;
    }

    public function attachTelegram(string $userId, string $telegramUserId): UserRecord
    {
        $existing = $this->get($userId);
        if ($existing === null) {
            throw new \OutOfBoundsException(sprintf('User "%s" not found.', $userId));
        }

        $previous = $this->telegramIndex[$telegramUserId] ?? null;
        if ($previous !== null && $previous !== $userId) {
            throw new \RuntimeException(sprintf(
                'Telegram id "%s" is already linked to user "%s".',
                $telegramUserId,
                $previous,
            ));
        }

        if ($existing->telegramUserId !== null && $existing->telegramUserId !== $telegramUserId) {
            unset($this->telegramIndex[$existing->telegramUserId]);
        }

        $updated = new UserRecord(
            userId: $existing->userId,
            telegramUserId: $telegramUserId,
            displayName: $existing->displayName,
            locale: $existing->locale,
            tenantId: $existing->tenantId,
            createdAt: $existing->createdAt,
        );

        $this->byId[$userId] = $updated;
        $this->telegramIndex[$telegramUserId] = $userId;

        return $updated;
    }

    public function detachTelegram(string $telegramUserId): void
    {
        $userId = $this->telegramIndex[$telegramUserId] ?? null;
        if ($userId === null) {
            return;
        }

        unset($this->telegramIndex[$telegramUserId]);
        $existing = $this->byId[$userId] ?? null;
        if ($existing === null) {
            return;
        }

        $this->byId[$userId] = new UserRecord(
            userId: $existing->userId,
            telegramUserId: null,
            displayName: $existing->displayName,
            locale: $existing->locale,
            tenantId: $existing->tenantId,
            createdAt: $existing->createdAt,
        );
    }
}
