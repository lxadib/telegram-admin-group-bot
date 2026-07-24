<?php

declare(strict_types=1);

namespace Platform\Modules\Permissions\Listener;

use Platform\Contracts\Event\EventListenerInterface;
use Platform\Contracts\Users\Events\UserLinked;
use Platform\Modules\Permissions\Application\RbacAuthorizer;
use Psr\Log\LoggerInterface;

/**
 * Seeds a baseline "users.view" grant when a Telegram identity is linked.
 */
final class UserLinkedListener implements EventListenerInterface
{
    /** @var list<string> */
    public array $seenUserIds = [];

    public function __construct(
        private readonly RbacAuthorizer $authorizer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function eventClass(): string
    {
        return UserLinked::class;
    }

    public function moduleId(): string
    {
        return 'permissions';
    }

    public function handle(object $event): void
    {
        if (!$event instanceof UserLinked) {
            return;
        }

        $this->seenUserIds[] = $event->userId;
        $this->authorizer->grant($event->userId, 'users.view');
        $this->logger->info('Permissions seeded users.view after link.', [
            'user' => $event->userId,
            'telegram' => $event->telegramUserId,
        ]);
    }
}
