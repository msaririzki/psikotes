<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, BookOpenText, Clock3, Layers3, PlayCircle } from 'lucide-vue-next';
import SimulationHistoryCard from '@/components/simulation/SimulationHistoryCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { SimulationRiwayatItem } from '@/types';

type CompositionItem = {
    subtest_id: number | null;
    subtest_name: string | null;
    category_name: string | null;
    question_count: number;
    available_questions: number;
    learning_modules: Array<{
        title: string;
        slug: string;
    }>;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Simulasi', href: '/simulations' },
            { title: 'Paket', href: '#' },
        ],
    },
});

defineProps<{
    simulationPackage: {
        id: number;
        title: string;
        slug: string;
        description: string | null;
        instruction: string | null;
        duration_minutes: number;
        question_count: number;
        tier_label: string;
        pace_label: string;
        subtests_count: number;
        analytics: {
            attempts_count: number;
            best_score: number | null;
            average_accuracy: number | null;
        };
        in_progress_attempt: {
            id: number;
            answered_questions: number;
            total_questions: number;
        } | null;
    };
    composition: CompositionItem[];
    recentAttempts: SimulationRiwayatItem[];
}>();
</script>

<template>
    <Head :title="simulationPackage.title" />

    <div class="page-shell space-y-6 sm:space-y-8">
        <section class="relative overflow-hidden rounded-[2rem] border border-border/50 bg-card shadow-sm transition-all dark:bg-[#0c111d]">
            <div class="absolute -top-32 -right-32 size-96 rounded-full bg-gradient-to-br from-indigo-500/20 to-violet-500/20 blur-[100px] pointer-events-none"></div>
            <div class="absolute -bottom-20 -left-20 size-64 rounded-full bg-blue-500/10 blur-[80px] pointer-events-none"></div>
            
            <div class="relative p-6 sm:p-8 lg:p-10 z-10 grid gap-6 xl:grid-cols-[1.1fr,0.9fr] xl:items-end">
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full border border-indigo-200/50 bg-indigo-50/50 px-3 py-1.5 text-xs font-semibold tracking-widest text-indigo-700 uppercase dark:border-indigo-500/30 dark:bg-indigo-900/20 dark:text-indigo-300">
                            {{ simulationPackage.tier_label }}
                        </span>
                        <span class="text-sm font-medium text-muted-foreground">
                            {{ simulationPackage.pace_label }}
                        </span>
                    </div>
                    <div>
                        <h1 class="font-display text-4xl font-bold tracking-tight text-foreground">
                            {{ simulationPackage.title }}
                        </h1>
                        <p class="mt-3 max-w-3xl text-sm leading-relaxed text-muted-foreground sm:text-base">
                            {{ simulationPackage.description }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-border/50 bg-white/40 p-4 backdrop-blur-sm sm:p-5 dark:border-white/5 dark:bg-white/5 shadow-sm dark:shadow-none">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Durasi</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">
                            {{ simulationPackage.duration_minutes }} menit
                        </p>
                    </div>
                    <div class="rounded-2xl border border-border/50 bg-white/40 p-4 backdrop-blur-sm sm:p-5 dark:border-white/5 dark:bg-white/5 shadow-sm dark:shadow-none">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Total soal</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">
                            {{ simulationPackage.question_count }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-border/50 bg-white/40 p-4 backdrop-blur-sm sm:p-5 dark:border-white/5 dark:bg-white/5 shadow-sm dark:shadow-none sm:col-span-2">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Isi paket</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">
                            {{ simulationPackage.subtests_count }} bagian
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.05fr,0.95fr]">
            <div class="space-y-4 sm:space-y-5">
                <Card class="rounded-[1.6rem] border-border/50 bg-card shadow-sm sm:rounded-[1.75rem]">
                    <CardHeader class="border-b border-border/40 pb-4">
                        <CardTitle class="flex items-center gap-2">
                            <PlayCircle class="size-5 text-indigo-600 dark:text-indigo-400" />
                            Mulai simulasi
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 pt-5 pb-5">
                        <p class="text-sm leading-relaxed text-muted-foreground">
                            Simulasi ini memakai batas waktu. Setelah mulai, soal dan pilihan jawaban disimpan supaya hasilnya tetap sesuai dengan yang kamu kerjakan.
                        </p>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <Button
                                as-child
                                class="rounded-xl w-full sm:w-auto bg-indigo-600 px-6 h-11 text-white hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition-all dark:bg-indigo-600"
                            >
                                <Link
                                    :href="`/simulations/${simulationPackage.slug}/attempts`"
                                    method="post"
                                    as="button"
                                >
                                    {{
                                        simulationPackage.in_progress_attempt
                                            ? 'Lanjutkan / Buka Sesi'
                                            : 'Mulai Simulasi'
                                    }}
                                    <ArrowRight v-if="!simulationPackage.in_progress_attempt" class="ml-2 size-4" />
                                </Link>
                            </Button>
                            <Button
                                v-if="simulationPackage.in_progress_attempt"
                                as-child
                                variant="outline"
                                class="rounded-xl w-full h-11 sm:w-auto"
                            >
                                <Link
                                    :href="`/simulations/attempts/${simulationPackage.in_progress_attempt.id}`"
                                >
                                    Lanjutkan sesi aktif
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.6rem] border-border/50 bg-card shadow-sm sm:rounded-[1.75rem]">
                    <CardHeader class="border-b border-border/40 pb-4">
                        <CardTitle class="flex items-center gap-2">
                            <Layers3 class="size-5 text-indigo-600 dark:text-indigo-400" />
                            Isi paket
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 pt-5 pb-5">
                        <div
                            v-for="item in composition"
                            :key="`${item.subtest_id}-${item.subtest_name}`"
                            class="rounded-2xl border border-border/40 bg-muted/20 p-5 dark:bg-black/10"
                        >
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <p class="font-semibold text-foreground">
                                        {{ item.subtest_name }}
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ item.category_name }}
                                    </p>
                                </div>
                                <div class="rounded-full bg-card px-3 py-1 text-xs font-semibold text-muted-foreground ring-1 ring-border/50">
                                    {{ item.question_count }} soal
                                </div>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-[1.15rem] bg-card p-4 ring-1 ring-border/50">
                                    <p class="text-[0.65rem] font-bold tracking-widest text-muted-foreground uppercase opacity-80 mb-2">Soal tersedia</p>
                                    <p class="text-xl font-bold text-foreground">
                                        {{ item.available_questions }}
                                    </p>
                                </div>
                                <div class="rounded-[1.15rem] bg-card p-4 ring-1 ring-border/50">
                                    <p class="text-[0.65rem] font-bold tracking-widest text-muted-foreground uppercase opacity-80 mb-2">Materi belajar</p>
                                    <div class="flex flex-wrap gap-2">
                                        <Link
                                            v-for="module in item.learning_modules"
                                            :key="module.slug"
                                            :href="`/learn/modules/${module.slug}`"
                                            class="rounded-full bg-indigo-50/50 px-3 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-200/50 hover:bg-indigo-100 hover:text-indigo-800 dark:bg-indigo-500/10 dark:text-indigo-300 dark:ring-indigo-500/20 dark:hover:bg-indigo-500/20"
                                        >
                                            {{ module.title }}
                                        </Link>
                                        <span
                                            v-if="item.learning_modules.length === 0"
                                            class="text-xs text-muted-foreground italic mt-1"
                                        >
                                            Belum ada materi belajar.
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-4 sm:space-y-5 lg:sticky lg:top-8 h-fit">
                <Card class="rounded-[1.6rem] border border-orange-200/50 bg-orange-50/50 shadow-sm sm:rounded-[1.75rem] dark:border-orange-500/10 dark:bg-orange-500/5">
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2 text-orange-800 dark:text-orange-400">
                            <Clock3 class="size-5" />
                            Aturan pengerjaan
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3 pt-2 pb-5 text-sm leading-relaxed text-orange-900/80 dark:text-orange-300/80">
                        <p>
                            {{ simulationPackage.instruction || 'Ikuti batas waktu, jawab semua soal sebisa mungkin, dan tandai soal yang masih ragu.' }}
                        </p>
                        <p>
                            Jika waktu habis, jawaban akan dikumpulkan otomatis.
                        </p>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.6rem] border-border/50 bg-card shadow-sm sm:rounded-[1.75rem]">
                    <CardHeader class="border-b border-border/40 pb-4">
                        <CardTitle class="flex items-center gap-2">
                            <BookOpenText class="size-5 text-indigo-600 dark:text-indigo-400" />
                            Riwayat paket ini
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3 pt-5 pb-5">
                        <SimulationHistoryCard
                            v-for="attempt in recentAttempts"
                            :key="attempt.id"
                            :attempt="attempt"
                        />
                        <p
                            v-if="recentAttempts.length === 0"
                            class="text-sm italic text-muted-foreground text-center py-2"
                        >
                            Belum ada sesi simulasi untuk paket ini.
                        </p>
                    </CardContent>
                </Card>

                <Button as-child variant="outline" class="w-full h-12 justify-between rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900/50">
                    <Link href="/simulations">
                        Kembali ke seluruh paket
                        <ArrowRight class="size-4" />
                    </Link>
                </Button>
            </div>
        </section>
    </div>
</template>


