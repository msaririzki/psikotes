<script setup lang="ts">
import type { ReadinessSummary } from '@/types';

defineProps<{
    readiness: ReadinessSummary;
}>();
</script>

<template>
    <div
        class="rounded-[1.4rem] border border-[transparent] border-border/40 bg-card p-4 sm:rounded-[1.75rem] sm:p-6"
    >
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
        >
            <div class="space-y-3">
                <span
                    class="inline-flex rounded-full bg-[var(--primary)] px-3 py-1.5 text-[11px] font-semibold tracking-[0.18em] text-white uppercase sm:px-4 sm:py-2 sm:text-xs"
                >
                    {{ readiness.label }}
                </span>
                <div>
                    <p
                        class="font-display text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
                    >
                        {{ readiness.headline }}
                    </p>
                    <p
                        class="mt-2 max-w-2xl text-sm leading-6 text-muted-foreground"
                    >
                        {{ readiness.description }}
                    </p>
                </div>
            </div>

            <div
                class="rounded-[1.2rem] bg-[transparent] p-3 ring-1 ring-[transparent] sm:p-4 lg:min-w-[220px]"
            >
                <p class="text-sm text-muted-foreground">Skor kesiapan</p>
                <p
                    class="mt-1 text-2xl font-semibold text-foreground sm:text-3xl"
                >
                    {{ readiness.score }}
                </p>
                <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-200">
                    <div
                        class="h-full rounded-full bg-gradient-to-r from-[var(--primary)] to-[var(--primary)]"
                        :style="{ width: `${readiness.score}%` }"
                    />
                </div>
            </div>
        </div>

        <div
            class="mt-4 grid gap-2.5 sm:mt-5 sm:gap-3 md:grid-cols-3 xl:grid-cols-6"
        >
            <div
                v-for="signal in readiness.signals"
                :key="signal.label"
                class="rounded-2xl bg-[transparent] p-3 ring-1 ring-[transparent] sm:p-4"
            >
                <p
                    class="text-xs tracking-[0.14em] text-muted-foreground uppercase"
                >
                    {{ signal.label }}
                </p>
                <p class="mt-2 text-xl font-semibold text-foreground">
                    {{ signal.value }}
                </p>
            </div>
        </div>
    </div>
</template>
