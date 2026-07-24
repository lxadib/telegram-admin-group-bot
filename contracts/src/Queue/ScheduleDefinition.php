<?php

declare(strict_types=1);

namespace Platform\Contracts\Queue;

/**
 * Declares a recurring schedule owned by a module.
 */
final readonly class ScheduleDefinition
{
    public function __construct(
        public string $id,
        public string $moduleId,
        public string $cronExpression,
        /** @var class-string<JobInterface> */
        public string $jobClass,
        /** @var array<string, mixed> */
        public array $payload = [],
    ) {
        if ($this->id === '' || $this->moduleId === '' || $this->cronExpression === '') {
            throw new \InvalidArgumentException('ScheduleDefinition requires id, moduleId, and cronExpression.');
        }
    }
}
