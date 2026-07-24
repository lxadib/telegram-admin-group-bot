<?php

declare(strict_types=1);

namespace Platform\Adapters\Storage\Postgres;

use Platform\Contracts\Storage\UnitOfWorkInterface;

/**
 * PDO-backed transaction coordinator. Supports one level of real transaction;
 * nested begins are tracked with a depth counter (savepoint-free, fail-fast).
 */
final class PdoUnitOfWork implements UnitOfWorkInterface
{
    private int $depth = 0;

    public function __construct(
        private readonly PdoConnection $connection,
    ) {
    }

    public function begin(): void
    {
        if ($this->depth === 0) {
            $this->connection->pdo()->beginTransaction();
        }
        ++$this->depth;
    }

    public function commit(): void
    {
        if ($this->depth === 0) {
            throw new \LogicException('commit() called without an active transaction.');
        }

        --$this->depth;
        if ($this->depth === 0) {
            $this->connection->pdo()->commit();
        }
    }

    public function rollback(): void
    {
        if ($this->depth === 0) {
            return;
        }

        $this->depth = 0;
        $pdo = $this->connection->pdo();
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }

    public function transactional(callable $callback): mixed
    {
        $this->begin();

        try {
            $result = $callback();
            $this->commit();

            return $result;
        } catch (\Throwable $e) {
            $this->rollback();

            throw $e;
        }
    }
}
