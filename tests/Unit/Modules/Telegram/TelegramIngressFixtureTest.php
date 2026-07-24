<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Modules\Telegram;

use DI\Container;
use PHPUnit\Framework\TestCase;
use Platform\Adapters\Telegram\RecordingBotGateway;
use Platform\Contracts\Module\ModuleId;
use Platform\Contracts\Module\ModuleState;
use Platform\Contracts\Telegram\BotGatewayInterface;
use Platform\Contracts\Telegram\IngressStatus;
use Platform\Contracts\Telegram\UpdateIngressInterface;
use Platform\Contracts\Users\UserDirectoryInterface;
use Platform\Kernel\Bootstrap\Kernel;
use Platform\Modules\Groups\GroupsModule;
use Platform\Modules\Licenses\LicensesModule;
use Platform\Modules\Permissions\PermissionsModule;
use Platform\Modules\Telegram\TelegramModule;
use Platform\Modules\Ui\UiModule;
use Platform\Modules\Users\UsersModule;

final class TelegramIngressFixtureTest extends TestCase
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
            UiModule::class,
            TelegramModule::class,
        ];
    }

    public function testStartCommandFixtureLinksUserAndSendsMessage(): void
    {
        $kernel = Kernel::boot(['env' => 'test', 'modules' => $this->modules()]);
        /** @var Container $container */
        $container = $kernel->container();
        $gateway = new RecordingBotGateway();
        $container->set(BotGatewayInterface::class, $gateway);

        /** @var array<string, mixed> $raw */
        $raw = json_decode(
            (string) file_get_contents(dirname(__DIR__, 3) . '/Fixtures/Telegram/start_command.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        /** @var UpdateIngressInterface $ingress */
        $ingress = $container->get(UpdateIngressInterface::class);
        $result = $ingress->handle($raw);

        self::assertSame(IngressStatus::Processed, $result->status);
        self::assertNotNull($result->view);
        self::assertStringContainsString('Ada', $result->view->text);
        self::assertSame('sendMessage', $gateway->calls[0]['method'] ?? null);
        self::assertSame('4242', $gateway->calls[0]['args']['chat_id'] ?? null);

        /** @var UserDirectoryInterface $users */
        $users = $container->get(UserDirectoryInterface::class);
        self::assertNotNull($users->findByTelegramId('4242'));
        self::assertSame(ModuleState::Enabled, $kernel->modules()->stateOf(new ModuleId('telegram')));
    }

    public function testDuplicateUpdateIsIgnored(): void
    {
        $kernel = Kernel::boot(['env' => 'test', 'modules' => $this->modules()]);
        /** @var Container $container */
        $container = $kernel->container();
        $container->set(BotGatewayInterface::class, new RecordingBotGateway());

        /** @var array<string, mixed> $raw */
        $raw = json_decode(
            (string) file_get_contents(dirname(__DIR__, 3) . '/Fixtures/Telegram/start_command.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        /** @var UpdateIngressInterface $ingress */
        $ingress = $container->get(UpdateIngressInterface::class);
        self::assertSame(IngressStatus::Processed, $ingress->handle($raw)->status);
        self::assertSame(IngressStatus::Duplicate, $ingress->handle($raw)->status);
    }

    public function testCallbackFixtureEditsMessage(): void
    {
        $kernel = Kernel::boot(['env' => 'test', 'modules' => $this->modules()]);
        /** @var Container $container */
        $container = $kernel->container();
        $gateway = new RecordingBotGateway();
        $container->set(BotGatewayInterface::class, $gateway);

        /** @var array<string, mixed> $raw */
        $raw = json_decode(
            (string) file_get_contents(dirname(__DIR__, 3) . '/Fixtures/Telegram/callback_home.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        /** @var UpdateIngressInterface $ingress */
        $ingress = $container->get(UpdateIngressInterface::class);
        $result = $ingress->handle($raw);

        self::assertSame(IngressStatus::Processed, $result->status);
        $methods = array_column($gateway->calls, 'method');
        self::assertContains('answerCallbackQuery', $methods);
        self::assertContains('editMessageText', $methods);
    }

    public function testWebhookSecretIsEnforcedWhenConfigured(): void
    {
        putenv('TELEGRAM_WEBHOOK_SECRET=s3cret');

        try {
            $kernel = Kernel::boot(['env' => 'test', 'modules' => $this->modules()]);
            /** @var Container $container */
            $container = $kernel->container();
            $container->set(BotGatewayInterface::class, new RecordingBotGateway());

            /** @var array<string, mixed> $raw */
            $raw = json_decode(
                (string) file_get_contents(dirname(__DIR__, 3) . '/Fixtures/Telegram/start_command.json'),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );

            /** @var UpdateIngressInterface $ingress */
            $ingress = $container->get(UpdateIngressInterface::class);
            self::assertSame(IngressStatus::Unauthorized, $ingress->handle($raw)->status);
            self::assertSame(IngressStatus::Processed, $ingress->handle($raw, 's3cret')->status);
        } finally {
            putenv('TELEGRAM_WEBHOOK_SECRET');
        }
    }
}
