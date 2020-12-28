FROM php:7.4-alpine

COPY --from=composer:2.0.2 /usr/bin/composer /usr/bin/composer

RUN mkdir -p /app

WORKDIR /app
