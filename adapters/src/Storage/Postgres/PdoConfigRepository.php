<?php

declare(strict_types=1);

namespace Platform\Adapters\Storage\Postgres;

use Platform\Contracts\Config\ConfigRepositoryInterface;

/**
 * Durable, namespaced configuration stored in platform_config.
 * Values are JSON-encoded so any scalar/array survives a round-trip.
 */
final class PdoConfigRepository implements ConfigRepositoryInterface
{
    public function __construct(
        private readonly PdoConnection $connection,
    ) {
    }

    public function get(string $namespace, string $key, mixed $default = null): mixed
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT value FROM platform_config WHERE namespace = :ns AND config_key = :k',
        );
        $stmt->execute(['ns' => $namespace, 'k' => $key]);
        $raw = $stmt->fetchColumn();

        if (!is_string($raw)) {
            return $default;
        }

        return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    }

    public function set(string $namespace, string $key, mixed $value): void
    {
        $encoded = json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

        $stmt = $this->connection->pdo()->prepare(
            'INSERT INTO platform_config (namespace, config_key, value) VALUES (:ns, :k, :v) '
            . 'ON CONFLICT (namespace, config_key) DO UPDATE SET value = EXCLUDED.value, updated_at = NOW()',
        );
        $stmt->execute(['ns' => $namespace, 'k' => $key, 'v' => $encoded]);
    }

    public function has(string $namespace, string $key): bool
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT 1 FROM platform_config WHERE namespace = :ns AND config_key = :k',
        );
        $stmt->execute(['ns' => $namespace, 'k' => $key]);

        return $stmt->fetchColumn() !== false;
    }

    public function all(string $namespace): array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT config_key, value FROM platform_config WHERE namespace = :ns',
        );
        $stmt->execute(['ns' => $namespace]);

        $out = [];
        /** @var array<array{config_key: string, value: string}> $rows */
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $out[$row['config_key']] = json_decode($row['value'], true, 512, JSON_THROW_ON_ERROR);
        }

        return $out;
    }
}
