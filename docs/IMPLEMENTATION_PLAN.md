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

## Phase 1 deliverables (this milestone)

- Root Composer project (`php ^8.3`) with path repos for `contracts` + `kernel`
- Directory layout matching architecture
- PHPUnit, PHPStan (level 8), PHP-CS-Fixer, Deptrac skeleton
- `.env.example`, `.gitignore`, `docker-compose.yml` (Postgres + Redis)
- GitHub Actions CI
- Minimal `Platform\Kernel\Platform` identity + `apps/console/bin/platform`
- Smoke unit test + ADR stubs

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
