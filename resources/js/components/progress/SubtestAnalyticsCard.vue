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
            strong: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
            weak: 'bg-rose-50 text-rose-800 ring-rose-200',
            developing: 'bg-amber-50 text-amber-800 ring-amber-200',
            not_enough_data: 'bg-slate-100 text-slate-700 ring-slate-200',
        })[props.item.status],
);

const trendIcon = computed(() =>
    props.item.trend.direction === 'declining' ? TrendingDown : TrendingUp,
);
</script>

<template>
    <div class="rounded-[1.6rem] border border-[#dfe8ef] bg-white/95 p-5 shadow-sm">
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
        >
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="rounded-full bg-[#f8fbff] px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-[#e5edf3]"
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
                        class="font-display text-2xl font-bold tracking-tight text-slate-950"
                    >
                        {{ item.subtest_name }}
                    </p>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ item.attempts_count }} percobaan •
                        {{ item.completed_modules }}/{{
                            item.published_modules_count
                        }}
                        modul selesai
                    </p>
                </div>
            </div>

            <div class="rounded-[1.3rem] bg-[#fbfdff] p-4 ring-1 ring-[#e6edf3]">
                <p class="text-sm text-slate-500">Akurasi rata-rata</p>
                <p class="mt-1 text-2xl font-semibold text-slate-950">
                    {{ item.average_accuracy ?? 0 }}%
                </p>
            </div>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl bg-[#fbfdff] p-4 ring-1 ring-[#e6edf3]">
                <p class="text-sm text-slate-500">Skor rata-rata</p>
                <p class="mt-1 font-semibold text-slate-950">
                    {{ item.average_score ?? 0 }}
                </p>
            </div>
            <div class="rounded-2xl bg-[#fbfdff] p-4 ring-1 ring-[#e6edf3]">
                <p class="text-sm text-slate-500">Durasi rata-rata</p>
                <p
                    class="mt-1 inline-flex items-center gap-2 font-semibold text-slate-950"
                >
                    <Clock3 class="size-4 text-[#b91c1c]" />
                    {{ Math.round((item.average_duration_seconds ?? 0) / 60) }}
                    menit
                </p>
            </div>
            <div class="rounded-2xl bg-[#fbfdff] p-4 ring-1 ring-[#e6edf3]">
                <p class="text-sm text-slate-500">Tingkat kosong</p>
                <p class="mt-1 font-semibold text-slate-950">
                    {{ item.blank_rate }}%
                </p>
            </div>
            <div class="rounded-2xl bg-[#fbfdff] p-4 ring-1 ring-[#e6edf3]">
                <p class="text-sm text-slate-500">Tren terbaru</p>
                <p
                    class="mt-1 inline-flex items-center gap-2 font-semibold text-slate-950"
                >
                    <component :is="trendIcon" class="size-4 text-[#b91c1c]" />
                    {{ item.trend.direction }} ({{ item.trend.delta }})
                </p>
            </div>
        </div>

        <div class="mt-5 flex flex-wrap gap-2">
            <span
                class="rounded-full bg-slate-50 px-3 py-1 text-xs text-slate-600 ring-1 ring-slate-200"
            >
                Mini kuis {{ item.mini_quiz_attempts }}
            </span>
            <span
                class="rounded-full bg-slate-50 px-3 py-1 text-xs text-slate-600 ring-1 ring-slate-200"
            >
                Latihan {{ item.practice_attempts }}
            </span>
            <span
                class="rounded-full bg-slate-50 px-3 py-1 text-xs text-slate-600 ring-1 ring-slate-200"
            >
                Simulasi {{ item.simulation_attempts }}
            </span>
        </div>

        <div class="mt-5 flex flex-wrap gap-3">
            <Link
                :href="`/practice/subtests/${item.subtest_slug}`"
                class="inline-flex items-center gap-2 rounded-full bg-[#0f172a] px-4 py-2 text-sm font-medium text-white hover:bg-[#111827]"
            >
                Buka latihan
                <ArrowRight class="size-4" />
            </Link>
            <Link
                href="/progress"
                class="inline-flex items-center gap-2 rounded-full border border-[#dfe8ef] px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
            >
                Lihat insight
            </Link>
        </div>
    </div>
</template>
