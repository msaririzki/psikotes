#!/bin/sh
set -e

echo "==> [Prikotes] Memulai setup aplikasi..."

mkdir -p \
    /var/www/html/bootstrap/cache \
    /var/www/html/storage/app/public \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/testing \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs

if [ ! -f /var/www/html/public/index.php ]; then
    echo "==> [Prikotes] Menyalin file public ke volume bersama..."
    cp -r /public-init/. /var/www/html/public/
fi

chown -R www-data:www-data \
    /var/www/html/bootstrap/cache \
    /var/www/html/public \
    /var/www/html/storage

APP_KEY_VALUE="${APP_KEY:-}"
if [ -z "$APP_KEY_VALUE" ] || [ "$APP_KEY_VALUE" = "base64:GENERATE_A_REAL_KEY" ]; then
    echo "==> [ERROR] APP_KEY belum valid. Isi APP_KEY di .env.docker terlebih dahulu."
    exit 1
fi

if [ "$DB_CONNECTION" = "mysql" ] || [ "$DB_CONNECTION" = "mariadb" ] || [ -n "${DB_HOST:-}" ]; then
    echo "==> [Prikotes] Menunggu database ${DB_HOST:-db}:${DB_PORT:-3306} siap..."
    php -r '
        $host = getenv("DB_HOST") ?: "db";
        $db = getenv("DB_DATABASE") ?: "prikotes_app";
        $user = getenv("DB_USERNAME") ?: "root";
        $pass = getenv("DB_PASSWORD") ?: "";
        $port = getenv("DB_PORT") ?: "3306";

        for ($i = 0; $i < 30; $i++) {
            try {
                new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass, [
                    PDO::ATTR_TIMEOUT => 3,
                ]);
                echo "==> [Prikotes] Database siap!\n";
                exit(0);
            } catch (Throwable $exception) {
                sleep(2);
            }
        }

        fwrite(STDERR, "==> [ERROR] Database timeout atau credential salah.\n");
        exit(1);
    '
fi

echo "==> [Prikotes] Refresh package discovery..."
php artisan package:discover --ansi

echo "==> [Prikotes] Menjalankan migrasi database..."
php artisan migrate --force

echo "==> [Prikotes] Membuat storage symlink..."
php artisan storage:link --force || true

echo "==> [Prikotes] Optimasi Laravel..."
php artisan optimize

chown -R www-data:www-data \
    /var/www/html/bootstrap/cache \
    /var/www/html/public \
    /var/www/html/storage
chmod -R 775 \
    /var/www/html/bootstrap/cache \
    /var/www/html/storage

echo "==> [Prikotes] Setup selesai. Menjalankan: $*"
exec "$@"
