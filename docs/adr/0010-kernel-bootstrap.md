# ADR-010: Kernel Bootstrap & Module Lifecycle

## Status

Accepted

## Context

Modules must be discovered, ordered, registered into DI, and booted without coupling to Telegram or domain code.

## Decision

- Kernel boots via `Platform\Kernel\Bootstrap\Kernel::boot()`.
- Module providers use `ServiceRegistrarInterface` during `register()` (PSR-11 is read-only).
- Dependency order is a topological sort of `ModuleManifest::$requires`.
- `ModuleBoundary` isolates listener/boot failures; command dispatch fails loudly.
- Phase 3 ships in-memory EventBus, JobBus, Scheduler, Outbox; Phase 4 replaces with durable adapters.

## Consequences

- Modules are enableable from `config/platform.php` class-strings.
- One module boot/listener failure does not crash the process.
- Infrastructure adapters bind over the same contracts later.
