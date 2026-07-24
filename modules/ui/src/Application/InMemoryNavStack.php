<?php

declare(strict_types=1);

namespace Platform\Modules\Ui\Application;

use Platform\Contracts\Navigation\NavStackInterface;

final class InMemoryNavStack implements NavStackInterface
{
    /** @var list<array{route: string, state: array<string, mixed>}> */
    private array $stack = [];

    public function __construct(
        private readonly string $sessionId,
    ) {
        if ($this->sessionId === '') {
            throw new \InvalidArgumentException('NavStack requires a session id.');
        }
    }

    public function sessionId(): string
    {
        return $this->sessionId;
    }

    public function push(string $route, array $state = []): void
    {
        if ($route === '') {
            throw new \InvalidArgumentException('Route must be non-empty.');
        }

        $this->stack[] = ['route' => $route, 'state' => $state];
    }

    public function pop(): ?array
    {
        if ($this->stack === []) {
            return null;
        }

        return array_pop($this->stack);
    }

    public function current(): ?array
    {
        if ($this->stack === []) {
            return null;
        }

        return $this->stack[array_key_last($this->stack)];
    }

    public function trail(): array
    {
        return $this->stack;
    }

    public function clear(): void
    {
        $this->stack = [];
    }
}
