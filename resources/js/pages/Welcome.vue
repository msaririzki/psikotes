<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ThemeToggle from '@/components/ThemeToggle.vue';
import AppLogo from '@/components/AppLogo.vue';
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
        class="min-h-screen bg-slate-50 text-slate-900 selection:bg-[#b91c1c]/10 selection:text-[#b91c1c] dark:bg-slate-950 dark:text-slate-100 dark:selection:bg-[#ef4444]/20"
    >
        <!-- Background Decorative Orbs for Glassmorphism -->
        <div class="pointer-events-none fixed inset-0 z-0 flex justify-center overflow-hidden">
            <div
                class="absolute -top-[20%] left-[-10%] h-[500px] w-[500px] rounded-full bg-red-600/5 mix-blend-multiply blur-[120px] dark:bg-red-500/10 dark:mix-blend-lighten"
            ></div>
            <div
                class="absolute right-[-5%] top-[10%] h-[600px] w-[600px] rounded-full bg-blue-600/5 mix-blend-multiply blur-[120px] dark:bg-blue-500/10 dark:mix-blend-lighten"
            ></div>
        </div>

        <div class="relative z-10 flex min-h-screen flex-col">
            <!-- Header -->
            <header
                class="sticky top-0 z-50 mx-auto flex w-full max-w-7xl items-center justify-between border-b border-slate-200/50 bg-white/60 px-6 py-4 backdrop-blur-xl lg:px-10 dark:border-slate-800/50 dark:bg-slate-950/60"
            >
                <Link href="/" class="flex items-center transition-opacity hover:opacity-80">
                    <AppLogo />
                </Link>
                <nav class="flex items-center gap-3 text-sm font-medium sm:gap-4">
                    <ThemeToggle compact />
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="hidden items-center justify-center rounded-full bg-slate-100 px-5 py-2.5 text-slate-700 transition-all hover:bg-slate-200 sm:inline-flex dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    >
                        Dasbor
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="hidden px-4 py-2 text-slate-600 transition-colors hover:text-slate-900 sm:inline-flex dark:text-slate-400 dark:hover:text-white"
                        >
                            Masuk
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="inline-flex items-center rounded-full bg-[#b91c1c] px-5 py-2.5 text-white shadow-md shadow-red-900/10 transition-all hover:-translate-y-0.5 hover:bg-[#991b1b] hover:shadow-lg dark:shadow-none"
                        >
                            Buat akun
                        </Link>
                    </template>
                </nav>
            </header>

            <!-- Main Content -->
            <main class="flex-1 px-6 pb-16 pt-10 lg:px-10 sm:pt-16">
                <div class="mx-auto max-w-7xl">
                    <!-- Hero Section -->
                    <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-8">
                        <div class="max-w-2xl">
                            <div
                                class="mb-6 inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-50 px-3 py-1 text-xs font-semibold text-red-600 dark:border-red-900/30 dark:bg-red-950/30 dark:text-red-400"
                            >
                                <span class="flex size-2 animate-pulse rounded-full bg-red-600"></span>
                                Phase 1: Siap Dikembangkan
                            </div>
                            <h1
                                class="font-display text-4xl font-extrabold leading-[1.15] tracking-tight text-slate-900 sm:text-5xl lg:text-6xl dark:text-white"
                            >
                                Belajar <span class="bg-gradient-to-r from-[#b91c1c] to-[#7f1d1d] bg-clip-text text-transparent">psikotes Polri</span> dari nol hingga siap simulasi.
                            </h1>
                            <p class="mt-6 text-lg leading-8 text-slate-600 dark:text-slate-400">
                                Prikotes menyiapkan struktur belajar yang presisi, domain soal yang lengkap, dan fondasi data yang rapi agar fase latihan Anda berjalan lancar di atas arsitektur yang stabil dan premium.
                            </p>

                            <div class="mt-10 flex flex-wrap items-center gap-4">
                                <Link
                                    v-if="!$page.props.auth.user && canRegister"
                                    :href="register()"
                                    class="inline-flex items-center justify-center rounded-full bg-slate-900 px-8 py-3.5 text-base font-semibold text-white shadow-xl shadow-slate-900/10 transition-all hover:-translate-y-1 hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
                                >
                                    Mulai Persiapan Sekarang
                                    <svg class="ml-2 size-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            fill-rule="evenodd"
                                            d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </Link>
                                <Link
                                    :href="$page.props.auth.user ? dashboard() : login()"
                                    class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-8 py-3.5 text-base font-semibold text-slate-700 shadow-sm transition-all hover:-translate-y-1 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900/50 dark:text-slate-300 dark:hover:border-slate-700"
                                >
                                    {{ $page.props.auth.user ? 'Buka Dashboard' : 'Masuk ke Akun' }}
                                </Link>
                            </div>
                        </div>

                        <!-- Hero Stats Glass Card -->
                        <div class="relative">
                            <!-- Background glow for card -->
                            <div
                                class="absolute -inset-1 rounded-[2.5rem] bg-gradient-to-br from-red-600/20 to-blue-600/20 opacity-60 blur-xl dark:opacity-40"
                            ></div>

                            <div
                                class="relative rounded-[2rem] border border-white/40 bg-white/60 p-8 shadow-2xl ring-1 ring-slate-900/5 backdrop-blur-2xl dark:border-slate-700/50 dark:bg-slate-900/60"
                            >
                                <div class="grid grid-cols-2 gap-4 sm:gap-6">
                                    <div
                                        class="group rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition-all hover:-translate-y-1 hover:shadow-md dark:border-slate-800 dark:bg-slate-800/80"
                                    >
                                        <div
                                            class="mb-3 flex size-10 items-center justify-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400"
                                        >
                                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
                                                />
                                            </svg>
                                        </div>
                                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Kategori</p>
                                        <p class="mt-1 font-display text-4xl font-bold text-slate-900 dark:text-white">{{ stats.categories }}</p>
                                    </div>
                                    <div
                                        class="group rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition-all hover:-translate-y-1 hover:shadow-md dark:border-slate-800 dark:bg-slate-800/80"
                                    >
                                        <div
                                            class="mb-3 flex size-10 items-center justify-center rounded-full bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400"
                                        >
                                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                                />
                                            </svg>
                                        </div>
                                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Subtes</p>
                                        <p class="mt-1 font-display text-4xl font-bold text-slate-900 dark:text-white">{{ stats.subtests }}</p>
                                    </div>
                                    <div
                                        class="group rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition-all hover:-translate-y-1 hover:shadow-md dark:border-slate-800 dark:bg-slate-800/80"
                                    >
                                        <div
                                            class="mb-3 flex size-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400"
                                        >
                                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"
                                                />
                                            </svg>
                                        </div>
                                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Modul</p>
                                        <p class="mt-1 font-display text-4xl font-bold text-slate-900 dark:text-white">{{ stats.modules }}</p>
                                    </div>
                                    <div
                                        class="group rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition-all hover:-translate-y-1 hover:shadow-md dark:border-slate-800 dark:bg-slate-800/80"
                                    >
                                        <div
                                            class="mb-3 flex size-10 items-center justify-center rounded-full bg-orange-50 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400"
                                        >
                                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                            </svg>
                                        </div>
                                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Soal</p>
                                        <p class="mt-1 font-display text-4xl font-bold text-slate-900 dark:text-white">{{ stats.questions }}</p>
                                    </div>
                                </div>
                                <div class="mt-6 rounded-2xl bg-slate-900 p-5 text-white shadow-inner sm:p-6 dark:bg-slate-950">
                                    <div class="mb-4 flex items-center justify-between">
                                        <p class="text-sm font-semibold uppercase tracking-wider text-slate-300">Domain Utama</p>
                                        <span class="relative flex size-3">
                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                                            <span class="relative inline-flex size-3 rounded-full bg-red-500"></span>
                                        </span>
                                    </div>
                                    <ul class="space-y-3 text-sm text-slate-300">
                                        <li class="flex items-start gap-2">
                                            <svg class="mt-0.5 size-5 shrink-0 text-[#b91c1c]" viewBox="0 0 20 20" fill="currentColor">
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span><strong class="text-white">Tes Kecerdasan:</strong> verbal, numerik, figural.</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <svg class="mt-0.5 size-5 shrink-0 text-[#b91c1c]" viewBox="0 0 20 20" fill="currentColor">
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span><strong class="text-white">Tes Kecermatan:</strong> Pauli, angka/huruf hilang.</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <svg class="mt-0.5 size-5 shrink-0 text-[#b91c1c]" viewBox="0 0 20 20" fill="currentColor">
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span><strong class="text-white">Kepribadian:</strong> PAPI, Wartegg, Baum, dll.</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Features Section -->
                    <div class="mt-20 grid gap-6 sm:gap-8 md:grid-cols-3">
                        <div
                            class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-8 shadow-sm transition-all hover:-translate-y-1.5 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900/40"
                        >
                            <div
                                class="mb-4 inline-flex size-12 items-center justify-center rounded-xl bg-red-50 text-[#b91c1c] dark:bg-red-950/40 dark:text-red-400"
                            >
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                    />
                                </svg>
                            </div>
                            <h2 class="font-display text-xl font-bold text-slate-900 dark:text-white">Fondasi Aman</h2>
                            <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                                Mengandalkan otentikasi mutakhir, verifikasi email, dan role-based access lengkap untuk mengamankan data pengguna dan administrasi sistem.
                            </p>
                        </div>
                        <div
                            class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-8 shadow-sm transition-all hover:-translate-y-1.5 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900/40"
                        >
                            <div
                                class="mb-4 inline-flex size-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                            >
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"
                                    />
                                </svg>
                            </div>
                            <h2 class="font-display text-xl font-bold text-slate-900 dark:text-white">Data-First</h2>
                            <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                                Skema *database* dirancang optimal untuk kategori, subtes, soal, hingga analitik agar fase latihan dapat diukur dan dievaluasi secara akurat.
                            </p>
                        </div>
                        <div
                            class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-8 shadow-sm transition-all hover:-translate-y-1.5 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900/40"
                        >
                            <div
                                class="mb-4 inline-flex size-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400"
                            >
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h2 class="font-display text-xl font-bold text-slate-900 dark:text-white">Siap Berkembang</h2>
                            <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                                Dibangun menggunakan Laravel, Inertia, Vue 3, dan TypeScript. Arsitekturnya mudah diperluas (*scalable*) untuk inovasi fitur-fitur baru ke depan.
                            </p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
