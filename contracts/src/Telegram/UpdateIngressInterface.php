<?php

declare(strict_types=1);

namespace Platform\Contracts\Telegram;

/**
 * Single entry for webhook and long-polling. Verifies secret, dedupes, routes, renders.
 */
interface UpdateIngressInterface
{
    /**
     * @param array<string, mixed> $rawUpdate Decoded Telegram Update object
     * @param string|null $providedSecret Value of X-Telegram-Bot-Api-Secret-Token (webhook)
     */
    public function handle(array $rawUpdate, ?string $providedSecret = null): IngressResult;
}
