<?php

declare(strict_types=1);

namespace Platform\Contracts\Module;

/**
 * A loadable platform module. Concrete modules implement this in their package root.
 */
interface ModuleInterface
{
    public function manifest(): ModuleManifest;
}
