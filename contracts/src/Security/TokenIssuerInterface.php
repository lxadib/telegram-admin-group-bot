<?php

declare(strict_types=1);

namespace Platform\Contracts\Security;

/**
 * Issues and verifies opaque / signed tokens (sessions, confirm nonces, license cache).
 */
interface TokenIssuerInterface
{
    /**
     * @param array<string, mixed> $claims
     */
    public function issue(array $claims, ?\DateTimeImmutable $expiresAt = null): string;

    /**
     * @return array<string, mixed>
     */
    public function verify(string $token): array;
}
