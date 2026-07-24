<?php

declare(strict_types=1);

namespace Platform\Modules\Telegram\Identity;

use Platform\Contracts\Auth\ActorContext;
use Platform\Contracts\Auth\ActorRole;
use Platform\Contracts\Groups\GroupRegistryInterface;
use Platform\Contracts\Telegram\IncomingUpdate;
use Platform\Contracts\Users\IdentityLinkerInterface;

/**
 * Builds a request-scoped ActorContext from a Telegram update.
 *
 * MVP tenancy model: every Telegram user owns a personal tenant ("tenant:{userId}").
 * A bound group belongs to the tenant of whoever bound it; an actor only receives a
 * groupId when the current chat is bound to *their own* tenant, so authorization and
 * data scope stay consistent. Cross-tenant co-admin delegation is a later phase.
 */
final class ActorContextResolver
{
    public function __construct(
        private readonly IdentityLinkerInterface $linker,
        private readonly GroupRegistryInterface $groups,
        private readonly ?string $superOwnerTelegramId = null,
    ) {
    }

    public function resolve(IncomingUpdate $update): ActorContext
    {
        if ($update->telegramUserId === null) {
            throw new \InvalidArgumentException('Cannot resolve an actor without a Telegram user id.');
        }

        $user = $this->linker->linkTelegram(
            $update->telegramUserId,
            attributes: [
                'displayName' => $update->displayName,
                'locale' => $update->languageCode,
            ],
        );

        $tenantId = 'tenant:' . $user->userId;

        /** @var list<ActorRole|string> $roles */
        $roles = [ActorRole::Customer];
        if ($this->superOwnerTelegramId !== null && $this->superOwnerTelegramId === $update->telegramUserId) {
            $roles[] = ActorRole::SuperOwner;
        }

        $groupId = null;
        if (!$update->isPrivateChat && $update->chatId !== null) {
            $group = $this->groups->findByTelegramChatId($update->chatId);
            if ($group !== null && $group->active && $group->tenantId === $tenantId) {
                $groupId = $group->groupId;
            }
        }

        return new ActorContext(
            actorId: $user->userId,
            roles: $roles,
            tenantId: $tenantId,
            groupId: $groupId,
            telegramUserId: $update->telegramUserId,
            locale: $user->locale,
        );
    }
}
