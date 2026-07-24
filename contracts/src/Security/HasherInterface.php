<?php

declare(strict_types=1);

namespace Platform\Contracts\Security;

interface HasherInterface
{
    public function hash(string $value): string;

    public function verify(string $value, string $hash): bool;
}
