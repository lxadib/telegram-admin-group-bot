<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Kernel\Infrastructure;

use PHPUnit\Framework\TestCase;
use Platform\Kernel\Config\ArrayConfigRepository;

final class ArrayConfigRepositoryTest extends TestCase
{
    public function testStoresAndReadsNamespacedKeys(): void
    {
        $config = new ArrayConfigRepository();
        $config->set('moderation', 'flood.max', 5);

        self::assertTrue($config->has('moderation', 'flood.max'));
        self::assertSame(5, $config->get('moderation', 'flood.max'));
        self::assertSame(['flood.max' => 5], $config->all('moderation'));
    }

    public function testReturnsDefaultForUnknownKey(): void
    {
        $config = new ArrayConfigRepository(['welcome' => ['enabled' => true]]);

        self::assertFalse($config->has('welcome', 'missing'));
        self::assertSame('fallback', $config->get('welcome', 'missing', 'fallback'));
        self::assertTrue($config->get('welcome', 'enabled'));
        self::assertSame([], $config->all('unknown'));
    }
}
