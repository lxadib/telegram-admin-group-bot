<?php

declare(strict_types=1);

namespace Platform\Modules\Telegram\Command;

use Platform\Contracts\Auth\ActorContext;
use Platform\Contracts\Navigation\ViewModel;
use Platform\Contracts\Telegram\IncomingUpdate;

/**
 * A Telegram slash-command handler. Transport orchestration only: it resolves an
 * ActorContext upstream and drives domain ports through contracts.
 */
interface CommandHandlerInterface
{
    /**
     * Command token this handler answers to, e.g. "/bind".
     *
     * @return non-empty-string
     */
    public function name(): string;

    public function handle(IncomingUpdate $update, ActorContext $actor): ViewModel;
}
