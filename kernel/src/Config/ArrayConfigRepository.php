<?php

declare(strict_types=1);

namespace Platform\Kernel\Config;

use Platform\Contracts\Config\ConfigRepositoryInterface;

/**
 * In-memory, namespaced configuration. Default binding when no durable store is present.
 */
final class ArrayConfigRepository implements ConfigRepositoryInterface
{
    /** @var array<string, array<string, mixed>> */
    private array $store = [];

    /**
     * @param array<string, array<string, mixed>> $initial
     */
    public function __construct(array $initial = [])
    {
        $this->store = $initial;
    }

    public function get(string $namespace, string $key, mixed $default = null): mixed
    {
        return $this->store[$namespace][$key] ?? $default;
    }

    public function set(string $namespace, string $key, mixed $value): void
    {
        $this->store[$namespace][$key] = $value;
    }

    public function has(string $namespace, string $key): bool
    {
        return isset($this->store[$namespace]) && array_key_exists($key, $this->store[$namespace]);
    }

    public function all(string $namespace): array
    {
        return $this->store[$namespace] ?? [];
    }
}
