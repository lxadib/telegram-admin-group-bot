<?php

declare(strict_types=1);

namespace Platform\Kernel\Bootstrap;

use Platform\Contracts\Module\ModuleInterface;

/**
 * Immutable boot configuration for the Kernel.
 *
 * @phpstan-type ModuleList list<class-string<ModuleInterface>>
 */
final readonly class KernelConfig
{
    /**
     * @param ModuleList $modules
     */
    public function __construct(
        public string $env = 'local',
        public bool $debug = true,
        public string $timezone = 'UTC',
        public array $modules = [],
    ) {
    }

    /**
     * @param array{
     *     env?: string,
     *     debug?: bool,
     *     timezone?: string,
     *     modules?: ModuleList
     * } $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            env: $config['env'] ?? 'local',
            debug: $config['debug'] ?? true,
            timezone: $config['timezone'] ?? 'UTC',
            modules: $config['modules'] ?? [],
        );
    }
}
