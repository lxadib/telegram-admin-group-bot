<?php

declare(strict_types=1);

namespace Platform\Contracts\Capability;

final class CapabilityDeniedException extends \RuntimeException
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $reason,
        string $message = '',
    ) {
        parent::__construct($message !== '' ? $message : sprintf(
            'Capability denied for tenant %s: %s',
            $this->tenantId,
            $this->reason,
        ));
    }
}
