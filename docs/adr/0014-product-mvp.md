# ADR-014: Product MVP — Admin Flows & Moderation

## Status

Accepted

## Context

Phases 1–6 delivered a bootable modular monolith with domain ports and a
transport-only Telegram module. Phase 7 turns those ports into a usable product:
an operator must activate a license, bind a group, and moderate members from
Telegram — with license (capability) gating before RBAC, and with Telegram kept
free of business rules.

## Decision

- **New `modules/moderation`** owns `ModerationServiceInterface`
  (`warn` / `mute` / `ban`, warning counts). The service enforces the tenant
  `moderation` license feature via `CapabilityGateInterface` so *every* caller —
  Telegram, future API, jobs — is protected uniformly, and emits `MemberModerated`
  domain events plus audit records. State is in-memory (durable store later).
- **Telegram command layer** (`modules/telegram/src/Command`) adds a
  `CommandHandlerInterface` + `CommandRegistry` and three handlers
  (`/bind`, `/activate`, `/warn|/mute|/ban`). Handlers depend on **contracts only**
  (`GroupRegistry`, `LicenseService`, `ModerationService`, `Authorizer`), so
  Deptrac `Modules → Contracts` still holds. `/start` and callback navigation are
  unchanged.
- **`ActorContextResolver`** builds a request-scoped `ActorContext` from an update.
  MVP tenancy: every Telegram user owns a personal tenant `tenant:{userId}`; a
  bound group belongs to whoever bound it, and an actor only receives a `groupId`
  when the current chat is bound to *their own* tenant. This keeps authorization
  and data scope consistent. Cross-tenant co-admin delegation is a later phase.
- **Authorization order** follows the architecture: capability (license feature /
  group limit) is checked in the domain service; RBAC (`Authorizer::can`) is checked
  in the command handler. `SuperOwner` (via `TELEGRAM_SUPER_OWNER_ID`) bypasses RBAC.
- **Permissions defaults** grant `Customer` self-serve rights (`licenses.activate`,
  `groups.bind`, `*.view`); `GroupAdmin`/`Moderator` receive `moderation.*`. Customer
  inherits GroupAdmin/Moderator defaults via the existing role hierarchy.

## Consequences

- End-to-end smoke (`/activate → /bind → /mute`) drives real gateway sends and
  mutates license/group/moderation state without a network.
- Binding without a usable license returns a friendly "activate first" reply
  (capability-denied path), never an exception to the user.
- MVP simplifications documented above (personal tenants, in-memory state,
  inline rendering) are the explicit next hardening targets.
