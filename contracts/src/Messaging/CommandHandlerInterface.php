<?php

declare(strict_types=1);

namespace Platform\Contracts\Messaging;

/**
 * @template T of CommandInterface
 */
interface CommandHandlerInterface
{
    /**
     * @return class-string<T>
     */
    public function commandClass(): string;

    /**
     * @param T $command
     */
    public function handle(CommandInterface $command): mixed;
}
