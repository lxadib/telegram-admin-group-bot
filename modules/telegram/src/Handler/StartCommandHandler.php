<?php

declare(strict_types=1);

namespace Platform\Modules\Telegram\Handler;

use Platform\Contracts\Localization\LocaleResolverInterface;
use Platform\Contracts\Localization\TranslatorInterface;
use Platform\Contracts\Navigation\MenuRegistryInterface;
use Platform\Contracts\Navigation\NavStackFactoryInterface;
use Platform\Contracts\Navigation\ViewModel;
use Platform\Contracts\Telegram\IncomingUpdate;
use Platform\Contracts\Users\IdentityLinkerInterface;

/**
 * Handles /start — links Telegram identity and returns the home ViewModel.
 * Orchestration only; no domain rules beyond calling Users + UI ports.
 */
final class StartCommandHandler
{
    public function __construct(
        private readonly IdentityLinkerInterface $linker,
        private readonly NavStackFactoryInterface $navFactory,
        private readonly MenuRegistryInterface $menus,
        private readonly LocaleResolverInterface $locales,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function handle(IncomingUpdate $update): ViewModel
    {
        if ($update->telegramUserId === null || $update->chatId === null) {
            throw new \InvalidArgumentException('/start requires a Telegram user and chat.');
        }

        $user = $this->linker->linkTelegram(
            $update->telegramUserId,
            attributes: [
                'displayName' => $update->displayName,
                'locale' => $this->locales->resolve($update->languageCode),
            ],
        );

        $sessionId = 'tg:' . $update->telegramUserId;
        $stack = $this->navFactory->forSession($sessionId);
        $stack->clear();
        $stack->push('ui.home', ['userId' => $user->userId]);

        $home = $this->menus->get('ui.home');
        $label = $home !== null
            ? $this->translator->trans($home->labelKey, [], $user->locale)
            : 'Home';

        $greeting = $this->translator->trans(
            'telegram.welcome',
            ['name' => $user->displayName ?? 'there'],
            $user->locale,
        );

        // Fallback when catalog has no key yet.
        if ($greeting === 'telegram.welcome') {
            $greeting = sprintf('Welcome, %s. Use the menu below.', $user->displayName ?? 'there');
        }

        return new ViewModel(
            text: $greeting,
            inlineKeyboard: [[
                [
                    'text' => $label === 'ui.menu.home' ? 'Home' : $label,
                    'callback_data' => json_encode(['r' => 'ui.home', 's' => $sessionId], JSON_THROW_ON_ERROR),
                ],
            ]],
            editMessage: false,
            meta: [
                'chat_id' => $update->chatId,
                'session_id' => $sessionId,
                'user_id' => $user->userId,
            ],
        );
    }
}
