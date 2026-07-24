<?php

declare(strict_types=1);

namespace Platform\Adapters\Queue\Redis;

use Platform\Contracts\Clock\ClockInterface;
use Platform\Contracts\Queue\JobHandlerInterface;
use Predis\Client;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Pulls jobs off a Redis queue and dispatches them to registered handlers.
 * Failures are retried up to maxAttempts, then routed to the dead-letter list.
 */
final class RedisJobConsumer
{
    /** @var array<string, JobHandlerInterface> jobName => handler */
    private array $handlers = [];

    public function __construct(
        private readonly Client $client,
        private readonly RedisJobBus $bus,
        private readonly ClockInterface $clock,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function register(JobHandlerInterface $handler): void
    {
        $this->handlers[$handler->jobClass()] = $handler;
    }

    /**
     * Promote any due delayed jobs, then process a single ready job.
     *
     * @return bool True if a job was processed.
     */
    public function processOnce(string $queue): bool
    {
        $this->promoteDelayed($queue);

        $raw = $this->client->lpop($this->bus->readyKey($queue));
        if (!is_string($raw)) {
            return false;
        }

        ['job' => $job, 'attempt' => $attempt, 'maxAttempts' => $maxAttempts] = JobEnvelope::decode($raw);
        $handler = $this->handlers[$job->jobName()] ?? null;

        if (!$handler instanceof JobHandlerInterface) {
            $this->logger->error('No handler for job; dead-lettering.', ['job' => $job->jobName()]);
            $this->client->rpush($this->bus->deadLetterKey($queue), [$raw]);

            return true;
        }

        try {
            $handler->handle($job);

            return true;
        } catch (\Throwable $e) {
            $this->onFailure($queue, $job, $attempt, $maxAttempts, $e);

            return true;
        }
    }

    /**
     * Drain the ready queue until empty (used by tests / one-shot workers).
     *
     * @return int Number of jobs processed.
     */
    public function drain(string $queue, int $max = 1000): int
    {
        $processed = 0;
        while ($processed < $max && $this->processOnce($queue)) {
            ++$processed;
        }

        return $processed;
    }

    private function promoteDelayed(string $queue): void
    {
        $now = $this->clock->now()->getTimestamp();
        $delayedKey = $this->bus->delayedKey($queue);

        /** @var list<string> $due */
        $due = $this->client->zrangebyscore($delayedKey, '-inf', (string) $now);
        foreach ($due as $envelope) {
            if ($this->client->zrem($delayedKey, $envelope) === 1) {
                $this->client->rpush($this->bus->readyKey($queue), [$envelope]);
            }
        }
    }

    private function onFailure(
        string $queue,
        EnvelopeJob $job,
        int $attempt,
        int $maxAttempts,
        \Throwable $e,
    ): void {
        if ($attempt >= $maxAttempts) {
            $this->logger->error('Job exhausted retries; dead-lettering.', [
                'job' => $job->jobName(),
                'attempt' => $attempt,
                'error' => $e->getMessage(),
            ]);
            $this->client->rpush($this->bus->deadLetterKey($queue), [JobEnvelope::encode($job, $attempt)]);

            return;
        }

        $this->logger->warning('Job failed; requeueing.', [
            'job' => $job->jobName(),
            'attempt' => $attempt,
            'error' => $e->getMessage(),
        ]);
        $this->client->rpush($this->bus->readyKey($queue), [JobEnvelope::encode($job, $attempt + 1)]);
    }
}
