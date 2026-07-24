<?php

declare(strict_types=1);

namespace Platform\Contracts\Queue;

/**
 * Cron/recurring tick → enqueue job descriptors.
 */
interface SchedulerInterface
{
    /**
     * Run due schedules once. Called by the scheduler process each minute.
     */
    public function tick(\DateTimeImmutable $now): void;
}
