<?php

declare(strict_types=1);

namespace Platform\Contracts\Groups;

/**
 * Read-side membership lookups. Roles are owned by Permissions; this port only
 * answers "is this user a member of this group?".
 */
interface MembershipQueryInterface
{
    public function isMember(string $groupId, string $userId): bool;

    public function addMember(string $groupId, string $userId): void;

    public function removeMember(string $groupId, string $userId): void;

    /**
     * @return list<string> user ids
     */
    public function membersOf(string $groupId): array;
}
