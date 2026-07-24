-- Platform base schema: durable configuration store.
CREATE TABLE IF NOT EXISTS platform_config (
    namespace  VARCHAR(191) NOT NULL,
    config_key VARCHAR(191) NOT NULL,
    value      JSONB        NOT NULL,
    updated_at TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    PRIMARY KEY (namespace, config_key)
);
