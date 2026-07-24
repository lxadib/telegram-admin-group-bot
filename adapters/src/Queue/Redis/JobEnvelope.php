<?php

declare(strict_types=1);

namespace Platform\Adapters\Queue\Redis;

use Platform\Contracts\Queue\JobInterface;

/**
 * Serializes jobs to/from the JSON envelope stored on Redis.
 *
 * @phpstan-type Envelope array{
 *     name: string,
 *     queue: string,
 *     priority: int,
 *     maxAttempts: int,
 *     delaySeconds: int,
 *     attempt: int,
 *     payload: array<string, mixed>
 * }
 */
final class JobEnvelope
{
    public static function encode(JobInterface $job, int $attempt = 1): string
    {
        return json_encode([
            'name' => $job->jobName(),
            'queue' => $job->queue(),
            'priority' => $job->priority(),
            'maxAttempts' => $job->maxAttempts(),
            'delaySeconds' => $job->delaySeconds(),
            'attempt' => $attempt,
            'payload' => $job->payload(),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return array{job: EnvelopeJob, attempt: int, maxAttempts: int}
     */
    public static function decode(string $raw): array
    {
        /** @var array<string, mixed> $data */
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        $name = is_string($data['name'] ?? null) ? $data['name'] : 'unknown';
        $queue = is_string($data['queue'] ?? null) ? $data['queue'] : 'default';
        $priority = is_int($data['priority'] ?? null) ? $data['priority'] : 100;
        $maxAttempts = is_int($data['maxAttempts'] ?? null) ? $data['maxAttempts'] : 3;
        $delay = is_int($data['delaySeconds'] ?? null) ? $data['delaySeconds'] : 0;
        $attempt = is_int($data['attempt'] ?? null) ? $data['attempt'] : 1;
        /** @var array<string, mixed> $payload */
        $payload = is_array($data['payload'] ?? null) ? $data['payload'] : [];

        return [
            'job' => new EnvelopeJob($name, $queue, $payload, $priority, $maxAttempts, $delay),
            'attempt' => $attempt,
            'maxAttempts' => $maxAttempts,
        ];
    }
}
