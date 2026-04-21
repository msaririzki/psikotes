<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookCheck,
    Brain,
    Clock3,
    Layers3,
    Sparkles,
} from 'lucide-vue-next';
import PracticeAnalyticsSnapshot from '@/components/practice/PracticeAnalyticsSnapshot.vue';
import PracticeHistoryCard from '@/components/practice/PracticeHistoryCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type {
    LatihanAnalytics,
    LatihanRiwayatItem,
    LatihanSummary,
} from '@/types';

type LatihanSubtestCard = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    instruction_excerpt: string | null;
    available_questions: number;
    difficulty_breakdown: {
        easy: number;
        medium: number;
        hard: number;
    };
    learning_modules: Array<{
        title: string;
        slug: string;
    }>;
    analytics: LatihanAnalytics;
    in_progress_attempt: {
        id: number;
        answered_questions: number;
        total_questions: number;
    } | null;
};

type LatihanCategoryCard = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    subtests: LatihanSubtestCard[];
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Latihan', href: '/practice' },
        ],
    },
});

defineProps<{
    summary: LatihanSummary;
    categories: LatihanCategoryCard[];
    recentAttempts: LatihanRiwayatItem[];
}>();
</script>

<template>
    <Head title="Latihan" />

    <div class="page-shell space-y-6 sm:space-y-8">
        <!-- Modern Hero Banner with Compact Stats -->
        <section class="relative overflow-hidden rounded-[2rem] border border-border/50 bg-card shadow-sm transition-all dark:bg-[#0c111d]">
            <div class="absolute -top-32 -right-32 size-96 rounded-full bg-gradient-to-br from-emerald-500/20 to-teal-500/20 blur-[100px] pointer-events-none"></div>
            <div class="absolute -bottom-20 -left-20 size-64 rounded-full bg-emerald-500/10 blur-[80px] pointer-events-none"></div>
            
            <div class="relative p-6 sm:p-8 lg:p-10 z-10 flex flex-col xl:flex-row gap-8 xl:items-center xl:justify-between">
                <div class="space-y-4 max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200/50 bg-emerald-50/50 px-3 py-1.5 text-xs font-semibold tracking-widest text-emerald-700 uppercase dark:border-emerald-500/30 dark:bg-emerald-900/20 dark:text-emerald-300">
                        <Layers3 class="size-3.5" />
                        Latihan Per Subtes
                    </div>
                    <div>
                        <h1 class="font-display text-3xl font-bold tracking-tight text-foreground sm:text-4xl lg:text-3xl">
                            Eksplorasi materi per kategori dengan pembahasan lengkap.
                        </h1>
                        <p class="mt-3 text-sm leading-relaxed text-muted-foreground sm:text-base">
                            Pilih tipe soal yang mau kamu kuasai, atur sendiri jumlah serta kesulitannya. Kamu bebas jeda istirahat kapan saja tanpa takut datamu hilang.
                        </p>
                    </div>
                </div>

                <!-- Compact Stat Bar di dalam hero -->
                <div class="grid grid-cols-2 gap-3 sm:gap-4 shrink-0 xl:grid-cols-2 xl:min-w-[400px]">
                    <div class="rounded-2xl border border-border/50 bg-white/40 p-4 backdrop-blur-sm sm:p-5 dark:border-white/5 dark:bg-white/5 shadow-sm dark:shadow-none">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Latihan Tersedia</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">{{ summary.subtests }}</p>
                    </div>
                    <div class="rounded-2xl border border-border/50 bg-white/40 p-4 backdrop-blur-sm sm:p-5 dark:border-white/5 dark:bg-white/5 shadow-sm dark:shadow-none">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Latihan Selesai</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">{{ summary.practice_attempts }}</p>
                    </div>
                    <div class="rounded-2xl border border-border/50 bg-white/40 p-4 backdrop-blur-sm sm:p-5 dark:border-white/5 dark:bg-white/5 shadow-sm dark:shadow-none">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Skor Tertinggi</p>
                        <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ summary.best_score ?? 'N/A' }}</p>
                    </div>
                    <div class="rounded-2xl border border-border/50 bg-white/40 p-4 backdrop-blur-sm sm:p-5 dark:border-white/5 dark:bg-white/5 shadow-sm dark:shadow-none">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Sedang Dikerjakan</p>
                        <p class="mt-1 text-2xl font-bold text-orange-600 dark:text-orange-400">{{ summary.in_progress_sessions }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.15fr,0.85fr]">
            <div class="space-y-4 sm:space-y-5">
                <div class="section-heading mb-6">
                    <Layers3 class="size-5 text-emerald-600 dark:text-emerald-400" />
                    <h2 class="font-display font-bold tracking-tight text-foreground">
                        Pilih Menu Belajar
                    </h2>
                </div>

                <Card
                    v-for="category in categories"
                    :key="category.id"
                    class="rounded-[1.6rem] border-border/50 bg-card shadow-sm sm:rounded-[1.9rem] overflow-hidden transition-all"
                >
                    <div class="bg-muted/10 border-b border-border/40 p-5 sm:p-6 pb-5">
                        <div class="space-y-2">
                            <h3 class="font-display text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                                {{ category.name }}
                            </h3>
                            <p class="max-w-2xl text-sm leading-relaxed text-muted-foreground">
                                {{ category.description }}
                            </p>
                        </div>
                    </div>
                    
                    <CardContent class="p-5 sm:p-6 space-y-5 relative">
                        <div
                            v-for="subtest in category.subtests"
                            :key="subtest.id"
                            class="rounded-3xl border border-border/40 bg-muted/20 hover:bg-muted/30 hover:border-emerald-500/30 transition-all p-5 sm:p-6 dark:bg-black/10 dark:hover:bg-white/5"
                        >
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-5">
                                <div class="space-y-2">
                                    <h4 class="text-xl font-bold tracking-tight text-foreground">
                                        {{ subtest.name }}
                                    </h4>
                                    <p class="text-sm leading-relaxed text-muted-foreground line-clamp-2">
                                        {{ subtest.description || subtest.instruction_excerpt }}
                                    </p>
                                </div>
                                <div class="shrink-0 flex items-center justify-center rounded-2xl bg-white px-4 py-2 ring-1 ring-border/50 shadow-sm dark:bg-card">
                                    <span class="text-xs font-bold whitespace-nowrap text-foreground">{{ subtest.available_questions }} Soal</span>
                                </div>
                            </div>

                            <div class="flex flex-col xl:flex-row gap-5 mb-5 border-t border-border/40 pt-5">
                                <div class="flex-1 space-y-3">
                                    <p class="text-[0.65rem] font-bold tracking-widest text-muted-foreground uppercase opacity-80">
                                        Tingkat Kesulitan Soal
                                    </p>
                                    <div class="flex flex-wrap gap-2 text-xs font-semibold">
                                        <span class="inline-flex items-center rounded-lg bg-green-50 px-2.5 py-1 text-green-700 ring-1 ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-400/20">
                                            Mudah: {{ subtest.difficulty_breakdown.easy }}
                                        </span>
                                        <span class="inline-flex items-center rounded-lg bg-orange-50 px-2.5 py-1 text-orange-700 ring-1 ring-orange-600/20 dark:bg-orange-500/10 dark:text-orange-400 dark:ring-orange-400/20">
                                            Sedang: {{ subtest.difficulty_breakdown.medium }}
                                        </span>
                                        <span class="inline-flex items-center rounded-lg bg-red-50 px-2.5 py-1 text-red-700 ring-1 ring-red-600/20 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-400/20">
                                            Sulit: {{ subtest.difficulty_breakdown.hard }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex-1 space-y-3 xl:border-l xl:border-border/40 xl:pl-5">
                                    <p class="text-[0.65rem] font-bold tracking-widest text-muted-foreground uppercase opacity-80">
                                        Pencapaian Kamu
                                    </p>
                                    <div class="flex items-center gap-6">
                                        <div>
                                            <p class="text-[0.65rem] text-muted-foreground uppercase tracking-widest">Sesi Selesai</p>
                                            <p class="mt-0.5 text-lg font-bold text-foreground">{{ subtest.analytics.attempts_count }}</p>
                                        </div>
                                        <div class="h-8 w-px bg-border/60"></div>
                                        <div>
                                            <p class="text-[0.65rem] text-muted-foreground uppercase tracking-widest">Skor Rekor</p>
                                            <p class="mt-0.5 text-lg font-bold text-emerald-600 dark:text-emerald-400">
                                                {{ subtest.analytics.best_score ?? '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="subtest.learning_modules.length > 0" class="mb-5">
                                <p class="text-[0.65rem] font-bold tracking-widest text-muted-foreground uppercase opacity-80 mb-2.5">
                                    Materi Tersedia
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <Link
                                        v-for="module in subtest.learning_modules"
                                        :key="module.slug"
                                        :href="`/learn/modules/${module.slug}`"
                                        class="inline-flex items-center rounded-xl bg-card px-3 py-1.5 text-xs font-semibold text-foreground ring-1 ring-border/50 shadow-sm transition-colors hover:text-emerald-600 hover:ring-emerald-500/30 dark:hover:text-emerald-400"
                                    >
                                        <BookCheck class="mr-1.5 size-3.5 opacity-70" />
                                        {{ module.title }}
                                    </Link>
                                </div>
                            </div>
                            
                            <div v-if="subtest.in_progress_attempt" class="mb-5 flex items-center justify-between rounded-2xl border border-amber-200/50 bg-amber-50/50 p-4 text-sm text-amber-900 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200">
                                <div class="flex items-center gap-3">
                                    <div class="size-2 rounded-full bg-amber-500 animate-pulse shrink-0"></div>
                                    <span>Ada latihan yang belum selesai: Kamu telah menjawab <strong>{{ subtest.in_progress_attempt.answered_questions }} dari total {{ subtest.in_progress_attempt.total_questions }}</strong> pertanyaan.</span>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center border-t border-border/40 pt-5">
                                <Button
                                    as-child
                                    class="w-full rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-md shadow-emerald-600/20 transition-all sm:w-auto"
                                >
                                    <Link :href="`/practice/subtests/${subtest.slug}`">
                                        {{ subtest.in_progress_attempt ? 'Bikin Latihan Baru' : 'Mulai Latihan' }}
                                        <ArrowRight class="ml-2 size-4" />
                                    </Link>
                                </Button>
                                <Button
                                    v-if="subtest.in_progress_attempt"
                                    as-child
                                    variant="outline"
                                    class="w-full rounded-xl border-amber-200 bg-white text-amber-900 hover:bg-amber-50 hover:text-amber-950 sm:w-auto dark:border-white/10 dark:bg-transparent dark:text-amber-400 dark:hover:bg-white/5"
                                >
                                    <Link :href="`/practice/attempts/${subtest.in_progress_attempt.id}`">
                                        Lanjutkan Belajarnya
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-4 sm:space-y-5 lg:sticky lg:top-8 h-fit">
                <PracticeAnalyticsSnapshot
                    :analytics="{
                        attempts_count: summary.practice_attempts,
                        best_score: summary.best_score,
                        average_accuracy: summary.average_accuracy,
                        latest_score: recentAttempts[0]?.score_total ?? null,
                        latest_accuracy: recentAttempts[0]?.accuracy ?? null,
                        last_submitted_at: recentAttempts[0]?.submitted_at ?? null,
                    }"
                />

                <Card class="relative overflow-hidden rounded-[1.6rem] border border-emerald-500/20 bg-[#0c111d] text-white shadow-xl sm:rounded-[1.75rem]">
                    <div class="absolute -right-10 -top-10 opacity-20 size-32 bg-emerald-500 blur-3xl rounded-full pointer-events-none"></div>
                    <CardHeader class="relative z-10 pb-2">
                        <CardTitle class="flex items-center gap-2 text-emerald-100">
                            <Brain class="size-5 text-emerald-400" />
                            Petunjuk Belajar
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="relative z-10 space-y-3 text-sm text-emerald-100/80 p-5 pt-3 sm:p-6 pb-6">
                        <div class="flex items-start gap-3 border-b border-emerald-500/20 pb-3">
                            <BookCheck class="mt-0.5 size-4 text-emerald-400 shrink-0" />
                            <span>Pilih materi yang baru kamu pelajari untuk langsung menguji daya ingatmu.</span>
                        </div>
                        <div class="flex items-start gap-3 border-b border-emerald-500/20 py-3">
                            <Clock3 class="mt-0.5 size-4 text-emerald-400 shrink-0" />
                            <span>Tidak perlu buru-buru, mulailah dengan tanpa diukur waktu jika masih tahap belajar rumusnya.</span>
                        </div>
                        <div class="flex items-start gap-3 pt-3">
                            <Sparkles class="mt-0.5 size-4 text-emerald-400 shrink-0" />
                            <span>Jangan lupa membaca ulang hasil & kunci pembahasan agar otakmu makin terlatih!</span>
                        </div>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.6rem] border-border/50 bg-card shadow-sm sm:rounded-[1.75rem]">
                    <CardHeader class="pb-3 border-b border-border/40">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <Sparkles class="size-4 text-emerald-600 dark:text-emerald-400" />    
                            Histori Terbaru
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3 pt-5 p-5 sm:p-6">
                        <PracticeHistoryCard
                            v-for="attempt in recentAttempts"
                            :key="attempt.id"
                            :attempt="attempt"
                        />
                        <p v-if="recentAttempts.length === 0" class="text-sm italic text-muted-foreground text-center py-4">
                            Kamu tak punya riwayat latihan bebas sejauh ini.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </section>
    </div>
</template>
