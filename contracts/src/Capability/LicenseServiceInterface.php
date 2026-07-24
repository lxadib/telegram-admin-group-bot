<?php

declare(strict_types=1);

namespace Platform\Contracts\Capability;

/**
 * License lifecycle operations. Hot-path reads go through CapabilityGateInterface.
 */
interface LicenseServiceInterface
{
    /**
     * Activate (or start a trial for) a tenant.
     *
     * @param array<string, bool> $features
     * @param array{maxGroups?: int, maxModules?: int, maxAdmins?: int, maxModerators?: int} $limits
     */
    public function activate(
        string $tenantId,
        string $licenseId,
        array $features = [],
        array $limits = [],
        string $state = 'active',
        ?\DateTimeImmutable $expiresAt = null,
        ?\DateTimeImmutable $graceUntil = null,
    ): CapabilitySnapshot;

    public function suspend(string $tenantId, string $reason = ''): void;

    public function revoke(string $tenantId, string $reason = ''): void;

    public function get(string $tenantId): ?CapabilitySnapshot;
}
