<?php

declare(strict_types=1);

namespace Platform\Contracts\Telegram;

/**
 * Transport-normalized update. Business code never sees raw Bot API arrays.
 *
 * @phpstan-type CallbackData array{route?: string, session?: string}|array<string, mixed>
 */
final readonly class IncomingUpdate
{
    /**
     * @param CallbackData|null $callbackData
     */
    public function __construct(
        public int $updateId,
        public UpdateKind $kind,
        public ?string $telegramUserId = null,
        public ?string $chatId = null,
        public ?string $messageId = null,
        public ?string $callbackQueryId = null,
        public ?string $text = null,
        public ?string $command = null,
        public ?array $callbackData = null,
        public ?string $languageCode = null,
        public ?string $displayName = null,
        public bool $isPrivateChat = false,
        public ?string $replyToUserId = null,
    ) {
    }

    public function isCommand(string $name): bool
    {
        return $this->command === $name;
    }

    /**
     * Whitespace-separated arguments after the command token.
     * Example: "/mute 999 being spammy" → ["999", "being", "spammy"].
     *
     * @return list<string>
     */
    public function arguments(): array
    {
        if ($this->text === null || $this->command === null) {
            return [];
        }

        $parts = preg_split('/\s+/', trim($this->text));
        if ($parts === false || $parts === []) {
            return [];
        }

        array_shift($parts);

        return array_values(array_filter($parts, static fn (string $p): bool => $p !== ''));
    }
}
