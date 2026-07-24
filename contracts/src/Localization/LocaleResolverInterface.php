<?php

declare(strict_types=1);

namespace Platform\Contracts\Localization;

interface LocaleResolverInterface
{
    /**
     * Resolve locale from actor / Telegram language code / tenant default.
     */
    public function resolve(?string $telegramLanguageCode = null, ?string $tenantId = null): string;
}
