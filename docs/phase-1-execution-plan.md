# Phase 1 Execution Plan

## Product understanding summary
- Produk adalah platform belajar dan simulasi psikotes Polri, bukan sekadar bank soal.
- Fokus nilai utama tahap awal adalah membangun fondasi akun, struktur domain tes, dan pintu masuk admin yang aman.
- Acuan domain utama diambil dari PDF bank soal: `Tes Kecerdasan`, `Tes Kecermatan dan Ketelitian`, dan `Kepribadian`, berikut subtes inti seperti verbal, numerik, figural, Pauli, angka/huruf hilang, PAPI, kuisioner, Wartegg, Baum, dan Draw a Person.

## Validated architecture
- Laravel 13 starter kit Vue dipakai sebagai fondasi resmi untuk auth, email verification, Inertia, Vue 3, TypeScript, dan Tailwind.
- Role disimpan sebagai PHP backed enum pada kolom string agar fleksibel untuk evolusi skema.
- Business logic awal dipisah ke service class:
  - `UserProvisioningService` untuk registrasi user + profile default.
  - `DashboardOverviewService` untuk agregasi data landing, dashboard user, dan dashboard admin.
- Hak akses admin divalidasi berlapis:
  - `UserPolicy` sebagai sumber aturan otorisasi.
  - `EnsureUserCanAccessAdmin` sebagai middleware route-level.
- Migrations inti disiapkan sekarang untuk menghindari refactor destruktif saat Phase 2-5.

## Phase breakdown
1. Phase 1: foundation project, auth, verification, role, schema inti, seed referensi, dashboard awal, admin gate.
2. Phase 2: CMS admin untuk categories, subtests, modules, questions, options.
3. Phase 3: learning mode dan mini quiz.
4. Phase 4: practice engine.
5. Phase 5: simulation engine.
6. Phase 6: history, analytics, recommendation.

## Core schema proposal
- `users`
- `user_profiles`
- `categories`
- `subtests`
- `learning_modules`
- `questions`
- `question_options`
- `attempts`
- `attempt_questions`
- `attempt_answers`

## Initial route map
- Public:
  - `GET /`
- Authenticated + verified:
  - `GET /dashboard`
  - `GET /settings/profile`
  - `GET /settings/security`
  - `GET /settings/appearance`
- Authenticated + verified + admin policy:
  - `GET /admin`

## Phase 1 implementation boundary
- Included:
  - starter kit scaffold
  - auth and email verification
  - role enum and admin protection
  - domain seed based on PDF
  - foundation schema for next phases
  - landing, dashboard, admin dashboard
- Excluded:
  - onboarding flow UI
  - CRUD admin CMS
  - question engine
  - attempt submission logic
  - analytics charts
