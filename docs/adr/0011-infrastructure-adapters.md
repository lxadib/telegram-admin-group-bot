# ADR-011: Infrastructure Adapters (PostgreSQL & Redis)

## Status

Accepted

## Context

Phase 3 shipped in-memory reference services so the Kernel could boot and be
tested without external systems. Phase 4 needs durable persistence, caching,
rate limiting, idempotency, and background queueing — without leaking any driver
detail into the Kernel or domain modules.

## Decision

- **Reference implementations live in the Kernel.** `ArrayConfigRepository`,
  `NullUnitOfWork`, `ArrayTranslator`, `StaticLocaleResolver`, `NativeHasher`,
  `HmacTokenIssuer`, `EnvSecretVault`, `InMemoryRateLimiter`, and
  `InMemoryIdempotencyStore` are bound as container defaults. The platform is
  fully functional with **zero external services**; adapters are opt-in.
- **Drivers live in a single `telegram-platform/adapters` package** rather than
  one Composer package per driver. Adapters share only infrastructure concerns,
  are swapped together in practice, and this keeps lock churn low. Deptrac still
  forbids `Adapters → Kernel`; adapters may depend on **Contracts only** (plus
  their driver libraries).
- **PostgreSQL** via PDO: `PdoConnection`, `PdoUnitOfWork`, `SqlMigrationRunner`
  (forward-only, ledgered in `platform_migrations`), `PdoConfigRepository`
  (JSONB), `PdoAuditLogger` (append-only).
- **Redis** via `predis/predis` (pure PHP — no `ext-redis` build dependency):
  `RedisCache` (PSR-16), `RedisRateLimiter` (atomic Lua INCR+EXPIRE),
  `RedisIdempotencyStore` (`SET NX EX`), `RedisJobBus` + `RedisJobConsumer`
  (ready list + delayed sorted-set, retries, dead-letter).
- **Integration tests skip when services are absent.** They read `DATABASE_URL`
  / `REDIS_URL`, probe the connection, and `markTestSkipped` otherwise, so the
  unit `composer qa` stays green offline while `make docker-integration` runs
  the suite against Docker Postgres/Redis.

## Consequences

- Any port can be re-bound to a durable adapter by a single container definition
  or module provider; business code never changes.
- Gracefully degrades: no DB/Redis still boots and serves in-memory behaviour.
- Job payloads must be JSON-serializable; the consumer rehydrates them into an
  `EnvelopeJob` and dispatches by job name to registered handlers.
- Queue priority is per-queue FIFO (fine-grained intra-queue priority is a
  later refinement).
