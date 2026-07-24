<?php

declare(strict_types=1);

namespace Platform\Modules\Permissions;

use Platform\Contracts\Auth\ActorRole;
use Platform\Contracts\Auth\AuthorizerInterface;
use Platform\Contracts\Auth\PermissionCatalogInterface;
use Platform\Contracts\Event\EventBusInterface;
use Platform\Contracts\Module\ModuleProviderInterface;
use Platform\Contracts\Module\ServiceRegistrarInterface;
use Platform\Modules\Permissions\Application\InMemoryPermissionCatalog;
use Platform\Modules\Permissions\Application\RbacAuthorizer;
use Platform\Modules\Permissions\Listener\UserLinkedListener;
use Psr\Container\ContainerInterface;

final class PermissionsModuleProvider implements ModuleProviderInterface
{
    public function register(ServiceRegistrarInterface $registrar): void
    {
        $registrar->factory(PermissionCatalogInterface::class, static function (ContainerInterface $c): PermissionCatalogInterface {
            return $c->get(InMemoryPermissionCatalog::class);
        });

        $registrar->factory(AuthorizerInterface::class, static function (ContainerInterface $c): AuthorizerInterface {
            return $c->get(RbacAuthorizer::class);
        });

        $registrar->factory(RbacAuthorizer::class, static function (ContainerInterface $c): RbacAuthorizer {
            return new RbacAuthorizer($c->get(PermissionCatalogInterface::class));
        });
    }

    public function boot(ContainerInterface $container): void
    {
        /** @var InMemoryPermissionCatalog $catalog */
        $catalog = $container->get(PermissionCatalogInterface::class);

        $catalog->register('users.view', 'View users');
        $catalog->register('users.manage', 'Manage users');
        $catalog->register('groups.view', 'View groups');
        $catalog->register('groups.bind', 'Bind Telegram groups');
        $catalog->register('groups.manage', 'Manage groups');
        $catalog->register('licenses.view', 'View license status');
        $catalog->register('licenses.activate', 'Activate a license');
        $catalog->register('moderation.warn', 'Warn members');
        $catalog->register('moderation.mute', 'Mute members');
        $catalog->register('moderation.ban', 'Ban members');

        // Customers own their tenant: they may manage licensing and bind their groups.
        $catalog->grantDefault(ActorRole::Customer, 'licenses.view');
        $catalog->grantDefault(ActorRole::Customer, 'licenses.activate');
        $catalog->grantDefault(ActorRole::Customer, 'groups.view');
        $catalog->grantDefault(ActorRole::Customer, 'groups.bind');

        $catalog->grantDefault(ActorRole::GroupAdmin, 'groups.view');
        $catalog->grantDefault(ActorRole::GroupAdmin, 'groups.manage');
        $catalog->grantDefault(ActorRole::GroupAdmin, 'moderation.warn');
        $catalog->grantDefault(ActorRole::GroupAdmin, 'moderation.mute');
        $catalog->grantDefault(ActorRole::GroupAdmin, 'moderation.ban');
        $catalog->grantDefault(ActorRole::Moderator, 'moderation.warn');
        $catalog->grantDefault(ActorRole::Moderator, 'moderation.mute');
        $catalog->grantDefault(ActorRole::Member, 'users.view');

        $container->get(EventBusInterface::class)->subscribe(
            $container->get(UserLinkedListener::class),
        );
    }
}
