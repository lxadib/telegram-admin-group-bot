<?php

declare(strict_types=1);

namespace Platform\Contracts\Telegram;

use Platform\Contracts\Navigation\ViewModel;

/**
 * Outcome of UpdateIngress::handle().
 */
final readonly class IngressResult
{
    public function __construct(
        public IngressStatus $status,
        public ?IncomingUpdate $update = null,
        public ?ViewModel $view = null,
        public ?string $reason = null,
    ) {
    }
}
