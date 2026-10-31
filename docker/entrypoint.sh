#!/bin/sh
set -e

# Bersihkan cache lama setiap start (config/route/view) supaya perubahan .env & view terpakai.
php artisan optimize:clear >/dev/null 2>&1 || true

# Production: cache ulang config/route/view. Local: biarkan kosong agar mudah debug.
if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Tunggu DB siap (compose healthcheck sudah jaga, ini jaring pengaman).
if [ "${WAIT_FOR_DB:-1}" = "1" ]; then
    i=0
    until php -r 'exit(@fsockopen(getenv("DB_HOST")?:"db", (int)(getenv("DB_PORT")?:3306)) ? 0 : 1);' 2>/dev/null; do
        i=$((i+1))
        [ "$i" -ge 30 ] && echo "DB belum siap, lanjut start." && break
        sleep 2
    done
fi

exec "$@"
