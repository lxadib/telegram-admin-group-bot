<?php

declare(strict_types=1);

namespace Platform\Contracts\Moderation;

/**
 * Moderation actions applied to a member within a bound group.
 */
enum ModerationAction: string
{
    case Warn = 'warn';
    case Mute = 'mute';
    case Ban = 'ban';
}
