<?php

declare(strict_types=1);

namespace Platform\Contracts\Health;

interface HealthMonitorInterface
{
    /**
     * @return list<HealthCheckResult>
     */
    public function runAll(): array;

    public function overall(): HealthStatus;
}
