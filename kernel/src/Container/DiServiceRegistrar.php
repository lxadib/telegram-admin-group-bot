<?php

declare(strict_types=1);

namespace Platform\Kernel\Container;

use DI\Container;
use DI\ContainerBuilder;
use Platform\Contracts\Module\ServiceRegistrarInterface;

/**
 * PHP-DI backed registrar used while the container is still being assembled.
 */
final class DiServiceRegistrar implements ServiceRegistrarInterface
{
    /** @var array<string, mixed> */
    private array $definitions = [];

    public function set(string $id, mixed $concrete): void
    {
        $this->definitions[$id] = $concrete;
    }

    public function factory(string $id, callable $factory): void
    {
        $this->definitions[$id] = $factory;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->definitions);
    }

    /**
     * @return array<string, mixed>
     */
    public function definitions(): array
    {
        return $this->definitions;
    }

    /**
     * @param ContainerBuilder<Container> $builder
     */
    public function applyTo(ContainerBuilder $builder): void
    {
        if ($this->definitions !== []) {
            $builder->addDefinitions($this->definitions);
        }
    }

    /**
     * Apply leftover/runtime bindings onto a built container.
     */
    public function applyToContainer(Container $container): void
    {
        foreach ($this->definitions as $id => $concrete) {
            $container->set($id, $concrete);
        }
    }
}
