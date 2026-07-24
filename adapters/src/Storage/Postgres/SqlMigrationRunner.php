<?php

declare(strict_types=1);

namespace Platform\Adapters\Storage\Postgres;

use Platform\Contracts\Storage\MigrationRunnerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Forward-only SQL migration runner. Each *.sql file runs once per module and is
 * recorded in platform_migrations. Files run in a transaction where possible.
 */
final class SqlMigrationRunner implements MigrationRunnerInterface
{
    private bool $bootstrapped = false;

    public function __construct(
        private readonly PdoConnection $connection,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function up(string $moduleId, array $migrationPaths): void
    {
        $this->ensureLedger();
        $pdo = $this->connection->pdo();

        foreach ($migrationPaths as $path) {
            $files = $this->expand($path);
            foreach ($files as $file) {
                $name = basename($file);
                if ($this->isApplied($moduleId, $name)) {
                    continue;
                }

                $sql = file_get_contents($file);
                if ($sql === false || trim($sql) === '') {
                    $this->logger->warning('Skipping empty migration.', ['module' => $moduleId, 'file' => $name]);

                    continue;
                }

                $pdo->beginTransaction();

                try {
                    $pdo->exec($sql);
                    $this->markApplied($moduleId, $name);
                    $pdo->commit();
                    $this->logger->info('Migration applied.', ['module' => $moduleId, 'file' => $name]);
                } catch (\Throwable $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }

                    throw new \RuntimeException(
                        sprintf('Migration "%s" for module "%s" failed: %s', $name, $moduleId, $e->getMessage()),
                        0,
                        $e,
                    );
                }
            }
        }
    }

    private function ensureLedger(): void
    {
        if ($this->bootstrapped) {
            return;
        }

        $this->connection->pdo()->exec(
            'CREATE TABLE IF NOT EXISTS platform_migrations ('
            . 'module_id VARCHAR(191) NOT NULL, '
            . 'migration VARCHAR(255) NOT NULL, '
            . 'applied_at TIMESTAMPTZ NOT NULL DEFAULT NOW(), '
            . 'PRIMARY KEY (module_id, migration))',
        );

        $this->bootstrapped = true;
    }

    private function isApplied(string $moduleId, string $migration): bool
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT 1 FROM platform_migrations WHERE module_id = :m AND migration = :f',
        );
        $stmt->execute(['m' => $moduleId, 'f' => $migration]);

        return $stmt->fetchColumn() !== false;
    }

    private function markApplied(string $moduleId, string $migration): void
    {
        $stmt = $this->connection->pdo()->prepare(
            'INSERT INTO platform_migrations (module_id, migration) VALUES (:m, :f)',
        );
        $stmt->execute(['m' => $moduleId, 'f' => $migration]);
    }

    /**
     * @return list<string> Sorted list of .sql files (a path may be a file or directory).
     */
    private function expand(string $path): array
    {
        if (is_file($path)) {
            return [$path];
        }

        if (!is_dir($path)) {
            throw new \RuntimeException(sprintf('Migration path "%s" does not exist.', $path));
        }

        $glob = glob(rtrim($path, '/') . '/*.sql');
        $files = $glob === false ? [] : $glob;
        sort($files);

        return $files;
    }
}
