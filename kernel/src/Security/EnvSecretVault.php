<?php

declare(strict_types=1);

namespace Platform\Kernel\Security;

use Platform\Contracts\Security\SecretVaultInterface;

/**
 * Reads secrets from environment (SECRET_<NAME>) with an in-memory overlay.
 * Reference implementation; a durable/encrypted vault adapter can replace it.
 */
final class EnvSecretVault implements SecretVaultInterface
{
    /** @var array<string, string> */
    private array $overlay = [];

    /**
     * @param array<string, string> $seed
     */
    public function __construct(
        array $seed = [],
        private readonly string $envPrefix = 'SECRET_',
    ) {
        $this->overlay = $seed;
    }

    public function get(string $name): string
    {
        if (array_key_exists($name, $this->overlay)) {
            return $this->overlay[$name];
        }

        $env = getenv($this->envKey($name));
        if (is_string($env) && $env !== '') {
            return $env;
        }

        throw new \OutOfBoundsException(sprintf('Secret "%s" not found.', $name));
    }

    public function set(string $name, string $value): void
    {
        $this->overlay[$name] = $value;
    }

    public function has(string $name): bool
    {
        if (array_key_exists($name, $this->overlay)) {
            return true;
        }

        $env = getenv($this->envKey($name));

        return is_string($env) && $env !== '';
    }

    public function delete(string $name): void
    {
        unset($this->overlay[$name]);
    }

    private function envKey(string $name): string
    {
        return $this->envPrefix . strtoupper(str_replace(['.', '-'], '_', $name));
    }
}
