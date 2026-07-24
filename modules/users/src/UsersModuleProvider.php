<?php

declare(strict_types=1);

namespace Platform\Modules\Users;

use Platform\Contracts\Module\ModuleProviderInterface;
use Platform\Contracts\Module\ServiceRegistrarInterface;
use Platform\Contracts\Users\IdentityLinkerInterface;
use Platform\Contracts\Users\UserDirectoryInterface;
use Platform\Modules\Users\Application\InMemoryIdentityLinker;
use Platform\Modules\Users\Application\InMemoryUserDirectory;
use Psr\Container\ContainerInterface;

final class UsersModuleProvider implements ModuleProviderInterface
{
    public function register(ServiceRegistrarInterface $registrar): void
    {
        $registrar->factory(UserDirectoryInterface::class, static function (ContainerInterface $c): UserDirectoryInterface {
            return $c->get(InMemoryUserDirectory::class);
        });

        $registrar->factory(IdentityLinkerInterface::class, static function (ContainerInterface $c): IdentityLinkerInterface {
            return $c->get(InMemoryIdentityLinker::class);
        });
    }

    public function boot(ContainerInterface $container): void
    {
        // Eagerly resolve so the singleton directory is shared with the linker.
        $container->get(UserDirectoryInterface::class);
        $container->get(IdentityLinkerInterface::class);
    }
}
