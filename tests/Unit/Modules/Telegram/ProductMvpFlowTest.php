<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Modules\Telegram;

use DI\Container;
use PHPUnit\Framework\TestCase;
use Platform\Adapters\Telegram\RecordingBotGateway;
use Platform\Contracts\Capability\LicenseServiceInterface;
use Platform\Contracts\Groups\GroupRegistryInterface;
use Platform\Contracts\Moderation\ModerationServiceInterface;
use Platform\Contracts\Telegram\IngressStatus;
use Platform\Contracts\Telegram\UpdateIngressInterface;
use Platform\Contracts\Users\UserDirectoryInterface;
use Platform\Kernel\Bootstrap\Kernel;
use Platform\Modules\Groups\GroupsModule;
use Platform\Modules\Licenses\LicensesModule;
use Platform\Modules\Moderation\ModerationModule;
use Platform\Modules\Permissions\PermissionsModule;
use Platform\Modules\Telegram\TelegramModule;
use Platform\Modules\Ui\UiModule;
use Platform\Modules\Users\UsersModule;

/**
 * End-to-end product MVP smoke: /activate → /bind → /mute over the Telegram
 * transport, asserting both the rendered replies and the mutated domain state.
 */
final class ProductMvpFlowTest extends TestCase
{
    /**
     * @return list<class-string<\Platform\Contracts\Module\ModuleInterface>>
     */
    private function modules(): array
    {
        return [
            UsersModule::class,
            LicensesModule::class,
            PermissionsModule::class,
            GroupsModule::class,
            ModerationModule::class,
            UiModule::class,
            TelegramModule::class,
        ];
    }

    private function bootContainer(RecordingBotGateway $gateway): Container
    {
        $kernel = Kernel::boot(['env' => 'test', 'modules' => $this->modules()]);
        /** @var Container $container */
        $container = $kernel->container();
        $container->set(\Platform\Contracts\Telegram\BotGatewayInterface::class, $gateway);

        return $container;
    }

    /**
     * @return array<string, mixed>
     */
    private function fixture(string $name): array
    {
        /** @var array<string, mixed> $raw */
        $raw = json_decode(
            (string) file_get_contents(dirname(__DIR__, 3) . '/Fixtures/Telegram/' . $name),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        return $raw;
    }

    public function testActivateThenBindThenMute(): void
    {
        $gateway = new RecordingBotGateway();
        $container = $this->bootContainer($gateway);

        /** @var UpdateIngressInterface $ingress */
        $ingress = $container->get(UpdateIngressInterface::class);

        $activate = $ingress->handle($this->fixture('activate_command.json'));
        self::assertSame(IngressStatus::Processed, $activate->status);
        self::assertNotNull($activate->view);
        self::assertStringContainsString('activated', $activate->view->text);
        self::assertStringContainsString('moderation', $activate->view->text);

        $bind = $ingress->handle($this->fixture('bind_command.json'));
        self::assertSame(IngressStatus::Processed, $bind->status);
        self::assertNotNull($bind->view);
        self::assertStringContainsString('bound', $bind->view->text);

        $mute = $ingress->handle($this->fixture('mute_command.json'));
        self::assertSame(IngressStatus::Processed, $mute->status);
        self::assertNotNull($mute->view);
        self::assertStringContainsString('Muted user 999', $mute->view->text);

        /** @var UserDirectoryInterface $users */
        $users = $container->get(UserDirectoryInterface::class);
        $user = $users->findByTelegramId('4242');
        self::assertNotNull($user);

        /** @var LicenseServiceInterface $licenses */
        $licenses = $container->get(LicenseServiceInterface::class);
        $snapshot = $licenses->get('tenant:' . $user->userId);
        self::assertNotNull($snapshot);
        self::assertTrue($snapshot->allows('moderation'));

        /** @var GroupRegistryInterface $groups */
        $groups = $container->get(GroupRegistryInterface::class);
        $group = $groups->findByTelegramChatId('-1001');
        self::assertNotNull($group);
        self::assertSame('tenant:' . $user->userId, $group->tenantId);

        /** @var ModerationServiceInterface $moderation */
        $moderation = $container->get(ModerationServiceInterface::class);
        self::assertTrue($moderation->isMuted($group->groupId, '999'));

        $methods = array_column($gateway->calls, 'method');
        self::assertSame(['sendMessage', 'sendMessage', 'sendMessage'], $methods);
    }

    public function testBindWithoutLicenseIsRejected(): void
    {
        $gateway = new RecordingBotGateway();
        $container = $this->bootContainer($gateway);

        /** @var UpdateIngressInterface $ingress */
        $ingress = $container->get(UpdateIngressInterface::class);

        $bind = $ingress->handle($this->fixture('bind_command.json'));
        self::assertSame(IngressStatus::Processed, $bind->status);
        self::assertNotNull($bind->view);
        self::assertStringContainsString('license', $bind->view->text);

        /** @var GroupRegistryInterface $groups */
        $groups = $container->get(GroupRegistryInterface::class);
        self::assertNull($groups->findByTelegramChatId('-1001'));
    }
}
