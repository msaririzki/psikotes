<script setup lang="ts">
import type { KesiapanSubtestProgress } from '@/types';

defineProps<{
    item: KesiapanSubtestProgress;
}>();

const toneMap = {
    foundation: 'bg-rose-50 text-rose-800 ring-rose-200',
    practice_ready: 'bg-sky-50 text-sky-800 ring-sky-200',
    simulation_ready: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
};
</script>

<template>
    <div class="rounded-[1.45rem] border border-[#dfe8ef] bg-white/95 p-5 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-2">
                <span
                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1"
                    :class="toneMap[item.state]"
                >
                    {{ item.label }}
                </span>
                <div>
                    <p class="font-semibold text-slate-950">
                        {{ item.subtest_name }}
                    </p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ item.reason }}
                    </p>
                </div>
            </div>

            <div class="text-right">
                <p class="text-sm text-slate-500">Kesiapan</p>
                <p class="mt-1 text-2xl font-semibold text-slate-950">
                    {{ item.progress }}%
                </p>
            </div>
        </div>

        <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-200">
            <div
                class="h-full rounded-full bg-gradient-to-r from-[#b91c1c] to-[#0f172a]"
                :style="{ width: `${item.progress}%` }"
            />
        </div>
    </div>
</template>



