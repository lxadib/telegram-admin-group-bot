<?php

declare(strict_types=1);

namespace Platform\Contracts\Navigation;

/**
 * Two-step confirmation for destructive actions.
 */
interface ConfirmGateInterface
{
    /**
     * Issues a one-time confirmation token bound to an action payload.
     *
     * @param array<string, mixed> $payload
     */
    public function issue(string $action, array $payload, int $ttlSeconds = 120): string;

    /**
     * Consumes a token. Returns payload or null if invalid/expired/used.
     *
     * @return array{action: string, payload: array<string, mixed>}|null
     */
    public function consume(string $token): ?array;
}
