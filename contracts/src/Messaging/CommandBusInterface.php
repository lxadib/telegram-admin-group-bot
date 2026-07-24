<?php

declare(strict_types=1);

namespace Platform\Contracts\Messaging;

interface CommandBusInterface
{
    public function dispatch(CommandInterface $command): mixed;
}
