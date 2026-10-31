FROM dunglas/frankenphp:1-php8.3 AS base
RUN apt-get update && apt-get install -y --no-install-recommends zip unzip curl nodejs npm \
    && install-php-extensions pdo_mysql pcntl redis intl bcmath gd opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Opcache produksi: file berubah setelah deploy tetap terdeteksi (validate_timestamps=1)
# karena kode di-mount sebagai volume saat dev.
RUN printf 'opcache.enable=1\nopcache.memory_consumption=192\nopcache.interned_strings_buffer=16\nopcache.max_accelerated_files=20000\nopcache.validate_timestamps=1\nopcache.revalidate_freq=0\nexpose_php=0\n' \
    > /usr/local/etc/php/conf.d/zz-app.ini

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
RUN chmod +x docker/entrypoint.sh && mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

EXPOSE 8000
ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
# Mode production: tanpa --watch. Tambah --watch hanya di lingkungan dev.
CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=8000", "--workers=auto", "--max-requests=500"]
