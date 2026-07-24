<?php

declare(strict_types=1);

namespace Platform\Modules\Ui;

use Platform\Contracts\Module\ModuleProviderInterface;
use Platform\Contracts\Module\ServiceRegistrarInterface;
use Platform\Contracts\Navigation\ConfirmGateInterface;
use Platform\Contracts\Navigation\MenuNode;
use Platform\Contracts\Navigation\MenuRegistryInterface;
use Platform\Contracts\Navigation\NavStackFactoryInterface;
use Platform\Modules\Ui\Application\InMemoryMenuRegistry;
use Platform\Modules\Ui\Application\InMemoryNavStackFactory;
use Platform\Modules\Ui\Application\TokenConfirmGate;
use Psr\Container\ContainerInterface;

final class UiModuleProvider implements ModuleProviderInterface
{
    public function register(ServiceRegistrarInterface $registrar): void
    {
        $registrar->factory(NavStackFactoryInterface::class, static function (ContainerInterface $c): NavStackFactoryInterface {
            return $c->get(InMemoryNavStackFactory::class);
        });

        $registrar->factory(MenuRegistryInterface::class, static function (ContainerInterface $c): MenuRegistryInterface {
            return $c->get(InMemoryMenuRegistry::class);
        });

        $registrar->factory(ConfirmGateInterface::class, static function (ContainerInterface $c): ConfirmGateInterface {
            return $c->get(TokenConfirmGate::class);
        });
    }

    public function boot(ContainerInterface $container): void
    {
        /** @var MenuRegistryInterface $menus */
        $menus = $container->get(MenuRegistryInterface::class);
        $menus->register(new MenuNode(
            id: 'ui.home',
            labelKey: 'ui.menu.home',
            route: 'ui.home',
            moduleId: 'ui',
        ));
    }
}
