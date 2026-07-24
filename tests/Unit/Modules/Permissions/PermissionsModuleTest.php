<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Modules\Permissions;

use PHPUnit\Framework\TestCase;
use Platform\Contracts\Auth\ActorContext;
use Platform\Contracts\Auth\ActorRole;
use Platform\Contracts\Auth\AuthorizerInterface;
use Platform\Contracts\Auth\PermissionCatalogInterface;
use Platform\Kernel\Bootstrap\Kernel;
use Platform\Modules\Permissions\PermissionsModule;
use Platform\Modules\Users\UsersModule;

final class PermissionsModuleTest extends TestCase
{
    public function testRoleDefaultsAndDenyOverride(): void
    {
        $kernel = Kernel::boot([
            'env' => 'test',
            'modules' => [UsersModule::class, PermissionsModule::class],
        ]);

        /** @var PermissionCatalogInterface $catalog */
        $catalog = $kernel->container()->get(PermissionCatalogInterface::class);
        self::assertContains('moderation.mute', $catalog->defaultsFor(ActorRole::Moderator));

        /** @var AuthorizerInterface $auth */
        $auth = $kernel->container()->get(AuthorizerInterface::class);
        $mod = new ActorContext('mod-1', roles: [ActorRole::Moderator]);
        self::assertTrue($auth->can($mod, 'moderation.mute'));

        /** @var \Platform\Modules\Permissions\Application\RbacAuthorizer $rbac */
        $rbac = $kernel->container()->get(\Platform\Modules\Permissions\Application\RbacAuthorizer::class);
        $rbac->deny('mod-1', 'moderation.mute');
        self::assertFalse($auth->can($mod, 'moderation.mute'));
    }

    public function testSuperOwnerBypasses(): void
    {
        $kernel = Kernel::boot([
            'env' => 'test',
            'modules' => [UsersModule::class, PermissionsModule::class],
        ]);

        /** @var AuthorizerInterface $auth */
        $auth = $kernel->container()->get(AuthorizerInterface::class);
        $owner = new ActorContext('so-1', roles: [ActorRole::SuperOwner]);

        self::assertTrue($auth->can($owner, 'anything.goes'));
    }
}
