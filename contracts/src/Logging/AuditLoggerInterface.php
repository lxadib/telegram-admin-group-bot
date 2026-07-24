<?php

declare(strict_types=1);

namespace Platform\Contracts\Logging;

/**
 * Append-only audit trail for security-sensitive actions.
 *
 * @phpstan-type AuditContext array<string, scalar|null>
 */
interface AuditLoggerInterface
{
    /**
     * @param AuditContext $context
     */
    public function record(
        string $action,
        ?string $actorId = null,
        ?string $tenantId = null,
        ?string $groupId = null,
        array $context = [],
    ): void;
}
