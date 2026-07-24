<?php

declare(strict_types=1);

namespace Platform\Kernel\Messaging;

use Platform\Contracts\Messaging\QueryBusInterface;
use Platform\Contracts\Messaging\QueryInterface;

/**
 * @phpstan-type QueryHandler callable(QueryInterface): mixed
 */
final class SimpleQueryBus implements QueryBusInterface
{
    /** @var array<class-string<QueryInterface>, QueryHandler> */
    private array $handlers = [];

    /**
     * @param class-string<QueryInterface> $queryClass
     * @param QueryHandler $handler
     */
    public function register(string $queryClass, callable $handler): void
    {
        $this->handlers[$queryClass] = $handler;
    }

    public function ask(QueryInterface $query): mixed
    {
        $class = $query::class;
        if (!isset($this->handlers[$class])) {
            throw new \RuntimeException(sprintf('No query handler registered for %s.', $class));
        }

        return ($this->handlers[$class])($query);
    }
}
