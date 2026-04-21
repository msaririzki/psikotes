<script setup lang="ts">
import type { ReadinessMilestone } from '@/types';

defineProps<{
    milestone: ReadinessMilestone;
}>();

const toneMap: Record<ReadinessMilestone['state'], string> = {
    starting: 'bg-muted text-muted-foreground ring-border',
    in_progress: 'bg-amber-50 text-amber-800 ring-amber-200',
    completed: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
};
</script>

<template>
    <div
        class="rounded-[1.35rem] border border-[transparent] border-border/40 bg-card p-4 sm:rounded-[1.6rem] sm:p-5"
    >
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
        >
            <div class="space-y-2">
                <span
                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1"
                    :class="toneMap[milestone.state]"
                >
                    {{ milestone.state.replace('_', ' ') }}
                </span>
                <div>
                    <p
                        class="font-display text-xl font-bold tracking-tight text-foreground sm:text-2xl"
                    >
                        {{ milestone.label }}
                    </p>
                    <p class="mt-2 text-sm leading-6 text-muted-foreground">
                        {{ milestone.description }}
                    </p>
                </div>
            </div>

            <div
                class="rounded-[1.2rem] bg-[transparent] px-3 py-3 ring-1 ring-[transparent] sm:px-4"
            >
                <p
                    class="text-xs tracking-[0.14em] text-muted-foreground uppercase"
                >
                    Progres
                </p>
                <p
                    class="mt-1 text-xl font-semibold text-foreground sm:text-2xl"
                >
                    {{ milestone.progress }}%
                </p>
            </div>
        </div>

        <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-200">
            <div
                class="h-full rounded-full bg-gradient-to-r from-[var(--primary)] to-[var(--primary)]"
                :style="{ width: `${milestone.progress}%` }"
            />
        </div>

        <div class="mt-4 grid gap-2.5 sm:mt-5 sm:gap-3 md:grid-cols-3">
            <div
                v-for="signal in milestone.signals"
                :key="signal.label"
                class="rounded-2xl bg-[transparent] p-3 ring-1 ring-[transparent] sm:p-4"
            >
                <p
                    class="text-xs tracking-[0.14em] text-muted-foreground uppercase"
                >
                    {{ signal.label }}
                </p>
                <p class="mt-2 text-lg font-semibold text-foreground">
                    {{ signal.current }} / {{ signal.target }}
                </p>
            </div>
        </div>
    </div>
</template>
