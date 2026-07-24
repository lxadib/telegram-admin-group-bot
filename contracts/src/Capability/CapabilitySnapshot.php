<?php

declare(strict_types=1);

namespace Platform\Contracts\Capability;

/**
 * Hot-path license/feature snapshot. Built by Licenses module; read-only elsewhere.
 *
 * @phpstan-type FeatureMap array<string, bool>
 */
final readonly class CapabilitySnapshot
{
    /**
     * @param FeatureMap $features
     */
    public function __construct(
        public string $tenantId,
        public string $licenseId,
        public string $state,
        public array $features = [],
        public int $maxGroups = 0,
        public int $maxModules = 0,
        public int $maxAdmins = 0,
        public int $maxModerators = 0,
        public ?\DateTimeImmutable $expiresAt = null,
        public ?\DateTimeImmutable $graceUntil = null,
        public ?\DateTimeImmutable $generatedAt = null,
    ) {
        if ($this->tenantId === '' || $this->licenseId === '') {
            throw new \InvalidArgumentException('CapabilitySnapshot requires tenantId and licenseId.');
        }
    }

    public function allows(string $feature): bool
    {
        return ($this->features[$feature] ?? false) === true;
    }

    public function isUsable(\DateTimeImmutable $now): bool
    {
        if (!in_array($this->state, ['trial', 'active', 'grace'], true)) {
            return false;
        }

        if ($this->expiresAt !== null && $now > $this->expiresAt) {
            if ($this->graceUntil === null || $now > $this->graceUntil) {
                return false;
            }
        }

        return true;
    }
}
