<?php

declare(strict_types=1);

namespace Platform\Modules\Groups\Listener;

use Platform\Contracts\Capability\Events\LicenseActivated;
use Platform\Contracts\Event\EventListenerInterface;
use Psr\Log\LoggerInterface;

/**
 * Observes license activation so Groups can later hydrate tenant entitlement caches.
 * Phase 5 keeps this as an audited no-op side effect (boundary + event wiring proof).
 */
final class LicenseActivatedListener implements EventListenerInterface
{
    /** @var list<string> */
    public array $seenTenantIds = [];

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function eventClass(): string
    {
        return LicenseActivated::class;
    }

    public function moduleId(): string
    {
        return 'groups';
    }

    public function handle(object $event): void
    {
        if (!$event instanceof LicenseActivated) {
            return;
        }

        $this->seenTenantIds[] = $event->tenantId;
        $this->logger->info('Groups noted license activation.', [
            'tenant' => $event->tenantId,
            'license' => $event->licenseId,
            'state' => $event->state,
        ]);
    }
}
