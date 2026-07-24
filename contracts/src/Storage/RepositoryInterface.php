<?php

declare(strict_types=1);

namespace Platform\Contracts\Storage;

/**
 * Marker for module-owned repositories. Implementations live in modules/adapters.
 * Query methods must enforce TenantContext deny-by-default scopes.
 */
interface RepositoryInterface
{
}
