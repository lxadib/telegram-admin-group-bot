<?php

declare(strict_types=1);

namespace Platform\Modules\Permissions\Application;

use Platform\Contracts\Auth\ActorRole;
use Platform\Contracts\Auth\PermissionCatalogInterface;

final class InMemoryPermissionCatalog implements PermissionCatalogInterface
{
    /** @var array<string, string|null> permission => description */
    private array $permissions = [];

    /** @var array<string, array<string, true>> role => [permission => true] */
    private array $defaults = [];

    public function register(string $permission, ?string $description = null): void
    {
        if ($permission === '') {
            throw new \InvalidArgumentException('Permission key must be non-empty.');
        }

        $this->permissions[$permission] = $description;
    }

    public function all(): array
    {
        return array_keys($this->permissions);
    }

    public function grantDefault(ActorRole $role, string $permission): void
    {
        $this->register($permission);
        $this->defaults[$role->value][$permission] = true;
    }

    public function defaultsFor(ActorRole $role): array
    {
        return array_keys($this->defaults[$role->value] ?? []);
    }
}
