<?php

declare(strict_types=1);

namespace Platform\Contracts\Messaging;

interface QueryBusInterface
{
    public function ask(QueryInterface $query): mixed;
}
