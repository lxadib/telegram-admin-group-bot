<?php

declare(strict_types=1);

namespace Platform\Kernel\Module;

use Platform\Contracts\Module\ModuleId;
use Platform\Contracts\Module\ModuleInterface;
use Platform\Contracts\Module\ModuleRegistryInterface;
use Platform\Contracts\Module\ModuleState;

final class ModuleRegistry implements ModuleRegistryInterface
{
    /** @var array<string, ModuleInterface> */
    private array $modules = [];

    /** @var array<string, ModuleState> */
    private array $states = [];

    public function register(ModuleInterface $module, ModuleState $state = ModuleState::Discovered): void
    {
        $id = (string) $module->manifest()->id;
        $this->modules[$id] = $module;
        $this->states[$id] = $state;
    }

    public function setState(ModuleId $id, ModuleState $state): void
    {
        $key = (string) $id;
        if (!isset($this->modules[$key])) {
            throw new \OutOfBoundsException(sprintf('Unknown module "%s".', $key));
        }
        $this->states[$key] = $state;
    }

    public function has(ModuleId $id): bool
    {
        return isset($this->modules[(string) $id]);
    }

    public function get(ModuleId $id): ModuleInterface
    {
        $key = (string) $id;
        if (!isset($this->modules[$key])) {
            throw new \OutOfBoundsException(sprintf('Unknown module "%s".', $key));
        }

        return $this->modules[$key];
    }

    public function all(): array
    {
        return array_values($this->modules);
    }

    public function enabled(): array
    {
        $enabled = [];
        foreach ($this->modules as $id => $module) {
            if (($this->states[$id] ?? ModuleState::Discovered) === ModuleState::Enabled) {
                $enabled[] = $module;
            }
        }

        return $enabled;
    }

    public function stateOf(ModuleId $id): ModuleState
    {
        $key = (string) $id;
        if (!isset($this->states[$key])) {
            throw new \OutOfBoundsException(sprintf('Unknown module "%s".', $key));
        }

        return $this->states[$key];
    }
}
