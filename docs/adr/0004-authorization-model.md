# ADR-004: Authorization Model

## Status

Accepted

## Context

Five actor tiers (Super Owner → Owner → Customer → Group Admin → Moderator) plus license feature limits.

## Decision

Hierarchical RBAC with scoped capabilities and deny overrides. Permission keys are namespaced (`moderation.mute`). Effective permissions = role defaults ∪ grants − denies, scoped by platform | partner | tenant | group. A license `CapabilitySnapshot` is checked **before** RBAC.

## Consequences

- Licensing and permissions stay decoupled (no circular module deps).
- Hot path reads snapshots, not live license joins.
