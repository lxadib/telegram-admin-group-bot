<?php

declare(strict_types=1);

namespace Platform\Kernel\Capability;

use Platform\Contracts\Capability\CapabilityGateInterface;
use Platform\Contracts\Capability\CapabilitySnapshot;

/**
 * Permissive default gate used until the Licenses module binds a real one.
 */
final class AllowAllCapabilityGate implements CapabilityGateInterface
{
    public function snapshotForTenant(string $tenantId): ?CapabilitySnapshot
    {
        return null;
    }

    public function assertFeature(string $tenantId, string $feature): void
    {
    }

    public function assertWithinGroupLimit(string $tenantId, int $currentGroups): void
    {
    }
}
