FROM php:8.4-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpq-dev \
        libicu-dev \
        libonig-dev \
    && docker-php-ext-install -j$(nproc) \
        mbstring \
        pdo_pgsql \
        intl \
        opcache \
        pcntl \
        sockets \
    && rm -rf /var/lib/apt/lists/*

# Redis PHP extension is optional in Phase 1; Phase 4 may use predis and/or ext-redis.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

CMD ["php", "-v"]
