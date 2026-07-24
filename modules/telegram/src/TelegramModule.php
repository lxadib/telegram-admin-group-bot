<?php

declare(strict_types=1);

namespace Platform\Modules\Telegram;

use Platform\Contracts\Module\ModuleId;
use Platform\Contracts\Module\ModuleInterface;
use Platform\Contracts\Module\ModuleManifest;
use Platform\Contracts\Telegram\UpdateIngressInterface;

final class TelegramModule implements ModuleInterface
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: new ModuleId('telegram'),
            version: '0.1.0',
            displayName: 'Telegram',
            provides: [
                UpdateIngressInterface::class,
            ],
            requires: [
                ['module' => 'users', 'version' => '^0.1'],
                ['module' => 'licenses', 'version' => '^0.1'],
                ['module' => 'permissions', 'version' => '^0.1'],
                ['module' => 'groups', 'version' => '^0.1'],
                ['module' => 'moderation', 'version' => '^0.1'],
                ['module' => 'ui', 'version' => '^0.1'],
            ],
            commands: ['/start', '/activate', '/bind', '/warn', '/mute', '/ban'],
            menus: ['ui.home'],
            i18n: ['en'],
            providerClass: TelegramModuleProvider::class,
        );
    }
}
