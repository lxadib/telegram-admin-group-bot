# ADR-012: Core Domain Modules

## Status

Accepted

## Context

Phase 5 introduces the first business modules. They must stay decoupled: no
module may import another module's concrete classes; Licensing and Permissions
must not form a cycle with Groups.

## Decision

- Each core module is a Composer path package under `modules/*`
  (`telegram-platform/module-*`) depending only on `telegram-platform/contracts`.
- Public ports for Users and Groups live in Contracts
  (`UserDirectoryInterface`, `IdentityLinkerInterface`, `GroupRegistryInterface`,
  `MembershipQueryInterface`). Permissions and Licenses bind the existing
  `AuthorizerInterface` / `CapabilityGateInterface` (plus
  `PermissionCatalogInterface` and `LicenseServiceInterface`).
- Cross-module side effects use domain events in Contracts
  (`UserLinked`, `GroupBound`, `LicenseActivated`). Listeners subscribe via
  `EventBusInterface::subscribe()` during `boot()`.
- Capability checks run **before** RBAC (ADR-004). Groups consult
  `CapabilityGateInterface` when binding; Kernel ships an allow-all default so
  modules can boot without Licenses, and Licenses overrides the binding.
- Phase 5 implementations are in-memory; durable storage reuses Phase 4 adapters
  later without changing module ports.

## Consequences

- Deptrac `Modules → Contracts` only; Apps may compose Modules.
- Boundary tests boot the full set and assert event wiring.
- Telegram (Phase 6) consumes ViewModels / NavStack / Authorizer — never
  module internals.
