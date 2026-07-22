# Telegram Bot Platform

Production-oriented **modular monolith** for Telegram group administration.
Telegram is a UI adapter; business logic lives in domain modules behind public contracts.

## Requirements

- PHP 8.3+ with extensions: `mbstring`, `json`, `dom`, `tokenizer` (Phase 4 also needs `pdo_pgsql`, `redis`)
- Composer 2
- Docker (recommended) for Postgres, Redis, and a consistent PHP CLI image

If host PHP lacks extensions, use Docker:

```bash
make docker-build
make docker-qa
```


## Quick start

```bash
composer install
cp .env.example .env
php apps/console/bin/platform
composer qa
```

Or via Docker PHP image (includes `mbstring`, `pdo_pgsql`, `redis`):

```bash
make docker-build
make docker-qa
```

## Layout

See [docs/IMPLEMENTATION_PLAN.md](docs/IMPLEMENTATION_PLAN.md) and ADRs under `docs/adr/`.

```
apps/           Process entrypoints (bot, worker, scheduler, console)
kernel/         Bootstrap, module loader, lifecycle
contracts/      Shared interfaces, DTOs, events (no IO)
modules/        Domain feature packages
adapters/       Infrastructure drivers (Postgres, Redis, …)
config/         Non-secret platform configuration
tests/          Unit, Integration, Architecture
```

## Quality gates

| Command | Purpose |
|---------|---------|
| `composer test` | PHPUnit |
| `composer phpstan` | Static analysis (level 8) |
| `composer cs-check` | PSR-12 + project rules |
| `composer deptrac` | Layer dependency rules |
| `composer qa` | All of the above |

## Status

Phase 1 (bootstrap) complete. Contracts and Kernel implementations follow the approved roadmap.
