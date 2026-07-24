<?php

declare(strict_types=1);

namespace Platform\Kernel\Module;

use Platform\Contracts\Module\ModuleId;
use Platform\Contracts\Module\ModuleInterface;
use Platform\Contracts\Module\ModuleManifest;

/**
 * Validates manifests and dependency declarations.
 */
final class ManifestValidator
{
    public function validate(ModuleManifest $manifest): void
    {
        foreach ($manifest->requires as $requirement) {
            if ($requirement['module'] === '' || $requirement['version'] === '') {
                throw new \InvalidArgumentException(sprintf(
                    'Module %s has an invalid requires entry.',
                    $manifest->id,
                ));
            }

            // Ensure required module id shape is valid.
            new ModuleId($requirement['module']);
        }

        foreach ($manifest->provides as $contract) {
            if ($contract === '') {
                throw new \InvalidArgumentException(sprintf(
                    'Module %s declares an invalid provides entry.',
                    $manifest->id,
                ));
            }
        }
    }

    /**
     * @param list<ModuleInterface> $modules
     */
    public function validateAll(array $modules): void
    {
        foreach ($modules as $module) {
            $this->validate($module->manifest());
        }
    }
}
