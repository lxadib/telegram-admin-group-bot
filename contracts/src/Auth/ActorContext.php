<?php

declare(strict_types=1);

namespace Platform\Contracts\Auth;

/**
 * Request-scoped identity carried through commands, jobs, and queries.
 * Every repository query must respect tenant/group scopes from this context.
 *
 * @phpstan-type RoleList list<ActorRole|string>
 */
final readonly class ActorContext
{
    /**
     * @param RoleList $roles
     */
    public function __construct(
        public string $actorId,
        public array $roles = [],
        public ?string $partnerId = null,
        public ?string $tenantId = null,
        public ?string $groupId = null,
        public ?string $telegramUserId = null,
        public ?string $locale = null,
    ) {
        if ($this->actorId === '') {
            throw new \InvalidArgumentException('ActorContext requires actorId.');
        }
    }

    public function hasRole(ActorRole $role): bool
    {
        foreach ($this->roles as $candidate) {
            if ($candidate === $role || $candidate === $role->value) {
                return true;
            }
        }

        return false;
    }

    public function isSuperOwner(): bool
    {
        return $this->hasRole(ActorRole::SuperOwner);
    }
}
