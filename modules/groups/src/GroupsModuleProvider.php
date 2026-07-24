<?php

declare(strict_types=1);

namespace Platform\Modules\Groups;

use Platform\Contracts\Event\EventBusInterface;
use Platform\Contracts\Groups\GroupRegistryInterface;
use Platform\Contracts\Groups\MembershipQueryInterface;
use Platform\Contracts\Module\ModuleProviderInterface;
use Platform\Contracts\Module\ServiceRegistrarInterface;
use Platform\Modules\Groups\Application\InMemoryGroupRegistry;
use Platform\Modules\Groups\Application\InMemoryMembershipQuery;
use Platform\Modules\Groups\Listener\LicenseActivatedListener;
use Psr\Container\ContainerInterface;

final class GroupsModuleProvider implements ModuleProviderInterface
{
    public function register(ServiceRegistrarInterface $registrar): void
    {
        $registrar->factory(GroupRegistryInterface::class, static function (ContainerInterface $c): GroupRegistryInterface {
            return $c->get(InMemoryGroupRegistry::class);
        });

        $registrar->factory(MembershipQueryInterface::class, static function (ContainerInterface $c): MembershipQueryInterface {
            return $c->get(InMemoryMembershipQuery::class);
        });
    }

    public function boot(ContainerInterface $container): void
    {
        $container->get(EventBusInterface::class)->subscribe(
            $container->get(LicenseActivatedListener::class),
        );
    }
}
