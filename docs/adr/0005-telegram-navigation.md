# ADR-005: Telegram Navigation

## Status

Accepted

## Context

All administration is Telegram-first: menus, pagination, search, confirms, breadcrumbs.

## Decision

Server-side conversation/UI sessions with a stack-based `NavStack`. Callbacks carry route + session id. Business handlers return ViewModels; the Telegram adapter renders keyboards. Idempotent handling via `update_id` + action nonce.

## Consequences

- Snappy in-place message edits.
- Navigation logic lives outside Telegram transport.
- Session store becomes an infrastructure concern.
