<?php

declare(strict_types=1);

namespace Platform\Adapters\Telegram;

use Platform\Contracts\Telegram\BotGatewayInterface;

/**
 * Records outbound Bot API calls for fixture tests. Never hits the network.
 */
final class RecordingBotGateway implements BotGatewayInterface
{
    /** @var list<array{method: string, args: array<string, mixed>}> */
    public array $calls = [];

    public function sendMessage(
        string $chatId,
        string $text,
        ?array $replyMarkup = null,
        bool $parseHtml = false,
    ): array {
        $this->calls[] = [
            'method' => 'sendMessage',
            'args' => [
                'chat_id' => $chatId,
                'text' => $text,
                'reply_markup' => $replyMarkup,
                'parse_html' => $parseHtml,
            ],
        ];

        return ['ok' => true, 'result' => ['message_id' => count($this->calls), 'chat' => ['id' => $chatId]]];
    }

    public function editMessageText(
        string $chatId,
        string $messageId,
        string $text,
        ?array $replyMarkup = null,
        bool $parseHtml = false,
    ): array {
        $this->calls[] = [
            'method' => 'editMessageText',
            'args' => [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'reply_markup' => $replyMarkup,
                'parse_html' => $parseHtml,
            ],
        ];

        return ['ok' => true, 'result' => ['message_id' => (int) $messageId, 'chat' => ['id' => $chatId]]];
    }

    public function answerCallbackQuery(string $callbackQueryId, ?string $text = null, bool $showAlert = false): void
    {
        $this->calls[] = [
            'method' => 'answerCallbackQuery',
            'args' => [
                'callback_query_id' => $callbackQueryId,
                'text' => $text,
                'show_alert' => $showAlert,
            ],
        ];
    }
}
