<?php

declare(strict_types=1);

namespace Platform\Contracts\Storage;

/**
 * Coordinates transactional work. Business code never opens raw connections.
 */
interface UnitOfWorkInterface
{
    public function begin(): void;

    public function commit(): void;

    public function rollback(): void;

    /**
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function transactional(callable $callback): mixed;
}
