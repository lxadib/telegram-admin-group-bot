<?php

declare(strict_types=1);

namespace Platform\Modules\Telegram\Routing;

use Platform\Contracts\Navigation\ViewModel;
use Platform\Contracts\Telegram\IncomingUpdate;
use Platform\Contracts\Telegram\UpdateKind;
use Platform\Modules\Telegram\Handler\CallbackRouteHandler;
use Platform\Modules\Telegram\Handler\StartCommandHandler;

/**
 * Maps normalized updates to handlers. No domain rules.
 */
final class UpdateRouter
{
    public function __construct(
        private readonly StartCommandHandler $start,
        private readonly CallbackRouteHandler $callbacks,
    ) {
    }

    public function route(IncomingUpdate $update): ?ViewModel
    {
        if ($update->kind === UpdateKind::Message && $update->isCommand('/start')) {
            return $this->start->handle($update);
        }

        if ($update->kind === UpdateKind::CallbackQuery) {
            return $this->callbacks->handle($update);
        }

        return null;
    }
}
