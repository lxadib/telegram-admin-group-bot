<?php

declare(strict_types=1);

namespace Platform\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Platform\Contracts\Package;
use Platform\Kernel\Platform;

final class PlatformIdentityTest extends TestCase
{
    public function testPlatformExposesStableIdentity(): void
    {
        $identity = Platform::identity();

        self::assertSame(Platform::NAME, $identity['name']);
        self::assertSame(Platform::VERSION, $identity['version']);
        self::assertNotSame('', $identity['php']);
        self::assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', Platform::VERSION);
    }

    public function testContractsPackageIsLoadable(): void
    {
        self::assertTrue(class_exists(Package::class));
        self::assertStringContainsString('contracts', Package::NAME);
    }
}
