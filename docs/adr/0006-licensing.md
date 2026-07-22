# ADR-006: Licensing

## Status

Accepted

## Context

Commercial SaaS requires activation codes, trials, renewals, suspend/revoke/transfer, usage limits, and resilient offline cache.

## Decision

Server-authoritative licenses with a signed offline `CapabilitySnapshot` cache (TTL + grace). Limits include max groups/modules/admins/moderators and feature flags. Enforcement reads the snapshot only on the hot path.

## Consequences

- Clear license state machine.
- Soft-fail policy must be auditable to avoid revenue leaks or outages.
