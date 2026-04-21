<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight, BookOpenText, CheckCircle2, FileQuestion, Sparkles } from 'lucide-vue-next';
import LearningModuleCard from '@/components/learn/LearningModuleCard.vue';
import { dashboard } from '@/routes';
import type { BelajarProgres, BelajarRecentMiniQuiz } from '@/types';

type ProgressSummary = {
    completed: number;
    in_progress: number;
    not_started: number;
    completion_rate: number;
};

type ModuleItem = {
    id: number;
    title: string;
    slug: string;
    summary: string | null;
    level: string | null;
    level_label: string | null;
    estimated_minutes: number | null;
    progress: BelajarProgres;
};

const props = defineProps<{
    category: {
        name: string;
        slug: string;
    };
    subtest: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        instruction: string | null;
        progress: ProgressSummary;
    };
    modules: ModuleItem[];
    recentMiniQuizzes: BelajarRecentMiniQuiz[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Belajar', href: '/learn' },
            { title: 'Subtes', href: '#' },
        ],
    },
});

// Cari modul pertama yang belum selesai untuk CTA utama
const nextModule = props.modules.find(m => m.progress.status !== 'completed') ?? props.modules[0];
</script>

<template>
    <Head :title="subtest.name" />

    <div class="flex flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8 max-w-[1400px] mx-auto w-full">

        <!-- BACK LINK -->
        <Link
            :href="`/learn/categories/${category.slug}`"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors w-fit"
        >
            <ArrowLeft class="size-4" />
            Kembali ke {{ category.name }}
        </Link>

        <!-- HERO SECTION -->
        <section class="relative overflow-hidden rounded-[2rem] border border-border/50 bg-card shadow-sm dark:bg-[#0c111d]">
            <!-- Glow background -->
            <div class="absolute -top-24 -right-24 size-72 rounded-full bg-gradient-to-br from-violet-500/20 to-indigo-500/15 blur-[80px] pointer-events-none"></div>

            <div class="relative z-10 p-6 sm:p-8 lg:p-10">
                <!-- Label Kategori -->
                <p class="text-xs font-bold tracking-widest text-violet-600 uppercase dark:text-violet-400 mb-3">
                    📚 {{ category.name }}
                </p>

                <div class="space-y-6">
                    <!-- Baris Atas: Judul + CTA -->
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="space-y-2">
                            <h1 class="font-display text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                                {{ subtest.name }}
                            </h1>
                            <p class="max-w-2xl text-base leading-relaxed text-muted-foreground">
                                {{ subtest.description }}
                            </p>
                        </div>
                        <!-- CTA Lanjut Belajar -->
                        <div v-if="nextModule" class="flex-shrink-0">
                            <Link
                                :href="`/learn/modules/${nextModule.slug}`"
                                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 hover:from-violet-700 hover:to-indigo-600 text-white text-sm font-semibold px-6 py-3 shadow-md shadow-violet-500/25 transition-all whitespace-nowrap"
                            >
                                <BookOpenText class="size-4" />
                                {{ subtest.progress.completed === 0 ? '🚀 Mulai Belajar' : '▶ Lanjutkan' }}
                            </Link>
                        </div>
                    </div>

                    <!-- Stats Row + Progress Bar -->
                    <div class="space-y-4">
                        <!-- 3 Kotak Stats Sejajar -->
                        <div class="grid grid-cols-3 gap-3">
                            <div class="rounded-2xl border border-border/50 bg-slate-50/80 px-4 py-3 text-center dark:bg-white/5">
                                <p class="text-2xl font-bold text-foreground">{{ modules.length }}</p>
                                <p class="text-xs text-muted-foreground mt-0.5">Jumlah Materi</p>
                            </div>
                            <div class="rounded-2xl border border-emerald-200/60 bg-emerald-50/60 px-4 py-3 text-center dark:bg-emerald-500/10 dark:border-emerald-500/20">
                                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ subtest.progress.completed }}</p>
                                <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-0.5">Sudah Selesai</p>
                            </div>
                            <div class="rounded-2xl border border-amber-200/60 bg-amber-50/60 px-4 py-3 text-center dark:bg-amber-500/10 dark:border-amber-500/20">
                                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ subtest.progress.in_progress }}</p>
                                <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">Lagi Dipelajari</p>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1.5">
                                <span class="font-medium text-foreground">Kemajuan Belajarmu</span>
                                <span class="font-bold text-violet-600 dark:text-violet-400">{{ subtest.progress.completion_rate }}% sudah selesai</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-slate-200 dark:bg-slate-700/50 overflow-hidden">
                                <div
                                    class="h-full rounded-full bg-gradient-to-r from-violet-500 to-indigo-500 transition-all duration-700"
                                    :style="{ width: `${subtest.progress.completion_rate}%` }"
                                ></div>
                            </div>
                            <p class="text-xs text-muted-foreground mt-1">
                                {{ subtest.progress.completed }} dari {{ modules.length }} materi sudah kamu selesaikan
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </section>

        <!-- CARA MENGERJAKAN -->
        <section v-if="subtest.instruction" class="rounded-[2rem] border border-border/50 bg-card shadow-sm overflow-hidden dark:bg-[#0c111d]">
            <div class="flex items-center gap-2.5 border-b border-border/40 bg-slate-50/50 px-6 py-4 dark:bg-slate-900/30">
                <Sparkles class="size-4 text-violet-600 dark:text-violet-400" />
                <h2 class="text-sm font-bold text-foreground">Bagaimana Cara Mengerjakan Soal Ini?</h2>
            </div>
            <div class="p-6 text-sm leading-relaxed text-muted-foreground whitespace-pre-line max-w-3xl">
                {{ subtest.instruction }}
            </div>
        </section>

        <!-- DAFTAR MODUL + SIDEBAR -->
        <section class="grid gap-6 xl:grid-cols-[1fr,300px]">

            <!-- Daftar Modul -->
            <div class="space-y-4">
                <div class="flex items-center gap-2.5">
                    <BookOpenText class="size-5 text-violet-600 dark:text-violet-400" />
                    <h2 class="text-lg font-bold tracking-tight text-foreground">Daftar Materi Belajar</h2>
                </div>

                <LearningModuleCard
                    v-for="(module, index) in modules"
                    :key="module.id"
                    :number="index + 1"
                    :title="module.title"
                    :slug="module.slug"
                    :summary="module.summary"
                    :level-label="module.level_label"
                    :estimated-minutes="module.estimated_minutes"
                    :progress="module.progress"
                />
            </div>

            <!-- Sidebar Kuis -->
            <aside class="space-y-5">
                <!-- Riwayat Mini Kuis -->
                <div class="rounded-[2rem] border border-border/50 bg-card shadow-sm overflow-hidden dark:bg-[#0c111d]">
                    <div class="flex items-center gap-2.5 border-b border-border/40 bg-slate-50/50 px-5 py-4 dark:bg-slate-900/30">
                        <FileQuestion class="size-4 text-violet-600 dark:text-violet-400" />
                        <h3 class="text-sm font-bold text-foreground">Riwayat Kuis</h3>
                    </div>
                    <div class="p-4 space-y-3">
                        <div
                            v-for="attempt in recentMiniQuizzes"
                            :key="attempt.id"
                            class="rounded-xl border border-border/40 bg-slate-50/50 p-3.5 dark:bg-white/5 dark:border-white/5 hover:border-violet-300/50 dark:hover:border-violet-500/20 transition-colors"
                        >
                            <p class="text-xs font-semibold text-foreground line-clamp-1">
                                {{ attempt.learning_module || 'Mini Kuis' }}
                            </p>
                            <p class="mt-0.5 text-[0.7rem] text-muted-foreground">
                                Skor {{ attempt.score_total ?? 0 }} · Akurasi {{ attempt.accuracy ?? 0 }}%
                            </p>
                            <Link
                                :href="`/learn/mini-quizzes/${attempt.id}/result`"
                                class="mt-2 inline-flex items-center gap-1 text-[0.7rem] font-semibold text-violet-600 hover:text-violet-700 dark:text-violet-400"
                            >
                                Lihat Hasil
                                <ArrowRight class="size-3" />
                            </Link>
                        </div>
                        <p v-if="recentMiniQuizzes.length === 0" class="text-xs text-muted-foreground text-center py-6">
                            Belum ada kuis yang pernah dikerjakan.<br />Selesaikan materi lalu uji dirimu! 🎯
                        </p>
                    </div>
                </div>

                <!-- Tips -->
                <div class="relative overflow-hidden rounded-[2rem] border border-violet-200/50 bg-gradient-to-br from-violet-600 to-indigo-600 p-5 text-white shadow-lg shadow-violet-500/20 dark:border-violet-500/30">
                    <div class="absolute -top-6 -right-6 size-24 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
                    <div class="relative space-y-3">
                        <div class="inline-flex rounded-full bg-white/15 px-3 py-1 text-[0.65rem] font-bold tracking-widest uppercase">
                            💡 Saran Belajar
                        </div>
                        <p class="text-sm font-bold">Ikuti urutan dari atas ke bawah ya.</p>
                        <ul class="space-y-2 text-xs text-violet-100">
                            <li class="flex items-start gap-2">
                                <CheckCircle2 class="size-3.5 mt-0.5 flex-none text-violet-200" />
                                <span>Baca dulu materinya sebelum langsung mengerjakan kuis.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <CheckCircle2 class="size-3.5 mt-0.5 flex-none text-violet-200" />
                                <span>Kalau nilai kuisnya masih kurang, ulangi materinya dulu sebelum lanjut.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <CheckCircle2 class="size-3.5 mt-0.5 flex-none text-violet-200" />
                                <span>Setelah semua materi selesai, coba latihan soal di menu Latihan.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </aside>
        </section>
    </div>
</template>
