<?php

declare(strict_types=1);

namespace Platform\Kernel\Queue;

use Platform\Contracts\Queue\JobBusInterface;
use Platform\Contracts\Queue\JobInterface;

/**
 * In-memory job bus for Kernel boot / tests. Phase 4 replaces with Redis.
 */
final class InMemoryJobBus implements JobBusInterface
{
    /** @var list<JobInterface> */
    private array $jobs = [];

    public function dispatch(JobInterface $job): void
    {
        $this->jobs[] = $job;
    }

    public function dispatchAll(iterable $jobs): void
    {
        foreach ($jobs as $job) {
            $this->dispatch($job);
        }
    }

    /**
     * @return list<JobInterface>
     */
    public function all(): array
    {
        return $this->jobs;
    }

    public function clear(): void
    {
        $this->jobs = [];
    }
}
