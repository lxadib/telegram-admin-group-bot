# ADR-003: Module Packaging

## Status

Accepted

## Context

Features must be installable without modifying Core.

## Decision

Each module is a Composer package with a `ModuleManifest` declaring id, version, contracts provided/required, permissions, migrations, commands, menus, jobs, event subscriptions, config schema, and i18n paths. Kernel discovers enabled modules from configuration and the install registry.

## Consequences

- Clear enable/disable lifecycle.
- Marketplace-ready later.
- Manifest validation becomes a Kernel responsibility.
