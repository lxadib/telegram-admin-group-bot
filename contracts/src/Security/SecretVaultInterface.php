<?php

declare(strict_types=1);

namespace Platform\Contracts\Security;

/**
 * Encrypted secret storage (bot tokens, API keys). Never log values.
 */
interface SecretVaultInterface
{
    public function get(string $name): string;

    public function set(string $name, string $value): void;

    public function has(string $name): bool;

    public function delete(string $name): void;
}
