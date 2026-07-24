<?php

declare(strict_types=1);

namespace Platform\Modules\Moderation;

use Platform\Contracts\Moderation\ModerationServiceInterface;
use Platform\Contracts\Module\ModuleId;
use Platform\Contracts\Module\ModuleInterface;
use Platform\Contracts\Module\ModuleManifest;

final class ModerationModule implements ModuleInterface
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: new ModuleId('moderation'),
            version: '0.1.0',
            displayName: 'Moderation',
            provides: [
                ModerationServiceInterface::class,
            ],
            requires: [
                ['module' => 'licenses', 'version' => '^0.1'],
            ],
            requiresLicenseFeatures: ['moderation'],
            permissions: [
                'moderation.warn',
                'moderation.mute',
                'moderation.ban',
            ],
            i18n: ['en'],
            providerClass: ModerationModuleProvider::class,
        );
    }
}
