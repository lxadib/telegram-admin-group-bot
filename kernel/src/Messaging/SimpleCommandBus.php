<?php

declare(strict_types=1);

namespace Platform\Kernel\Messaging;

use Platform\Contracts\Messaging\CommandBusInterface;
use Platform\Contracts\Messaging\CommandHandlerInterface;
use Platform\Contracts\Messaging\CommandInterface;

final class SimpleCommandBus implements CommandBusInterface
{
    /** @var array<class-string<CommandInterface>, CommandHandlerInterface<CommandInterface>> */
    private array $handlers = [];

    /**
     * @param CommandHandlerInterface<CommandInterface> $handler
     */
    public function register(CommandHandlerInterface $handler): void
    {
        $this->handlers[$handler->commandClass()] = $handler;
    }

    public function dispatch(CommandInterface $command): mixed
    {
        $class = $command::class;
        if (!isset($this->handlers[$class])) {
            throw new \RuntimeException(sprintf('No command handler registered for %s.', $class));
        }

        return $this->handlers[$class]->handle($command);
    }
}
