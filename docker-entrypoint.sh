#!/bin/sh
set -e

cd /var/www/html

composer install --no-interaction --prefer-dist --optimize-autoloader

# On Railway (and similar PaaS), APP_KEY/DB_*/etc. are injected as real
# process env vars via the dashboard — no .env file exists or is needed.
# Only fall back to the local-dev convenience of copying .env.example and
# generating a throwaway key when APP_KEY isn't already set some other way.
if [ -z "$APP_KEY" ]; then
    [ -f .env ] || cp .env.example .env
    grep -q "^APP_KEY=.\+" .env || php artisan key:generate --ansi
fi

# Railway assigns the public port at runtime via $PORT; local Docker Compose
# doesn't set it, so this defaults back to 80 (matching the compose files'
# "8000:80" port mapping) when running locally.
PORT="${PORT:-80}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

echo "Esperando a que la base de datos acepte conexiones..."
retries=30
until php artisan db:show > /dev/null 2>&1 || [ "$retries" -eq 0 ]; do
    retries=$((retries - 1))
    sleep 1
done

php artisan migrate --force

# --relative avoids a Docker Desktop/WSL2 bind-mount quirk where absolute
# symlinks get rewritten to a host-side path that doesn't exist inside the
# container, leaving the storage symlink dangling (Apache: "Symbolic link
# not allowed or link target not accessible").
[ -L public/storage ] || php artisan storage:link --relative

exec "$@"
