# ADR-009: Contracts Package Purity

## Status

Accepted

## Context

Modules and adapters must share types without depending on each other's internals.

## Decision

`telegram-platform/contracts` contains only:

- interfaces
- enums
- `final readonly` DTOs / value objects
- domain exceptions

No IO, no Telegram, no database drivers, no concrete services.

## Consequences

- Kernel and adapters implement these ports.
- Architecture tests (`ContractsPurityTest`) enforce purity.
- Breaking contract changes require a major version bump.
