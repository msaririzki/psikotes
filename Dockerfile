FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-scripts \
    --optimize-autoloader

COPY . .

RUN composer dump-autoload \
    --classmap-authoritative \
    --no-dev \
    --optimize


FROM php:8.3-cli-bookworm AS frontend

WORKDIR /app

COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
COPY --from=vendor /app/vendor ./vendor

RUN npm run build


FROM php:8.3-apache-bookworm AS runtime

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev \
        libonig-dev \
        libsqlite3-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install \
        bcmath \
        intl \
        mbstring \
        opcache \
        pdo_mysql \
        pdo_sqlite \
        xml \
        zip \
    && a2enmod headers rewrite expires \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/prikotes-entrypoint

RUN chmod +x /usr/local/bin/prikotes-entrypoint \
    && mkdir -p \
        bootstrap/cache \
        database \
        storage/app/public \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
    && chown -R www-data:www-data \
        bootstrap/cache \
        database \
        storage

ENTRYPOINT ["prikotes-entrypoint"]
CMD ["apache2-foreground"]
