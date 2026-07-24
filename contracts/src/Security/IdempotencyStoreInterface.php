<?php

declare(strict_types=1);

namespace Platform\Contracts\Security;

/**
 * Guards against duplicate processing of the same logical action.
 */
interface IdempotencyStoreInterface
{
    /**
     * Returns true if this key was unseen and is now reserved; false if already seen.
     *
     * @param non-empty-string $key
     */
    public function claim(string $key, int $ttlSeconds): bool;
}
