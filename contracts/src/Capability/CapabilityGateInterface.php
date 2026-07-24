<?php

declare(strict_types=1);

namespace Platform\Contracts\Capability;

/**
 * License feature gate — runs before RBAC on the hot path.
 */
interface CapabilityGateInterface
{
    public function snapshotForTenant(string $tenantId): ?CapabilitySnapshot;

    public function assertFeature(string $tenantId, string $feature): void;

    public function assertWithinGroupLimit(string $tenantId, int $currentGroups): void;
}
