<?php

declare(strict_types=1);

namespace Platform\Contracts\Module;

/**
 * Mutable binding API used during module register().
 * PSR-11 ContainerInterface is read-only; providers need this to contribute services.
 */
interface ServiceRegistrarInterface
{
    /**
     * Bind an identifier to a concrete instance or class name.
     *
     * @param class-string|string $id
     */
    public function set(string $id, mixed $concrete): void;

    /**
     * Bind an identifier to a factory callable resolved at get()-time.
     *
     * @param class-string|string $id
     * @param callable(): mixed $factory
     */
    public function factory(string $id, callable $factory): void;

    public function has(string $id): bool;
}
