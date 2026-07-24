<?php

declare(strict_types=1);

namespace Platform\Contracts\Health;

interface HealthCheckInterface
{
    public function name(): string;

    public function check(): HealthCheckResult;
}
