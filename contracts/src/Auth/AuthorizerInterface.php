<?php

declare(strict_types=1);

namespace Platform\Contracts\Auth;

/**
 * RBAC authorizer. License CapabilityGate runs before this.
 */
interface AuthorizerInterface
{
    /**
     * @param non-empty-string $permission Namespaced key, e.g. "moderation.mute"
     */
    public function can(ActorContext $actor, string $permission, ?string $groupId = null): bool;

    /**
     * @param non-empty-string $permission
     * @throws \Platform\Contracts\Auth\AuthorizationDeniedException
     */
    public function assertCan(ActorContext $actor, string $permission, ?string $groupId = null): void;
}
