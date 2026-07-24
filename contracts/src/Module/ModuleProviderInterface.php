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
    public function register(ContainerInterface $container): void;

    public function boot(ContainerInterface $container): void;
}
