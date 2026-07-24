<?php

declare(strict_types=1);

namespace Platform\Modules\Users;

use Platform\Contracts\Module\ModuleId;
use Platform\Contracts\Module\ModuleInterface;
use Platform\Contracts\Module\ModuleManifest;
use Platform\Contracts\Users\IdentityLinkerInterface;
use Platform\Contracts\Users\UserDirectoryInterface;

final class UsersModule implements ModuleInterface
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: new ModuleId('users'),
            version: '0.1.0',
            displayName: 'Users',
            provides: [
                UserDirectoryInterface::class,
                IdentityLinkerInterface::class,
            ],
            permissions: [
                'users.view',
                'users.manage',
            ],
            i18n: ['en'],
            providerClass: UsersModuleProvider::class,
        );
    }
}
