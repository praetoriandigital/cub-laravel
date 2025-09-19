FROM php:8.3-cli-alpine

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    PATH="/app/vendor/bin:${PATH}"

ARG UID=1000
ARG GID=1000

RUN set -eux; \
    apk add --no-cache git unzip bash; \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS icu-dev libzip-dev zlib-dev sqlite-dev oniguruma-dev; \
    docker-php-ext-configure zip; \
    docker-php-ext-install -j"$(nproc)" intl mbstring zip pdo_sqlite; \
    apk add --no-cache --virtual .run-deps icu-libs libzip sqlite-libs oniguruma; \
    apk del .build-deps

COPY --from=composer:2.8 /usr/bin/composer /usr/local/bin/composer

RUN addgroup -g "$GID" -S app && adduser -u "$UID" -S app -G app \
    && mkdir -p /app && chown -R app:app /app
WORKDIR /app
USER app

CMD ["php", "-v"]
