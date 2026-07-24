<?php

declare(strict_types=1);

namespace Platform\Modules\Users\Application;

use Platform\Contracts\Event\EventBusInterface;
use Platform\Contracts\Identity\IdGeneratorInterface;
use Platform\Contracts\Users\Events\UserLinked;
use Platform\Contracts\Users\IdentityLinkerInterface;
use Platform\Contracts\Users\UserRecord;

final class InMemoryIdentityLinker implements IdentityLinkerInterface
{
    public function __construct(
        private readonly InMemoryUserDirectory $directory,
        private readonly IdGeneratorInterface $ids,
        private readonly EventBusInterface $events,
    ) {
    }

    public function linkTelegram(string $telegramUserId, ?string $userId = null, array $attributes = []): UserRecord
    {
        if ($telegramUserId === '') {
            throw new \InvalidArgumentException('telegramUserId is required.');
        }

        $existing = $this->directory->findByTelegramId($telegramUserId);
        if ($existing !== null) {
            return $existing;
        }

        $userId ??= $this->ids->generate();
        $this->directory->ensure($userId, $attributes);
        $record = $this->directory->attachTelegram($userId, $telegramUserId);

        $this->events->dispatch(new UserLinked(
            userId: $record->userId,
            telegramUserId: $telegramUserId,
            tenantId: $record->tenantId,
        ));

        return $record;
    }

    public function unlinkTelegram(string $telegramUserId): void
    {
        $this->directory->detachTelegram($telegramUserId);
    }
}
