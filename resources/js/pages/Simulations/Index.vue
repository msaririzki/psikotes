<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    Clock3,
    Layers3,
    MonitorPlay,
    ShieldCheck,
} from 'lucide-vue-next';
import SimulationHistoryCard from '@/components/simulation/SimulationHistoryCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { SimulationRiwayatItem, SimulationSummary } from '@/types';

type SimulasiPackageCard = {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    duration_minutes: number;
    question_count: number;
    subtests_count: number;
    subtests: Array<{
        name: string | null;
        question_count: number;
    }>;
    in_progress_attempt: {
        id: number;
        answered_questions: number;
        total_questions: number;
    } | null;
    analytics: {
        attempts_count: number;
        best_score: number | null;
    };
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Simulasi', href: '/simulations' },
        ],
    },
});

defineProps<{
    summary: SimulationSummary;
    packages: SimulasiPackageCard[];
    recentAttempts: SimulationRiwayatItem[];
}>();
</script>

<template>
    <Head title="Simulasi" />

    <div class="page-shell space-y-6 sm:space-y-8">
        <!-- Modern Hero Banner with Compact Stats -->
        <section class="relative overflow-hidden rounded-[2rem] border border-border/50 bg-card shadow-sm transition-all dark:bg-[#0c111d]">
            <div class="absolute -top-32 -right-32 size-96 rounded-full bg-gradient-to-br from-indigo-500/20 to-violet-500/20 blur-[100px] pointer-events-none"></div>
            <div class="absolute -bottom-20 -left-20 size-64 rounded-full bg-blue-500/10 blur-[80px] pointer-events-none"></div>
            
            <div class="relative p-6 sm:p-8 lg:p-10 z-10 flex flex-col xl:flex-row gap-8 xl:items-center xl:justify-between">
                <div class="space-y-4 max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-indigo-200/50 bg-indigo-50/50 px-3 py-1.5 text-xs font-semibold tracking-widest text-indigo-700 uppercase dark:border-indigo-500/30 dark:bg-indigo-900/20 dark:text-indigo-300">
                        <MonitorPlay class="size-3.5" />
                        Mode Simulasi / CAT
                    </div>
                    <div>
                        <h1 class="font-display text-3xl font-bold tracking-tight text-foreground sm:text-4xl lg:text-3xl">
                            Paket simulasi penuh untuk menguji performa secara formal.
                        </h1>
                        <p class="mt-3 text-sm leading-relaxed text-muted-foreground sm:text-base">
                            Kerjakan paket dengan timer penuh, navigator soal, flag ragu-ragu, dan review hasil yang tetap stabil walau bank soal berubah.
                        </p>
                    </div>
                </div>

                <!-- Compact Stat Bar di dalam hero sebelah kanan -->
                <div class="grid grid-cols-2 gap-3 sm:gap-4 shrink-0 xl:grid-cols-2 xl:min-w-[400px]">
                    <div class="rounded-2xl border border-border/50 bg-white/40 p-4 backdrop-blur-sm sm:p-5 dark:border-white/5 dark:bg-white/5 shadow-sm dark:shadow-none">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Paket Aktif</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">{{ summary.packages }}</p>
                    </div>
                    <div class="rounded-2xl border border-border/50 bg-white/40 p-4 backdrop-blur-sm sm:p-5 dark:border-white/5 dark:bg-white/5 shadow-sm dark:shadow-none">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Histori</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">{{ summary.attempts }}</p>
                    </div>
                    <div class="rounded-2xl border border-border/50 bg-white/40 p-4 backdrop-blur-sm sm:p-5 dark:border-white/5 dark:bg-white/5 shadow-sm dark:shadow-none">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Skor Terbaik</p>
                        <p class="mt-1 text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ summary.best_score ?? 'N/A' }}</p>
                    </div>
                    <div class="rounded-2xl border border-border/50 bg-white/40 p-4 backdrop-blur-sm sm:p-5 dark:border-white/5 dark:bg-white/5 shadow-sm dark:shadow-none">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Akurasi</p>
                        <p class="mt-1 text-2xl font-bold text-orange-600 dark:text-orange-400">{{ summary.average_accuracy ?? 0 }}%</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.15fr,0.85fr]">
            <div class="space-y-4 sm:space-y-5">
                <div class="section-heading mb-6">
                    <Layers3 class="size-5 text-indigo-600 dark:text-indigo-400" />
                    <h2 class="font-display font-bold tracking-tight text-foreground">
                        Pilih paket simulasi
                    </h2>
                </div>

                <Card
                    v-for="simulationPackage in packages"
                    :key="simulationPackage.id"
                    class="rounded-[1.6rem] border-border/50 bg-card shadow-sm sm:rounded-[1.9rem] overflow-hidden transition-all duration-300 hover:shadow-md hover:border-indigo-500/30"
                >
                    <CardContent class="p-0">
                        <div class="p-5 sm:p-6 space-y-6">
                            <!-- Paket Title & Basic Info -->
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="space-y-2">
                                    <h3 class="font-display text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                                        {{ simulationPackage.title }}
                                    </h3>
                                    <p class="max-w-xl text-sm leading-relaxed text-muted-foreground">
                                        {{ simulationPackage.description }}
                                    </p>
                                </div>
                                <div class="flex flex-wrap gap-2 text-xs font-medium text-muted-foreground">
                                    <span class="rounded-full bg-muted/60 px-3 py-1 ring-1 ring-border/50 dark:bg-muted/30">
                                        {{ simulationPackage.question_count }} soal
                                    </span>
                                    <span class="rounded-full bg-muted/60 px-3 py-1 ring-1 ring-border/50 dark:bg-muted/30">
                                        {{ simulationPackage.duration_minutes }} menit
                                    </span>
                                </div>
                            </div>

                            <!-- Simplified Composition Section -->
                            <div class="grid gap-4 lg:grid-cols-2">
                                <div class="rounded-2xl border border-border/40 bg-muted/20 p-4 dark:bg-black/10">
                                    <p class="text-[0.65rem] font-bold tracking-widest text-muted-foreground uppercase opacity-80 mb-3">
                                        Komposisi Subtes ({{ simulationPackage.subtests_count }} bagian)
                                    </p>
                                    <div class="flex flex-wrap gap-1.5">
                                        <span
                                            v-for="subtest in simulationPackage.subtests"
                                            :key="`${simulationPackage.id}-${subtest.name}`"
                                            class="inline-flex items-center rounded-lg bg-card px-2 py-1 text-xs text-foreground ring-1 ring-border/50 shadow-sm"
                                        >
                                            {{ subtest.name }} <span class="ml-1.5 text-muted-foreground/70">{{ subtest.question_count }}</span>
                                        </span>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-border/40 bg-muted/20 p-4 dark:bg-black/10">
                                    <p class="text-[0.65rem] font-bold tracking-widest text-muted-foreground uppercase opacity-80 mb-3">
                                        Ringkasan Progres
                                    </p>
                                    <div class="flex items-center gap-6">
                                        <div>
                                            <p class="text-xs text-muted-foreground">Attempt</p>
                                            <p class="mt-0.5 text-lg font-bold text-foreground">{{ simulationPackage.analytics.attempts_count }}</p>
                                        </div>
                                        <div class="h-8 w-px bg-border/60"></div>
                                        <div>
                                            <p class="text-xs text-muted-foreground">Skor terbaik</p>
                                            <p class="mt-0.5 text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                                {{ simulationPackage.analytics.best_score ?? '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- In Progress Banner -->
                            <div
                                v-if="simulationPackage.in_progress_attempt"
                                class="flex items-center justify-between rounded-2xl border border-amber-200/50 bg-amber-50/50 p-4 text-sm text-amber-900 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="size-2 rounded-full bg-amber-500 animate-pulse"></div>
                                    <span>Ada sesi yang belum selesai: <strong>{{ simulationPackage.in_progress_attempt.answered_questions }} dari {{ simulationPackage.in_progress_attempt.total_questions }}</strong> soal terjawab.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer actions -->
                        <div class="bg-muted/10 border-t border-border/40 p-5 sm:p-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <Button
                                as-child
                                class="w-full rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition-all sm:w-auto"
                            >
                                <Link :href="`/simulations/${simulationPackage.slug}`">
                                    Lihat Detail Paket
                                    <ArrowRight class="ml-2 size-4" />
                                </Link>
                            </Button>
                            
                            <Button
                                v-if="simulationPackage.in_progress_attempt"
                                as-child
                                variant="outline"
                                class="w-full rounded-xl border-amber-200 bg-white text-amber-900 hover:bg-amber-50 hover:text-amber-950 sm:w-auto dark:border-white/10 dark:bg-transparent dark:text-amber-400 dark:hover:bg-white/5"
                            >
                                <Link :href="`/simulations/attempts/${simulationPackage.in_progress_attempt.id}`">
                                    Lanjutkan Sesi Terakhir
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Panel Sidebar -->
            <div class="space-y-4 sm:space-y-5 lg:sticky lg:top-8 h-fit">
                <!-- Info Snapshot (Dark accent component) -->
                <Card class="relative overflow-hidden rounded-[1.6rem] border border-indigo-500/20 bg-[#0c111d] text-white shadow-xl sm:rounded-[1.75rem]">
                    <div class="absolute -right-10 -top-10 opacity-20 size-32 bg-indigo-500 blur-3xl rounded-full"></div>
                    <CardHeader class="relative z-10 pb-2">
                        <CardTitle class="flex items-center gap-2 text-indigo-100">
                            <ShieldCheck class="size-5 text-indigo-400" />
                            Sistem Snapshot
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="relative z-10 space-y-3 text-sm text-indigo-200/80">
                        <p>Setiap attempt menyimpan salinan soal/opsi statis saat sesi dibuat.</p>
                        <p>Hasil review akan selalu konsisten 100% dari waktu pengerjaan walau admin merubah bank soal setelahnya.</p>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.6rem] border-border/50 bg-card shadow-sm sm:rounded-[1.75rem]">
                    <CardHeader class="pb-3 border-b border-border/40">
                        <CardTitle class="flex items-center gap-2">
                            <MonitorPlay class="size-5 text-indigo-600 dark:text-indigo-400" />
                            Histori simulasi
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3 pt-5 p-5 sm:p-6">
                        <SimulationHistoryCard
                            v-for="attempt in recentAttempts"
                            :key="attempt.id"
                            :attempt="attempt"
                        />
                        <p v-if="recentAttempts.length === 0" class="text-sm italic text-muted-foreground text-center py-4">
                            Belum ada histori simulasi yg terekam.
                        </p>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.6rem] border border-orange-200/50 bg-orange-50/50 shadow-sm sm:rounded-[1.75rem] dark:border-orange-500/10 dark:bg-orange-500/5">
                    <CardContent class="space-y-3 p-5 text-sm text-orange-900/80 dark:text-orange-200/70 sm:p-6">
                        <div class="flex items-start gap-3">
                            <Clock3 class="mt-0.5 size-4 text-orange-600 shrink-0 dark:text-orange-400" />
                            <span>Gunakan menu <strong>Latihan</strong> dulu jika penguasaan materimu belum solid, sebelum merusak rasio statistik simulasi.</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <Clock3 class="mt-0.5 size-4 text-orange-600 shrink-0 dark:text-orange-400" />
                            <span>Flag soal yang ragu, selesaikan sisanya lalu review kembali sebelum menekan submit final.</span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </section>
    </div>
</template>
