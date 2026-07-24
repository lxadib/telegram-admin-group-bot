<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Modules\Users;

use PHPUnit\Framework\TestCase;
use Platform\Contracts\Event\EventListenerInterface;
use Platform\Contracts\Users\Events\UserLinked;
use Platform\Contracts\Users\IdentityLinkerInterface;
use Platform\Contracts\Users\UserDirectoryInterface;
use Platform\Kernel\Bootstrap\Kernel;
use Platform\Modules\Users\UsersModule;

final class UsersModuleTest extends TestCase
{
    public function testLinksTelegramIdentityAndEmitsEvent(): void
    {
        $kernel = Kernel::boot([
            'env' => 'test',
            'modules' => [UsersModule::class],
        ]);

        $listener = new class () implements EventListenerInterface {
            /** @var list<string> */
            public array $seen = [];

            public function eventClass(): string
            {
                return UserLinked::class;
            }

            public function moduleId(): string
            {
                return 'test';
            }

            public function handle(object $event): void
            {
                if ($event instanceof UserLinked) {
                    $this->seen[] = $event->userId;
                }
            }
        };

        $kernel->events()->subscribe($listener);

        /** @var IdentityLinkerInterface $linker */
        $linker = $kernel->container()->get(IdentityLinkerInterface::class);
        $user = $linker->linkTelegram('tg-42', attributes: ['displayName' => 'Ada']);

        self::assertSame('Ada', $user->displayName);
        self::assertSame('tg-42', $user->telegramUserId);
        self::assertSame([$user->userId], $listener->seen);

        /** @var UserDirectoryInterface $directory */
        $directory = $kernel->container()->get(UserDirectoryInterface::class);
        self::assertSame($user->userId, $directory->findByTelegramId('tg-42')?->userId);
    }

    public function testLinkIsIdempotent(): void
    {
        $kernel = Kernel::boot(['env' => 'test', 'modules' => [UsersModule::class]]);
        /** @var IdentityLinkerInterface $linker */
        $linker = $kernel->container()->get(IdentityLinkerInterface::class);

        $a = $linker->linkTelegram('tg-1');
        $b = $linker->linkTelegram('tg-1');

        self::assertSame($a->userId, $b->userId);
    }
}
