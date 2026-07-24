<?php

declare(strict_types=1);

namespace Platform\Contracts\Localization;

/**
 * Multi-language translator. Plugins ship their own catalogs under module namespace.
 */
interface TranslatorInterface
{
    /**
     * @param array<string, scalar|null> $parameters
     */
    public function trans(string $key, array $parameters = [], ?string $locale = null): string;

    public function locale(): string;
}
