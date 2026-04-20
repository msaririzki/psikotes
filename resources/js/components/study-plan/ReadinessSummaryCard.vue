<script setup lang="ts">
import type { KesiapanSummary } from '@/types';

defineProps<{
    readiness: KesiapanSummary;
}>();
</script>

<template>
    <div class="rounded-[1.75rem] border border-[#dfe8ef] bg-white/95 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="space-y-3">
                <span class="inline-flex rounded-full bg-[#0f172a] px-4 py-2 text-xs font-semibold tracking-[0.18em] text-white uppercase">
                    {{ readiness.label }}
                </span>
                <div>
                    <p class="font-display text-3xl font-bold tracking-tight text-slate-950">
                        {{ readiness.headline }}
                    </p>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                        {{ readiness.description }}
                    </p>
                </div>
            </div>

            <div class="rounded-[1.35rem] bg-[#f8fbff] p-4 ring-1 ring-[#e6edf3] lg:min-w-[220px]">
                <p class="text-sm text-slate-500">Skor kesiapan</p>
                <p class="mt-1 text-3xl font-semibold text-slate-950">
                    {{ readiness.score }}
                </p>
                <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-200">
                    <div
                        class="h-full rounded-full bg-gradient-to-r from-[#b91c1c] to-[#0f172a]"
                        :style="{ width: `${readiness.score}%` }"
                    />
                </div>
            </div>
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-3 xl:grid-cols-6">
            <div
                v-for="signal in readiness.signals"
                :key="signal.label"
                class="rounded-2xl bg-[#fbfdff] p-4 ring-1 ring-[#e6edf3]"
            >
                <p class="text-xs tracking-[0.14em] text-slate-500 uppercase">
                    {{ signal.label }}
                </p>
                <p class="mt-2 text-xl font-semibold text-slate-950">
                    {{ signal.value }}
                </p>
            </div>
        </div>
    </div>
</template>


