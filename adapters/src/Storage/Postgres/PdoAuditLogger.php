<?php

declare(strict_types=1);

namespace Platform\Adapters\Storage\Postgres;

use Platform\Contracts\Clock\ClockInterface;
use Platform\Contracts\Logging\AuditLoggerInterface;

/**
 * Append-only audit trail persisted in platform_audit_log.
 */
final class PdoAuditLogger implements AuditLoggerInterface
{
    public function __construct(
        private readonly PdoConnection $connection,
        private readonly ClockInterface $clock,
    ) {
    }

    public function record(
        string $action,
        ?string $actorId = null,
        ?string $tenantId = null,
        ?string $groupId = null,
        array $context = [],
    ): void {
        $stmt = $this->connection->pdo()->prepare(
            'INSERT INTO platform_audit_log (action, actor_id, tenant_id, group_id, context, recorded_at) '
            . 'VALUES (:action, :actor, :tenant, :grp, :ctx, :at)',
        );

        $stmt->execute([
            'action' => $action,
            'actor' => $actorId,
            'tenant' => $tenantId,
            'grp' => $groupId,
            'ctx' => json_encode($context, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
            'at' => $this->clock->now()->format(\DateTimeInterface::ATOM),
        ]);
    }
}
