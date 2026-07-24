<?php

declare(strict_types=1);

namespace Platform\Modules\Ui\Application;

use Platform\Contracts\Navigation\PaginatorInterface;

/**
 * Offset/limit paginator for Telegram keyboards.
 */
final class OffsetPaginator implements PaginatorInterface
{
    public function __construct(
        private readonly int $page,
        private readonly int $perPage,
        private readonly int $total,
    ) {
        if ($this->page < 1 || $this->perPage < 1 || $this->total < 0) {
            throw new \InvalidArgumentException('Invalid pagination parameters.');
        }
    }

    public function page(): int
    {
        return $this->page;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function total(): int
    {
        return $this->total;
    }

    public function hasNext(): bool
    {
        return $this->page * $this->perPage < $this->total;
    }

    public function hasPrevious(): bool
    {
        return $this->page > 1;
    }

    public function meta(): array
    {
        return [
            'page' => $this->page,
            'per_page' => $this->perPage,
            'total' => $this->total,
            'has_next' => $this->hasNext(),
            'has_previous' => $this->hasPrevious(),
        ];
    }
}
