<?php

declare(strict_types=1);

namespace Platform\Modules\Licenses;

use Platform\Contracts\Capability\CapabilityGateInterface;
use Platform\Contracts\Capability\LicenseServiceInterface;
use Platform\Contracts\Clock\ClockInterface;
use Platform\Contracts\Event\EventBusInterface;
use Platform\Contracts\Logging\AuditLoggerInterface;
use Platform\Contracts\Module\ModuleProviderInterface;
use Platform\Contracts\Module\ServiceRegistrarInterface;
use Platform\Modules\Licenses\Application\InMemoryLicenseService;
use Psr\Container\ContainerInterface;

final class LicensesModuleProvider implements ModuleProviderInterface
{
    public function register(ServiceRegistrarInterface $registrar): void
    {
        $registrar->factory(InMemoryLicenseService::class, static function (ContainerInterface $c): InMemoryLicenseService {
            return new InMemoryLicenseService(
                $c->get(ClockInterface::class),
                $c->get(EventBusInterface::class),
                $c->get(AuditLoggerInterface::class),
            );
        });

        $registrar->factory(LicenseServiceInterface::class, static function (ContainerInterface $c): LicenseServiceInterface {
            return $c->get(InMemoryLicenseService::class);
        });

        $registrar->factory(CapabilityGateInterface::class, static function (ContainerInterface $c): CapabilityGateInterface {
            return $c->get(InMemoryLicenseService::class);
        });
    }

    public function boot(ContainerInterface $container): void
    {
        $container->get(LicenseServiceInterface::class);
    }
}
