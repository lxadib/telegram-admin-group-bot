<?php

declare(strict_types=1);

/**
 * Platform runtime configuration (non-secret defaults).
 * Secrets and environment-specific values come from .env / process env.
 *
 * @return array{
 *     name: string,
 *     env: string,
 *     debug: bool,
 *     timezone: string,
 *     modules: list<class-string<\Platform\Contracts\Module\ModuleInterface>>
 * }
 */
$env = getenv('APP_ENV');
$debug = getenv('APP_DEBUG');
$timezone = getenv('APP_TIMEZONE');

return [
    'name' => 'Telegram Bot Platform',
    'env' => is_string($env) && $env !== '' ? $env : 'local',
    'debug' => filter_var(is_string($debug) ? $debug : 'true', FILTER_VALIDATE_BOOL),
    'timezone' => is_string($timezone) && $timezone !== '' ? $timezone : 'UTC',
    'modules' => [
        \Platform\Modules\Users\UsersModule::class,
        \Platform\Modules\Licenses\LicensesModule::class,
        \Platform\Modules\Permissions\PermissionsModule::class,
        \Platform\Modules\Groups\GroupsModule::class,
        \Platform\Modules\Ui\UiModule::class,
    ],
];
