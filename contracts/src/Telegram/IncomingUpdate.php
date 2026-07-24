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
    ) {
    }

    public function isCommand(string $name): bool
    {
        return $this->command === $name;
    }
}
