# ADR-002: PHP Stack

## Status

Accepted

## Context

Need a long-lived PHP platform without coupling modules to a full-stack framework lifecycle.

## Decision

- PHP 8.3+
- Composer path monorepo
- PSR-3 / PSR-11 / PSR-14 / PSR-16 / PSR-20
- PHP-DI (introduced in Kernel phase) for the container
- PostgreSQL as primary store; Redis for cache/queue
- Thin custom Telegram Bot API client behind adapter interfaces

## Consequences

- Modules stay framework-agnostic.
- We own bootstrap complexity (Kernel).
- Full Laravel/Symfony apps are intentionally avoided.
