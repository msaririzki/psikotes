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
            class="grid items-end gap-3 rounded-[1.6rem] border border-border/60 bg-muted/35 p-5"
            :style="{
                gridTemplateColumns: `repeat(${points.length}, minmax(0, 1fr))`,
            }"
        >
            <div
                v-for="point in points"
                :key="point.id"
                class="flex min-h-48 flex-col justify-end gap-3"
            >
                <div
                    class="flex h-40 items-end justify-center rounded-2xl bg-card p-2 ring-1 ring-border/60"
                >
                    <div
                        class="w-full rounded-xl bg-gradient-to-t from-primary to-[#b91c1c]"
                        :style="{ height: `${Math.max(point.accuracy, 8)}%` }"
                    />
                </div>
                <div class="space-y-1 text-center">
                    <p class="text-xs font-semibold text-foreground">
                        {{ point.label }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{ point.mode_label }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{ point.accuracy }}%
                    </p>
                </div>
            </div>
        </div>

        <p v-else class="text-sm text-muted-foreground">
            Belum ada cukup histori attempt untuk membentuk tren performa.
        </p>
    </div>
</template>
