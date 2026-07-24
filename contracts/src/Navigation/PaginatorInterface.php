<?php

declare(strict_types=1);

namespace Platform\Contracts\Navigation;

/**
 * Cursor-based pagination for Telegram inline keyboards.
 */
interface PaginatorInterface
{
    public function page(): int;

    public function perPage(): int;

    public function total(): int;

    public function hasNext(): bool;

    public function hasPrevious(): bool;

    /**
     * @return array{page: int, per_page: int, total: int, has_next: bool, has_previous: bool}
     */
    public function meta(): array;
}
