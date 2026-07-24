<?php

declare(strict_types=1);

namespace Platform\Contracts\Config;

/**
 * Namespaced configuration. Each module owns keys under its module id.
 * No global configuration god-object.
 */
interface ConfigRepositoryInterface
{
    /**
     * @param non-empty-string $key Dotted key, e.g. "moderation.flood.max"
     */
    public function get(string $namespace, string $key, mixed $default = null): mixed;

    /**
     * @param non-empty-string $key
     */
    public function set(string $namespace, string $key, mixed $value): void;

    /**
     * @param non-empty-string $key
     */
    public function has(string $namespace, string $key): bool;

    /**
     * @return array<string, mixed>
     */
    public function all(string $namespace): array;
}
