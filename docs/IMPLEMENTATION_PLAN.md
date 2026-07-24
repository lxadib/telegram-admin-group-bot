# Implementation Plan

Source of truth: approved architecture in `platform-architecture.canvas.tsx` (ADR-001 … ADR-006).

## Principles

- Modular monolith; Composer path packages under `contracts/`, `kernel/`, `modules/*`, `adapters/*`.
- Cross-module access only via `Platform\Contracts\*` and the event bus.
- Telegram is an adapter only — no domain logic.
- Each phase must install, analyse, and pass tests before the next phase starts.
- Every commit leaves the tree runnable (`composer install` + `vendor/bin/phpunit`).

## Phase map

| Phase | Scope | Exit criteria |
|-------|--------|----------------|
| **1 · Bootstrap** | Composer, structure, QA tooling, env, CI, git | `composer qa` green; console prints version |
| **2 · Contracts** | Shared interfaces/DTOs/events only | PHPStan green; no concrete IO |
| **3 · Kernel** | Module loader, manifest, DI, lifecycle, dispatchers | Kernel boots modules from config; unit tests |
| **4 · Infrastructure** | Postgres, Redis queue/cache, config, logging, event bus, audit, i18n | Integration tests against Docker services |
| **5 · Core modules** | Users, Groups, Permissions, Licenses, Navigation (UI) | Boundary tests; communicate via contracts/events |
| **6 · Telegram adapter** | Update ingress, BotGateway, UI render | Fixtures → commands; no business rules |
| **7 · Product MVP** | Bind groups, license flows, admin menus, basic moderation/automation | End-to-end Telegram smoke |

## Phase 1 deliverables

- Root Composer project (`php ^8.3`) with path repos for `contracts` + `kernel`
- Directory layout matching architecture
- PHPUnit, PHPStan (level 8), PHP-CS-Fixer, Deptrac skeleton
- `.env.example`, `.gitignore`, `docker-compose.yml` (Postgres + Redis)
- GitHub Actions CI
- Minimal `Platform\Kernel\Platform` identity + `apps/console/bin/platform`
- Smoke unit test + ADR stubs

## Phase 2 deliverables (this milestone)

Pure `telegram-platform/contracts` package — interfaces, enums, readonly DTOs only:

| Area | Contracts |
|------|-----------|
| Module | `ModuleId`, `ModuleManifest`, `ModuleInterface`, `ModuleRegistryInterface`, `ModuleProviderInterface`, `ModuleState` |
| Event | `EventInterface`, `DomainEventInterface`, `EventBusInterface`, `EventListenerInterface`, `EventOutboxInterface` |
| Queue | `JobInterface`, `JobBusInterface`, `JobHandlerInterface`, `SchedulerInterface`, `ScheduleDefinition` |
| Messaging | `CommandBusInterface`, `QueryBusInterface`, handlers |
| Storage | `UnitOfWorkInterface`, `MigrationRunnerInterface`, `RepositoryInterface` |
| Security | `SecretVaultInterface`, `HasherInterface`, `TokenIssuerInterface`, `RateLimiterInterface`, `IdempotencyStoreInterface` |
| Config | `ConfigRepositoryInterface` |
| Logging | `AuditLoggerInterface` (+ PSR-3 for structured logs) |
| Navigation | `NavStackInterface`, `PaginatorInterface`, `ConfirmGateInterface`, `MenuRegistryInterface`, `MenuNode`, `ViewModel` |
| Auth | `ActorContext`, `ActorRole`, `AuthorizerInterface` |
| Capability | `CapabilitySnapshot`, `CapabilityGateInterface` |
| Misc | `ClockInterface`, `IdGeneratorInterface`, Health, Localization |

Exit: purity architecture tests + value-object unit tests + `composer qa` green.

## Phase 3 deliverables

`telegram-platform/kernel` becomes a bootable runtime:

- `Kernel::boot()` — timezone, DI (PHP-DI), module discover → register → boot
- `ModuleLoader`, `DependencyResolver`, `ManifestValidator`, `ModuleRegistry`
- `ServiceRegistrarInterface` + `DiServiceRegistrar` (contract improvement: PSR-11 cannot bind)
- `ModuleBoundary` failure isolation
- In-memory `EventBus`, `EventOutbox`, `JobBus`, `Scheduler`
- `SimpleCommandBus`, `SimpleQueryBus`
- System clock, id generator, stderr/null logger, audit logger, health monitor
- Console entry boots the kernel and prints health + enabled module count

Exit: Kernel boots fixture modules; event isolation tests; `composer qa` green.

## Dependency direction (never reverse)

```
apps → kernel → contracts ← modules
apps → adapters → contracts
modules → contracts only
adapters → contracts (+ driver libs)
```

## Package naming

Composer vendor: `telegram-platform/*`  
PHP namespaces: `Platform\{Contracts,Kernel,Modules\*,Adapters\*}`.
