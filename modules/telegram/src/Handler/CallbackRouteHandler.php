<?php

declare(strict_types=1);

namespace Platform\Modules\Telegram\Handler;

use Platform\Contracts\Navigation\MenuRegistryInterface;
use Platform\Contracts\Navigation\NavStackFactoryInterface;
use Platform\Contracts\Navigation\ViewModel;
use Platform\Contracts\Telegram\IncomingUpdate;

/**
 * Handles inline-keyboard callbacks by route id. Transport-only navigation.
 */
final class CallbackRouteHandler
{
    public function __construct(
        private readonly NavStackFactoryInterface $navFactory,
        private readonly MenuRegistryInterface $menus,
    ) {
    }

    public function handle(IncomingUpdate $update): ViewModel
    {
        $route = isset($update->callbackData['route']) && is_string($update->callbackData['route'])
            ? $update->callbackData['route']
            : null;
        $sessionId = isset($update->callbackData['session']) && is_string($update->callbackData['session'])
            ? $update->callbackData['session']
            : ($update->telegramUserId !== null ? 'tg:' . $update->telegramUserId : 'anon');

        if ($route === null || $route === '') {
            return new ViewModel(
                text: 'Unknown action.',
                editMessage: true,
                meta: [
                    'chat_id' => $update->chatId,
                    'message_id' => $update->messageId,
                    'callback_query_id' => $update->callbackQueryId,
                ],
            );
        }

        $stack = $this->navFactory->forSession($sessionId);
        $current = $stack->current();
        if ($current === null || $current['route'] !== $route) {
            $stack->push($route);
        }

        $node = $this->menus->get($route);
        $title = $node !== null ? $node->labelKey : $route;

        return new ViewModel(
            text: sprintf('Menu: %s', $title === 'ui.menu.home' ? 'Home' : $title),
            inlineKeyboard: [[
                [
                    'text' => 'Home',
                    'callback_data' => json_encode(['r' => 'ui.home', 's' => $sessionId], JSON_THROW_ON_ERROR),
                ],
            ]],
            editMessage: true,
            meta: [
                'chat_id' => $update->chatId,
                'message_id' => $update->messageId,
                'callback_query_id' => $update->callbackQueryId,
                'session_id' => $sessionId,
                'route' => $route,
            ],
        );
    }
}
