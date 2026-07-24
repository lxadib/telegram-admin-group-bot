<?php

declare(strict_types=1);

namespace Platform\Contracts\Module;

/**
 * Kernel-facing registry of discovered / enabled modules.
 */
interface ModuleRegistryInterface
{
    public function has(ModuleId $id): bool;

    public function get(ModuleId $id): ModuleInterface;

    /**
     * @return list<ModuleInterface>
     */
    public function all(): array;

    /**
     * @return list<ModuleInterface>
     */
    public function enabled(): array;

    public function stateOf(ModuleId $id): ModuleState;
}
