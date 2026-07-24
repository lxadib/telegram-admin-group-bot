<?php

declare(strict_types=1);

namespace Platform\Kernel\Identity;

use Platform\Contracts\Identity\IdGeneratorInterface;

/**
 * URL-safe random identifier (16 bytes → 32 hex chars).
 */
final class RandomIdGenerator implements IdGeneratorInterface
{
    public function generate(): string
    {
        return bin2hex(random_bytes(16));
    }
}
