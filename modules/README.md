# Domain modules

Each subdirectory is a Composer path package (`telegram-platform/module-*`) that
depends **only** on `telegram-platform/contracts`.

Enabled modules are listed in `config/platform.php`. Cross-module communication
is via contract ports and the event bus — never via another module's concrete
classes.

| Package | Namespace | Provides |
|---------|-----------|----------|
| `users` | `Platform\Modules\Users` | `UserDirectoryInterface`, `IdentityLinkerInterface` |
| `groups` | `Platform\Modules\Groups` | `GroupRegistryInterface`, `MembershipQueryInterface` |
| `permissions` | `Platform\Modules\Permissions` | `AuthorizerInterface`, `PermissionCatalogInterface` |
| `licenses` | `Platform\Modules\Licenses` | `LicenseServiceInterface`, `CapabilityGateInterface` |
| `ui` | `Platform\Modules\Ui` | `NavStackFactoryInterface`, `MenuRegistryInterface`, `ConfirmGateInterface` |
| `telegram` | `Platform\Modules\Telegram` | `UpdateIngressInterface` (transport only) |
