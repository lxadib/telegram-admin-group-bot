<?php

declare(strict_types=1);

namespace Platform\Adapters\Storage\Postgres;

/**
 * Owns a single PDO handle. Adapters share this; business code uses ports only.
 */
final class PdoConnection
{
    private ?\PDO $pdo = null;

    /**
     * @param array<int, mixed> $options
     */
    public function __construct(
        private readonly string $dsn,
        private readonly ?string $username = null,
        private readonly ?string $password = null,
        private readonly array $options = [],
    ) {
    }

    /**
     * Build from a URL like pgsql://user:pass@host:5432/dbname.
     */
    public static function fromUrl(string $url): self
    {
        $parts = parse_url($url);
        if ($parts === false || !isset($parts['host'])) {
            throw new \InvalidArgumentException(sprintf('Invalid database URL "%s".', $url));
        }

        $scheme = $parts['scheme'] ?? 'pgsql';
        $driver = $scheme === 'postgres' || $scheme === 'postgresql' ? 'pgsql' : $scheme;
        $host = $parts['host'];
        $port = $parts['port'] ?? 5432;
        $db = isset($parts['path']) ? ltrim($parts['path'], '/') : '';

        $dsn = sprintf('%s:host=%s;port=%d;dbname=%s', $driver, $host, $port, $db);

        return new self(
            $dsn,
            isset($parts['user']) ? rawurldecode($parts['user']) : null,
            isset($parts['pass']) ? rawurldecode($parts['pass']) : null,
        );
    }

    public function pdo(): \PDO
    {
        if ($this->pdo instanceof \PDO) {
            return $this->pdo;
        }

        $options = $this->options + [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->pdo = new \PDO($this->dsn, $this->username, $this->password, $options);

        return $this->pdo;
    }
}
