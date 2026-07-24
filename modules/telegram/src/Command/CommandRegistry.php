<?php

declare(strict_types=1);

namespace Platform\Modules\Telegram\Command;

/**
 * Maps command tokens to handlers. Populated at module boot; read at request time.
 */
final class CommandRegistry
{
    /** @var array<string, CommandHandlerInterface> */
    private array $handlers = [];

    /**
     * @param iterable<CommandHandlerInterface> $handlers
     */
    public function __construct(iterable $handlers = [])
    {
        foreach ($handlers as $handler) {
            $this->register($handler);
        }
    }

    public function register(CommandHandlerInterface $handler): void
    {
        $this->handlers[$handler->name()] = $handler;
    }

    public function find(string $command): ?CommandHandlerInterface
    {
        return $this->handlers[$command] ?? null;
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_keys($this->handlers);
    }
}
