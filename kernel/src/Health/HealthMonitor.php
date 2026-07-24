<?php

declare(strict_types=1);

namespace Platform\Kernel\Health;

use Platform\Contracts\Health\HealthCheckInterface;
use Platform\Contracts\Health\HealthMonitorInterface;
use Platform\Contracts\Health\HealthStatus;

final class HealthMonitor implements HealthMonitorInterface
{
    /** @var list<HealthCheckInterface> */
    private array $checks = [];

    public function add(HealthCheckInterface $check): void
    {
        $this->checks[] = $check;
    }

    public function runAll(): array
    {
        $results = [];
        foreach ($this->checks as $check) {
            $results[] = $check->check();
        }

        return $results;
    }

    public function overall(): HealthStatus
    {
        $results = $this->runAll();
        if ($results === []) {
            return HealthStatus::Ok;
        }

        $hasFailed = false;
        $hasDegraded = false;
        foreach ($results as $result) {
            if ($result->status === HealthStatus::Failed) {
                $hasFailed = true;
            }
            if ($result->status === HealthStatus::Degraded) {
                $hasDegraded = true;
            }
        }

        if ($hasFailed) {
            return HealthStatus::Failed;
        }
        if ($hasDegraded) {
            return HealthStatus::Degraded;
        }

        return HealthStatus::Ok;
    }
}
