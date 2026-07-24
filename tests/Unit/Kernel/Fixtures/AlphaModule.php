<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Kernel\Fixtures;

use Platform\Contracts\Module\ModuleId;
use Platform\Contracts\Module\ModuleInterface;
use Platform\Contracts\Module\ModuleManifest;

final class AlphaModule implements ModuleInterface
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: new ModuleId('alpha'),
            version: '1.0.0',
            displayName: 'Alpha',
            providerClass: AlphaModuleProvider::class,
        );
    }
}
