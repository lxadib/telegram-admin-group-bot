<?php

declare(strict_types=1);

namespace Platform\Contracts\Groups;

/**
 * Registers and looks up bound groups. Bound by the Groups module.
 */
interface GroupRegistryInterface
{
    public function get(string $groupId): ?GroupRecord;

    public function findByTelegramChatId(string $telegramChatId): ?GroupRecord;

    /**
     * Bind a Telegram chat to a tenant. Enforces uniqueness of telegramChatId.
     *
     * @throws \RuntimeException when the chat is already bound elsewhere
     */
    public function bind(string $tenantId, string $telegramChatId, ?string $title = null, ?string $groupId = null): GroupRecord;

    public function deactivate(string $groupId): void;

    /**
     * @return list<GroupRecord>
     */
    public function listForTenant(string $tenantId): array;

    public function countForTenant(string $tenantId): int;
}
