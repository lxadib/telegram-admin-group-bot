<?php

declare(strict_types=1);

namespace Platform\Modules\Moderation;

use Platform\Contracts\Capability\CapabilityGateInterface;
use Platform\Contracts\Clock\ClockInterface;
use Platform\Contracts\Event\EventBusInterface;
use Platform\Contracts\Logging\AuditLoggerInterface;
use Platform\Contracts\Moderation\ModerationServiceInterface;
use Platform\Contracts\Module\ModuleProviderInterface;
use Platform\Contracts\Module\ServiceRegistrarInterface;
use Platform\Modules\Moderation\Application\InMemoryModerationService;
use Psr\Container\ContainerInterface;

final class ModerationModuleProvider implements ModuleProviderInterface
{
    public function register(ServiceRegistrarInterface $registrar): void
    {
        $registrar->factory(InMemoryModerationService::class, static function (ContainerInterface $c): InMemoryModerationService {
            return new InMemoryModerationService(
                $c->get(CapabilityGateInterface::class),
                $c->get(EventBusInterface::class),
                $c->get(AuditLoggerInterface::class),
                $c->get(ClockInterface::class),
            );
        });

        $registrar->factory(ModerationServiceInterface::class, static function (ContainerInterface $c): ModerationServiceInterface {
            return $c->get(InMemoryModerationService::class);
        });
    }

    public function boot(ContainerInterface $container): void
    {
        $container->get(ModerationServiceInterface::class);
    }
}
