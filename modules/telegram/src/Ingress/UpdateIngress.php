<?php

declare(strict_types=1);

namespace Platform\Modules\Telegram\Ingress;

use Platform\Contracts\Security\IdempotencyStoreInterface;
use Platform\Contracts\Telegram\IngressResult;
use Platform\Contracts\Telegram\IngressStatus;
use Platform\Contracts\Telegram\UpdateIngressInterface;
use Platform\Modules\Telegram\Rendering\UiRenderer;
use Platform\Modules\Telegram\Routing\UpdateRouter;
use Psr\Log\LoggerInterface;

/**
 * Webhook/polling entry: verify secret → claim update_id → normalize → route → render.
 */
final class UpdateIngress implements UpdateIngressInterface
{
    public function __construct(
        private readonly UpdateNormalizer $normalizer,
        private readonly UpdateRouter $router,
        private readonly UiRenderer $renderer,
        private readonly IdempotencyStoreInterface $idempotency,
        private readonly LoggerInterface $logger,
        private readonly ?string $webhookSecret = null,
        private readonly int $idempotencyTtlSeconds = 86400,
    ) {
    }

    public function handle(array $rawUpdate, ?string $providedSecret = null): IngressResult
    {
        if ($this->webhookSecret !== null && $this->webhookSecret !== '') {
            if ($providedSecret === null || !hash_equals($this->webhookSecret, $providedSecret)) {
                $this->logger->warning('Telegram webhook secret mismatch.');

                return new IngressResult(IngressStatus::Unauthorized, reason: 'invalid_secret');
            }
        }

        $update = $this->normalizer->normalize($rawUpdate);
        if ($update->updateId <= 0) {
            return new IngressResult(IngressStatus::Ignored, $update, reason: 'missing_update_id');
        }

        $claimed = $this->idempotency->claim(
            'telegram:update:' . $update->updateId,
            $this->idempotencyTtlSeconds,
        );
        if (!$claimed) {
            return new IngressResult(IngressStatus::Duplicate, $update, reason: 'duplicate_update_id');
        }

        $view = $this->router->route($update);
        if ($view === null) {
            $this->logger->info('Telegram update ignored by router.', [
                'update_id' => $update->updateId,
                'kind' => $update->kind->value,
            ]);

            return new IngressResult(IngressStatus::Ignored, $update, reason: 'unhandled');
        }

        $this->renderer->render($view);

        return new IngressResult(IngressStatus::Processed, $update, $view);
    }
}
