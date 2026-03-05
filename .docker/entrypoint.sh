#!/bin/sh
set -e

# ---------------------------------------------------------------------------
# Ensure writable directories exist (volumes may be empty on first start)
# ---------------------------------------------------------------------------
mkdir -p storage/app/public \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# ---------------------------------------------------------------------------
# Bootstrap Laravel (must run before config:cache so package list is fresh)
# ---------------------------------------------------------------------------
php artisan package:discover --ansi

# ---------------------------------------------------------------------------
# Cache config / routes / views (speeds up every request)
# ---------------------------------------------------------------------------
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ---------------------------------------------------------------------------
# Run pending migrations
# ---------------------------------------------------------------------------
php artisan migrate --force

# ---------------------------------------------------------------------------
# Create the public storage symlink (idempotent)
# ---------------------------------------------------------------------------
php artisan storage:link --force

# ---------------------------------------------------------------------------
# Hand off to supervisord (nginx + php-fpm + queue worker + scheduler)
# ---------------------------------------------------------------------------
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
