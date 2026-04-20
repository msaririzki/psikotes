<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, BrainCircuit, ChartColumnBig, Sparkles, TrendingUp } from 'lucide-vue-next';
import InsightCard from '@/components/progress/InsightCard.vue';
import PerformanceTrendChart from '@/components/progress/PerformanceTrendChart.vue';
import RecommendationCard from '@/components/progress/RecommendationCard.vue';
import SubtestAnalyticsCard from '@/components/progress/SubtestAnalyticsCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { ProgressSummary, ProgressTrendPoint, RecommendationItem, SubtestAnalyticsItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Progres', href: '/progress' },
        ],
    },
});

defineProps<{
    summary: ProgressSummary;
    insights: {
        strongest_area: SubtestAnalyticsItem | null;
        weakest_area: SubtestAnalyticsItem | null;
    };
    trend: ProgressTrendPoint[];
    subtest_analytics: SubtestAnalyticsItem[];
    recommendations: RecommendationItem[];
}>();
</script>

<template>
    <Head title="Progres" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <section
            class="overflow-hidden rounded-[2rem] border border-[#dbe6ee] bg-[radial-gradient(circle_at_top_left,_rgba(185,28,28,0.14),_transparent_32%),linear-gradient(135deg,_#f8fbff_0%,_#eef5fb_48%,_#ffffff_100%)] p-6 shadow-sm"
        >
            <div class="grid gap-6 xl:grid-cols-[1.1fr,0.9fr] xl:items-end">
                <div class="space-y-4">
                    <div class="inline-flex items-center rounded-full bg-[#b91c1c] px-4 py-2 text-xs font-semibold tracking-[0.18em] text-white uppercase">
                        Progres Dasbor
                    </div>
                    <div>
                        <h1 class="font-display text-4xl font-bold tracking-tight text-slate-950">
                            Insight yang menyatukan belajar, latihan, dan simulasi.
                        </h1>
                        <p class="mt-3 max-w-3xl text-base leading-7 text-slate-600">
                            Dasbor ini tidak hanya menampilkan angka mentah. Sistem mulai membaca histori nyata untuk menunjukkan area kuat, area lemah, tren performa, dan rekomendasi langkah berikutnya.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <Button as-child class="rounded-2xl bg-[#0f172a] text-white hover:bg-[#111827]">
                        <Link href="/history">
                            Buka pusat riwayat
                            <ArrowRight class="ml-2 size-4" />
                        </Link>
                    </Button>
                    <Button as-child variant="outline" class="rounded-2xl">
                        <Link href="/practice">Kembali ke latihan</Link>
                    </Button>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <InsightCard
                title="Sesi belajar"
                :value="summary.learning_sessions"
                helper="Modul yang pernah dibuka atau diselesaikan"
            />
            <InsightCard
                title="Latihan"
                :value="summary.practice_attempts"
                helper="Attempt latihan yang sudah dikirim"
            />
            <InsightCard
                title="Simulasi"
                :value="summary.simulation_attempts"
                helper="Attempt CAT full yang sudah submitted"
            />
            <InsightCard
                title="Akurasi rata-rata"
                :value="`${summary.average_accuracy ?? 0}%`"
                helper="Rata-rata dari mini quiz, latihan, dan simulasi"
            />
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.05fr,0.95fr]">
            <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <TrendingUp class="size-5 text-[#b91c1c]" />
                        Tren performa terbaru
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <PerformanceTrendChart :points="trend" />
                </CardContent>
            </Card>

            <div class="space-y-5">
                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Sparkles class="size-5 text-[#b91c1c]" />
                            Area terkuat
                        </CardTitle>
                    </CardHeader>
                    <CardContent v-if="insights.strongest_area" class="space-y-3 text-sm text-slate-600">
                        <p class="font-semibold text-slate-950">
                            {{ insights.strongest_area.subtest_name }}
                        </p>
                        <p>
                            Akurasi rata-rata {{ insights.strongest_area.average_accuracy }}%
                            dari {{ insights.strongest_area.attempts_count }} attempt.
                        </p>
                    </CardContent>
                    <CardContent v-else class="text-sm text-slate-500">
                        Belum ada cukup data untuk menentukan area terkuat.
                    </CardContent>
                </Card>

                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <BrainCircuit class="size-5 text-[#b91c1c]" />
                            Area terlemah
                        </CardTitle>
                    </CardHeader>
                    <CardContent v-if="insights.weakest_area" class="space-y-3 text-sm text-slate-600">
                        <p class="font-semibold text-slate-950">
                            {{ insights.weakest_area.subtest_name }}
                        </p>
                        <p>
                            Akurasi rata-rata {{ insights.weakest_area.average_accuracy }}%
                            dengan blank rate {{ insights.weakest_area.blank_rate }}%.
                        </p>
                    </CardContent>
                    <CardContent v-else class="text-sm text-slate-500">
                        Belum ada cukup data untuk menentukan area terlemah.
                    </CardContent>
                </Card>
            </div>
        </section>

        <section class="space-y-5">
            <div class="flex items-center gap-2">
                <ChartColumnBig class="size-5 text-[#b91c1c]" />
                <h2 class="font-display text-2xl font-bold tracking-tight text-slate-950">
                    Mesin rekomendasi dasar
                </h2>
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                <RecommendationCard
                    v-for="item in recommendations"
                    :key="item.headline"
                    :item="item"
                />
            </div>

            <Card
                v-if="recommendations.length === 0"
                class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
            >
                <CardContent class="p-6 text-sm text-slate-500">
                    Belum ada rekomendasi yang cukup kuat. Tambahkan riwayat belajar, latihan, atau simulasi agar sistem bisa memberi arahan yang lebih presisi.
                </CardContent>
            </Card>
        </section>

        <section class="space-y-5">
            <div class="flex items-center gap-2">
                <ChartColumnBig class="size-5 text-[#b91c1c]" />
                <h2 class="font-display text-2xl font-bold tracking-tight text-slate-950">
                    Breakdown per subtes
                </h2>
            </div>

            <SubtestAnalyticsCard
                v-for="item in subtest_analytics"
                :key="item.subtest_id"
                :item="item"
            />

            <Card
                v-if="subtest_analytics.length === 0"
                class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
            >
                <CardContent class="p-6 text-sm text-slate-500">
                    Belum ada analytics subtes yang cukup untuk ditampilkan.
                </CardContent>
            </Card>
        </section>
    </div>
</template>



