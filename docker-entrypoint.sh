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

# Masked diagnostics so we can see in the deploy logs whether DB_URL/DB_HOST
# actually resolved to something real (vs. an unresolved "${{...}}" reference
# or just being empty) — without leaking the password.
echo "DB_CONNECTION=${DB_CONNECTION}"
echo "DB_URL is set: $([ -n "$DB_URL" ] && echo yes || echo no) — starts with: $(echo "$DB_URL" | cut -c1-15)..."
echo "DB_HOST=${DB_HOST}"

echo "Esperando a que la base de datos acepte conexiones..."
retries=15
# A raw PDO connection attempt — not `db:show` (which formats table sizes via
# Illuminate\Support\Number and throws if the "intl" extension is missing,
# making it fail regardless of whether the DB is actually reachable).
until output=$(php artisan tinker --execute="DB::connection()->getPdo();" 2>&1); do
    retries=$((retries - 1))
    if [ "$retries" -le 0 ]; then
        echo "No se pudo conectar a la base de datos después de 15 intentos. Último error:"
        echo "$output"
        break
    fi
    echo "Sin conexión todavía, reintentando... ($retries intentos restantes)"
    sleep 2
done

php artisan migrate --force

# --relative avoids a Docker Desktop/WSL2 bind-mount quirk where absolute
# symlinks get rewritten to a host-side path that doesn't exist inside the
# container, leaving the storage symlink dangling (Apache: "Symbolic link
# not allowed or link target not accessible").
[ -L public/storage ] || php artisan storage:link --relative

exec "$@"
