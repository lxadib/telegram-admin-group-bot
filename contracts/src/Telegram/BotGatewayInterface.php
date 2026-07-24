<?php

declare(strict_types=1);

namespace Platform\Contracts\Telegram;

/**
 * Outbound Bot API port. Domain modules never call Telegram HTTP directly.
 */
interface BotGatewayInterface
{
    /**
     * @param array{inline_keyboard?: list<list<array<string, mixed>>>}|null $replyMarkup
     * @return array<string, mixed> Telegram API result payload
     */
    public function sendMessage(
        string $chatId,
        string $text,
        ?array $replyMarkup = null,
        bool $parseHtml = false,
    ): array;

    /**
     * @param array{inline_keyboard?: list<list<array<string, mixed>>>}|null $replyMarkup
     * @return array<string, mixed>
     */
    public function editMessageText(
        string $chatId,
        string $messageId,
        string $text,
        ?array $replyMarkup = null,
        bool $parseHtml = false,
    ): array;

    public function answerCallbackQuery(string $callbackQueryId, ?string $text = null, bool $showAlert = false): void;
}
