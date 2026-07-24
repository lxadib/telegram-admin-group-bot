<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Modules\Licenses;

use PHPUnit\Framework\TestCase;
use Platform\Contracts\Capability\CapabilityDeniedException;
use Platform\Contracts\Capability\CapabilityGateInterface;
use Platform\Contracts\Capability\LicenseServiceInterface;
use Platform\Kernel\Bootstrap\Kernel;
use Platform\Modules\Licenses\LicensesModule;

final class LicensesModuleTest extends TestCase
{
    public function testActivateAndGateFeaturesAndLimits(): void
    {
        $kernel = Kernel::boot(['env' => 'test', 'modules' => [LicensesModule::class]]);

        /** @var LicenseServiceInterface $licenses */
        $licenses = $kernel->container()->get(LicenseServiceInterface::class);
        $licenses->activate(
            tenantId: 't-1',
            licenseId: 'lic-1',
            features: ['moderation' => true],
            limits: ['maxGroups' => 2],
        );

        /** @var CapabilityGateInterface $gate */
        $gate = $kernel->container()->get(CapabilityGateInterface::class);
        $gate->assertFeature('t-1', 'moderation');
        $gate->assertWithinGroupLimit('t-1', 1);

        $this->expectException(CapabilityDeniedException::class);
        $gate->assertWithinGroupLimit('t-1', 2);
    }

    public function testSuspendBlocksFeatures(): void
    {
        $kernel = Kernel::boot(['env' => 'test', 'modules' => [LicensesModule::class]]);
        /** @var LicenseServiceInterface $licenses */
        $licenses = $kernel->container()->get(LicenseServiceInterface::class);
        $licenses->activate('t-1', 'lic-1', features: ['moderation' => true]);
        $licenses->suspend('t-1', 'nonpayment');

        /** @var CapabilityGateInterface $gate */
        $gate = $kernel->container()->get(CapabilityGateInterface::class);

        $this->expectException(CapabilityDeniedException::class);
        $gate->assertFeature('t-1', 'moderation');
    }
}
