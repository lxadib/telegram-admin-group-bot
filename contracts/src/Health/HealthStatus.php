<?php

declare(strict_types=1);

namespace Platform\Contracts\Health;

enum HealthStatus: string
{
    case Ok = 'ok';
    case Degraded = 'degraded';
    case Failed = 'failed';
}
