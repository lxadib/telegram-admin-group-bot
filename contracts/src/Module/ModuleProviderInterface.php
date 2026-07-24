<?php

declare(strict_types=1);

namespace Platform\Contracts\Module;

use Psr\Container\ContainerInterface;

/**
 * Optional service provider registered by a module during enable.
 * Implementations live inside modules; Kernel invokes them.
 */
interface ModuleProviderInterface
{
    /**
     * Contribute service bindings. Must not resolve other modules' services yet.
     */
    public function register(ServiceRegistrarInterface $registrar): void;

    /**
     * Wire listeners/menus/jobs after all modules have registered.
     */
    public function boot(ContainerInterface $container): void;
}
