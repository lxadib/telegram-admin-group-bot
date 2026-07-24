<?php

declare(strict_types=1);

namespace Platform\Tests\Integration\Adapters\Fixtures;

use Platform\Contracts\Queue\JobHandlerInterface;
use Platform\Contracts\Queue\JobInterface;

final class RecordingJobHandler implements JobHandlerInterface
{
    /** @var list<array<string, mixed>> */
    public array $handled = [];

    public function __construct(
        private readonly bool $failFirst = false,
    ) {
    }

    private int $calls = 0;

    public function jobClass(): string
    {
        return RecordingJob::class;
    }

    public function handle(JobInterface $job): void
    {
        ++$this->calls;
        if ($this->failFirst && $this->calls === 1) {
            throw new \RuntimeException('Simulated transient failure.');
        }

        $this->handled[] = $job->payload();
    }
}
