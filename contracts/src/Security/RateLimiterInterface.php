<?php

declare(strict_types=1);

namespace Platform\Contracts\Security;

/**
 * Token-bucket / sliding-window rate limiting (bot, chat, tenant, actor).
 */
interface RateLimiterInterface
{
    /**
     * @param non-empty-string $key
     */
    public function attempt(string $key, int $maxAttempts, int $windowSeconds): bool;

    /**
     * @param non-empty-string $key
     */
    public function remaining(string $key, int $maxAttempts, int $windowSeconds): int;

    /**
     * @param non-empty-string $key
     */
    public function reset(string $key): void;
}
