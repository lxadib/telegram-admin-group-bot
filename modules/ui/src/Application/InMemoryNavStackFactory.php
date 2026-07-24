<?php

declare(strict_types=1);

namespace Platform\Modules\Ui\Application;

use Platform\Contracts\Navigation\NavStackFactoryInterface;
use Platform\Contracts\Navigation\NavStackInterface;

final class InMemoryNavStackFactory implements NavStackFactoryInterface
{
    /** @var array<string, InMemoryNavStack> */
    private array $sessions = [];

    public function forSession(string $sessionId): NavStackInterface
    {
        if ($sessionId === '') {
            throw new \InvalidArgumentException('sessionId is required.');
        }

        return $this->sessions[$sessionId] ??= new InMemoryNavStack($sessionId);
    }
}
