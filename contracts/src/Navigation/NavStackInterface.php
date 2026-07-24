<?php

declare(strict_types=1);

namespace Platform\Contracts\Navigation;

/**
 * Server-side navigation stack (breadcrumbs / back button).
 */
interface NavStackInterface
{
    public function sessionId(): string;

    /**
     * @param array<string, mixed> $state
     */
    public function push(string $route, array $state = []): void;

    /**
     * @return array{route: string, state: array<string, mixed>}|null
     */
    public function pop(): ?array;

    /**
     * @return array{route: string, state: array<string, mixed>}|null
     */
    public function current(): ?array;

    /**
     * @return list<array{route: string, state: array<string, mixed>}>
     */
    public function trail(): array;

    public function clear(): void;
}
