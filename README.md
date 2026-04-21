# Prikotes

Platform belajar psikotes Polri berbasis Laravel 13, Inertia, Vue 3, TypeScript, dan Tailwind.

## Default local dev

Default setup lokal sekarang memakai SQLite supaya bootstrap paling cepat dan tidak perlu MySQL dulu.

- PHP `^8.3`
- Composer
- Node.js `^22`
- npm

## Quick start

1. Install dependency dan bootstrap project:

```bash
composer setup
```

2. Jalankan app:

```bash
composer dev
```

3. Buka aplikasi:

- App: [http://127.0.0.1:8000](http://127.0.0.1:8000)

`composer setup` akan otomatis:

- copy `.env.example` ke `.env`
- membuat `database/database.sqlite`
- generate app key
- migrate + seed demo data
- install dependency frontend
- build asset production

## Manual setup

Kalau ingin setup manual:

```bash
composer install
php -r "file_exists('.env') || copy('.env.example', '.env');"
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan key:generate
php artisan migrate --seed
npm install
composer dev
```

## MySQL local alternative

Kalau ingin pakai MySQL:

1. Ubah `.env`
2. Ganti:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prikotes_app
DB_USERNAME=root
DB_PASSWORD=
```

3. Jalankan ulang:

```bash
php artisan migrate:fresh --seed
```

## Demo accounts

Semua akun demo memakai password yang sama:

- Password: `password`

Daftar akun:

- Super admin: `superadmin@prikotes.test`
- Admin konten: `admin@prikotes.test`
- Peserta demo dengan histori siap coba: `peserta@prikotes.test`
- Peserta baru untuk coba flow onboarding: `userbaru@prikotes.test`

## Demo data yang tersedia

Seeder demo sekarang menyiapkan data agar app langsung bisa diuji:

- kategori dan subtes inti
- learning modules published
- bank soal published untuk:
  - verbal
  - numerik
  - figural
  - hitung cepat
  - koran pauli
  - angka hilang / huruf hilang
- simulation packages published
- histori demo untuk user peserta:
  - module progress
  - mini quiz
  - practice
  - simulation

## Flow utama yang bisa dicoba

User:

1. Register / login
2. Onboarding via `/onboarding`
3. Buka `/learn`
4. Selesaikan modul
5. Mini quiz
6. Practice
7. Simulation
8. Lihat `/history`
9. Lihat `/progress`
10. Lihat `/study-plan`

Admin:

1. Login ke `/login`
2. Buka `/admin`
3. Kelola categories
4. Kelola subtests
5. Kelola learning modules
6. Kelola questions dan options
7. Kelola simulation packages

## Seed dan reset database

Reset database SQLite/MySQL lalu seed ulang:

```bash
php artisan migrate:fresh --seed
```

## Local dev notes

- Default `.env.example` memakai SQLite, `file` session/cache, dan `sync` queue supaya local run tidak bergantung service tambahan.
- Registrasi user baru akan auto-verified di `APP_ENV=local` agar flow UAT tidak tertahan email verification.

## Quality checks

```bash
php artisan test
npm run types:check
npm run lint:check
npm run build
```

## Docker deploy untuk server

Stack Docker yang disiapkan di repo ini memakai:

- `web`: Apache + PHP 8.3 + Laravel app
- `db`: MySQL 8.4

Targetnya adalah deploy sederhana dari VM Linux, termasuk kalau kamu login ke VM sebagai `root`.

### File yang dipakai

- `Dockerfile`
- `docker-compose.yml`
- `.env.docker.example`
- `docker/entrypoint.sh`

### Langkah deploy di VM

1. Clone repo ke server.
2. Masuk ke folder project.
3. Copy file env docker:

```bash
cp .env.docker.example .env.docker
```

4. Generate `APP_KEY` lalu tempel ke `.env.docker`.

Kalau di VM belum ada PHP lokal, pakai Docker:

```bash
docker run --rm php:8.3-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

5. Sesuaikan nilai penting di `.env.docker`:

- `APP_URL`
- `APP_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `DB_ROOT_PASSWORD`

6. Jalankan deploy:

```bash
docker compose --env-file .env.docker up -d --build
```

7. Cek status:

```bash
docker compose --env-file .env.docker ps
docker compose --env-file .env.docker logs -f web
```

### Catatan deploy

- Migrate database dijalankan otomatis saat container `web` start.
- `storage:link` juga dijalankan otomatis saat boot container.
- Data MySQL dan folder `storage` disimpan di Docker volume supaya tidak hilang saat recreate container.
- Default expose web ada di port `80`. Kalau ingin ganti, ubah `APP_PORT` di `.env.docker`.

### Update aplikasi

Kalau code di server berubah:

```bash
docker compose --env-file .env.docker up -d --build
```

Kalau ingin menjalankan command Laravel manual:

```bash
docker compose --env-file .env.docker exec web php artisan about
docker compose --env-file .env.docker exec web php artisan migrate:status
```

## Manual smoke test

Checklist manual tersedia di:

- [docs/manual-smoke-test.md](docs/manual-smoke-test.md)
# psikotes  
