<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Modules\Groups;

use PHPUnit\Framework\TestCase;
use Platform\Contracts\Capability\CapabilityDeniedException;
use Platform\Contracts\Capability\LicenseServiceInterface;
use Platform\Contracts\Groups\GroupRegistryInterface;
use Platform\Contracts\Groups\MembershipQueryInterface;
use Platform\Kernel\Bootstrap\Kernel;
use Platform\Modules\Groups\GroupsModule;
use Platform\Modules\Licenses\LicensesModule;
use Platform\Modules\Users\UsersModule;

final class GroupsModuleTest extends TestCase
{
    public function testBindRespectsLicenseGroupLimit(): void
    {
        $kernel = Kernel::boot([
            'env' => 'test',
            'modules' => [UsersModule::class, LicensesModule::class, GroupsModule::class],
        ]);

        /** @var LicenseServiceInterface $licenses */
        $licenses = $kernel->container()->get(LicenseServiceInterface::class);
        $licenses->activate('t-1', 'lic-1', limits: ['maxGroups' => 1]);

        /** @var GroupRegistryInterface $groups */
        $groups = $kernel->container()->get(GroupRegistryInterface::class);
        $groups->bind('t-1', '-1001', 'First');

        $this->expectException(CapabilityDeniedException::class);
        $groups->bind('t-1', '-1002', 'Second');
    }

    public function testMembershipQuery(): void
    {
        $kernel = Kernel::boot([
            'env' => 'test',
            'modules' => [UsersModule::class, LicensesModule::class, GroupsModule::class],
        ]);

        /** @var LicenseServiceInterface $licenses */
        $licenses = $kernel->container()->get(LicenseServiceInterface::class);
        $licenses->activate('t-1', 'lic-1', limits: ['maxGroups' => 5]);

        /** @var GroupRegistryInterface $groups */
        $groups = $kernel->container()->get(GroupRegistryInterface::class);
        $group = $groups->bind('t-1', '-2001');

        /** @var MembershipQueryInterface $members */
        $members = $kernel->container()->get(MembershipQueryInterface::class);
        $members->addMember($group->groupId, 'u-1');

        self::assertTrue($members->isMember($group->groupId, 'u-1'));
        self::assertSame(['u-1'], $members->membersOf($group->groupId));
    }
}
