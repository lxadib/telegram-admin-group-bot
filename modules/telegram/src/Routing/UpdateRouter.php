<?php

declare(strict_types=1);

namespace Platform\Modules\Telegram\Routing;

use Platform\Contracts\Navigation\ViewModel;
use Platform\Contracts\Telegram\IncomingUpdate;
use Platform\Contracts\Telegram\UpdateKind;
use Platform\Modules\Telegram\Command\CommandRegistry;
use Platform\Modules\Telegram\Handler\CallbackRouteHandler;
use Platform\Modules\Telegram\Handler\StartCommandHandler;
use Platform\Modules\Telegram\Identity\ActorContextResolver;

/**
 * Maps normalized updates to handlers. No domain rules: /start links + shows home,
 * registered slash-commands run with a resolved ActorContext, callbacks navigate.
 */
final class UpdateRouter
{
    public function __construct(
        private readonly StartCommandHandler $start,
        private readonly CallbackRouteHandler $callbacks,
        private readonly ActorContextResolver $actors,
        private readonly CommandRegistry $commands,
    ) {
    }

    public function route(IncomingUpdate $update): ?ViewModel
    {
        if ($update->kind === UpdateKind::Message && $update->command !== null) {
            if ($update->isCommand('/start')) {
                return $this->start->handle($update);
            }

            $handler = $this->commands->find($update->command);
            if ($handler !== null) {
                return $handler->handle($update, $this->actors->resolve($update));
            }
        }

        if ($update->kind === UpdateKind::CallbackQuery) {
            return $this->callbacks->handle($update);
        }

        return null;
    }
}
