<?php

declare(strict_types=1);

namespace Platform\Modules\Permissions;

use Platform\Contracts\Auth\AuthorizerInterface;
use Platform\Contracts\Auth\PermissionCatalogInterface;
use Platform\Contracts\Module\ModuleId;
use Platform\Contracts\Module\ModuleInterface;
use Platform\Contracts\Module\ModuleManifest;
use Platform\Contracts\Users\Events\UserLinked;

final class PermissionsModule implements ModuleInterface
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: new ModuleId('permissions'),
            version: '0.1.0',
            displayName: 'Permissions',
            provides: [
                AuthorizerInterface::class,
                PermissionCatalogInterface::class,
            ],
            requires: [
                ['module' => 'users', 'version' => '^0.1'],
            ],
            permissions: [
                'permissions.view',
                'permissions.grant',
            ],
            subscriptions: [
                UserLinked::class,
            ],
            i18n: ['en'],
            providerClass: PermissionsModuleProvider::class,
        );
    }
}
