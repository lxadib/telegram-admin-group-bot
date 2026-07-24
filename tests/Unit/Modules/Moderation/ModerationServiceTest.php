<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Modules\Moderation;

use DI\Container;
use PHPUnit\Framework\TestCase;
use Platform\Contracts\Capability\CapabilityDeniedException;
use Platform\Contracts\Capability\LicenseServiceInterface;
use Platform\Contracts\Moderation\ModerationServiceInterface;
use Platform\Kernel\Bootstrap\Kernel;
use Platform\Modules\Licenses\LicensesModule;
use Platform\Modules\Moderation\ModerationModule;

final class ModerationServiceTest extends TestCase
{
    private function moderation(): ModerationServiceInterface
    {
        $kernel = Kernel::boot([
            'env' => 'test',
            'modules' => [LicensesModule::class, ModerationModule::class],
        ]);
        /** @var Container $container */
        $container = $kernel->container();

        /** @var ModerationServiceInterface $service */
        $service = $container->get(ModerationServiceInterface::class);

        return $service;
    }

    public function testMuteRequiresModerationFeature(): void
    {
        $this->expectException(CapabilityDeniedException::class);
        $this->moderation()->mute('tenant:x', 'g1', '999', 'admin');
    }

    public function testWarnAndMuteAfterActivation(): void
    {
        $kernel = Kernel::boot([
            'env' => 'test',
            'modules' => [LicensesModule::class, ModerationModule::class],
        ]);
        /** @var Container $container */
        $container = $kernel->container();

        /** @var LicenseServiceInterface $licenses */
        $licenses = $container->get(LicenseServiceInterface::class);
        $licenses->activate('tenant:x', 'PRO', features: ['moderation' => true], limits: ['maxGroups' => 5]);

        /** @var ModerationServiceInterface $moderation */
        $moderation = $container->get(ModerationServiceInterface::class);

        self::assertSame(1, $moderation->warn('tenant:x', 'g1', '999', 'admin', 'spam'));
        self::assertSame(2, $moderation->warn('tenant:x', 'g1', '999', 'admin'));

        $moderation->mute('tenant:x', 'g1', '999', 'admin');
        self::assertTrue($moderation->isMuted('g1', '999'));
        self::assertFalse($moderation->isBanned('g1', '999'));

        $moderation->ban('tenant:x', 'g1', '999', 'admin');
        self::assertTrue($moderation->isBanned('g1', '999'));
        self::assertFalse($moderation->isMuted('g1', '999'));
    }
}
