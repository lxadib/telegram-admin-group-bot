<?php

declare(strict_types=1);

namespace Platform\Contracts\Identity;

/**
 * Generates unique identifiers for aggregates and correlation ids.
 */
interface IdGeneratorInterface
{
    public function generate(): string;
}
