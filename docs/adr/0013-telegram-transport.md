# ADR-013: Telegram Transport Adapter

## Status

Accepted

## Context

All administration is Telegram-first, but business rules must never live in the
Bot API client. Phase 6 introduces the transport path without product features.

## Decision

- **Contracts** own the ports: `UpdateIngressInterface`, `BotGatewayInterface`,
  `IncomingUpdate`, `IngressResult`. Domain modules never see raw Update arrays
  or HTTP.
- **`modules/telegram`** owns ingress (webhook secret + idempotency),
  normalization, command/callback routing, and `UiRenderer` (ViewModel → gateway).
  Handlers orchestrate existing ports (`IdentityLinker`, `NavStack`, menus) only.
- **`adapters/Telegram`** owns the HTTP Bot API client (`HttpBotGateway`) and a
  `RecordingBotGateway` for fixture tests. Kernel ships `NullBotGateway` as the
  default binding so the platform boots without a token.
- **`apps/bot`** is the process entry: `poll` (long-polling) or `handle` (one
  JSON update). Apps may override `BotGatewayInterface` after `Kernel::boot()`
  and before resolving ingress.
- Idempotency key: `telegram:update:{update_id}`. Webhook secret compared with
  `hash_equals` when `TELEGRAM_WEBHOOK_SECRET` is set.

## Consequences

- Fixture tests prove `/start` → user link → `sendMessage` without a network.
- Outbound flood control / worker queue remains a later refinement (Phase 7+);
  Phase 6 renders inline for simplicity.
- Product menus, bind-group flows, and moderation stay in Phase 7.
