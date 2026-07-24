<?php

declare(strict_types=1);

namespace Platform\Kernel\Module;

use Platform\Contracts\Module\ModuleId;
use Platform\Contracts\Module\ModuleInterface;
use Platform\Contracts\Module\ModuleProviderInterface;
use Platform\Contracts\Module\ModuleState;
use Platform\Contracts\Module\ServiceRegistrarInterface;
use Platform\Kernel\Boundary\ModuleBoundary;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Discovers, orders, registers, and boots modules in two phases.
 */
final class ModuleLoader
{
    /** @var list<array{module: ModuleInterface, provider: ModuleProviderInterface|null}> */
    private array $bootPlan = [];

    public function __construct(
        private readonly ManifestValidator $validator,
        private readonly DependencyResolver $resolver,
        private readonly ModuleRegistry $registry,
        private readonly ModuleBoundary $boundary,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Phase A: instantiate, validate, order, and collect provider register() bindings.
     *
     * @param list<class-string<ModuleInterface>> $moduleClasses
     * @return list<ModuleInterface>
     */
    public function discoverAndRegister(array $moduleClasses, ServiceRegistrarInterface $registrar): array
    {
        $this->bootPlan = [];
        $modules = [];

        foreach ($moduleClasses as $class) {
            /** @var ModuleInterface $module */
            $module = new $class();
            $this->validator->validate($module->manifest());
            $modules[] = $module;
            $this->registry->register($module, ModuleState::Discovered);
        }

        $ordered = $this->resolver->resolve($modules);

        foreach ($ordered as $module) {
            $id = $module->manifest()->id;
            $this->registry->setState($id, ModuleState::Installed);

            $providerClass = $module->manifest()->providerClass;
            $provider = null;

            if ($providerClass !== null) {
                if (!is_a($providerClass, ModuleProviderInterface::class, true)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Provider %s for module %s must implement %s.',
                        $providerClass,
                        $id,
                        ModuleProviderInterface::class,
                    ));
                }

                /** @var ModuleProviderInterface $provider */
                $provider = new $providerClass();
                $provider->register($registrar);
            }

            $this->bootPlan[] = ['module' => $module, 'provider' => $provider];
        }

        return $ordered;
    }

    /**
     * Phase B: boot providers against the built container and mark enabled/failed.
     */
    public function boot(ContainerInterface $container): void
    {
        foreach ($this->bootPlan as $entry) {
            $module = $entry['module'];
            $provider = $entry['provider'];
            $id = $module->manifest()->id;

            if ($provider === null) {
                $this->registry->setState($id, ModuleState::Enabled);
                $this->logger->info('Module enabled (no provider).', ['module_id' => (string) $id]);
                continue;
            }

            $ok = $this->boundary->run((string) $id, static function () use ($provider, $container): true {
                $provider->boot($container);

                return true;
            });

            if ($ok === true) {
                $this->registry->setState($id, ModuleState::Enabled);
                $this->logger->info('Module enabled.', ['module_id' => (string) $id]);
            } else {
                $this->registry->setState($id, ModuleState::Failed);
                $this->logger->error('Module failed during boot.', ['module_id' => (string) $id]);
            }
        }
    }

    public function registry(): ModuleRegistry
    {
        return $this->registry;
    }

    public function isEnabled(ModuleId $id): bool
    {
        return $this->registry->has($id)
            && $this->registry->stateOf($id) === ModuleState::Enabled;
    }
}
