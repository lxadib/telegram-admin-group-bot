<?php

declare(strict_types=1);

namespace Platform\Contracts\Queue;

/**
 * Enqueues jobs for background workers.
 */
interface JobBusInterface
{
    public function dispatch(JobInterface $job): void;

    /**
     * @param iterable<JobInterface> $jobs
     */
    public function dispatchAll(iterable $jobs): void;
}
