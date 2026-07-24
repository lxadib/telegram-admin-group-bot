<?php

declare(strict_types=1);

namespace Platform\Modules\Licenses;

use Platform\Contracts\Capability\CapabilityGateInterface;
use Platform\Contracts\Capability\LicenseServiceInterface;
use Platform\Contracts\Module\ModuleId;
use Platform\Contracts\Module\ModuleInterface;
use Platform\Contracts\Module\ModuleManifest;

final class LicensesModule implements ModuleInterface
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: new ModuleId('licenses'),
            version: '0.1.0',
            displayName: 'Licenses',
            provides: [
                LicenseServiceInterface::class,
                CapabilityGateInterface::class,
            ],
            permissions: [
                'licenses.view',
                'licenses.activate',
                'licenses.suspend',
            ],
            i18n: ['en'],
            providerClass: LicensesModuleProvider::class,
        );
    }
}
