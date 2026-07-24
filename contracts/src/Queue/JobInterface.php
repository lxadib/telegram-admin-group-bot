<?php

declare(strict_types=1);

namespace Platform\Contracts\Queue;

/**
 * Asynchronous unit of work executed by workers.
 */
interface JobInterface
{
    /**
     * Queue name: high|default|low|license|telegram_outbound|…
     */
    public function queue(): string;

    /**
     * Lower number = higher priority within the queue.
     */
    public function priority(): int;

    /**
     * Max delivery attempts before dead-letter.
     */
    public function maxAttempts(): int;

    /**
     * Delay before the job becomes available (seconds).
     */
    public function delaySeconds(): int;

    /**
     * @return array<string, mixed>
     */
    public function payload(): array;

    public function jobName(): string;
}
