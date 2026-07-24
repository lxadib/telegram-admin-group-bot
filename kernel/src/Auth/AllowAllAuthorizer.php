<?php

declare(strict_types=1);

namespace Platform\Kernel\Auth;

use Platform\Contracts\Auth\ActorContext;
use Platform\Contracts\Auth\AuthorizerInterface;

/**
 * Permissive default authorizer used until the Permissions module binds a real one.
 */
final class AllowAllAuthorizer implements AuthorizerInterface
{
    public function can(ActorContext $actor, string $permission, ?string $groupId = null): bool
    {
        return true;
    }

    public function assertCan(ActorContext $actor, string $permission, ?string $groupId = null): void
    {
    }
}
