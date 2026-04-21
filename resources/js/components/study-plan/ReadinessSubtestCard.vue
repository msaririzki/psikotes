<script setup lang="ts">
import type { ReadinessSubtestProgress } from '@/types';

defineProps<{
    item: ReadinessSubtestProgress;
}>();

const toneMap: Record<ReadinessSubtestProgress['state'], string> = {
    foundation: 'bg-rose-50 text-rose-800 ring-rose-200',
    practice_ready: 'bg-sky-50 text-sky-800 ring-sky-200',
    simulation_ready: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
};
</script>

<template>
    <div
        class="rounded-[1.25rem] border border-[transparent] border-border/40 bg-card p-4 sm:rounded-[1.45rem] sm:p-5"
    >
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
        >
            <div class="space-y-2">
                <span
                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1"
                    :class="toneMap[item.state]"
                >
                    {{ item.label }}
                </span>
                <div>
                    <p class="font-semibold text-foreground">
                        {{ item.subtest_name }}
                    </p>
                    <p class="mt-2 text-sm leading-6 text-muted-foreground">
                        {{ item.reason }}
                    </p>
                </div>
            </div>

            <div class="text-right">
                <p class="text-sm text-muted-foreground">Kesiapan</p>
                <p
                    class="mt-1 text-xl font-semibold text-foreground sm:text-2xl"
                >
                    {{ item.progress }}%
                </p>
            </div>
        </div>

        <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-200">
            <div
                class="h-full rounded-full bg-gradient-to-r from-[var(--primary)] to-[var(--primary)]"
                :style="{ width: `${item.progress}%` }"
            />
        </div>
    </div>
</template>
