<?php

declare(strict_types=1);

namespace Platform\Contracts\Telegram;

/**
 * Kind of normalized Telegram update the platform understands in Phase 6.
 */
enum UpdateKind: string
{
    case Message = 'message';
    case CallbackQuery = 'callback_query';
    case Unknown = 'unknown';
}
