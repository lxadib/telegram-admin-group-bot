<?php

declare(strict_types=1);

namespace Platform\Kernel\Container;

use DI\Container;
use DI\ContainerBuilder;
use Platform\Contracts\Clock\ClockInterface;
use Platform\Contracts\Event\EventBusInterface;
use Platform\Contracts\Event\EventOutboxInterface;
use Platform\Contracts\Health\HealthMonitorInterface;
use Platform\Contracts\Identity\IdGeneratorInterface;
use Platform\Contracts\Logging\AuditLoggerInterface;
use Platform\Contracts\Messaging\CommandBusInterface;
use Platform\Contracts\Messaging\QueryBusInterface;
use Platform\Contracts\Module\ModuleRegistryInterface;
use Platform\Contracts\Queue\JobBusInterface;
use Platform\Contracts\Queue\SchedulerInterface;
use Platform\Kernel\Boundary\ModuleBoundary;
use Platform\Kernel\Clock\SystemClock;
use Platform\Kernel\Event\InMemoryEventBus;
use Platform\Kernel\Event\InMemoryEventOutbox;
use Platform\Kernel\Health\HealthMonitor;
use Platform\Kernel\Health\PlatformHealthCheck;
use Platform\Kernel\Identity\RandomIdGenerator;
use Platform\Kernel\Logging\PsrAuditLogger;
use Platform\Kernel\Logging\StderrLogger;
use Platform\Kernel\Messaging\SimpleCommandBus;
use Platform\Kernel\Messaging\SimpleQueryBus;
use Platform\Kernel\Module\DependencyResolver;
use Platform\Kernel\Module\ManifestValidator;
use Platform\Kernel\Module\ModuleLoader;
use Platform\Kernel\Module\ModuleRegistry;
use Platform\Kernel\Queue\InMemoryJobBus;
use Platform\Kernel\Queue\InMemoryScheduler;
use Psr\Log\LoggerInterface;

/**
 * Builds the DI container with Kernel defaults. Module registrars add more definitions.
 */
final class ContainerFactory
{
    /**
     * @return ContainerBuilder<Container>
     */
    public function createBuilder(): ContainerBuilder
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(true);
        $builder->useAttributes(false);

        $builder->addDefinitions($this->kernelDefinitions());

        return $builder;
    }

    /**
     * @return array<string, mixed>
     */
    public function kernelDefinitions(): array
    {
        return [
            LoggerInterface::class => static fn (): LoggerInterface => new StderrLogger(),
            ClockInterface::class => static fn (): ClockInterface => new SystemClock(),
            IdGeneratorInterface::class => static fn (): IdGeneratorInterface => new RandomIdGenerator(),
            ModuleBoundary::class => static function (Container $c): ModuleBoundary {
                return new ModuleBoundary($c->get(LoggerInterface::class));
            },
            ManifestValidator::class => static fn (): ManifestValidator => new ManifestValidator(),
            DependencyResolver::class => static fn (): DependencyResolver => new DependencyResolver(),
            ModuleRegistry::class => static fn (): ModuleRegistry => new ModuleRegistry(),
            ModuleRegistryInterface::class => static function (Container $c): ModuleRegistryInterface {
                return $c->get(ModuleRegistry::class);
            },
            ModuleLoader::class => static function (Container $c): ModuleLoader {
                return new ModuleLoader(
                    $c->get(ManifestValidator::class),
                    $c->get(DependencyResolver::class),
                    $c->get(ModuleRegistry::class),
                    $c->get(ModuleBoundary::class),
                    $c->get(LoggerInterface::class),
                );
            },
            EventBusInterface::class => static function (Container $c): EventBusInterface {
                return new InMemoryEventBus(
                    $c->get(ModuleBoundary::class),
                );
            },
            InMemoryEventBus::class => static function (Container $c): InMemoryEventBus {
                /** @var InMemoryEventBus $bus */
                $bus = $c->get(EventBusInterface::class);

                return $bus;
            },
            EventOutboxInterface::class => static fn (): EventOutboxInterface => new InMemoryEventOutbox(),
            CommandBusInterface::class => static fn (): CommandBusInterface => new SimpleCommandBus(),
            QueryBusInterface::class => static fn (): QueryBusInterface => new SimpleQueryBus(),
            JobBusInterface::class => static fn (): JobBusInterface => new InMemoryJobBus(),
            SchedulerInterface::class => static function (Container $c): SchedulerInterface {
                return new InMemoryScheduler($c->get(JobBusInterface::class));
            },
            AuditLoggerInterface::class => static function (Container $c): AuditLoggerInterface {
                return new PsrAuditLogger($c->get(LoggerInterface::class));
            },
            HealthMonitor::class => static function (): HealthMonitor {
                $monitor = new HealthMonitor();
                $monitor->add(new PlatformHealthCheck());

                return $monitor;
            },
            HealthMonitorInterface::class => static function (Container $c): HealthMonitorInterface {
                return $c->get(HealthMonitor::class);
            },
        ];
    }
}
