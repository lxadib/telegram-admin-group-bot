<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Modules\Boundary;

use PHPUnit\Framework\TestCase;
use Platform\Contracts\Capability\LicenseServiceInterface;
use Platform\Contracts\Groups\GroupRegistryInterface;
use Platform\Contracts\Module\ModuleId;
use Platform\Contracts\Module\ModuleState;
use Platform\Contracts\Users\IdentityLinkerInterface;
use Platform\Kernel\Bootstrap\Kernel;
use Platform\Modules\Groups\GroupsModule;
use Platform\Modules\Groups\Listener\LicenseActivatedListener;
use Platform\Modules\Licenses\LicensesModule;
use Platform\Modules\Permissions\Listener\UserLinkedListener;
use Platform\Modules\Permissions\PermissionsModule;
use Platform\Modules\Ui\UiModule;
use Platform\Modules\Users\UsersModule;

/**
 * Boots the full Phase 5 module set and proves cross-module event wiring.
 */
final class CoreModulesBoundaryTest extends TestCase
{
    public function testAllCoreModulesBootAndWireEvents(): void
    {
        $kernel = Kernel::boot([
            'env' => 'test',
            'modules' => [
                UsersModule::class,
                LicensesModule::class,
                PermissionsModule::class,
                GroupsModule::class,
                UiModule::class,
            ],
        ]);

        foreach (['users', 'groups', 'permissions', 'licenses', 'ui'] as $id) {
            self::assertSame(
                ModuleState::Enabled,
                $kernel->modules()->stateOf(new ModuleId($id)),
            );
        }

        /** @var IdentityLinkerInterface $linker */
        $linker = $kernel->container()->get(IdentityLinkerInterface::class);
        $user = $linker->linkTelegram('tg-boundary', attributes: ['tenantId' => 't-bound']);

        /** @var UserLinkedListener $userListener */
        $userListener = $kernel->container()->get(UserLinkedListener::class);
        self::assertContains($user->userId, $userListener->seenUserIds);

        /** @var LicenseServiceInterface $licenses */
        $licenses = $kernel->container()->get(LicenseServiceInterface::class);
        $licenses->activate('t-bound', 'lic-bound', limits: ['maxGroups' => 3], features: ['moderation' => true]);

        /** @var LicenseActivatedListener $licenseListener */
        $licenseListener = $kernel->container()->get(LicenseActivatedListener::class);
        self::assertContains('t-bound', $licenseListener->seenTenantIds);

        /** @var GroupRegistryInterface $groups */
        $groups = $kernel->container()->get(GroupRegistryInterface::class);
        $group = $groups->bind('t-bound', '-999', 'Boundary Group');

        self::assertSame('t-bound', $group->tenantId);
        self::assertCount(5, $kernel->modules()->enabled());
    }
}
