<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Modules\Ui;

use PHPUnit\Framework\TestCase;
use Platform\Contracts\Navigation\ConfirmGateInterface;
use Platform\Contracts\Navigation\MenuRegistryInterface;
use Platform\Contracts\Navigation\NavStackFactoryInterface;
use Platform\Kernel\Bootstrap\Kernel;
use Platform\Modules\Ui\Application\OffsetPaginator;
use Platform\Modules\Ui\UiModule;

final class UiModuleTest extends TestCase
{
    public function testNavStackPushPopAndMenuRoot(): void
    {
        $kernel = Kernel::boot(['env' => 'test', 'modules' => [UiModule::class]]);

        /** @var NavStackFactoryInterface $factory */
        $factory = $kernel->container()->get(NavStackFactoryInterface::class);
        $stack = $factory->forSession('s-1');
        $stack->push('ui.home');
        $stack->push('ui.settings', ['tab' => 'general']);

        $current = $stack->current();
        self::assertNotNull($current);
        self::assertSame('ui.settings', $current['route']);
        self::assertSame(['tab' => 'general'], $current['state']);

        $popped = $stack->pop();
        self::assertNotNull($popped);
        self::assertSame('ui.settings', $popped['route']);

        $home = $stack->current();
        self::assertNotNull($home);
        self::assertSame('ui.home', $home['route']);

        /** @var MenuRegistryInterface $menus */
        $menus = $kernel->container()->get(MenuRegistryInterface::class);
        self::assertNotNull($menus->get('ui.home'));
        self::assertCount(1, $menus->roots());
    }

    public function testConfirmGateIsOneTime(): void
    {
        $kernel = Kernel::boot(['env' => 'test', 'modules' => [UiModule::class]]);

        /** @var ConfirmGateInterface $gate */
        $gate = $kernel->container()->get(ConfirmGateInterface::class);
        $token = $gate->issue('ban.user', ['userId' => 'u-9']);

        $first = $gate->consume($token);
        self::assertNotNull($first);
        self::assertSame('ban.user', $first['action']);
        self::assertSame(['userId' => 'u-9'], $first['payload']);
        self::assertNull($gate->consume($token));
    }

    public function testOffsetPaginatorMeta(): void
    {
        $pager = new OffsetPaginator(page: 2, perPage: 10, total: 25);

        self::assertTrue($pager->hasPrevious());
        self::assertTrue($pager->hasNext());
        self::assertSame(2, $pager->meta()['page']);
    }
}
