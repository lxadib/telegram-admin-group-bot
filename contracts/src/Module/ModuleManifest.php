<?php

declare(strict_types=1);

namespace Platform\Contracts\Module;

/**
 * Declares what a module ships. Parsed by Kernel; owned by each module package.
 *
 * @phpstan-type ModuleRequirement array{module: string, version: string}
 */
final readonly class ModuleManifest
{
    /**
     * @param list<string> $provides Contract FQCNs this module binds
     * @param list<ModuleRequirement> $requires
     * @param list<string> $requiresLicenseFeatures
     * @param list<string> $permissions Permission keys published by this module
     * @param list<string> $migrations Relative migration paths
     * @param list<string> $subscriptions Event class FQCNs this module listens to
     * @param list<string> $jobs Job class FQCNs
     * @param list<string> $menus Menu route ids contributed
     * @param list<string> $commands Command names (e.g. "/start")
     * @param array<string, mixed> $configSchema
     * @param list<string> $i18n Locale codes shipped
     */
    public function __construct(
        public ModuleId $id,
        public string $version,
        public string $displayName,
        public array $provides = [],
        public array $requires = [],
        public array $requiresLicenseFeatures = [],
        public array $permissions = [],
        public array $migrations = [],
        public array $subscriptions = [],
        public array $jobs = [],
        public array $menus = [],
        public array $commands = [],
        public array $configSchema = [],
        public array $i18n = [],
        public ?string $providerClass = null,
    ) {
        if ($this->version === '' || preg_match('/^\d+\.\d+\.\d+(-[a-zA-Z0-9.]+)?$/', $this->version) !== 1) {
            throw new \InvalidArgumentException(sprintf('Invalid semver "%s" for module %s.', $this->version, $this->id));
        }

        if ($this->displayName === '') {
            throw new \InvalidArgumentException(sprintf('Module %s requires a display name.', $this->id));
        }
    }
}
