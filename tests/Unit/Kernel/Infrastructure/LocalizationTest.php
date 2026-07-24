<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Kernel\Infrastructure;

use PHPUnit\Framework\TestCase;
use Platform\Kernel\Localization\ArrayTranslator;
use Platform\Kernel\Localization\StaticLocaleResolver;

final class LocalizationTest extends TestCase
{
    public function testTranslatesWithPlaceholderSubstitution(): void
    {
        $translator = new ArrayTranslator([
            'en' => ['welcome' => 'Hello, {name}!'],
        ]);

        self::assertSame('Hello, Ada!', $translator->trans('welcome', ['name' => 'Ada']));
    }

    public function testFallsBackToFallbackLocaleThenKey(): void
    {
        $translator = new ArrayTranslator(
            ['en' => ['ping' => 'pong']],
            defaultLocale: 'fa',
            fallbackLocale: 'en',
        );

        self::assertSame('pong', $translator->trans('ping'));
        self::assertSame('unknown.key', $translator->trans('unknown.key'));
    }

    public function testAddCatalogMergesMessages(): void
    {
        $translator = new ArrayTranslator();
        $translator->addCatalog('en', ['a' => 'A']);
        $translator->addCatalog('en', ['b' => 'B']);

        self::assertSame('A', $translator->trans('a'));
        self::assertSame('B', $translator->trans('b'));
    }

    public function testLocaleResolverAppliesAllowListAndDefault(): void
    {
        $resolver = new StaticLocaleResolver(['en', 'fa'], 'en');

        self::assertSame('fa', $resolver->resolve('fa-IR'));
        self::assertSame('en', $resolver->resolve('de'));
        self::assertSame('en', $resolver->resolve(null));
    }
}
