<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ThemeToggle from '@/components/ThemeToggle.vue';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
        stats: {
            categories: number;
            subtests: number;
            modules: number;
            questions: number;
        };
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Prikotes" />
    <div
        class="min-h-screen bg-[linear-gradient(180deg,_#f4f8fb_0%,_#ffffff_52%,_#eef3f8_100%)] text-[#0f172a] dark:bg-[linear-gradient(180deg,_#050816_0%,_#0a1120_52%,_#08101d_100%)] dark:text-slate-100"
    >
        <header
            class="mx-auto flex w-full max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-4 sm:px-6 sm:py-6 lg:px-10"
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex size-10 items-center justify-center rounded-2xl bg-[#0f172a] text-white shadow-sm sm:size-11"
                >
                    <span class="font-display text-lg font-bold">P</span>
                </div>
                <div>
                    <p class="font-display text-lg font-bold tracking-tight">
                        Prikotes
                    </p>
                    <p
                        class="text-xs text-slate-500 sm:text-sm dark:text-slate-400"
                    >
                        Belajar dan simulasi psikotes Polri
                    </p>
                </div>
            </div>
            <nav
                class="flex flex-wrap items-center justify-end gap-2 text-sm sm:gap-3"
            >
                <ThemeToggle compact />
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-flex items-center rounded-full border border-[#cbd5e1] bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:border-slate-400 sm:px-5 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:border-slate-500"
                >
                    Dasbor
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-flex items-center rounded-full px-3 py-2 text-sm font-medium text-slate-600 transition hover:text-slate-900 sm:px-4 dark:text-slate-300 dark:hover:text-white"
                    >
                        Masuk
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="inline-flex items-center rounded-full bg-[#b91c1c] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#991b1b] sm:px-5"
                    >
                        Buat akun
                    </Link>
                </template>
            </nav>
        </header>
        <main
            class="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 pt-2 pb-12 sm:gap-10 sm:px-6 sm:pt-4 sm:pb-14 lg:px-10"
        >
            <section
                class="grid gap-6 sm:gap-8 lg:grid-cols-[1.1fr,0.9fr] lg:items-center"
            >
                <div class="space-y-5 sm:space-y-6">
                    <span
                        class="inline-flex rounded-full border border-[#cbd5e1] bg-white/80 px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm sm:px-4 sm:py-2 sm:text-sm dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-300"
                    >
                        Fokus Phase 1: fondasi platform yang siap dikembangkan
                    </span>
                    <div class="space-y-4">
                        <h1
                            class="max-w-3xl font-display text-4xl font-bold tracking-tight text-[#0f172a] sm:text-5xl lg:text-6xl dark:text-slate-50"
                        >
                            Belajar psikotes Polri dari nol sampai siap
                            simulasi.
                        </h1>
                        <p
                            class="max-w-2xl text-base leading-7 text-slate-600 sm:text-lg sm:leading-8 dark:text-slate-300"
                        >
                            Prikotes menyiapkan struktur belajar, domain soal,
                            dan fondasi akun yang rapi agar fase latihan,
                            simulasi, dan analitik bisa tumbuh di atas
                            arsitektur yang stabil.
                        </p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <Link
                            v-if="!$page.props.auth.user && canRegister"
                            :href="register()"
                            class="inline-flex items-center justify-center rounded-full bg-[#0f172a] px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-[#111827]"
                        >
                            Mulai persiapan
                        </Link>
                        <Link
                            :href="
                                $page.props.auth.user ? dashboard() : login()
                            "
                            class="inline-flex items-center justify-center rounded-full border border-[#cbd5e1] bg-white px-6 py-3 font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:border-slate-500"
                        >
                            {{
                                $page.props.auth.user
                                    ? 'Buka dashboard'
                                    : 'Masuk ke akun'
                            }}
                        </Link>
                    </div>
                </div>

                <div
                    class="relative overflow-hidden rounded-[1.6rem] border border-[#d8e1e8] bg-[radial-gradient(circle_at_top,_rgba(185,28,28,0.16),_transparent_32%),linear-gradient(135deg,_#ffffff_0%,_#eef4f8_70%,_#e2e8f0_100%)] p-5 shadow-xl sm:rounded-[2rem] sm:p-6 dark:border-slate-800 dark:bg-[radial-gradient(circle_at_top,_rgba(185,28,28,0.22),_transparent_32%),linear-gradient(135deg,_#0f172a_0%,_#0b1220_70%,_#020617_100%)]"
                >
                    <div class="grid gap-3 sm:grid-cols-2 sm:gap-4">
                        <div
                            class="rounded-[1.25rem] bg-white/85 p-4 shadow-sm ring-1 ring-white sm:rounded-[1.5rem] sm:p-5 dark:bg-slate-900/80 dark:ring-slate-800"
                        >
                            <p
                                class="text-xs tracking-[0.22em] text-slate-500 uppercase dark:text-slate-400"
                            >
                                Kategori
                            </p>
                            <p
                                class="mt-2 text-3xl font-bold text-slate-900 dark:text-white"
                            >
                                {{ stats.categories }}
                            </p>
                        </div>
                        <div
                            class="rounded-[1.25rem] bg-white/85 p-4 shadow-sm ring-1 ring-white sm:rounded-[1.5rem] sm:p-5 dark:bg-slate-900/80 dark:ring-slate-800"
                        >
                            <p
                                class="text-xs tracking-[0.22em] text-slate-500 uppercase dark:text-slate-400"
                            >
                                Subtes
                            </p>
                            <p
                                class="mt-2 text-3xl font-bold text-slate-900 dark:text-white"
                            >
                                {{ stats.subtests }}
                            </p>
                        </div>
                        <div
                            class="rounded-[1.25rem] bg-white/85 p-4 shadow-sm ring-1 ring-white sm:rounded-[1.5rem] sm:p-5 dark:bg-slate-900/80 dark:ring-slate-800"
                        >
                            <p
                                class="text-xs tracking-[0.22em] text-slate-500 uppercase dark:text-slate-400"
                            >
                                Modul
                            </p>
                            <p
                                class="mt-2 text-3xl font-bold text-slate-900 dark:text-white"
                            >
                                {{ stats.modules }}
                            </p>
                        </div>
                        <div
                            class="rounded-[1.25rem] bg-white/85 p-4 shadow-sm ring-1 ring-white sm:rounded-[1.5rem] sm:p-5 dark:bg-slate-900/80 dark:ring-slate-800"
                        >
                            <p
                                class="text-xs tracking-[0.22em] text-slate-500 uppercase dark:text-slate-400"
                            >
                                Soal
                            </p>
                            <p
                                class="mt-2 text-3xl font-bold text-slate-900 dark:text-white"
                            >
                                {{ stats.questions }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="mt-5 rounded-[1.25rem] bg-[#0f172a] p-4 text-white shadow-sm sm:mt-6 sm:rounded-[1.5rem] sm:p-5"
                    >
                        <p
                            class="text-sm tracking-[0.2em] text-slate-300 uppercase"
                        >
                            Domain awal dari PDF
                        </p>
                        <ul class="mt-3 space-y-2 text-sm text-slate-100">
                            <li>Tes kecerdasan: verbal, numerik, figural.</li>
                            <li>
                                Tes kecermatan: Pauli, angka hilang, huruf
                                hilang.
                            </li>
                            <li>
                                Kepribadian: PAPI, kuisioner, Wartegg, Baum,
                                Draw a Person.
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="grid gap-3 sm:gap-4 lg:grid-cols-3">
                <div
                    class="rounded-[1.4rem] border border-[#dce4ea] bg-white p-5 shadow-sm sm:rounded-[1.75rem] sm:p-6 dark:border-slate-800 dark:bg-slate-900/80"
                >
                    <p class="text-sm font-semibold text-[#b91c1c]">
                        Fondasi aman
                    </p>
                    <h2
                        class="mt-3 font-display text-xl font-bold tracking-tight text-slate-900 sm:text-2xl dark:text-white"
                    >
                        Auth, verifikasi email, dan role-based access.
                    </h2>
                    <p
                        class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300"
                    >
                        Phase 1 menutup akses admin dengan policy dan middleware
                        agar CMS berikutnya tidak dibangun di atas authorization
                        yang rapuh.
                    </p>
                </div>
                <div
                    class="rounded-[1.4rem] border border-[#dce4ea] bg-white p-5 shadow-sm sm:rounded-[1.75rem] sm:p-6 dark:border-slate-800 dark:bg-slate-900/80"
                >
                    <p class="text-sm font-semibold text-[#b91c1c]">
                        Data-first
                    </p>
                    <h2
                        class="mt-3 font-display text-xl font-bold tracking-tight text-slate-900 sm:text-2xl dark:text-white"
                    >
                        Schema inti sudah siap untuk latihan dan simulasi.
                    </h2>
                    <p
                        class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300"
                    >
                        Tabel kategori, subtes, modul, soal, opsi, attempt, dan
                        jawaban disiapkan sejak awal agar fase berikutnya tidak
                        perlu refactor destruktif.
                    </p>
                </div>
                <div
                    class="rounded-[1.4rem] border border-[#dce4ea] bg-white p-5 shadow-sm sm:rounded-[1.75rem] sm:p-6 dark:border-slate-800 dark:bg-slate-900/80"
                >
                    <p class="text-sm font-semibold text-[#b91c1c]">
                        Siap berkembang
                    </p>
                    <h2
                        class="mt-3 font-display text-xl font-bold tracking-tight text-slate-900 sm:text-2xl dark:text-white"
                    >
                        Laravel 13 + Inertia + Vue 3 + TypeScript.
                    </h2>
                    <p
                        class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300"
                    >
                        Struktur starter kit resmi dipertahankan, lalu diperluas
                        dengan enum, service, policy, seeder, dan dashboard awal
                        yang production-friendly.
                    </p>
                </div>
            </section>
        </main>
    </div>
</template>
