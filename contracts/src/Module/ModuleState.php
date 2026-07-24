<?php

declare(strict_types=1);

namespace Platform\Contracts\Module;

/**
 * Lifecycle states for an installed module.
 */
enum ModuleState: string
{
    case Discovered = 'discovered';
    case Installed = 'installed';
    case Enabled = 'enabled';
    case Disabled = 'disabled';
    case Failed = 'failed';
}
