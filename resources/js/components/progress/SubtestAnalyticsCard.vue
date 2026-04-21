<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Clock3, TrendingDown, TrendingUp } from 'lucide-vue-next';
import { computed } from 'vue';
import type { SubtestAnalyticsItem } from '@/types';

const props = defineProps<{
    item: SubtestAnalyticsItem;
}>();

const toneClass = computed(
    () =>
        ({
            strong: 'bg-emerald-50 text-emerald-800 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/30',
            weak: 'bg-rose-50 text-rose-800 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/30',
            developing:
                'bg-amber-50 text-amber-800 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30',
            not_enough_data: 'bg-muted text-muted-foreground ring-border',
        })[props.item.status],
);

const trendIcon = computed(() =>
    props.item.trend.direction === 'declining' ? TrendingDown : TrendingUp,
);
</script>

<template>
    <div class="rounded-[1.6rem] border border-border/60 bg-card p-5 shadow-sm">
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
        >
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="rounded-full bg-muted/60 px-3 py-1 text-xs font-semibold text-foreground ring-1 ring-border/60"
                    >
                        {{ item.category_name }}
                    </span>
                    <span
                        class="rounded-full px-3 py-1 text-xs font-semibold ring-1"
                        :class="toneClass"
                    >
                        {{ item.status }}
                    </span>
                </div>
                <div>
                    <p
                        class="font-display text-2xl font-bold tracking-tight text-foreground"
                    >
                        {{ item.subtest_name }}
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ item.attempts_count }} percobaan ·
                        {{ item.completed_modules }}/{{
                            item.published_modules_count
                        }}
                        modul selesai
                    </p>
                </div>
            </div>

            <div class="rounded-[1.3rem] bg-muted/35 p-4 ring-1 ring-border/60">
                <p class="text-sm text-muted-foreground">Akurasi rata-rata</p>
                <p class="mt-1 text-2xl font-semibold text-foreground">
                    {{ item.average_accuracy ?? 0 }}%
                </p>
            </div>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl bg-muted/35 p-4 ring-1 ring-border/60">
                <p class="text-sm text-muted-foreground">Skor rata-rata</p>
                <p class="mt-1 font-semibold text-foreground">
                    {{ item.average_score ?? 0 }}
                </p>
            </div>
            <div class="rounded-2xl bg-muted/35 p-4 ring-1 ring-border/60">
                <p class="text-sm text-muted-foreground">Durasi rata-rata</p>
                <p
                    class="mt-1 inline-flex items-center gap-2 font-semibold text-foreground"
                >
                    <Clock3 class="size-4 text-[#b91c1c]" />
                    {{ Math.round((item.average_duration_seconds ?? 0) / 60) }}
                    menit
                </p>
            </div>
            <div class="rounded-2xl bg-muted/35 p-4 ring-1 ring-border/60">
                <p class="text-sm text-muted-foreground">Tingkat kosong</p>
                <p class="mt-1 font-semibold text-foreground">
                    {{ item.blank_rate }}%
                </p>
            </div>
            <div class="rounded-2xl bg-muted/35 p-4 ring-1 ring-border/60">
                <p class="text-sm text-muted-foreground">Tren terbaru</p>
                <p
                    class="mt-1 inline-flex items-center gap-2 font-semibold text-foreground"
                >
                    <component :is="trendIcon" class="size-4 text-[#b91c1c]" />
                    {{ item.trend.direction }} ({{ item.trend.delta }})
                </p>
            </div>
        </div>

        <div class="mt-5 flex flex-wrap gap-2">
            <span
                class="rounded-full bg-muted px-3 py-1 text-xs text-muted-foreground ring-1 ring-border"
            >
                Mini kuis {{ item.mini_quiz_attempts }}
            </span>
            <span
                class="rounded-full bg-muted px-3 py-1 text-xs text-muted-foreground ring-1 ring-border"
            >
                Latihan {{ item.practice_attempts }}
            </span>
            <span
                class="rounded-full bg-muted px-3 py-1 text-xs text-muted-foreground ring-1 ring-border"
            >
                Simulasi {{ item.simulation_attempts }}
            </span>
        </div>

        <div class="mt-5 flex flex-wrap gap-3">
            <Link
                :href="`/practice/subtests/${item.subtest_slug}`"
                class="inline-flex items-center gap-2 rounded-full bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
            >
                Buka latihan
                <ArrowRight class="size-4" />
            </Link>
            <Link
                href="/progress"
                class="inline-flex items-center gap-2 rounded-full border border-border px-4 py-2 text-sm font-medium text-foreground hover:bg-muted"
            >
                Lihat insight
            </Link>
        </div>
    </div>
</template>
