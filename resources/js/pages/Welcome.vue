<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
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
        class="min-h-screen bg-[linear-gradient(180deg,_#f4f8fb_0%,_#ffffff_52%,_#eef3f8_100%)] text-[#0f172a]"
    >
        <header
            class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-6 lg:px-10"
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex size-11 items-center justify-center rounded-2xl bg-[#0f172a] text-white shadow-sm"
                >
                    <span class="font-display text-lg font-bold">P</span>
                </div>
                <div>
                    <p class="font-display text-lg font-bold tracking-tight">
                        Prikotes
                    </p>
                    <p class="text-sm text-slate-500">
                        Belajar dan simulasi psikotes Polri
                    </p>
                </div>
            </div>
            <nav class="flex items-center gap-3 text-sm">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-flex items-center rounded-full border border-[#cbd5e1] bg-white px-5 py-2 font-medium text-slate-700 shadow-sm transition hover:border-slate-400"
                >
                    Dasbor
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-flex items-center rounded-full px-4 py-2 font-medium text-slate-600 transition hover:text-slate-900"
                    >
                        Masuk
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="inline-flex items-center rounded-full bg-[#b91c1c] px-5 py-2 font-semibold text-white shadow-sm transition hover:bg-[#991b1b]"
                    >
                        Buat akun
                    </Link>
                </template>
            </nav>
        </header>
        <main
            class="mx-auto flex w-full max-w-7xl flex-col gap-10 px-6 pt-4 pb-14 lg:px-10"
        >
            <section
                class="grid gap-8 lg:grid-cols-[1.1fr,0.9fr] lg:items-center"
            >
                <div class="space-y-6">
                    <span
                        class="inline-flex rounded-full border border-[#cbd5e1] bg-white/80 px-4 py-2 text-sm font-medium text-slate-600 shadow-sm"
                    >
                        Fokus Phase 1: fondasi platform yang siap dikembangkan
                    </span>
                    <div class="space-y-4">
                        <h1
                            class="max-w-3xl font-display text-5xl font-bold tracking-tight text-[#0f172a] lg:text-6xl"
                        >
                            Belajar psikotes Polri dari nol sampai siap
                            simulasi.
                        </h1>
                        <p class="max-w-2xl text-lg leading-8 text-slate-600">
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
                            class="inline-flex items-center justify-center rounded-full border border-[#cbd5e1] bg-white px-6 py-3 font-semibold text-slate-700 shadow-sm transition hover:border-slate-400"
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
                    class="relative overflow-hidden rounded-[2rem] border border-[#d8e1e8] bg-[radial-gradient(circle_at_top,_rgba(185,28,28,0.16),_transparent_32%),linear-gradient(135deg,_#ffffff_0%,_#eef4f8_70%,_#e2e8f0_100%)] p-6 shadow-xl"
                >
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div
                            class="rounded-[1.5rem] bg-white/85 p-5 shadow-sm ring-1 ring-white"
                        >
                            <p
                                class="text-xs tracking-[0.22em] text-slate-500 uppercase"
                            >
                                Kategori
                            </p>
                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                {{ stats.categories }}
                            </p>
                        </div>
                        <div
                            class="rounded-[1.5rem] bg-white/85 p-5 shadow-sm ring-1 ring-white"
                        >
                            <p
                                class="text-xs tracking-[0.22em] text-slate-500 uppercase"
                            >
                                Subtes
                            </p>
                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                {{ stats.subtests }}
                            </p>
                        </div>
                        <div
                            class="rounded-[1.5rem] bg-white/85 p-5 shadow-sm ring-1 ring-white"
                        >
                            <p
                                class="text-xs tracking-[0.22em] text-slate-500 uppercase"
                            >
                                Modul
                            </p>
                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                {{ stats.modules }}
                            </p>
                        </div>
                        <div
                            class="rounded-[1.5rem] bg-white/85 p-5 shadow-sm ring-1 ring-white"
                        >
                            <p
                                class="text-xs tracking-[0.22em] text-slate-500 uppercase"
                            >
                                Soal
                            </p>
                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                {{ stats.questions }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="mt-6 rounded-[1.5rem] bg-[#0f172a] p-5 text-white shadow-sm"
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

            <section class="grid gap-4 lg:grid-cols-3">
                <div
                    class="rounded-[1.75rem] border border-[#dce4ea] bg-white p-6 shadow-sm"
                >
                    <p class="text-sm font-semibold text-[#b91c1c]">
                        Fondasi aman
                    </p>
                    <h2
                        class="mt-3 font-display text-2xl font-bold tracking-tight text-slate-900"
                    >
                        Auth, verifikasi email, dan role-based access.
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        Phase 1 menutup akses admin dengan policy dan middleware
                        agar CMS berikutnya tidak dibangun di atas authorization
                        yang rapuh.
                    </p>
                </div>
                <div
                    class="rounded-[1.75rem] border border-[#dce4ea] bg-white p-6 shadow-sm"
                >
                    <p class="text-sm font-semibold text-[#b91c1c]">
                        Data-first
                    </p>
                    <h2
                        class="mt-3 font-display text-2xl font-bold tracking-tight text-slate-900"
                    >
                        Schema inti sudah siap untuk latihan dan simulasi.
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        Tabel kategori, subtes, modul, soal, opsi, attempt, dan
                        jawaban disiapkan sejak awal agar fase berikutnya tidak
                        perlu refactor destruktif.
                    </p>
                </div>
                <div
                    class="rounded-[1.75rem] border border-[#dce4ea] bg-white p-6 shadow-sm"
                >
                    <p class="text-sm font-semibold text-[#b91c1c]">
                        Siap berkembang
                    </p>
                    <h2
                        class="mt-3 font-display text-2xl font-bold tracking-tight text-slate-900"
                    >
                        Laravel 13 + Inertia + Vue 3 + TypeScript.
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        Struktur starter kit resmi dipertahankan, lalu diperluas
                        dengan enum, service, policy, seeder, dan dashboard awal
                        yang production-friendly.
                    </p>
                </div>
            </section>
        </main>
    </div>
</template>

