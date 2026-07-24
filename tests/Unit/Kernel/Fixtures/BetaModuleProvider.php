<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Kernel\Fixtures;

use Platform\Contracts\Module\ModuleProviderInterface;
use Platform\Contracts\Module\ServiceRegistrarInterface;
use Psr\Container\ContainerInterface;

final class BetaModuleProvider implements ModuleProviderInterface
{
    public function register(ServiceRegistrarInterface $registrar): void
    {
        $registrar->set('fixture.beta', 'beta-ready');
    }

    public function boot(ContainerInterface $container): void
    {
        // no-op
    }
}
