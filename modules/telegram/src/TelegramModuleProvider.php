<?php

declare(strict_types=1);

namespace Platform\Modules\Telegram;

use Platform\Contracts\Auth\AuthorizerInterface;
use Platform\Contracts\Capability\LicenseServiceInterface;
use Platform\Contracts\Groups\GroupRegistryInterface;
use Platform\Contracts\Localization\LocaleResolverInterface;
use Platform\Contracts\Localization\TranslatorInterface;
use Platform\Contracts\Moderation\ModerationAction;
use Platform\Contracts\Moderation\ModerationServiceInterface;
use Platform\Contracts\Module\ModuleProviderInterface;
use Platform\Contracts\Module\ServiceRegistrarInterface;
use Platform\Contracts\Navigation\MenuRegistryInterface;
use Platform\Contracts\Navigation\NavStackFactoryInterface;
use Platform\Contracts\Security\IdempotencyStoreInterface;
use Platform\Contracts\Telegram\BotGatewayInterface;
use Platform\Contracts\Telegram\UpdateIngressInterface;
use Platform\Contracts\Users\IdentityLinkerInterface;
use Platform\Modules\Telegram\Command\ActivateLicenseCommand;
use Platform\Modules\Telegram\Command\BindGroupCommand;
use Platform\Modules\Telegram\Command\CommandRegistry;
use Platform\Modules\Telegram\Command\ModerationCommand;
use Platform\Modules\Telegram\Handler\CallbackRouteHandler;
use Platform\Modules\Telegram\Handler\StartCommandHandler;
use Platform\Modules\Telegram\Identity\ActorContextResolver;
use Platform\Modules\Telegram\Ingress\UpdateIngress;
use Platform\Modules\Telegram\Ingress\UpdateNormalizer;
use Platform\Modules\Telegram\Rendering\UiRenderer;
use Platform\Modules\Telegram\Routing\UpdateRouter;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class TelegramModuleProvider implements ModuleProviderInterface
{
    public function register(ServiceRegistrarInterface $registrar): void
    {
        $registrar->factory(UpdateNormalizer::class, static fn (): UpdateNormalizer => new UpdateNormalizer());

        $registrar->factory(StartCommandHandler::class, static function (ContainerInterface $c): StartCommandHandler {
            return new StartCommandHandler(
                $c->get(IdentityLinkerInterface::class),
                $c->get(NavStackFactoryInterface::class),
                $c->get(MenuRegistryInterface::class),
                $c->get(LocaleResolverInterface::class),
                $c->get(TranslatorInterface::class),
            );
        });

        $registrar->factory(CallbackRouteHandler::class, static function (ContainerInterface $c): CallbackRouteHandler {
            return new CallbackRouteHandler(
                $c->get(NavStackFactoryInterface::class),
                $c->get(MenuRegistryInterface::class),
            );
        });

        $registrar->factory(ActorContextResolver::class, static function (ContainerInterface $c): ActorContextResolver {
            $superOwner = getenv('TELEGRAM_SUPER_OWNER_ID');

            return new ActorContextResolver(
                $c->get(IdentityLinkerInterface::class),
                $c->get(GroupRegistryInterface::class),
                is_string($superOwner) && $superOwner !== '' ? $superOwner : null,
            );
        });

        $registrar->factory(BindGroupCommand::class, static function (ContainerInterface $c): BindGroupCommand {
            return new BindGroupCommand(
                $c->get(GroupRegistryInterface::class),
                $c->get(AuthorizerInterface::class),
            );
        });

        $registrar->factory(ActivateLicenseCommand::class, static function (ContainerInterface $c): ActivateLicenseCommand {
            return new ActivateLicenseCommand(
                $c->get(LicenseServiceInterface::class),
                $c->get(AuthorizerInterface::class),
            );
        });

        $registrar->factory(CommandRegistry::class, static function (ContainerInterface $c): CommandRegistry {
            $moderation = $c->get(ModerationServiceInterface::class);
            $authorizer = $c->get(AuthorizerInterface::class);

            return new CommandRegistry([
                $c->get(BindGroupCommand::class),
                $c->get(ActivateLicenseCommand::class),
                new ModerationCommand('/warn', ModerationAction::Warn, 'moderation.warn', $moderation, $authorizer),
                new ModerationCommand('/mute', ModerationAction::Mute, 'moderation.mute', $moderation, $authorizer),
                new ModerationCommand('/ban', ModerationAction::Ban, 'moderation.ban', $moderation, $authorizer),
            ]);
        });

        $registrar->factory(UpdateRouter::class, static function (ContainerInterface $c): UpdateRouter {
            return new UpdateRouter(
                $c->get(StartCommandHandler::class),
                $c->get(CallbackRouteHandler::class),
                $c->get(ActorContextResolver::class),
                $c->get(CommandRegistry::class),
            );
        });

        $registrar->factory(UiRenderer::class, static function (ContainerInterface $c): UiRenderer {
            return new UiRenderer($c->get(BotGatewayInterface::class));
        });

        $registrar->factory(UpdateIngressInterface::class, static function (ContainerInterface $c): UpdateIngressInterface {
            $secret = getenv('TELEGRAM_WEBHOOK_SECRET');
            $webhookSecret = is_string($secret) && $secret !== '' ? $secret : null;

            return new UpdateIngress(
                $c->get(UpdateNormalizer::class),
                $c->get(UpdateRouter::class),
                $c->get(UiRenderer::class),
                $c->get(IdempotencyStoreInterface::class),
                $c->get(LoggerInterface::class),
                $webhookSecret,
            );
        });
    }

    public function boot(ContainerInterface $container): void
    {
        // Ingress is resolved lazily so apps can override BotGatewayInterface first.
    }
}
