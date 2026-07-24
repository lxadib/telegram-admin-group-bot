<?php

declare(strict_types=1);

namespace Platform\Kernel\Queue;

use Platform\Contracts\Queue\JobBusInterface;
use Platform\Contracts\Queue\JobInterface;
use Platform\Contracts\Queue\ScheduleDefinition;
use Platform\Contracts\Queue\SchedulerInterface;

/**
 * Minimal cron ticker. Evaluates due schedules and enqueues jobs.
 * Full cron parsing lands with Phase 4; this supports exact minute matches of "M H * * *" or "* * * * *".
 */
final class InMemoryScheduler implements SchedulerInterface
{
    /** @var list<ScheduleDefinition> */
    private array $schedules = [];

    public function __construct(
        private readonly JobBusInterface $jobBus,
    ) {
    }

    public function add(ScheduleDefinition $definition): void
    {
        $this->schedules[] = $definition;
    }

    public function tick(\DateTimeImmutable $now): void
    {
        foreach ($this->schedules as $schedule) {
            if (!$this->isDue($schedule->cronExpression, $now)) {
                continue;
            }

            $jobClass = $schedule->jobClass;

            /** @var JobInterface $job */
            $job = new $jobClass();
            $this->jobBus->dispatch($job);
        }
    }

    /**
     * @return list<ScheduleDefinition>
     */
    public function schedules(): array
    {
        return $this->schedules;
    }

    private function isDue(string $expression, \DateTimeImmutable $now): bool
    {
        $expression = trim($expression);
        if ($expression === '* * * * *') {
            return true;
        }

        $parts = preg_split('/\s+/', $expression);
        if ($parts === false || count($parts) !== 5) {
            return false;
        }

        [$minute, $hour] = $parts;
        $matchesMinute = $minute === '*' || $minute === $now->format('i') || (int) $minute === (int) $now->format('i');
        $matchesHour = $hour === '*' || $hour === $now->format('G') || (int) $hour === (int) $now->format('G');

        return $matchesMinute && $matchesHour;
    }
}
