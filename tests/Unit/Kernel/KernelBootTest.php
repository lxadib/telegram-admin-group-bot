<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Kernel;

use PHPUnit\Framework\TestCase;
use Platform\Contracts\Module\ModuleId;
use Platform\Contracts\Module\ModuleState;
use Platform\Kernel\Bootstrap\Kernel;
use Platform\Kernel\Boundary\ModuleBoundary;
use Platform\Kernel\Event\InMemoryEventBus;
use Platform\Kernel\Module\DependencyResolver;
use Platform\Tests\Unit\Kernel\Fixtures\AlphaModule;
use Platform\Tests\Unit\Kernel\Fixtures\BetaModule;
use Platform\Tests\Unit\Kernel\Fixtures\FailingListener;
use Platform\Tests\Unit\Kernel\Fixtures\RecordingListener;
use Platform\Tests\Unit\Kernel\Fixtures\SampleEvent;
use Psr\Log\NullLogger;

final class KernelBootTest extends TestCase
{
    public function testDependencyResolverOrdersByRequires(): void
    {
        $resolver = new DependencyResolver();
        $ordered = $resolver->resolve([
            new BetaModule(),
            new AlphaModule(),
        ]);

        self::assertSame('alpha', (string) $ordered[0]->manifest()->id);
        self::assertSame('beta', (string) $ordered[1]->manifest()->id);
    }

    public function testDependencyResolverDetectsMissingDependency(): void
    {
        $this->expectException(\RuntimeException::class);
        (new DependencyResolver())->resolve([new BetaModule()]);
    }

    public function testKernelBootsModulesInOrderAndExposesBindings(): void
    {
        $kernel = Kernel::boot([
            'env' => 'test',
            'modules' => [BetaModule::class, AlphaModule::class],
        ]);

        self::assertTrue($kernel->modules()->has(new ModuleId('alpha')));
        self::assertTrue($kernel->modules()->has(new ModuleId('beta')));
        self::assertSame(ModuleState::Enabled, $kernel->modules()->stateOf(new ModuleId('alpha')));
        self::assertSame(ModuleState::Enabled, $kernel->modules()->stateOf(new ModuleId('beta')));
        self::assertSame('alpha-ready', $kernel->container()->get('fixture.alpha'));
        self::assertSame('beta-ready', $kernel->container()->get('fixture.beta'));
        self::assertSame('ok', $kernel->health()->overall()->value);
    }

    public function testEventBusIsolatesFailingListeners(): void
    {
        $boundary = new ModuleBoundary(new NullLogger());
        $bus = new InMemoryEventBus($boundary);
        $recording = new RecordingListener();
        $bus->subscribe(new FailingListener());
        $bus->subscribe($recording);

        $bus->dispatch(new SampleEvent('hello'));

        self::assertCount(1, $recording->seen);
        self::assertSame('hello', $recording->seen[0]->message);
        self::assertSame(1, $boundary->failureCount('broken'));
    }
}
