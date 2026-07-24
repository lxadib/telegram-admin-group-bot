<?php

declare(strict_types=1);

namespace Platform\Contracts\Health;

final readonly class HealthCheckResult
{
    /**
     * @param array<string, scalar|null> $details
     */
    public function __construct(
        public string $name,
        public HealthStatus $status,
        public string $message = '',
        public array $details = [],
    ) {
    }
}
