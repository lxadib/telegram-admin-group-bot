<?php

declare(strict_types=1);

namespace Platform\Modules\Telegram\Command;

use Platform\Contracts\Auth\ActorContext;
use Platform\Contracts\Auth\AuthorizerInterface;
use Platform\Contracts\Capability\CapabilityDeniedException;
use Platform\Contracts\Moderation\ModerationAction;
use Platform\Contracts\Moderation\ModerationServiceInterface;
use Platform\Contracts\Navigation\ViewModel;
use Platform\Contracts\Telegram\IncomingUpdate;

/**
 * /warn | /mute | /ban — one class configured per action so all three share the
 * same target-resolution, capability, and RBAC handling.
 */
final class ModerationCommand implements CommandHandlerInterface
{
    /**
     * @param non-empty-string $command
     * @param non-empty-string $permission
     */
    public function __construct(
        private readonly string $command,
        private readonly ModerationAction $action,
        private readonly string $permission,
        private readonly ModerationServiceInterface $moderation,
        private readonly AuthorizerInterface $authorizer,
    ) {
    }

    public function name(): string
    {
        return $this->command;
    }

    public function handle(IncomingUpdate $update, ActorContext $actor): ViewModel
    {
        $tenantId = $actor->tenantId;
        $groupId = $actor->groupId;

        if ($update->isPrivateChat || $groupId === null || $tenantId === null) {
            return $this->reply($update, 'Use this command inside a group bound to your workspace (/bind first).');
        }

        if (!$this->authorizer->can($actor, $this->permission, $groupId)) {
            return $this->reply($update, 'You are not allowed to moderate this group.');
        }

        $args = $update->arguments();
        $target = $update->replyToUserId;
        if ($target === null) {
            $target = $args[0] ?? null;
            $args = array_slice($args, 1);
        }

        if ($target === null || $target === '') {
            return $this->reply($update, sprintf('Reply to a member or pass a user id: %s <user_id> [reason]', $this->command));
        }

        $reason = implode(' ', $args);

        try {
            $message = $this->apply($tenantId, $groupId, $target, $actor->actorId, $reason);
        } catch (CapabilityDeniedException) {
            return $this->reply($update, 'Moderation is not available on your current plan.');
        }

        if ($reason !== '') {
            $message .= sprintf(' Reason: %s', $reason);
        }

        return $this->reply($update, $message);
    }

    private function apply(string $tenantId, string $groupId, string $target, string $actorId, string $reason): string
    {
        return match ($this->action) {
            ModerationAction::Warn => sprintf(
                'Warned user %s (total warnings: %d).',
                $target,
                $this->moderation->warn($tenantId, $groupId, $target, $actorId, $reason),
            ),
            ModerationAction::Mute => $this->muteMessage($tenantId, $groupId, $target, $actorId, $reason),
            ModerationAction::Ban => $this->banMessage($tenantId, $groupId, $target, $actorId, $reason),
        };
    }

    private function muteMessage(string $tenantId, string $groupId, string $target, string $actorId, string $reason): string
    {
        $this->moderation->mute($tenantId, $groupId, $target, $actorId, $reason);

        return sprintf('Muted user %s.', $target);
    }

    private function banMessage(string $tenantId, string $groupId, string $target, string $actorId, string $reason): string
    {
        $this->moderation->ban($tenantId, $groupId, $target, $actorId, $reason);

        return sprintf('Banned user %s.', $target);
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
