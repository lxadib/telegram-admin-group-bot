<?php

declare(strict_types=1);

namespace Platform\Kernel\Localization;

use Platform\Contracts\Localization\TranslatorInterface;

/**
 * Catalog-based translator with {placeholder} substitution and locale fallback.
 */
final class ArrayTranslator implements TranslatorInterface
{
    /**
     * @param array<string, array<string, string>> $catalogs locale => (key => message)
     */
    public function __construct(
        private array $catalogs = [],
        private string $defaultLocale = 'en',
        private string $fallbackLocale = 'en',
    ) {
    }

    /**
     * @param array<string, string> $messages key => message
     */
    public function addCatalog(string $locale, array $messages): void
    {
        $existing = $this->catalogs[$locale] ?? [];
        $this->catalogs[$locale] = array_merge($existing, $messages);
    }

    public function trans(string $key, array $parameters = [], ?string $locale = null): string
    {
        $locale ??= $this->defaultLocale;
        $message = $this->catalogs[$locale][$key]
            ?? $this->catalogs[$this->fallbackLocale][$key]
            ?? $key;

        if ($parameters === []) {
            return $message;
        }

        $replacements = [];
        foreach ($parameters as $name => $value) {
            $replacements['{' . $name . '}'] = $value === null ? '' : (string) $value;
        }

        return strtr($message, $replacements);
    }

    public function locale(): string
    {
        return $this->defaultLocale;
    }

    public function withLocale(string $locale): self
    {
        $clone = clone $this;
        $clone->defaultLocale = $locale;

        return $clone;
    }
}
