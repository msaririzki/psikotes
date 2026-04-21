#!/bin/sh
set -eu

cd /var/www/html

mkdir -p \
    bootstrap/cache \
    database \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs

chown -R www-data:www-data bootstrap/cache storage database

if [ "${DB_CONNECTION:-sqlite}" = "mysql" ]; then
    echo "Waiting for MySQL at ${DB_HOST:-db}:${DB_PORT:-3306}..."
    attempts=0

    until php -r '
        $host = getenv("DB_HOST") ?: "db";
        $port = getenv("DB_PORT") ?: "3306";
        $database = getenv("DB_DATABASE") ?: "prikotes_app";
        $username = getenv("DB_USERNAME") ?: "prikotes";
        $password = getenv("DB_PASSWORD") ?: "";

        try {
            new PDO(
                "mysql:host={$host};port={$port};dbname={$database}",
                $username,
                $password,
                [PDO::ATTR_TIMEOUT => 3]
            );
        } catch (Throwable $exception) {
            fwrite(STDERR, $exception->getMessage() . PHP_EOL);
            exit(1);
        }
    '; do
        attempts=$((attempts + 1))

        if [ "$attempts" -ge 30 ]; then
            echo "MySQL is still unreachable after 30 attempts."
            exit 1
        fi

        sleep 2
    done
fi

php artisan storage:link --force || true
php artisan migrate --force

exec "$@"
