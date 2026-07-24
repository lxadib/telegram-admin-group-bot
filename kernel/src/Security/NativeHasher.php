<?php

declare(strict_types=1);

namespace Platform\Kernel\Security;

use Platform\Contracts\Security\HasherInterface;

/**
 * Argon2id/bcrypt hasher using PHP's native password_hash.
 */
final class NativeHasher implements HasherInterface
{
    public function __construct(
        private readonly string $algo = PASSWORD_DEFAULT,
    ) {
    }

    public function hash(string $value): string
    {
        return password_hash($value, $this->algo);
    }

    public function verify(string $value, string $hash): bool
    {
        return password_verify($value, $hash);
    }
}
