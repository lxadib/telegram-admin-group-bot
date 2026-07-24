<?php

declare(strict_types=1);

namespace Platform\Modules\Permissions\Application;

use Platform\Contracts\Auth\ActorContext;
use Platform\Contracts\Auth\ActorRole;
use Platform\Contracts\Auth\AuthorizationDeniedException;
use Platform\Contracts\Auth\AuthorizerInterface;
use Platform\Contracts\Auth\PermissionCatalogInterface;

/**
 * Hierarchical RBAC: SuperOwner bypasses; otherwise role defaults ∪ grants − denies.
 * Grants/denies are scoped by optional group id (null = tenant/platform scope).
 */
final class RbacAuthorizer implements AuthorizerInterface
{
    /** @var array<string, array<string, array<string, true>>> actorId => scope => [permission => true] */
    private array $grants = [];

    /** @var array<string, array<string, array<string, true>>> */
    private array $denies = [];

    public function __construct(
        private readonly PermissionCatalogInterface $catalog,
    ) {
    }

    /**
     * @param non-empty-string $permission
     */
    public function grant(string $actorId, string $permission, ?string $groupId = null): void
    {
        $this->grants[$actorId][$this->scope($groupId)][$permission] = true;
        unset($this->denies[$actorId][$this->scope($groupId)][$permission]);
    }

    /**
     * @param non-empty-string $permission
     */
    public function deny(string $actorId, string $permission, ?string $groupId = null): void
    {
        $this->denies[$actorId][$this->scope($groupId)][$permission] = true;
        unset($this->grants[$actorId][$this->scope($groupId)][$permission]);
    }

    public function can(ActorContext $actor, string $permission, ?string $groupId = null): bool
    {
        if ($actor->isSuperOwner()) {
            return true;
        }

        $scope = $this->scope($groupId ?? $actor->groupId);
        if (isset($this->denies[$actor->actorId][$scope][$permission])
            || isset($this->denies[$actor->actorId]['*'][$permission])) {
            return false;
        }

        if (isset($this->grants[$actor->actorId][$scope][$permission])
            || isset($this->grants[$actor->actorId]['*'][$permission])) {
            return true;
        }

        foreach ($actor->roles as $role) {
            $enum = $role instanceof ActorRole ? $role : ActorRole::tryFrom($role);
            if ($enum === null) {
                continue;
            }

            if (in_array($permission, $this->catalog->defaultsFor($enum), true)) {
                return true;
            }

            // Higher roles inherit lower defaults via hierarchy walk.
            foreach ($this->inheritedRoles($enum) as $inherited) {
                if (in_array($permission, $this->catalog->defaultsFor($inherited), true)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function assertCan(ActorContext $actor, string $permission, ?string $groupId = null): void
    {
        if (!$this->can($actor, $permission, $groupId)) {
            throw new AuthorizationDeniedException($permission, $actor->actorId);
        }
    }

    private function scope(?string $groupId): string
    {
        return $groupId ?? '*';
    }

    /**
     * @return list<ActorRole>
     */
    private function inheritedRoles(ActorRole $role): array
    {
        return match ($role) {
            ActorRole::SuperOwner => [
                ActorRole::Owner,
                ActorRole::Customer,
                ActorRole::GroupAdmin,
                ActorRole::Moderator,
                ActorRole::Member,
            ],
            ActorRole::Owner => [
                ActorRole::Customer,
                ActorRole::GroupAdmin,
                ActorRole::Moderator,
                ActorRole::Member,
            ],
            ActorRole::Customer => [
                ActorRole::GroupAdmin,
                ActorRole::Moderator,
                ActorRole::Member,
            ],
            ActorRole::GroupAdmin => [
                ActorRole::Moderator,
                ActorRole::Member,
            ],
            ActorRole::Moderator => [ActorRole::Member],
            ActorRole::Member, ActorRole::Guest => [],
        };
    }
}
