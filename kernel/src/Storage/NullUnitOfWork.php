<?php

declare(strict_types=1);

namespace Platform\Kernel\Storage;

use Platform\Contracts\Storage\UnitOfWorkInterface;

/**
 * Default UnitOfWork used when no database is bound. Executes callbacks directly.
 */
final class NullUnitOfWork implements UnitOfWorkInterface
{
    public function begin(): void
    {
    }

    public function commit(): void
    {
    }

    public function rollback(): void
    {
    }

    public function transactional(callable $callback): mixed
    {
        return $callback();
    }
}
