<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Kernel\Infrastructure;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Platform\Contracts\Auth\AuthorizerInterface;
use Platform\Contracts\Capability\CapabilityGateInterface;
use Platform\Contracts\Config\ConfigRepositoryInterface;
use Platform\Contracts\Localization\LocaleResolverInterface;
use Platform\Contracts\Localization\TranslatorInterface;
use Platform\Contracts\Security\HasherInterface;
use Platform\Contracts\Security\IdempotencyStoreInterface;
use Platform\Contracts\Security\RateLimiterInterface;
use Platform\Contracts\Security\SecretVaultInterface;
use Platform\Contracts\Security\TokenIssuerInterface;
use Platform\Contracts\Storage\UnitOfWorkInterface;
use Platform\Kernel\Bootstrap\Kernel;

final class ContainerDefaultsTest extends TestCase
{
    /**
     * @return list<array{class-string}>
     */
    public static function defaultBindings(): array
    {
        return [
            [ConfigRepositoryInterface::class],
            [UnitOfWorkInterface::class],
            [TranslatorInterface::class],
            [LocaleResolverInterface::class],
            [HasherInterface::class],
            [SecretVaultInterface::class],
            [RateLimiterInterface::class],
            [IdempotencyStoreInterface::class],
            [TokenIssuerInterface::class],
            [AuthorizerInterface::class],
            [CapabilityGateInterface::class],
        ];
    }

    /**
     * @param class-string $contract
     */
    #[DataProvider('defaultBindings')]
    public function testKernelResolvesInfrastructureDefaults(string $contract): void
    {
        $kernel = Kernel::boot(['env' => 'test']);

        self::assertInstanceOf($contract, $kernel->container()->get($contract));
    }
}
