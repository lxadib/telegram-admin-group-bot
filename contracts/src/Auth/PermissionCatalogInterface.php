<?php

declare(strict_types=1);

namespace Platform\Contracts\Auth;

/**
 * Catalog of permission keys and default role grants. Bound by Permissions.
 */
interface PermissionCatalogInterface
{
    /**
     * Register a namespaced permission key (e.g. "moderation.mute").
     */
    public function register(string $permission, ?string $description = null): void;

    /**
     * @return list<string>
     */
    public function all(): array;

    /**
     * Grant a permission to a role by default (platform-wide).
     */
    public function grantDefault(ActorRole $role, string $permission): void;

    /**
     * @return list<string>
     */
    public function defaultsFor(ActorRole $role): array;
}
