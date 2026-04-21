<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, BookOpenText, Brain, CheckCircle2, Circle, Clock, Sparkles } from 'lucide-vue-next';
import LearnProgressBadge from '@/components/learn/LearnProgressBadge.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type {
    BelajarProgres,
    BelajarRecentMiniQuiz,
    BelajarSummary,
} from '@/types';

type FeaturedModule = {
    id: number;
    title: string;
    slug: string;
    progress: BelajarProgres;
};

type BelajarSubtestCard = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    instruction_excerpt: string | null;
    progress: {
        completed: number;
        in_progress: number;
        not_started: number;
        completion_rate: number;
    };
    modules_count: number;
    featured_modules: FeaturedModule[];
};

type BelajarCategoryCard = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    progress: {
        completed: number;
        in_progress: number;
        not_started: number;
        completion_rate: number;
    };
    subtests: BelajarSubtestCard[];
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Belajar', href: '/learn' },
        ],
    },
});

defineProps<{
    summary: BelajarSummary;
    categories: BelajarCategoryCard[];
    recentMiniQuizzes: BelajarRecentMiniQuiz[];
}>();
</script>

<template>
    <Head title="Belajar" />

    <div class="flex flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8 max-w-[1400px] mx-auto w-full">

        <!-- HERO SECTION -->
        <section class="relative overflow-hidden rounded-[2rem] border border-border/50 bg-card shadow-sm dark:bg-[#0c111d]">
            <div class="absolute -top-32 -right-32 size-96 rounded-full bg-gradient-to-br from-violet-500/20 to-indigo-500/20 blur-[100px] pointer-events-none"></div>
            <div class="absolute -bottom-20 -left-20 size-64 rounded-full bg-indigo-500/10 blur-[80px] pointer-events-none"></div>

            <div class="relative p-6 sm:p-8 lg:p-10 z-10 grid gap-8 xl:grid-cols-[1fr,0.9fr] xl:items-center">
                <!-- Kiri: Judul -->
                <div class="space-y-5">
                    <p class="text-xs font-bold tracking-widest text-violet-600 uppercase dark:text-violet-400">
                        📚 Mode Belajar
                    </p>
                    <div>
                        <h1 class="font-display text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                            Tempat Belajar Psikotes dengan Nyaman
                        </h1>
                        <p class="mt-3 max-w-2xl text-base leading-relaxed text-muted-foreground">
                            Pilih materi yang mau dipelajari, baca satu per satu, dan coba kuis kecil untuk mengecek pemahaman kamu. Semua progres tersimpan otomatis.
                        </p>
                    </div>
                    <Button as-child class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 text-white hover:from-violet-700 hover:to-indigo-600 border-none shadow-md shadow-violet-500/20 h-11 px-6">
                        <a href="#jalur-belajar">
                            Mulai Belajar
                            <ArrowRight class="ml-2 size-4" />
                        </a>
                    </Button>
                </div>

                <!-- Kanan: Compact Stats -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-border/50 bg-white/60 p-4 dark:bg-white/5 dark:border-white/10 backdrop-blur-sm">
                        <p class="text-xs font-medium text-muted-foreground">Jumlah Materi</p>
                        <p class="mt-1 text-3xl font-bold text-foreground">{{ summary.modules }}</p>
                        <p class="mt-0.5 text-xs text-muted-foreground">materi tersedia</p>
                    </div>
                    <div class="rounded-2xl border border-border/50 bg-white/60 p-4 dark:bg-white/5 dark:border-white/10 backdrop-blur-sm">
                        <p class="text-xs font-medium text-muted-foreground">Sudah Selesai</p>
                        <p class="mt-1 text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ summary.completed }}</p>
                        <p class="mt-0.5 text-xs text-muted-foreground">materi sudah tuntas</p>
                    </div>
                    <div class="rounded-2xl border border-border/50 bg-white/60 p-4 dark:bg-white/5 dark:border-white/10 backdrop-blur-sm">
                        <p class="text-xs font-medium text-muted-foreground">Lagi Dipelajari</p>
                        <p class="mt-1 text-3xl font-bold text-amber-600 dark:text-amber-400">{{ summary.in_progress }}</p>
                        <p class="mt-0.5 text-xs text-muted-foreground">materi belum selesai</p>
                    </div>
                    <div class="rounded-2xl border border-border/50 bg-white/60 p-4 dark:bg-white/5 dark:border-white/10 backdrop-blur-sm">
                        <p class="text-xs font-medium text-muted-foreground">Kemajuan Kamu</p>
                        <p class="mt-1 text-3xl font-bold text-violet-600 dark:text-violet-400">{{ summary.completion_rate }}%</p>
                        <p class="mt-0.5 text-xs text-muted-foreground">sudah diselesaikan</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- KONTEN UTAMA -->
        <section class="grid gap-6 xl:grid-cols-[1fr,300px]">

            <!-- KIRI: Jalur Belajar -->
            <div id="jalur-belajar" class="space-y-5">
                <div class="flex items-center gap-2.5">
                    <BookOpenText class="size-5 text-violet-600 dark:text-violet-400" />
                    <h2 class="text-lg font-bold tracking-tight text-foreground">Pilih Materi yang Mau Dipelajari</h2>
                </div>

                <!-- Card per Kategori -->
                <div
                    v-for="category in categories"
                    :key="category.id"
                    class="overflow-hidden rounded-[2rem] border border-border/50 bg-card shadow-sm dark:bg-[#0c111d]"
                >
                    <!-- Header Kategori -->
                    <div class="px-6 pt-6 pb-4 sm:px-7 sm:pt-7 border-b border-border/40 bg-slate-50/50 dark:bg-slate-900/30">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="space-y-1.5">
                                <h3 class="text-xl font-bold tracking-tight text-foreground sm:text-2xl">
                                    {{ category.name }}
                                </h3>
                                <p class="text-sm text-muted-foreground leading-relaxed max-w-xl">
                                    {{ category.description }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <LearnProgressBadge
                                    :status="
                                        category.progress.completion_rate === 100
                                            ? 'completed'
                                            : category.progress.in_progress > 0
                                              ? 'in_progress'
                                              : 'not_started'
                                    "
                                    :label="`${category.progress.completion_rate}% selesai`"
                                />
                                <Button as-child class="rounded-xl h-9 text-sm px-5 font-semibold bg-violet-600 hover:bg-violet-700 text-white border-none shadow-sm shadow-violet-500/20">
                                    <Link :href="`/learn/categories/${category.slug}`">
                                        Lihat Semua Materi
                                        <ArrowRight class="ml-1.5 size-4" />
                                    </Link>
                                </Button>
                            </div>
                        </div>
                        <!-- Progress Bar -->
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-xs text-muted-foreground mb-1.5">
                                <span>Total kemajuan belajar</span>
                                <span class="font-semibold">{{ category.progress.completion_rate }}%</span>
                            </div>
                            <div class="h-1.5 w-full rounded-full bg-slate-200 dark:bg-slate-700/50 overflow-hidden">
                                <div
                                    class="h-full rounded-full bg-gradient-to-r from-violet-500 to-indigo-500 transition-all duration-700"
                                    :style="{ width: `${category.progress.completion_rate}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- Grid Subtes -->
                    <div class="p-5 sm:p-6 grid gap-4 lg:grid-cols-2">
                        <div
                            v-for="subtest in category.subtests"
                            :key="subtest.id"
                            class="group relative overflow-hidden rounded-[1.5rem] border border-border/50 bg-background p-4 sm:p-5 transition-all duration-200 hover:border-violet-300/60 hover:shadow-md hover:shadow-violet-500/5 dark:hover:border-violet-500/30"
                        >
                            <!-- Header Subtes -->
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="space-y-1">
                                    <p class="font-semibold text-foreground leading-snug">{{ subtest.name }}</p>
                                    <p class="text-xs text-muted-foreground leading-relaxed line-clamp-2">
                                        {{ subtest.description || subtest.instruction_excerpt }}
                                    </p>
                                </div>
                                <LearnProgressBadge
                                    :status="
                                        subtest.progress.completion_rate === 100
                                            ? 'completed'
                                            : subtest.progress.in_progress > 0
                                              ? 'in_progress'
                                              : 'not_started'
                                    "
                                    :label="`${subtest.progress.completion_rate}%`"
                                />
                            </div>

                            <!-- Progress Bar Subtes -->
                            <div class="mb-3">
                                <div class="h-1 w-full rounded-full bg-slate-200 dark:bg-slate-700/50 overflow-hidden">
                                    <div
                                        class="h-full rounded-full transition-all duration-700"
                                        :class="subtest.progress.completion_rate === 100 ? 'bg-emerald-500' : 'bg-gradient-to-r from-violet-500 to-indigo-400'"
                                        :style="{ width: `${subtest.progress.completion_rate}%` }"
                                    ></div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="flex items-center justify-between pt-1">
                                <span class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                    <BookOpenText class="size-3.5" />
                                    {{ subtest.modules_count }} modul
                                </span>
                                <Link
                                    :href="`/learn/categories/${category.slug}/subtests/${subtest.slug}`"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold px-4 py-2 transition-colors shadow-sm shadow-violet-500/20"
                                >
                                    📖 Buka & Mulai Belajar
                                    <ArrowRight class="size-3.5" />
                                </Link>
                            </div>

                            <!-- Modul Terkini -->
                            <div v-if="subtest.featured_modules.length > 0" class="mt-4 space-y-2 border-t border-border/40 pt-4">
                                <p class="text-[0.65rem] font-bold uppercase tracking-widest text-muted-foreground">Materi yang sedang dipelajari</p>
                                <div
                                    v-for="module in subtest.featured_modules"
                                    :key="module.id"
                                    class="flex items-center justify-between gap-3 rounded-xl border border-border/40 bg-slate-50/50 px-3 py-2.5 dark:bg-white/5 dark:border-white/5"
                                >
                                    <div class="flex items-center gap-2 min-w-0">
                                        <component
                                            :is="module.progress.status === 'completed' ? CheckCircle2 : module.progress.status === 'in_progress' ? Clock : Circle"
                                            class="size-3.5 flex-none"
                                            :class="module.progress.status === 'completed' ? 'text-emerald-500' : module.progress.status === 'in_progress' ? 'text-amber-500' : 'text-slate-400'"
                                        />
                                        <p class="text-xs font-medium text-foreground truncate">{{ module.title }}</p>
                                    </div>
                                    <LearnProgressBadge
                                        :status="module.progress.status"
                                        :label="module.progress.label"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KANAN: Sidebar -->
            <aside class="space-y-5">

                <!-- Aktivitas Terbaru -->
                <div class="rounded-[2rem] border border-border/50 bg-card shadow-sm overflow-hidden dark:bg-[#0c111d]">
                    <div class="flex items-center gap-2.5 border-b border-border/40 bg-slate-50/50 px-5 py-4 dark:bg-slate-900/30">
                        <Brain class="size-4 text-violet-600 dark:text-violet-400" />
                        <h3 class="text-sm font-bold text-foreground">Aktivitas Terakhir</h3>
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
                            Belum ada kuis yang pernah dikerjakan.<br />Yuk, mulai belajar dulu! 🎯
                        </p>
                    </div>
                </div>

                <!-- Tips Belajar -->
                <div class="relative overflow-hidden rounded-[2rem] border border-violet-200/50 bg-gradient-to-br from-violet-600 to-indigo-600 p-5 text-white shadow-lg shadow-violet-500/20 dark:border-violet-500/30">
                    <div class="absolute -top-8 -right-8 size-32 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
                    <div class="relative space-y-4">
                        <div class="inline-flex rounded-full bg-white/15 px-3 py-1 text-[0.65rem] font-bold tracking-widest uppercase">
                            💡 Tips Belajar
                        </div>
                        <p class="text-base font-bold leading-snug">
                            Mulai dari materi paling dasar.
                        </p>
                        <div class="space-y-2.5 text-xs text-violet-100">
                            <div class="flex items-start gap-2.5">
                                <Sparkles class="mt-0.5 size-3.5 flex-none text-violet-200" />
                                <span>Baca dulu materinya, pahami polanya, baru kerjakan kuisnya.</span>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <Sparkles class="mt-0.5 size-3.5 flex-none text-violet-200" />
                                <span>Jangan langsung ke simulasi kalau belum paham materinya.</span>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <Sparkles class="mt-0.5 size-3.5 flex-none text-violet-200" />
                                <span>Lihat hasil kuis untuk tahu bagian mana yang perlu diulang.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

        </section>
    </div>
</template>
