<?php

declare(strict_types=1);

namespace Platform\Modules\Telegram\Rendering;

use Platform\Contracts\Navigation\ViewModel;
use Platform\Contracts\Telegram\BotGatewayInterface;

/**
 * Renders a ViewModel through BotGateway. Never contains business rules.
 */
final class UiRenderer
{
    public function __construct(
        private readonly BotGatewayInterface $bot,
    ) {
    }

    public function render(ViewModel $view): void
    {
        $chatId = isset($view->meta['chat_id']) ? (string) $view->meta['chat_id'] : '';
        if ($chatId === '') {
            throw new \InvalidArgumentException('ViewModel meta.chat_id is required to render.');
        }

        $markup = $view->inlineKeyboard === []
            ? null
            : ['inline_keyboard' => $this->mapKeyboard($view->inlineKeyboard)];

        $callbackId = isset($view->meta['callback_query_id']) ? (string) $view->meta['callback_query_id'] : null;
        if ($callbackId !== null && $callbackId !== '') {
            $this->bot->answerCallbackQuery($callbackId);
        }

        $messageId = isset($view->meta['message_id']) ? (string) $view->meta['message_id'] : null;
        if ($view->editMessage && $messageId !== null && $messageId !== '') {
            $this->bot->editMessageText($chatId, $messageId, $view->text, $markup, $view->parseHtml);

            return;
        }

        $this->bot->sendMessage($chatId, $view->text, $markup, $view->parseHtml);
    }

    /**
     * @param list<list<array{text: string, callback_data?: string, url?: string}>> $rows
     * @return list<list<array<string, mixed>>>
     */
    private function mapKeyboard(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $mapped = [];
            foreach ($row as $button) {
                $btn = ['text' => $button['text']];
                if (isset($button['callback_data'])) {
                    $btn['callback_data'] = $button['callback_data'];
                }
                if (isset($button['url'])) {
                    $btn['url'] = $button['url'];
                }
                $mapped[] = $btn;
            }
            $out[] = $mapped;
        }

        return $out;
    }
}
