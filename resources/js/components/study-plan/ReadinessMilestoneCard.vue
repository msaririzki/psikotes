<script setup lang="ts">
import type { ReadinessMilestone } from '@/types';

defineProps<{
    milestone: ReadinessMilestone;
}>();

const toneMap: Record<ReadinessMilestone['state'], string> = {
    starting: 'bg-slate-100 text-slate-700 ring-slate-200',
    in_progress: 'bg-amber-50 text-amber-800 ring-amber-200',
    completed: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
};
</script>

<template>
    <div class="rounded-[1.6rem] border border-[#dfe8ef] bg-white/95 p-5 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-2">
                <span
                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1"
                    :class="toneMap[milestone.state]"
                >
                    {{ milestone.state.replace('_', ' ') }}
                </span>
                <div>
                    <p class="font-display text-2xl font-bold tracking-tight text-slate-950">
                        {{ milestone.label }}
                    </p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ milestone.description }}
                    </p>
                </div>
            </div>

            <div class="rounded-[1.2rem] bg-[#f8fbff] px-4 py-3 ring-1 ring-[#e6edf3]">
                <p class="text-xs tracking-[0.14em] text-slate-500 uppercase">Progres</p>
                <p class="mt-1 text-2xl font-semibold text-slate-950">
                    {{ milestone.progress }}%
                </p>
            </div>
        </div>

        <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-200">
            <div
                class="h-full rounded-full bg-gradient-to-r from-[#b91c1c] to-[#0f172a]"
                :style="{ width: `${milestone.progress}%` }"
            />
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-3">
            <div
                v-for="signal in milestone.signals"
                :key="signal.label"
                class="rounded-2xl bg-[#fbfdff] p-4 ring-1 ring-[#e6edf3]"
            >
                <p class="text-xs tracking-[0.14em] text-slate-500 uppercase">
                    {{ signal.label }}
                </p>
                <p class="mt-2 text-lg font-semibold text-slate-950">
                    {{ signal.current }} / {{ signal.target }}
                </p>
            </div>
        </div>
    </div>
</template>


