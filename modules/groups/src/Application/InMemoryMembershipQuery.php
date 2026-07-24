<?php

declare(strict_types=1);

namespace Platform\Modules\Groups\Application;

use Platform\Contracts\Groups\MembershipQueryInterface;

final class InMemoryMembershipQuery implements MembershipQueryInterface
{
    /** @var array<string, array<string, true>> groupId => [userId => true] */
    private array $members = [];

    public function isMember(string $groupId, string $userId): bool
    {
        return isset($this->members[$groupId][$userId]);
    }

    public function addMember(string $groupId, string $userId): void
    {
        $this->members[$groupId][$userId] = true;
    }

    public function removeMember(string $groupId, string $userId): void
    {
        unset($this->members[$groupId][$userId]);
    }

    public function membersOf(string $groupId): array
    {
        return array_keys($this->members[$groupId] ?? []);
    }
}
