<?php

declare(strict_types=1);

namespace Platform\Contracts\Queue;

/**
 * Executes a specific job type. Registered by modules via manifest.
 */
interface JobHandlerInterface
{
    /**
     * @return class-string<JobInterface>
     */
    public function jobClass(): string;

    public function handle(JobInterface $job): void;
}
