<?php

declare(strict_types=1);

namespace Platform\Adapters\Telegram;

use Platform\Contracts\Telegram\BotGatewayInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Thin HTTPS client for api.telegram.org. Used by apps/bot when a token is configured.
 */
final class HttpBotGateway implements BotGatewayInterface
{
    public function __construct(
        private readonly string $token,
        private readonly LoggerInterface $logger = new NullLogger(),
        private readonly string $apiBase = 'https://api.telegram.org',
    ) {
        if ($this->token === '') {
            throw new \InvalidArgumentException('HttpBotGateway requires a non-empty bot token.');
        }
    }

    public function sendMessage(
        string $chatId,
        string $text,
        ?array $replyMarkup = null,
        bool $parseHtml = false,
    ): array {
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
        ];
        if ($parseHtml) {
            $payload['parse_mode'] = 'HTML';
        }
        if ($replyMarkup !== null) {
            $payload['reply_markup'] = $replyMarkup;
        }

        return $this->call('sendMessage', $payload);
    }

    public function editMessageText(
        string $chatId,
        string $messageId,
        string $text,
        ?array $replyMarkup = null,
        bool $parseHtml = false,
    ): array {
        $payload = [
            'chat_id' => $chatId,
            'message_id' => (int) $messageId,
            'text' => $text,
        ];
        if ($parseHtml) {
            $payload['parse_mode'] = 'HTML';
        }
        if ($replyMarkup !== null) {
            $payload['reply_markup'] = $replyMarkup;
        }

        return $this->call('editMessageText', $payload);
    }

    public function answerCallbackQuery(string $callbackQueryId, ?string $text = null, bool $showAlert = false): void
    {
        $payload = [
            'callback_query_id' => $callbackQueryId,
            'show_alert' => $showAlert,
        ];
        if ($text !== null) {
            $payload['text'] = $text;
        }

        $this->call('answerCallbackQuery', $payload);
    }

    /**
     * Long-polling helper for apps/bot. Not part of BotGatewayInterface.
     *
     * @return list<array<string, mixed>>
     */
    public function getUpdates(int $offset = 0, int $timeout = 25, int $limit = 100): array
    {
        /** @var array{ok?: bool, result?: list<array<string, mixed>>} $response */
        $response = $this->call('getUpdates', [
            'offset' => $offset,
            'timeout' => $timeout,
            'limit' => $limit,
        ]);

        $result = $response['result'] ?? null;
        if (!is_array($result)) {
            return [];
        }

        /** @var list<array<string, mixed>> $updates */
        $updates = [];
        foreach ($result as $item) {
            $updates[] = $item;
        }

        return $updates;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function call(string $method, array $payload): array
    {
        $url = sprintf('%s/bot%s/%s', rtrim($this->apiBase, '/'), $this->token, $method);
        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $body,
                'timeout' => 15,
                'ignore_errors' => true,
            ],
        ]);

        $response = file_get_contents($url, false, $context);
        if ($response === false) {
            $this->logger->error('Telegram API transport failure.', ['method' => $method]);

            throw new \RuntimeException(sprintf('Telegram API call "%s" failed.', $method));
        }

        $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded) || ($decoded['ok'] ?? false) !== true) {
            $description = is_array($decoded) && isset($decoded['description']) && is_string($decoded['description'])
                ? $decoded['description']
                : 'unknown';
            $this->logger->error('Telegram API error.', [
                'method' => $method,
                'description' => $description,
            ]);

            throw new \RuntimeException(sprintf(
                'Telegram API "%s" rejected: %s',
                $method,
                $description,
            ));
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }
}
