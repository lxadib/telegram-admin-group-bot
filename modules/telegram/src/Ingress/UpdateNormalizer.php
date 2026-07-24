<?php

declare(strict_types=1);

namespace Platform\Modules\Telegram\Ingress;

use Platform\Contracts\Telegram\IncomingUpdate;
use Platform\Contracts\Telegram\UpdateKind;

/**
 * Maps raw Bot API Update arrays to IncomingUpdate. No business logic.
 */
final class UpdateNormalizer
{
    /**
     * @param array<string, mixed> $raw
     */
    public function normalize(array $raw): IncomingUpdate
    {
        $updateId = isset($raw['update_id']) && is_numeric($raw['update_id']) ? (int) $raw['update_id'] : 0;

        if (isset($raw['callback_query']) && is_array($raw['callback_query'])) {
            return $this->fromCallback($updateId, $raw['callback_query']);
        }

        if (isset($raw['message']) && is_array($raw['message'])) {
            return $this->fromMessage($updateId, $raw['message']);
        }

        return new IncomingUpdate($updateId, UpdateKind::Unknown);
    }

    /**
     * @param array<string, mixed> $message
     */
    private function fromMessage(int $updateId, array $message): IncomingUpdate
    {
        $from = isset($message['from']) && is_array($message['from']) ? $message['from'] : [];
        $chat = isset($message['chat']) && is_array($message['chat']) ? $message['chat'] : [];
        $text = isset($message['text']) && is_string($message['text']) ? $message['text'] : null;
        $command = $this->extractCommand($text);

        $replyFrom = [];
        if (isset($message['reply_to_message']) && is_array($message['reply_to_message'])
            && isset($message['reply_to_message']['from']) && is_array($message['reply_to_message']['from'])) {
            $replyFrom = $message['reply_to_message']['from'];
        }

        return new IncomingUpdate(
            updateId: $updateId,
            kind: UpdateKind::Message,
            telegramUserId: $this->stringId($from['id'] ?? null),
            chatId: $this->stringId($chat['id'] ?? null),
            messageId: $this->stringId($message['message_id'] ?? null),
            text: $text,
            command: $command,
            languageCode: isset($from['language_code']) && is_string($from['language_code']) ? $from['language_code'] : null,
            displayName: $this->displayName($from),
            isPrivateChat: ($chat['type'] ?? null) === 'private',
            replyToUserId: $this->stringId($replyFrom['id'] ?? null),
        );
    }

    /**
     * @param array<string, mixed> $callback
     */
    private function fromCallback(int $updateId, array $callback): IncomingUpdate
    {
        $from = isset($callback['from']) && is_array($callback['from']) ? $callback['from'] : [];
        $message = isset($callback['message']) && is_array($callback['message']) ? $callback['message'] : [];
        $chat = isset($message['chat']) && is_array($message['chat']) ? $message['chat'] : [];
        $dataRaw = isset($callback['data']) && is_string($callback['data']) ? $callback['data'] : '';
        $data = $this->decodeCallbackData($dataRaw);

        return new IncomingUpdate(
            updateId: $updateId,
            kind: UpdateKind::CallbackQuery,
            telegramUserId: $this->stringId($from['id'] ?? null),
            chatId: $this->stringId($chat['id'] ?? null),
            messageId: $this->stringId($message['message_id'] ?? null),
            callbackQueryId: isset($callback['id']) ? (string) $callback['id'] : null,
            callbackData: $data,
            languageCode: isset($from['language_code']) && is_string($from['language_code']) ? $from['language_code'] : null,
            displayName: $this->displayName($from),
            isPrivateChat: ($chat['type'] ?? null) === 'private',
        );
    }

    private function extractCommand(?string $text): ?string
    {
        if ($text === null || $text === '' || !str_starts_with($text, '/')) {
            return null;
        }

        $space = strpos($text, ' ');
        $token = $space === false ? $text : substr($text, 0, $space);
        if ($token === '') {
            return null;
        }

        // Strip @BotName suffix: /start@MyBot → /start
        $at = strpos($token, '@');
        if ($at !== false) {
            $token = substr($token, 0, $at);
        }

        return strtolower($token);
    }

    /**
     * Callback payloads are compact JSON: {"r":"ui.home","s":"sess"} or plain route id.
     *
     * @return array<string, mixed>
     */
    private function decodeCallbackData(string $raw): array
    {
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            /** @var array<string, mixed> $decoded */
            if (isset($decoded['r']) && is_string($decoded['r'])) {
                $decoded['route'] = $decoded['r'];
                unset($decoded['r']);
            }
            if (isset($decoded['s']) && is_string($decoded['s'])) {
                $decoded['session'] = $decoded['s'];
                unset($decoded['s']);
            }

            return $decoded;
        }

        return ['route' => $raw];
    }

    /**
     * @param array<string, mixed> $from
     */
    private function displayName(array $from): ?string
    {
        $first = isset($from['first_name']) && is_string($from['first_name']) ? $from['first_name'] : '';
        $last = isset($from['last_name']) && is_string($from['last_name']) ? $from['last_name'] : '';
        $name = trim($first . ' ' . $last);

        return $name !== '' ? $name : null;
    }

    private function stringId(mixed $value): ?string
    {
        if (is_int($value) || is_string($value)) {
            $id = (string) $value;

            return $id !== '' ? $id : null;
        }

        return null;
    }
}
