# ADR-001: Modular Monolith

## Status

Accepted

## Context

We need a Telegram Bot Platform that can evolve into commercial SaaS, with installable modules and strong boundaries, without premature microservice cost.

## Decision

Ship a single deployable **modular monolith**. Each module is a Composer path package. Cross-module access only via `Platform\Contracts` and the event bus.

## Consequences

- Simple ops (one deploy) while enforcing boundaries with Deptrac/PHPStan.
- Modules can later be extracted into services if needed.
- Requires discipline and automated architecture tests.
