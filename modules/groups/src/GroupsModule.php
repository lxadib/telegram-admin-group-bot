<?php

declare(strict_types=1);

namespace Platform\Modules\Groups;

use Platform\Contracts\Groups\GroupRegistryInterface;
use Platform\Contracts\Groups\MembershipQueryInterface;
use Platform\Contracts\Module\ModuleId;
use Platform\Contracts\Module\ModuleInterface;
use Platform\Contracts\Module\ModuleManifest;

final class GroupsModule implements ModuleInterface
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: new ModuleId('groups'),
            version: '0.1.0',
            displayName: 'Groups',
            provides: [
                GroupRegistryInterface::class,
                MembershipQueryInterface::class,
            ],
            requires: [
                ['module' => 'users', 'version' => '^0.1'],
            ],
            permissions: [
                'groups.view',
                'groups.bind',
                'groups.manage',
            ],
            subscriptions: [
                \Platform\Contracts\Capability\Events\LicenseActivated::class,
            ],
            i18n: ['en'],
            providerClass: GroupsModuleProvider::class,
        );
    }
}
