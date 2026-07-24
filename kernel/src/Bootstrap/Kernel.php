<?php

declare(strict_types=1);

namespace Platform\Kernel\Bootstrap;

use DI\Container;
use Platform\Contracts\Event\EventBusInterface;
use Platform\Contracts\Health\HealthMonitorInterface;
use Platform\Contracts\Messaging\CommandBusInterface;
use Platform\Contracts\Messaging\QueryBusInterface;
use Platform\Contracts\Module\ModuleRegistryInterface;
use Platform\Contracts\Queue\JobBusInterface;
use Platform\Contracts\Queue\SchedulerInterface;
use Platform\Kernel\Boundary\ModuleBoundary;
use Platform\Kernel\Container\ContainerFactory;
use Platform\Kernel\Container\DiServiceRegistrar;
use Platform\Kernel\Logging\StderrLogger;
use Platform\Kernel\Module\DependencyResolver;
use Platform\Kernel\Module\ManifestValidator;
use Platform\Kernel\Module\ModuleLoader;
use Platform\Kernel\Module\ModuleRegistry;
use Platform\Kernel\Platform;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Application kernel: boots DI, loads modules, exposes platform ports.
 * Contains no domain or Telegram logic.
 */
final class Kernel
{
    private function __construct(
        private readonly KernelConfig $config,
        private readonly Container $container,
        private readonly ModuleLoader $moduleLoader,
    ) {
    }

    /**
     * @param array{
     *     env?: string,
     *     debug?: bool,
     *     timezone?: string,
     *     modules?: list<class-string<\Platform\Contracts\Module\ModuleInterface>>
     * }|KernelConfig $config
     */
    public static function boot(array|KernelConfig $config = []): self
    {
        $kernelConfig = $config instanceof KernelConfig ? $config : KernelConfig::fromArray($config);
        date_default_timezone_set($kernelConfig->timezone);

        $logger = $kernelConfig->env === 'test' ? new NullLogger() : new StderrLogger();
        $boundary = new ModuleBoundary($logger);
        $registry = new ModuleRegistry();
        $loader = new ModuleLoader(
            new ManifestValidator(),
            new DependencyResolver(),
            $registry,
            $boundary,
            $logger,
        );

        $registrar = new DiServiceRegistrar();
        $loader->discoverAndRegister($kernelConfig->modules, $registrar);

        $factory = new ContainerFactory();
        $builder = $factory->createBuilder();
        $registrar->applyTo($builder);

        // Prefer the pre-built loader/registry instances so boot state is preserved.
        $builder->addDefinitions([
            LoggerInterface::class => $logger,
            ModuleBoundary::class => $boundary,
            ModuleRegistry::class => $registry,
            ModuleRegistryInterface::class => $registry,
            ModuleLoader::class => $loader,
        ]);

        /** @var Container $container */
        $container = $builder->build();
        $loader->boot($container);

        $logger->info('Kernel booted.', [
            'platform' => Platform::NAME,
            'version' => Platform::VERSION,
            'env' => $kernelConfig->env,
            'modules' => count($kernelConfig->modules),
        ]);

        return new self($kernelConfig, $container, $loader);
    }

    public function config(): KernelConfig
    {
        return $this->config;
    }

    public function container(): ContainerInterface
    {
        return $this->container;
    }

    public function modules(): ModuleRegistryInterface
    {
        return $this->moduleLoader->registry();
    }

    public function events(): EventBusInterface
    {
        return $this->container->get(EventBusInterface::class);
    }

    public function commands(): CommandBusInterface
    {
        return $this->container->get(CommandBusInterface::class);
    }

    public function queries(): QueryBusInterface
    {
        return $this->container->get(QueryBusInterface::class);
    }

    public function jobs(): JobBusInterface
    {
        return $this->container->get(JobBusInterface::class);
    }

    public function scheduler(): SchedulerInterface
    {
        return $this->container->get(SchedulerInterface::class);
    }

    public function health(): HealthMonitorInterface
    {
        return $this->container->get(HealthMonitorInterface::class);
    }
}
