<?php

declare(strict_types=1);

namespace Platform\Modules\Ui;

use Platform\Contracts\Module\ModuleId;
use Platform\Contracts\Module\ModuleInterface;
use Platform\Contracts\Module\ModuleManifest;
use Platform\Contracts\Navigation\ConfirmGateInterface;
use Platform\Contracts\Navigation\MenuRegistryInterface;
use Platform\Contracts\Navigation\NavStackFactoryInterface;

final class UiModule implements ModuleInterface
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: new ModuleId('ui'),
            version: '0.1.0',
            displayName: 'UI / Navigation',
            provides: [
                NavStackFactoryInterface::class,
                MenuRegistryInterface::class,
                ConfirmGateInterface::class,
            ],
            menus: ['ui.home'],
            i18n: ['en'],
            providerClass: UiModuleProvider::class,
        );
    }
}
