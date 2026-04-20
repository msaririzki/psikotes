# Manual Smoke Test

Dokumen ini dipakai untuk pre-UAT lokal setelah `php artisan migrate:fresh --seed`.

## Akun demo

- Super admin: `superadmin@prikotes.test` / `password`
- Admin: `admin@prikotes.test` / `password`
- Peserta demo: `peserta@prikotes.test` / `password`
- Peserta baru: `userbaru@prikotes.test` / `password`

## User flow

### 1. Register / login

- [ ] User baru bisa register
- [ ] User baru bisa login
- [ ] User demo `peserta@prikotes.test` bisa login

### 2. Onboarding

- [ ] Login sebagai `userbaru@prikotes.test`
- [ ] Buka `/onboarding`
- [ ] Isi target ujian, level belajar, target menit harian, dan fokus utama
- [ ] Submit onboarding berhasil dan kembali ke dashboard
- [ ] Banner onboarding di dashboard hilang setelah submit

### 3. Learn

- [ ] Buka `/learn`
- [ ] Daftar kategori dan subtes tampil
- [ ] Buka detail kategori
- [ ] Buka detail subtes
- [ ] Buka detail modul
- [ ] Konten modul menampilkan pengenalan, tujuan, cara mengerjakan, tips, trik, contoh

### 4. Module completion

- [ ] Tombol tandai modul selesai bekerja
- [ ] Status modul berubah menjadi selesai

### 5. Mini quiz

- [ ] Mini quiz bisa dimulai dari modul
- [ ] Soal tampil dengan opsi jawaban
- [ ] Submit mini quiz berhasil
- [ ] Result mini quiz tampil dengan skor dan rekomendasi

### 6. Practice

- [ ] Buka `/practice`
- [ ] Buka salah satu subtes
- [ ] Pilih difficulty, jumlah soal, dan timer opsional
- [ ] Start practice berhasil
- [ ] Jawaban bisa disimpan
- [ ] Submit practice berhasil
- [ ] Result practice tampil dengan pembahasan

### 7. Simulation

- [ ] Buka `/simulations`
- [ ] Buka detail simulation package
- [ ] Start simulation berhasil
- [ ] Timer tampil
- [ ] Navigator soal tampil
- [ ] Flag soal bekerja
- [ ] Submit simulation berhasil
- [ ] Result simulation tampil dengan breakdown dan review snapshot

### 8. History / progress

- [ ] `/history` menampilkan timeline aktivitas
- [ ] `/progress` menampilkan insight dan analytics dasar
- [ ] Attempt lama bisa dibuka kembali dari history/result pages

### 9. Study plan / tasks / goals

- [ ] `/study-plan` menampilkan task, agenda, cadence, readiness, dan goals
- [ ] Task bisa `Done`
- [ ] Task bisa `Snooze`
- [ ] Task bisa `Reschedule`
- [ ] Goal weekly dan monthly tampil

## Admin flow

### 10. Admin login

- [ ] Login sebagai `admin@prikotes.test`
- [ ] Dashboard admin bisa diakses

### 11. Kelola konten inti

- [ ] Admin bisa buka categories index
- [ ] Admin bisa create/edit category
- [ ] Admin bisa buka subtests index
- [ ] Admin bisa create/edit subtest
- [ ] Admin bisa buka modules index
- [ ] Admin bisa create/edit/publish module
- [ ] Admin bisa buka questions index
- [ ] Admin bisa create/edit/publish question
- [ ] Admin bisa create/edit question options

### 12. Kelola simulation package

- [ ] Admin bisa buka simulation packages index
- [ ] Admin bisa create/edit simulation package
- [ ] Admin bisa atur komposisi subtes
- [ ] Admin bisa publish/unpublish package

## Quick regression

- [ ] Landing page tetap terbuka untuk guest
- [ ] User biasa tidak bisa akses `/admin`
- [ ] Admin bisa akses `/admin`
- [ ] Result simulation lama tetap konsisten walau CMS nanti berubah
