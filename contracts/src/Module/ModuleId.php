<?php

declare(strict_types=1);

namespace Platform\Contracts\Module;

/**
 * Unique module identifier (e.g. "users", "licenses").
 */
final readonly class ModuleId
{
    public function __construct(
        public string $value,
    ) {
        if ($this->value === '' || preg_match('/^[a-z][a-z0-9_-]*$/', $this->value) !== 1) {
            throw new \InvalidArgumentException(sprintf('Invalid module id "%s".', $this->value));
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
