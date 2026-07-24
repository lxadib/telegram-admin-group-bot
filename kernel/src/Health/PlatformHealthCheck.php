<?php

declare(strict_types=1);

namespace Platform\Kernel\Health;

use Platform\Contracts\Health\HealthCheckInterface;
use Platform\Contracts\Health\HealthCheckResult;
use Platform\Contracts\Health\HealthStatus;
use Platform\Kernel\Platform;

final class PlatformHealthCheck implements HealthCheckInterface
{
    public function name(): string
    {
        return 'platform';
    }

    public function check(): HealthCheckResult
    {
        return new HealthCheckResult(
            name: $this->name(),
            status: HealthStatus::Ok,
            message: Platform::NAME . ' ' . Platform::VERSION,
            details: Platform::identity(),
        );
    }
}
