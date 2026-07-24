<?php

declare(strict_types=1);

namespace Platform\Contracts\Navigation;

/**
 * Transport-agnostic UI result. Telegram adapter renders this to messages/keyboards.
 *
 * @phpstan-type KeyboardButton array{text: string, callback_data?: string, url?: string}
 * @phpstan-type KeyboardRow list<KeyboardButton>
 */
final readonly class ViewModel
{
    /**
     * @param list<KeyboardRow> $inlineKeyboard
     * @param array<string, mixed> $meta
     */
    public function __construct(
        public string $text,
        public array $inlineKeyboard = [],
        public bool $parseHtml = false,
        public bool $editMessage = true,
        public array $meta = [],
    ) {
    }
}
