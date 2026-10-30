FROM dunglas/frankenphp:1-php8.3 AS base
RUN apt-get update && apt-get install -y --no-install-recommends zip unzip nodejs npm \
    && install-php-extensions pdo_mysql pcntl redis intl bcmath gd opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
WORKDIR /var/www/html
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

FROM base AS deps
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

FROM base AS app
COPY . .
COPY --from=deps /var/www/html/vendor ./vendor
RUN composer dump-autoload --optimize
RUN if [ -f package.json ]; then npm ci --no-audit --no-fund && npm run build && rm -rf node_modules; fi
EXPOSE 8000
CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=8000"]
