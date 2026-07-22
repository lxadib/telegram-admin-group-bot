<?php

declare(strict_types=1);

namespace Platform\Kernel;

/**
 * Platform identity. Kernel must not contain domain or Telegram logic.
 */
final class Platform
{
    public const string NAME = 'Telegram Bot Platform';

    public const string VERSION = '0.1.0';

    /**
     * @return array{name: string, version: string, php: string}
     */
    public static function identity(): array
    {
        return [
            'name' => self::NAME,
            'version' => self::VERSION,
            'php' => PHP_VERSION,
        ];
    }
}
