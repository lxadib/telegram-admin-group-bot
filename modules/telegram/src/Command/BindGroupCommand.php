<?php

declare(strict_types=1);

namespace Platform\Modules\Telegram\Command;

use Platform\Contracts\Auth\ActorContext;
use Platform\Contracts\Auth\AuthorizerInterface;
use Platform\Contracts\Capability\CapabilityDeniedException;
use Platform\Contracts\Groups\GroupRegistryInterface;
use Platform\Contracts\Navigation\ViewModel;
use Platform\Contracts\Telegram\IncomingUpdate;

/**
 * /bind — bind the current Telegram group to the actor's tenant.
 */
final class BindGroupCommand implements CommandHandlerInterface
{
    public function __construct(
        private readonly GroupRegistryInterface $groups,
        private readonly AuthorizerInterface $authorizer,
    ) {
    }

    public function name(): string
    {
        return '/bind';
    }

    public function handle(IncomingUpdate $update, ActorContext $actor): ViewModel
    {
        if ($update->isPrivateChat || $update->chatId === null) {
            return $this->reply($update, 'Run /bind inside the group you want to manage.');
        }

        $tenantId = $actor->tenantId;
        if ($tenantId === null) {
            return $this->reply($update, 'Your account is not ready yet. Send /start first.');
        }

        if (!$this->authorizer->can($actor, 'groups.bind')) {
            return $this->reply($update, 'You are not allowed to bind groups.');
        }

        try {
            $record = $this->groups->bind($tenantId, $update->chatId, $update->displayName);
        } catch (CapabilityDeniedException) {
            return $this->reply($update, 'No usable license or group limit reached. Activate a plan with /activate, then retry /bind.');
        } catch (\RuntimeException) {
            return $this->reply($update, 'This group is already bound to another workspace.');
        }

        return $this->reply($update, sprintf('Group bound (id %s). You can now use moderation commands here.', $record->groupId));
    }

    private function reply(IncomingUpdate $update, string $text): ViewModel
    {
        return new ViewModel(
            text: $text,
            editMessage: false,
            meta: ['chat_id' => $update->chatId],
        );
    }
}
