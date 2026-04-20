# IMPLEMENTASI PLAN MAKSIMAL
## Aplikasi Belajar & Simulasi Psikotes Polri
### Versi khusus untuk development memakai AI Codex

---

# 1. TUJUAN DOKUMEN

Dokumen ini dibuat sebagai **master implementation plan** yang bisa langsung dipakai untuk:
- acuan arsitektur project
- acuan desain fitur
- acuan database
- acuan alur user
- acuan alur admin
- acuan task breakdown
- acuan prompt ke AI Codex
- acuan roadmap build bertahap sampai production-ready

Target akhir produk:
> Membangun platform web modern untuk belajar psikotes Polri dari nol, latihan per topik, simulasi seperti tes asli, analisis hasil, histori progres, serta rekomendasi belajar otomatis.

Dokumen ini sengaja dibuat sangat detail agar AI Codex:
- tidak salah arah
- tidak membangun fitur secara acak
- mengikuti pola Laravel yang rapi
- bisa membangun per fase
- mudah diaudit hasil kerjanya

---

# 2. KONSEP PRODUK

## 2.1 Nama Konsep
**Platform Belajar dan Simulasi Psikotes Polri**

## 2.2 Positioning
Bukan sekadar bank soal, tetapi:
- platform belajar bertahap
- platform tryout
- platform analisis progres pribadi
- platform persiapan pemula sampai siap simulasi

## 2.3 Masalah yang Ingin Diselesaikan
Calon peserta biasanya:
- tidak tahu jenis tes
- tidak paham cara mengerjakan
- bingung membaca pola soal
- tidak tahu kelemahan dirinya
- hanya menghafal soal tanpa memahami logika
- tidak punya sistem progres yang rapi

## 2.4 Solusi Produk
Aplikasi harus menyediakan:
1. materi pengantar untuk pemula
2. latihan per topik
3. simulasi bergaya CAT
4. hasil lengkap
5. pembahasan
6. tips dan trik
7. histori progres
8. analisis kekuatan dan kelemahan
9. rekomendasi belajar berikutnya

---

# 3. REKOMENDASI TEKNOLOGI FINAL

Untuk kebutuhan cepat, modern, dan cocok dibangun dengan bantuan AI Codex, stack utama yang direkomendasikan adalah:

## 3.1 Backend
- Laravel 13
- PHP 8.3+
- MySQL 8
- Redis opsional
- Queue untuk proses async
- Scheduler untuk pekerjaan berkala

Laravel saat ini menyediakan starter kit resmi untuk Vue menggunakan Inertia, TypeScript, Tailwind, dan shadcn-vue, sehingga ini cocok untuk membangun aplikasi full-stack yang cepat namun tetap modern. Laravel juga menyediakan starter kit auth yang sudah mencakup alur login, register, reset password, dan email verification. citeturn431419search2turn431419search9turn431419search13

## 3.2 Frontend
- Inertia.js
- Vue 3
- TypeScript
- Tailwind CSS
- shadcn-vue
- ApexCharts atau Chart.js

## 3.3 Auth & Security
- starter kit Laravel resmi
- email verification
- policy authorization
- form request validation
- CSRF protection
- throttling / rate limiting
- audit log admin

## 3.4 Dev Tools
- Vite
- Pint
- PHPStan / Larastan
- Pest atau PHPUnit
- ESLint
- Prettier

## 3.5 Codex Support
Laravel saat ini juga punya dokumentasi dan tooling AI resmi melalui Laravel Boost, yang ditujukan untuk membantu AI assistant mengikuti konvensi Laravel secara idiomatis. Ini relevan jika nanti kamu benar-benar ingin mempercepat kerja Codex dengan pedoman Laravel yang lebih presisi. citeturn431419search17turn431419search20

## 3.6 Kenapa Stack Ini Dipilih
Karena:
- cepat dibangun
- full-stack tetap terasa modern
- auth tidak perlu bikin dari nol
- dashboard SPA-like tapi tetap nyaman di Laravel
- lebih mudah dikelola daripada split backend API dan frontend terpisah
- cocok untuk proyek edukasi berbasis akun, histori, dan analitik

---

# 4. FILOSOFI PENGEMBANGAN

Project ini harus dibangun dengan prinsip:

## 4.1 Modular
Setiap modul berdiri jelas:
- auth
- materi
- bank soal
- latihan
- simulasi
- hasil
- progres
- admin

## 4.2 Incremental
Jangan bangun semua sekaligus.
Bangun bertahap:
1. fondasi
2. learning
3. practice
4. simulation
5. analytics
6. personality & drawing tests
7. production hardening

## 4.3 Data-first
Semua yang dikerjakan user harus bisa dianalisis.
Maka sejak awal:
- attempt
- answer
- duration
- score
- tags
- recommendation
harus dipikirkan matang.

## 4.4 UX-first untuk Pemula
Aplikasi ini harus mudah dipakai orang yang baru pertama kali belajar.
Jadi alur harus:
- sederhana
- visual jelas
- tidak membingungkan
- ada arahan berikutnya

## 4.5 AI-friendly Codebase
Supaya AI Codex mudah bekerja, project harus:
- punya struktur folder jelas
- punya naming convention konsisten
- punya service class terpisah
- punya docs internal
- punya acceptance criteria per fitur

---

# 5. TIPE PENGGUNA

## 5.1 Guest
Bisa:
- melihat landing page
- melihat penjelasan produk
- melihat preview fitur
- melihat contoh materi terbatas
- register dan login

## 5.2 User / Peserta
Bisa:
- register
- login
- verifikasi email
- mengisi onboarding
- belajar materi
- mengikuti mini quiz
- latihan per topik
- ikut simulasi
- melihat hasil
- melihat histori
- melihat progres
- bookmark soal
- melihat pembahasan
- menerima rekomendasi belajar

## 5.3 Admin
Bisa:
- CRUD kategori
- CRUD subtes
- CRUD modul belajar
- CRUD soal
- CRUD pembahasan
- CRUD paket simulasi
- melihat statistik penggunaan
- melihat statistik performa soal
- mengelola tips dan trik

## 5.4 Super Admin
Bisa:
- semua akses admin
- manajemen role admin
- konfigurasi sistem
- lihat audit log
- maintenance

---

# 6. FITUR INTI PRODUK

## 6.1 Authentication
- register
- login
- logout
- reset password
- verify email
- edit profile
- change password

## 6.2 Onboarding
- pilih level awal
- pilih target belajar
- pilih fokus tes
- sistem membuat roadmap awal

## 6.3 Mode Belajar
- penjelasan tiap subtes
- contoh soal
- strategi menjawab
- tips dan trik
- mini quiz

## 6.4 Mode Latihan
- pilih subtes
- pilih level
- pilih jumlah soal
- acak soal
- pembahasan
- simpan hasil

## 6.5 Mode Simulasi
- paket simulasi
- timer
- auto save jawaban
- navigasi soal
- tandai ragu
- hasil akhir
- laporan detail

## 6.6 Hasil & Analitik
- skor total
- skor per subtes
- akurasi
- durasi
- tren hasil
- topik lemah
- rekomendasi

## 6.7 Histori
- histori latihan
- histori simulasi
- review jawaban lama
- bandingkan performa

## 6.8 Bookmark & Review
- simpan soal sulit
- kumpulan soal salah
- ulangi soal salah
- review topik lemah

## 6.9 Admin CMS
- input materi
- input soal
- input opsi
- input pembahasan
- input paket simulasi
- lihat statistik soal

---

# 7. ALUR PRODUK SECARA MENYELURUH

## 7.1 First-Time User Flow
1. user buka landing page
2. user daftar
3. verifikasi email
4. login
5. onboarding muncul
6. user isi level awal
7. sistem buat dashboard personal
8. sistem rekomendasikan:
   - modul pengantar
   - mini quiz diagnostik
   - latihan pertama

## 7.2 Returning User Flow
1. user login
2. masuk dashboard
3. lihat progres
4. lihat rekomendasi
5. pilih:
   - lanjut belajar
   - latihan
   - simulasi
   - review hasil lama

## 7.3 Practice Flow
1. pilih subtes
2. pilih tingkat kesulitan
3. pilih jumlah soal
4. klik mulai
5. kerjakan
6. submit
7. lihat hasil dan pembahasan
8. hasil disimpan ke histori
9. sistem update progres

## 7.4 Simulation Flow
1. pilih paket simulasi
2. baca instruksi
3. mulai attempt
4. timer aktif
5. soal bisa dinavigasi
6. jawaban autosave
7. submit / waktu habis
8. hasil dihitung
9. dashboard update
10. sistem keluarkan insight

---

# 8. USER EXPERIENCE YANG WAJIB ADA

## 8.1 Landing Page
Harus terasa modern dan meyakinkan.
Section:
- hero
- manfaat
- cara kerja
- fitur utama
- preview dashboard
- FAQ
- CTA register

## 8.2 Dashboard User
Harus menampilkan:
- greeting personal
- progress hari ini
- streak belajar
- latihan terakhir
- simulasi terakhir
- grafik progres
- subtes lemah
- rekomendasi aksi berikutnya

## 8.3 Halaman Belajar
Harus menampilkan:
- intro tes
- tujuan tes
- strategi
- contoh soal
- tips
- mini quiz

## 8.4 Halaman Latihan / Simulasi
Harus menampilkan:
- timer
- nomor soal
- status jawaban
- tombol next/prev
- tanda ragu
- progress answered/unanswered

## 8.5 Halaman Hasil
Harus menampilkan:
- nilai total
- breakdown per subtes
- benar/salah
- pembahasan
- insight
- CTA latihan lagi

---

# 9. STRUKTUR MODUL BELAJAR

Setiap subtes minimal punya struktur konten seperti ini:

## 9.1 Pengenalan
- tes ini apa
- apa yang diukur
- kenapa penting

## 9.2 Cara Mengerjakan
- bagaimana membaca soal
- pola umum
- langkah menjawab cepat

## 9.3 Kesalahan Umum
- terlalu cepat
- tidak paham konteks
- tidak cek pola
- terjebak pilihan yang mirip

## 9.4 Tips dan Trik
- teknik eliminasi
- teknik identifikasi pola
- manajemen waktu
- fokus akurasi

## 9.5 Contoh Soal
- 3 sampai 10 soal
- pembahasan langkah demi langkah

## 9.6 Mini Quiz
- 5 sampai 10 soal
- hasil sederhana
- rekomendasi lanjut latihan

---

# 10. STRUKTUR FITUR LATIHAN

## 10.1 Konfigurasi Latihan
User bisa memilih:
- subtes
- tingkat kesulitan
- jumlah soal
- mode pembahasan langsung / akhir

## 10.2 Perilaku Sistem
Saat user klik mulai:
- sistem generate attempt
- sistem memilih soal acak sesuai filter
- sistem simpan relasi attempt-question
- sistem tampilkan soal satu per satu atau list mode

## 10.3 Setelah Selesai
Sistem menampilkan:
- skor
- jumlah benar
- jumlah salah
- durasi
- akurasi
- pembahasan
- rekomendasi topik berikutnya

---

# 11. STRUKTUR FITUR SIMULASI

## 11.1 Tujuan
Memberikan pengalaman yang menyerupai tes asli.

## 11.2 Elemen Utama
- timer
- paket soal
- autosave
- anti refresh loss
- submit confirmation
- status answer panel
- review sebelum submit

## 11.3 Jenis Paket
- simulasi verbal
- simulasi numerik
- simulasi kecermatan
- simulasi campuran
- simulasi full

## 11.4 Aturan
- jawaban disimpan berkala
- ketika waktu habis attempt auto submit
- user tidak melihat pembahasan sebelum selesai
- hasil hanya muncul setelah evaluasi

---

# 12. SISTEM ANALISIS HASIL

Ini adalah jantung nilai tambah aplikasi.

## 12.1 Data yang Dihitung
Per attempt:
- total soal
- jumlah benar
- jumlah salah
- jumlah kosong
- skor total
- skor per subtes
- durasi total
- durasi rata-rata per soal
- akurasi
- improvement dibanding attempt sebelumnya

## 12.2 Insight Otomatis
Contoh:
- hasil verbal meningkat 12% dari attempt sebelumnya
- user lambat di numerik level sedang
- user sering salah pada antonim kata abstrak
- akurasi bagus tapi waktu terlalu lama
- perlu fokus latihan angka hilang

## 12.3 Output Insight
Harus ditampilkan sebagai:
- ringkasan singkat
- grafik
- rekomendasi aksi

## 12.4 Recommendation Engine Sederhana
Aturan awal:
- jika akurasi subtes < 60% -> ulang modul dasar
- jika waktu rata-rata terlalu lambat -> tampilkan trik kecepatan
- jika 3 attempt naik berturut -> naikkan level
- jika 3 attempt stagnan -> turunkan level dan ulang konsep

---

# 13. HISTORI DAN PROGRESS

Karena user harus register, semua data penting harus disimpan.

## 13.1 Yang Harus Tersimpan
- histori login utama bila perlu
- modul yang telah dipelajari
- mini quiz yang dikerjakan
- latihan yang pernah diambil
- simulasi yang pernah diambil
- hasil setiap attempt
- jawaban per soal
- durasi per soal bila bisa
- topik yang sering salah
- soal bookmark
- rekomendasi yang pernah diberikan

## 13.2 Dashboard Progress
- grafik mingguan
- grafik bulanan
- rerata skor
- rerata akurasi
- subtes paling kuat
- subtes paling lemah
- progres streak belajar

---

# 14. RANCANGAN DATABASE MAKSIMAL

## 14.1 users
- id
- name
- email
- phone nullable
- password
- role enum
- email_verified_at nullable
- avatar nullable
- onboarding_completed boolean
- is_active boolean
- last_login_at nullable
- created_at
- updated_at

## 14.2 user_profiles
- id
- user_id
- education_level nullable
- target_exam nullable
- learning_level enum: pemula, menengah, mahir
- target_daily_minutes nullable
- preferred_focus nullable
- onboarding_answers json nullable
- created_at
- updated_at

## 14.3 categories
- id
- name
- slug
- description nullable
- sort_order
- is_active
- created_at
- updated_at

## 14.4 subtests
- id
- category_id
- name
- slug
- description nullable
- instruction longtext nullable
- scoring_type enum
- default_duration_minutes nullable
- sort_order
- is_active
- created_at
- updated_at

## 14.5 learning_modules
- id
- subtest_id
- title
- slug
- summary text nullable
- content longtext
- tips longtext nullable
- tricks longtext nullable
- level enum: basic, intermediate, advanced
- estimated_minutes nullable
- is_published boolean
- published_at nullable
- created_by
- updated_by
- created_at
- updated_at

## 14.6 questions
- id
- category_id
- subtest_id
- code nullable
- question_type enum
- difficulty enum
- question_text longtext
- question_image nullable
- extra_data json nullable
- explanation_text longtext nullable
- answer_key_text nullable
- status enum: draft, review, published, archived
- source_reference nullable
- created_by
- updated_by
- created_at
- updated_at

## 14.7 question_options
- id
- question_id
- option_key
- option_text nullable
- option_image nullable
- weight nullable
- is_correct nullable
- sort_order
- created_at
- updated_at

## 14.8 question_tags
- id
- name
- slug
- created_at
- updated_at

## 14.9 question_tag_maps
- id
- question_id
- question_tag_id

## 14.10 simulation_packages
- id
- name
- slug
- description nullable
- package_type enum
- duration_minutes
- instructions longtext nullable
- is_active
- created_by
- updated_by
- created_at
- updated_at

## 14.11 simulation_package_items
- id
- simulation_package_id
- subtest_id
- question_count
- duration_minutes nullable
- difficulty_mix json nullable
- sort_order
- created_at
- updated_at

## 14.12 attempts
- id
- user_id
- mode enum: mini_quiz, practice, simulation
- category_id nullable
- subtest_id nullable
- simulation_package_id nullable
- status enum: draft, in_progress, submitted, expired, cancelled
- started_at
- submitted_at nullable
- duration_seconds default 0
- total_questions default 0
- answered_questions default 0
- correct_answers default 0
- wrong_answers default 0
- blank_answers default 0
- score_total nullable
- accuracy nullable
- result_summary json nullable
- analysis_text longtext nullable
- created_at
- updated_at

## 14.13 attempt_questions
- id
- attempt_id
- question_id
- display_order
- section_name nullable
- created_at
- updated_at

## 14.14 attempt_answers
- id
- attempt_id
- question_id
- selected_option_id nullable
- answer_text nullable
- answer_json nullable
- is_flagged boolean
- is_correct nullable
- score nullable
- time_spent_seconds nullable
- answered_at nullable
- created_at
- updated_at

## 14.15 bookmarks
- id
- user_id
- question_id
- note nullable
- created_at
- updated_at

## 14.16 user_module_progress
- id
- user_id
- learning_module_id
- status enum: not_started, in_progress, completed
- progress_percent
- completed_at nullable
- created_at
- updated_at

## 14.17 recommendations
- id
- user_id
- source_attempt_id nullable
- title
- description
- action_type
- action_payload json nullable
- is_read boolean
- created_at
- updated_at

## 14.18 admin_activity_logs
- id
- admin_id
- action
- target_type
- target_id nullable
- payload json nullable
- created_at

## 14.19 system_settings
- id
- key
- value longtext nullable
- type
- created_at
- updated_at

---

# 15. JENIS QUESTION TYPE

Agar fleksibel, gunakan `question_type` yang jelas:

- multiple_choice_single
- multiple_choice_image
- true_false
- fill_blank
- sequence
- matching
- personality_forced_choice
- personality_likert_5
- upload_image
- pauli_grid
- missing_number
- missing_letter

Ini membantu AI Codex membangun engine yang tetap bisa berkembang.

---

# 16. SCORING STRATEGY

## 16.1 Objective Test
Untuk:
- sinonim
- antonim
- analogi
- numerik
- figural
- angka hilang
- huruf hilang

Skor awal:
- benar = 1
- salah = 0
- kosong = 0

## 16.2 Weighted Objective Test
Untuk soal sulit:
- mudah = 1
- sedang = 2
- sulit = 3

## 16.3 Accuracy-Speed Hybrid
Untuk:
- kecermatan
- hitung cepat
- pauli

Komponen:
- akurasi
- jumlah soal selesai
- waktu rata-rata

## 16.4 Personality Profile
Untuk:
- PAPI
- forced choice
- likert 5

Output:
- bukan benar/salah
- profil kecenderungan
- indikator konsistensi
- catatan latihan

## 16.5 Drawing Test
Untuk:
- Wartegg
- Baum
- Draw a Person

Tahap awal:
- hanya panduan dan upload
- review manual opsional

Tahap lanjut:
- rubrik evaluasi semi-manual

---

# 17. ARSITEKTUR LARAVEL YANG DISARANKAN

## 17.1 Folder Strategy

```txt
app/
  Actions/
  DTOs/
  Enums/
  Events/
  Http/
    Controllers/
    Middleware/
    Requests/
    Resources/
  Jobs/
  Models/
  Notifications/
  Policies/
  Providers/
  Repositories/
  Rules/
  Services/
  Support/
  ViewModels/
bootstrap/
config/
database/
  factories/
  migrations/
  seeders/
public/
resources/
  js/
    Components/
    Composables/
    Layouts/
    Pages/
    Types/
  css/
routes/
  web.php
  auth.php
storage/
tests/
```

## 17.2 Service Classes yang Wajib
- QuestionSelectionService
- AttemptCreationService
- AttemptSubmissionService
- ScoringService
- RecommendationService
- ProgressAnalyticsService
- BookmarkService
- SimulationTimerService

## 17.3 Repository Layer
Pakai repository jika memang dibutuhkan untuk query kompleks.
Contoh:
- QuestionRepository
- AttemptRepository
- AnalyticsRepository

## 17.4 Enum yang Disarankan
- UserRoleEnum
- AttemptModeEnum
- AttemptStatusEnum
- QuestionTypeEnum
- DifficultyEnum
- ScoringTypeEnum

## 17.5 Policy yang Disarankan
- QuestionPolicy
- LearningModulePolicy
- AttemptPolicy
- UserPolicy
- RecommendationPolicy

Laravel saat ini menempatkan service provider user-defined pada `bootstrap/providers.php`, jadi struktur modern Laravel perlu diikuti supaya AI Codex tidak menghasilkan pola lama yang keliru. citeturn431419search16

---

# 18. ROUTE MAP

## Public
- GET /
- GET /about
- GET /features
- GET /pricing (opsional nanti)
- GET /faq

## Auth
- GET /register
- POST /register
- GET /login
- POST /login
- POST /logout
- GET /forgot-password
- POST /forgot-password
- GET /reset-password/{token}
- POST /reset-password
- GET /verify-email
- GET /email/verify/{id}/{hash}
- POST /email/verification-notification

## User Dashboard
- GET /dashboard
- GET /profile
- PUT /profile
- PUT /profile/password

## Onboarding
- GET /onboarding
- POST /onboarding

## Learn
- GET /learn
- GET /learn/{category:slug}
- GET /learn/{category:slug}/{subtest:slug}
- GET /modules/{module:slug}
- POST /modules/{module}/complete
- POST /mini-quiz/start
- POST /mini-quiz/{attempt}/save-answer
- POST /mini-quiz/{attempt}/submit

## Practice
- GET /practice
- POST /practice/start
- GET /practice/attempts/{attempt}
- POST /practice/attempts/{attempt}/save-answer
- POST /practice/attempts/{attempt}/flag-question
- POST /practice/attempts/{attempt}/submit
- GET /practice/attempts/{attempt}/result

## Simulation
- GET /simulation
- GET /simulation/packages/{package:slug}
- POST /simulation/packages/{package}/start
- GET /simulation/attempts/{attempt}
- POST /simulation/attempts/{attempt}/save-answer
- POST /simulation/attempts/{attempt}/flag-question
- POST /simulation/attempts/{attempt}/submit
- GET /simulation/attempts/{attempt}/result

## History
- GET /history
- GET /history/practice
- GET /history/simulation
- GET /history/attempts/{attempt}

## Progress
- GET /progress
- GET /progress/analytics

## Bookmarks
- GET /bookmarks
- POST /bookmarks/{question}
- DELETE /bookmarks/{question}

## Recommendations
- GET /recommendations
- POST /recommendations/{recommendation}/mark-read

## Admin
- GET /admin
- resource /admin/categories
- resource /admin/subtests
- resource /admin/modules
- resource /admin/questions
- resource /admin/simulation-packages
- GET /admin/statistics
- GET /admin/users
- GET /admin/logs

---

# 19. HALAMAN YANG HARUS ADA

## Public
- LandingPage
- FeaturesPage
- FAQPage
- LoginPage
- RegisterPage

## User
- DashboardPage
- OnboardingPage
- LearnIndexPage
- LearnSubtestPage
- ModuleDetailPage
- PracticeConfigPage
- PracticeSessionPage
- PracticeResultPage
- SimulationIndexPage
- SimulationConfigPage
- SimulationSessionPage
- SimulationResultPage
- HistoryPage
- HistoryDetailPage
- ProgressPage
- BookmarkPage
- ProfilePage

## Admin
- AdminDashboardPage
- CategoryIndexPage
- SubtestIndexPage
- ModuleIndexPage
- QuestionIndexPage
- QuestionCreateEditPage
- SimulationPackageIndexPage
- UserAnalyticsPage
- ActivityLogPage

---

# 20. DESAIN VISUAL YANG DISARANKAN

Target user adalah anak muda, maka desain harus:
- clean
- modern
- tegas
- tidak terlalu ramai
- terasa seperti edtech premium

## 20.1 Warna
- background putih / soft slate
- primary dark navy
- accent merah
- success hijau
- warning amber

## 20.2 Komponen UI
- top nav clean
- sidebar dashboard modern
- card rounded-xl
- stat cards
- chart cards
- quiz cards
- answer state badges
- progress bars

## 20.3 Nuansa
- profesional tapi tetap ringan
- percaya diri
- motivasional
- cocok untuk pengguna pemula

Starter kit Vue resmi Laravel saat ini memang sudah memakai TypeScript, Tailwind, dan shadcn-vue, jadi ini sejalan dengan target UI modern yang kamu mau tanpa terlalu banyak setup manual dari nol. citeturn431419search2turn431419search12

---

# 21. AUTH, SESSION, DAN KEAMANAN

## 21.1 Kebutuhan Minimum
- password hashed
- email verification wajib
- auth middleware
- guest middleware
- rate limit login
- rate limit register
- CSRF protection
- XSS sanitization
- authorization via policies
- secure headers

## 21.2 Admin Security
- role-based access
- audit log
- optional 2FA
- session timeout lebih ketat

## 21.3 Data Privacy
- user hanya boleh melihat data sendiri
- admin terbatas sesuai role
- data analitik personal tidak boleh bocor

Laravel starter kit resmi dan sistem auth-nya memang ditujukan untuk mempercepat scaffolding login, register, reset password, dan email verification, sehingga bagian fondasi keamanan ini sebaiknya tidak dibangun manual dari nol. citeturn431419search0turn431419search13turn431419search15

---

# 22. ANALYTICS MODEL

## 22.1 Metrics Utama User
- total sesi belajar
- total latihan
- total simulasi
- rata-rata skor
- rata-rata akurasi
- total waktu belajar
- streak harian
- topik paling sering salah

## 22.2 Metrics Utama Admin
- subtes paling sering diakses
- soal paling sering salah
- soal terlalu mudah
- soal terlalu sulit
- retention user mingguan
- attempt completion rate
- average session duration

## 22.3 Metrics Dashboard
- weekly score trend
- monthly score trend
- category breakdown
- accuracy vs speed
- strongest area
- weakest area

---

# 23. ROADMAP PENGEMBANGAN TERPERINCI

## PHASE 1 — Project Foundation
### Target
Membangun fondasi project.

### Scope
- install Laravel starter kit Vue
- setup auth
- setup role
- setup base layout
- setup dashboard dummy
- setup migrations inti
- setup seeders inti
- setup admin middleware

### Deliverables
- user bisa register
- user bisa login
- user bisa verify email
- ada dashboard awal
- ada role admin dan user
- database inti siap

### Acceptance Criteria
- register/login jalan
- email verification jalan
- route admin terlindungi
- user biasa tidak bisa akses admin
- migration bersih dan bisa fresh seed

---

## PHASE 2 — Bank Soal & CMS Admin
### Scope
- categories CRUD
- subtests CRUD
- modules CRUD
- questions CRUD
- options CRUD
- tags
- status draft/published

### Deliverables
- admin bisa kelola konten
- admin bisa publish soal
- data siap dipakai latihan

### Acceptance Criteria
- admin bisa buat kategori
- admin bisa buat subtes
- admin bisa input soal A-E
- admin bisa tambah pembahasan
- admin bisa filter soal

---

## PHASE 3 — Mode Belajar
### Scope
- halaman learn
- halaman subtes
- halaman modul
- mini quiz
- progress module

### Deliverables
- user pemula bisa belajar
- user bisa tandai modul selesai
- mini quiz tersimpan

### Acceptance Criteria
- modul tampil rapi
- mini quiz submit jalan
- progress modul tersimpan

---

## PHASE 4 — Mode Latihan
### Scope
- practice config
- attempt creation
- question selection
- answer saving
- submit
- result page

### Deliverables
- user bisa latihan per topik
- hasil dan pembahasan tersimpan

### Acceptance Criteria
- attempt tidak ganda
- jawaban tersimpan
- skor tepat
- result page tampil

---

## PHASE 5 — Mode Simulasi
### Scope
- simulation package
- timer UI
- autosave
- review question map
- submit final
- result summary

### Deliverables
- simulasi seperti tes asli
- waktu habis auto submit

### Acceptance Criteria
- timer sinkron
- autosave jalan
- submit aman
- result akurat

---

## PHASE 6 — History & Analytics
### Scope
- history page
- detail history
- progress dashboard
- recommendation engine
- charts

### Deliverables
- user lihat perjalanan belajarnya
- user dapat rekomendasi

### Acceptance Criteria
- grafik tampil benar
- history detail bisa dibuka
- rekomendasi muncul dari data real

---

## PHASE 7 — Personality & Drawing
### Scope
- forced choice engine
- likert engine
- upload drawing
- rubrik admin review

### Deliverables
- kepribadian basic support
- tes gambar basic support

### Acceptance Criteria
- question type baru stabil
- upload aman
- admin bisa review

---

## PHASE 8 — Production Hardening
### Scope
- queue
- cache
- log monitoring
- backup
- SEO basic
- rate limits tuning
- performance optimization

### Deliverables
- siap deploy production
- stabil untuk user nyata

---

# 24. MVP YANG PALING MASUK AKAL

Kalau mau cepat launch, MVP sebaiknya hanya fokus pada:

## MVP V1
- auth
- onboarding sederhana
- dashboard user
- kategori & subtes
- modul belajar dasar
- latihan verbal
- latihan numerik
- hasil latihan
- histori dasar
- admin CRUD soal
- simulasi sederhana

## MVP V2
- kecermatan
- recommendation engine
- grafik progres
- bookmark
- review soal salah

## MVP V3
- personality
- Wartegg / Baum / DAP support basic
- paket simulasi lengkap
- admin analytics lebih dalam

---

# 25. SEED DATA YANG HARUS DISIAPKAN

Saat development, siapkan seed minimal:
- role admin
- role user
- 3 kategori utama
- 8 sampai 12 subtes awal
- 20-50 soal per subtes inti
- 5 modul belajar awal
- 2 paket simulasi awal
- 1 akun superadmin default dev

---

# 26. TESTING STRATEGY

## 26.1 Backend Tests
- auth flow
- authorization policy
- attempt creation
- scoring service
- recommendation rules
- admin CRUD

## 26.2 Frontend Tests
- page rendering
- form validation
- timer state
- answer save action
- result rendering

## 26.3 Manual QA Scenarios
- register user baru
- verify email
- isi onboarding
- ambil modul
- mulai latihan
- submit latihan
- review hasil
- mulai simulasi
- waktu habis
- auto submit
- login admin
- input soal baru

---

# 27. ACCEPTANCE CRITERIA GLOBAL

Project dianggap masuk standar ketika:
- auth stabil
- data user aman
- admin bisa input konten tanpa ribet
- user bisa belajar dari nol
- latihan berjalan mulus
- simulasi terasa natural
- hasil terbaca jelas
- histori dan progres tersimpan
- rekomendasi belajar muncul
- UI terasa modern di mobile dan desktop

---

# 28. TASK BREAKDOWN UNTUK AI CODEX

Berikut pola kerja yang ideal:
Jangan suruh Codex membangun semua sekaligus.
Pecah menjadi task kecil.

## Urutan kerja terbaik
1. scaffold project
2. auth + roles
3. layouts + dashboard
4. migrations inti
5. admin CRUD kategori/subtes
6. admin CRUD soal
7. modul belajar
8. practice engine
9. simulation engine
10. result & analytics
11. recommendation engine
12. polishing UI
13. testing
14. production hardening

---

# 29. PROMPT MASTER UNTUK AI CODEX

Gunakan prompt semacam ini saat mulai project:

## Prompt Master 1
Buat aplikasi Laravel 13 full-stack menggunakan starter kit resmi Vue + Inertia + TypeScript + Tailwind. Project ini adalah platform belajar dan simulasi psikotes Polri. Implementasikan fondasi project terlebih dahulu: auth, email verification, role user/admin, dashboard dasar, layout modern, dan struktur database awal untuk categories, subtests, learning_modules, questions, question_options, attempts, dan attempt_answers. Gunakan code yang rapi, modular, dan idiomatis Laravel. Jangan bangun semua fitur sekaligus. Mulai dari Phase 1 saja.

## Prompt Master 2
Lanjutkan project ini dengan membangun admin CMS untuk categories, subtests, learning_modules, dan questions. Gunakan form request validation, authorization policy, dan halaman admin yang modern dengan tabel, filter, search, pagination, create, edit, delete, publish/unpublish.

## Prompt Master 3
Bangun feature mode belajar. User bisa melihat daftar subtes, membuka modul belajar, menandai modul selesai, dan mengerjakan mini quiz. Simpan progres user dan tampilkan hasil mini quiz serta rekomendasi sederhana.

## Prompt Master 4
Bangun feature mode latihan. User bisa memilih subtes, tingkat kesulitan, dan jumlah soal. Sistem harus membuat attempt, memilih soal acak, menyimpan jawaban, menghitung skor, dan menampilkan pembahasan.

## Prompt Master 5
Bangun feature mode simulasi dengan timer, autosave, question navigator, flag question, dan auto submit ketika waktu habis. Hasil simulasi harus disimpan ke histori dan tampilkan analisis skor per subtes.

## Prompt Master 6
Bangun feature progress dan analytics. Tampilkan grafik tren hasil, subtes lemah, subtes kuat, histori latihan, histori simulasi, serta recommendation engine berbasis aturan sederhana.

---

# 30. PROMPT RULES UNTUK AI CODEX

Setiap kali memberi task ke Codex, tambahkan aturan ini:

- gunakan Laravel convention
- gunakan Form Request untuk validasi
- gunakan Policy untuk authorization
- gunakan Service class untuk business logic
- jangan taruh logic besar di controller
- gunakan migration terpisah dan rapi
- gunakan enum bila relevan
- gunakan TypeScript pada frontend
- gunakan composables untuk state frontend yang reusable
- buat code yang mudah dibaca dan mudah dites
- tambahkan komentar seperlunya, jangan berlebihan
- setelah selesai, tampilkan file yang dibuat/diubah
- jelaskan keputusan arsitektur singkat

---

# 31. STRATEGI KERJA PRAKTIS DENGAN CODEX

## 31.1 Jangan lakukan ini
- “buatkan seluruh aplikasi lengkap sekarang juga”
- “bangun semuanya sekalian”
- “buat sistem tryout full beserta semua role, semua dashboard, semua analytics”

Itu rawan hasil berantakan.

## 31.2 Lakukan ini
- minta 1 fase
- review hasil
- lanjut fase berikutnya
- minta refactor bila perlu
- pastikan testing basic ada

## 31.3 Pola ideal
1. kasih context project
2. kasih scope kecil
3. kasih acceptance criteria
4. kasih batasan teknis
5. suruh Codex list file changed
6. review
7. lanjut

---

# 32. CONTOH TASK DETAIL UNTUK CODEX

## Task Example 1
Implement role-based middleware dan policy untuk membedakan user dan admin. Pastikan route /admin hanya dapat diakses admin.

## Task Example 2
Implement migration dan model untuk categories dan subtests, lengkap dengan relasi dan seeder awal.

## Task Example 3
Implement admin page untuk mengelola questions dan question_options dengan form dinamis.

## Task Example 4
Implement PracticeStartAction yang menerima subtest, difficulty, question_count, lalu membuat attempt dan memilih soal random yang published.

## Task Example 5
Implement ScoringService untuk menghitung objective questions dan simpan result_summary ke tabel attempts.

---

# 33. STRATEGI CONTENT OPERATIONS

Agar project cepat tumbuh, siapkan workflow admin:

## 33.1 Workflow Konten
1. buat kategori
2. buat subtes
3. buat modul belajar
4. input soal
5. input opsi
6. input pembahasan
7. publish

## 33.2 Standar Soal
Setiap soal minimal punya:
- subtes
- difficulty
- pertanyaan
- opsi
- kunci
- pembahasan
- tag

## 33.3 Standar Modul
Setiap modul minimal punya:
- intro
- strategi
- trik
- contoh
- mini quiz

---

# 34. DEPLOYMENT PLAN

## 34.1 Environment
- Ubuntu
- Nginx
- PHP-FPM
- MySQL
- Redis opsional
- Supervisor untuk queue worker

## 34.2 Build Flow
- clone repo
- install composer deps
- install node deps
- setup .env
- migrate --seed
- build assets
- queue worker
- scheduler

## 34.3 Monitoring
- error logs
- failed jobs
- database backup
- uptime monitoring

---

# 35. NON-FUNCTIONAL REQUIREMENTS

Aplikasi harus:
- cepat dibuka
- responsif
- aman
- nyaman di mobile
- mudah diperluas
- maintainable
- mudah dibaca AI Codex dan developer manusia

---

# 36. FITUR LANJUTAN OPSIONAL

Setelah versi inti stabil, bisa ditambah:
- leaderboard mingguan
- target harian
- gamification
- badge
- paket premium
- mentor dashboard
- export hasil PDF
- reminder email
- notifikasi progres
- dark mode
- multi-tenant admin panel bila nanti berkembang jadi platform bimbel

---

# 37. KESIMPULAN FINAL

Implementasi terbaik untuk project ini adalah:

## Stack Final
- Laravel 13
- Starter kit resmi Vue + Inertia + TypeScript + Tailwind + shadcn-vue
- MySQL 8
- Redis opsional
- ApexCharts
- Policy + Form Request + Service class
- Auth dengan email verification
- Build bertahap berbasis fase

Laravel saat ini mendukung pendekatan ini dengan starter kit resmi yang memang ditujukan untuk membangun aplikasi modern berbasis Inertia, Vue, Tailwind, dan auth siap pakai. Laravel juga punya dokumentasi AI-assisted development dan Laravel Boost yang relevan untuk workflow dengan coding agents. citeturn431419search2turn431419search14turn431419search17turn431419search20

## Fokus Utama Build
1. fondasi auth aman
2. bank soal terstruktur
3. learning mode
4. practice mode
5. simulation mode
6. analytics & history
7. recommendation engine
8. polishing dan hardening

## Prinsip Kerja dengan Codex
- pecah task kecil
- build per fase
- review setiap fase
- pakai acceptance criteria
- jaga code tetap idiomatis Laravel

Dengan plan ini, Codex akan jauh lebih mudah menghasilkan aplikasi yang:
- cepat jadi
- desain modern
- aman
- scalable
- cocok untuk anak muda
- dan benar-benar berguna untuk belajar psikotes Polri dari nol sampai siap simulasi.
