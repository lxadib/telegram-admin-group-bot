-- Platform base schema: append-only audit trail.
CREATE TABLE IF NOT EXISTS platform_audit_log (
    id          BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    action      VARCHAR(191) NOT NULL,
    actor_id    VARCHAR(191),
    tenant_id   VARCHAR(191),
    group_id    VARCHAR(191),
    context     JSONB        NOT NULL DEFAULT '{}'::jsonb,
    recorded_at TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_audit_actor ON platform_audit_log (actor_id);
CREATE INDEX IF NOT EXISTS idx_audit_tenant ON platform_audit_log (tenant_id);
CREATE INDEX IF NOT EXISTS idx_audit_recorded_at ON platform_audit_log (recorded_at);
