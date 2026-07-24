<?php

declare(strict_types=1);

namespace Platform\Kernel\Localization;

use Platform\Contracts\Localization\LocaleResolverInterface;

/**
 * Resolves locale from Telegram language code with an allow-list + default fallback.
 */
final class StaticLocaleResolver implements LocaleResolverInterface
{
    /**
     * @param list<string> $supported
     */
    public function __construct(
        private readonly array $supported = ['en'],
        private readonly string $default = 'en',
    ) {
    }

    public function resolve(?string $telegramLanguageCode = null, ?string $tenantId = null): string
    {
        if ($telegramLanguageCode === null) {
            return $this->default;
        }

        $normalized = strtolower(substr($telegramLanguageCode, 0, 2));
        if ($normalized !== '' && in_array($normalized, $this->supported, true)) {
            return $normalized;
        }

        return $this->default;
    }
}
