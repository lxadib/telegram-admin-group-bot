<?php

declare(strict_types=1);

namespace Platform\Modules\Licenses\Application;

use Platform\Contracts\Capability\CapabilityDeniedException;
use Platform\Contracts\Capability\CapabilityGateInterface;
use Platform\Contracts\Capability\CapabilitySnapshot;
use Platform\Contracts\Capability\Events\LicenseActivated;
use Platform\Contracts\Capability\LicenseServiceInterface;
use Platform\Contracts\Clock\ClockInterface;
use Platform\Contracts\Event\EventBusInterface;
use Platform\Contracts\Logging\AuditLoggerInterface;

/**
 * In-memory license store + hot-path CapabilityGate. Snapshot is the only read model.
 */
final class InMemoryLicenseService implements LicenseServiceInterface, CapabilityGateInterface
{
    /** @var array<string, CapabilitySnapshot> tenantId => snapshot */
    private array $snapshots = [];

    public function __construct(
        private readonly ClockInterface $clock,
        private readonly EventBusInterface $events,
        private readonly AuditLoggerInterface $audit,
    ) {
    }

    public function activate(
        string $tenantId,
        string $licenseId,
        array $features = [],
        array $limits = [],
        string $state = 'active',
        ?\DateTimeImmutable $expiresAt = null,
        ?\DateTimeImmutable $graceUntil = null,
    ): CapabilitySnapshot {
        if ($tenantId === '' || $licenseId === '') {
            throw new \InvalidArgumentException('tenantId and licenseId are required.');
        }

        if (!in_array($state, ['trial', 'active', 'grace'], true)) {
            throw new \InvalidArgumentException(sprintf('Cannot activate with state "%s".', $state));
        }

        $snapshot = new CapabilitySnapshot(
            tenantId: $tenantId,
            licenseId: $licenseId,
            state: $state,
            features: $features,
            maxGroups: $limits['maxGroups'] ?? 1,
            maxModules: $limits['maxModules'] ?? 10,
            maxAdmins: $limits['maxAdmins'] ?? 5,
            maxModerators: $limits['maxModerators'] ?? 20,
            expiresAt: $expiresAt,
            graceUntil: $graceUntil,
            generatedAt: $this->clock->now(),
        );

        $this->snapshots[$tenantId] = $snapshot;

        $this->audit->record('licenses.activated', tenantId: $tenantId, context: [
            'licenseId' => $licenseId,
            'state' => $state,
        ]);

        $this->events->dispatch(new LicenseActivated(
            tenantId: $tenantId,
            licenseId: $licenseId,
            state: $state,
        ));

        return $snapshot;
    }

    public function suspend(string $tenantId, string $reason = ''): void
    {
        $current = $this->require($tenantId);
        $this->snapshots[$tenantId] = new CapabilitySnapshot(
            tenantId: $current->tenantId,
            licenseId: $current->licenseId,
            state: 'suspended',
            features: $current->features,
            maxGroups: $current->maxGroups,
            maxModules: $current->maxModules,
            maxAdmins: $current->maxAdmins,
            maxModerators: $current->maxModerators,
            expiresAt: $current->expiresAt,
            graceUntil: $current->graceUntil,
            generatedAt: $this->clock->now(),
        );

        $this->audit->record('licenses.suspended', tenantId: $tenantId, context: [
            'reason' => $reason,
        ]);
    }

    public function revoke(string $tenantId, string $reason = ''): void
    {
        $current = $this->require($tenantId);
        $this->snapshots[$tenantId] = new CapabilitySnapshot(
            tenantId: $current->tenantId,
            licenseId: $current->licenseId,
            state: 'revoked',
            features: [],
            maxGroups: 0,
            maxModules: 0,
            maxAdmins: 0,
            maxModerators: 0,
            expiresAt: $current->expiresAt,
            graceUntil: null,
            generatedAt: $this->clock->now(),
        );

        $this->audit->record('licenses.revoked', tenantId: $tenantId, context: [
            'reason' => $reason,
        ]);
    }

    public function get(string $tenantId): ?CapabilitySnapshot
    {
        return $this->snapshots[$tenantId] ?? null;
    }

    public function snapshotForTenant(string $tenantId): ?CapabilitySnapshot
    {
        return $this->get($tenantId);
    }

    public function assertFeature(string $tenantId, string $feature): void
    {
        $snapshot = $this->usableSnapshot($tenantId);
        if (!$snapshot->allows($feature)) {
            throw new CapabilityDeniedException($tenantId, sprintf('feature:%s', $feature));
        }
    }

    public function assertWithinGroupLimit(string $tenantId, int $currentGroups): void
    {
        $snapshot = $this->usableSnapshot($tenantId);
        if ($currentGroups >= $snapshot->maxGroups) {
            throw new CapabilityDeniedException(
                $tenantId,
                sprintf('group_limit:%d', $snapshot->maxGroups),
            );
        }
    }

    private function usableSnapshot(string $tenantId): CapabilitySnapshot
    {
        $snapshot = $this->get($tenantId);
        if ($snapshot === null) {
            throw new CapabilityDeniedException($tenantId, 'missing_license');
        }

        if (!$snapshot->isUsable($this->clock->now())) {
            throw new CapabilityDeniedException($tenantId, sprintf('state:%s', $snapshot->state));
        }

        return $snapshot;
    }

    private function require(string $tenantId): CapabilitySnapshot
    {
        $snapshot = $this->get($tenantId);
        if ($snapshot === null) {
            throw new \OutOfBoundsException(sprintf('No license for tenant "%s".', $tenantId));
        }

        return $snapshot;
    }
}
