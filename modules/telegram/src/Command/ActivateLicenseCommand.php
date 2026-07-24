<?php

declare(strict_types=1);

namespace Platform\Modules\Telegram\Command;

use Platform\Contracts\Auth\ActorContext;
use Platform\Contracts\Auth\AuthorizerInterface;
use Platform\Contracts\Capability\LicenseServiceInterface;
use Platform\Contracts\Navigation\ViewModel;
use Platform\Contracts\Telegram\IncomingUpdate;

/**
 * /activate [PLAN] — activate (or switch) the actor tenant's license.
 * MVP plans are hard-coded; billing/provisioning is a later phase.
 */
final class ActivateLicenseCommand implements CommandHandlerInterface
{
    public function __construct(
        private readonly LicenseServiceInterface $licenses,
        private readonly AuthorizerInterface $authorizer,
    ) {
    }

    public function name(): string
    {
        return '/activate';
    }

    public function handle(IncomingUpdate $update, ActorContext $actor): ViewModel
    {
        $tenantId = $actor->tenantId;
        if ($tenantId === null) {
            return $this->reply($update, 'Your account is not ready yet. Send /start first.');
        }

        if (!$this->authorizer->can($actor, 'licenses.activate')) {
            return $this->reply($update, 'You are not allowed to activate a license.');
        }

        $args = $update->arguments();
        $code = strtoupper($args[0] ?? 'TRIAL');
        $plan = $this->plan($code);

        $snapshot = $this->licenses->activate(
            $tenantId,
            licenseId: $code,
            features: $plan['features'],
            limits: $plan['limits'],
            state: 'active',
        );

        $enabled = array_keys(array_filter($snapshot->features));
        $featureList = $enabled === [] ? 'none' : implode(', ', $enabled);

        return $this->reply($update, sprintf(
            'License "%s" activated. Features: %s. Groups allowed: %d.',
            $code,
            $featureList,
            $snapshot->maxGroups,
        ));
    }

    /**
     * @return array{features: array<string, bool>, limits: array{maxGroups: int, maxModules: int, maxAdmins: int, maxModerators: int}}
     */
    private function plan(string $code): array
    {
        return match ($code) {
            'PRO' => [
                'features' => ['moderation' => true, 'automation' => true, 'analytics' => true],
                'limits' => ['maxGroups' => 10, 'maxModules' => 25, 'maxAdmins' => 20, 'maxModerators' => 100],
            ],
            default => [
                'features' => ['moderation' => true, 'automation' => true],
                'limits' => ['maxGroups' => 2, 'maxModules' => 10, 'maxAdmins' => 5, 'maxModerators' => 20],
            ],
        };
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
