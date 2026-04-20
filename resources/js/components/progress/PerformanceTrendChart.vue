<script setup lang="ts">
import type { ProgressTrendPoint } from '@/types';

defineProps<{
    points: ProgressTrendPoint[];
}>();
</script>

<template>
    <div class="space-y-4">
        <div
            v-if="points.length > 0"
            class="grid items-end gap-3 rounded-[1.6rem] border border-[#e4ebf2] bg-[#fbfdff] p-5"
            :style="{ gridTemplateColumns: `repeat(${points.length}, minmax(0, 1fr))` }"
        >
            <div
                v-for="point in points"
                :key="point.id"
                class="flex min-h-48 flex-col justify-end gap-3"
            >
                <div class="flex h-40 items-end justify-center rounded-2xl bg-white p-2 ring-1 ring-[#e6edf3]">
                    <div
                        class="w-full rounded-xl bg-gradient-to-t from-[#0f172a] to-[#b91c1c]"
                        :style="{ height: `${Math.max(point.accuracy, 8)}%` }"
                    />
                </div>
                <div class="space-y-1 text-center">
                    <p class="text-xs font-semibold text-slate-800">
                        {{ point.label }}
                    </p>
                    <p class="text-xs text-slate-500">
                        {{ point.mode_label }}
                    </p>
                    <p class="text-xs text-slate-500">
                        {{ point.accuracy }}%
                    </p>
                </div>
            </div>
        </div>

        <p v-else class="text-sm text-slate-500">
            Belum ada cukup histori attempt untuk membentuk tren performa.
        </p>
    </div>
</template>


