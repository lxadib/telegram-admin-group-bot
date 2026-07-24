<?php

declare(strict_types=1);

namespace Platform\Kernel\Logging;

use Platform\Contracts\Logging\AuditLoggerInterface;
use Psr\Log\LoggerInterface;

/**
 * Audit sink that writes structured lines via PSR-3 until a durable adapter exists.
 */
final class PsrAuditLogger implements AuditLoggerInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function record(
        string $action,
        ?string $actorId = null,
        ?string $tenantId = null,
        ?string $groupId = null,
        array $context = [],
    ): void {
        $this->logger->info('audit.' . $action, [
            'actor_id' => $actorId,
            'tenant_id' => $tenantId,
            'group_id' => $groupId,
            'context' => $context,
        ]);
    }
}
