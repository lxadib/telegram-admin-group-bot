<?php

declare(strict_types=1);

namespace Platform\Kernel\Telegram;

use Platform\Contracts\Telegram\BotGatewayInterface;

/**
 * No-op gateway used when TELEGRAM_BOT_TOKEN is unset. Safe default for tests/console.
 */
final class NullBotGateway implements BotGatewayInterface
{
    public function sendMessage(
        string $chatId,
        string $text,
        ?array $replyMarkup = null,
        bool $parseHtml = false,
    ): array {
        return ['ok' => true, 'result' => ['message_id' => 0, 'chat' => ['id' => $chatId]]];
    }

    public function editMessageText(
        string $chatId,
        string $messageId,
        string $text,
        ?array $replyMarkup = null,
        bool $parseHtml = false,
    ): array {
        return ['ok' => true, 'result' => ['message_id' => (int) $messageId, 'chat' => ['id' => $chatId]]];
    }

    public function answerCallbackQuery(string $callbackQueryId, ?string $text = null, bool $showAlert = false): void
    {
    }
}
