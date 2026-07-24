<?php

declare(strict_types=1);

namespace Platform\Tests\Integration\Adapters;

use PHPUnit\Framework\TestCase;
use Platform\Adapters\Storage\Postgres\PdoAuditLogger;
use Platform\Adapters\Storage\Postgres\PdoConfigRepository;
use Platform\Adapters\Storage\Postgres\PdoConnection;
use Platform\Adapters\Storage\Postgres\PdoUnitOfWork;
use Platform\Adapters\Storage\Postgres\SqlMigrationRunner;
use Platform\Kernel\Clock\SystemClock;

/**
 * Exercises the PostgreSQL adapters against a live database.
 * Skips automatically when DATABASE_URL is absent or unreachable.
 */
final class PostgresAdapterTest extends TestCase
{
    private PdoConnection $connection;

    protected function setUp(): void
    {
        $url = getenv('DATABASE_URL');
        if (!is_string($url) || $url === '') {
            self::markTestSkipped('DATABASE_URL not set; skipping Postgres integration test.');
        }

        if (!extension_loaded('pdo_pgsql')) {
            self::markTestSkipped('ext-pdo_pgsql not loaded; skipping Postgres integration test.');
        }

        $this->connection = PdoConnection::fromUrl($url);

        try {
            $this->connection->pdo()->exec('SELECT 1');
        } catch (\Throwable $e) {
            self::markTestSkipped('Postgres unreachable: ' . $e->getMessage());
        }

        $this->connection->pdo()->exec('DROP TABLE IF EXISTS platform_config, platform_audit_log, platform_migrations CASCADE');
        (new SqlMigrationRunner($this->connection))->up('platform', [__DIR__ . '/../../../adapters/migrations']);
    }

    public function testMigrationsAreIdempotent(): void
    {
        (new SqlMigrationRunner($this->connection))->up('platform', [__DIR__ . '/../../../adapters/migrations']);
        $stmt = $this->connection->pdo()->query('SELECT COUNT(*) FROM platform_migrations');
        self::assertNotFalse($stmt);

        self::assertSame(2, (int) $stmt->fetchColumn());
    }

    public function testConfigRepositoryPersistsValues(): void
    {
        $config = new PdoConfigRepository($this->connection);
        $config->set('welcome', 'message', 'Hi {name}');
        $config->set('welcome', 'buttons', ['rules', 'faq']);

        self::assertTrue($config->has('welcome', 'message'));
        self::assertSame('Hi {name}', $config->get('welcome', 'message'));
        self::assertSame(['rules', 'faq'], $config->get('welcome', 'buttons'));
        self::assertEqualsCanonicalizing(
            ['message' => 'Hi {name}', 'buttons' => ['rules', 'faq']],
            $config->all('welcome'),
        );
    }

    public function testUnitOfWorkRollsBackOnFailure(): void
    {
        $config = new PdoConfigRepository($this->connection);
        $uow = new PdoUnitOfWork($this->connection);

        try {
            $uow->transactional(static function () use ($config): void {
                $config->set('tx', 'key', 'value');

                throw new \RuntimeException('boom');
            });
        } catch (\RuntimeException) {
            // expected
        }

        self::assertFalse($config->has('tx', 'key'));
    }

    public function testAuditLoggerAppends(): void
    {
        $audit = new PdoAuditLogger($this->connection, new SystemClock());
        $audit->record('ban.user', actorId: 'admin-1', tenantId: 't-1', groupId: 'g-1', context: ['reason' => 'spam']);

        $stmt = $this->connection->pdo()
            ->query("SELECT action, actor_id, context FROM platform_audit_log WHERE action = 'ban.user'");
        self::assertNotFalse($stmt);
        $row = $stmt->fetch();

        self::assertIsArray($row);
        self::assertSame('ban.user', $row['action']);
        self::assertSame('admin-1', $row['actor_id']);
    }
}
