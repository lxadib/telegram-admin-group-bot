# Telegram Bot Platform

Production-oriented **modular monolith** for Telegram group administration.
Telegram is a UI adapter; business logic lives in domain modules behind public contracts.

## Requirements

- PHP 8.3+ with extensions: `mbstring`, `json`, `dom`, `tokenizer` (Postgres adapters also need `pdo_pgsql`; Redis uses `predis/predis`, no extension required)
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

Or via Docker PHP image (includes `mbstring`, `pdo_pgsql`):

```bash
make docker-build
make docker-qa          # unit QA, no external services required
make docker-integration # Postgres + Redis integration tests
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

Phase 7 (product MVP) complete: Telegram admin commands drive real domain flows —
`/activate` a license, `/bind` a group, and `/warn` · `/mute` · `/ban` members —
with license (capability) gating before RBAC and audit + domain events on every
action. Telegram stays transport-only; handlers orchestrate contracts.

- `modules/moderation` provides `ModerationServiceInterface` (feature-gated).
- `ActorContextResolver` derives a per-request `ActorContext` (MVP: one tenant per
  Telegram user; set `TELEGRAM_SUPER_OWNER_ID` for a bypass operator).
- End-to-end fixture flow (`/activate → /bind → /mute`) runs without a network;
  `apps/bot/bin/bot poll` long-polls when `TELEGRAM_BOT_TOKEN` is set.

Console boots 7 modules (`modules_enabled=7`). Next: durable persistence for
moderation/nav state, outbound queue/flood-control, and richer admin menus.
