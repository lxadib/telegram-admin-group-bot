<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Kernel\Fixtures;

use Platform\Contracts\Module\ModuleId;
use Platform\Contracts\Module\ModuleInterface;
use Platform\Contracts\Module\ModuleManifest;

final class BetaModule implements ModuleInterface
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: new ModuleId('beta'),
            version: '1.0.0',
            displayName: 'Beta',
            requires: [
                ['module' => 'alpha', 'version' => '^1.0'],
            ],
            providerClass: BetaModuleProvider::class,
        );
    }
}
