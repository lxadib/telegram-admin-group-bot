<?php

declare(strict_types=1);

namespace Platform\Contracts\Telegram;

/**
 * Result of processing one Telegram update through UpdateIngress.
 */
enum IngressStatus: string
{
    case Processed = 'processed';
    case Duplicate = 'duplicate';
    case Ignored = 'ignored';
    case Unauthorized = 'unauthorized';
}
