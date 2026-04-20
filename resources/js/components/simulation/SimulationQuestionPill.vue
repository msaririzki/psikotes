<script setup lang="ts">
import { cn } from '@/lib/utils';

defineProps<{
    number: number;
    active?: boolean;
    answered?: boolean;
    flagged?: boolean;
}>();
</script>

<template>
    <div
        :class="
            cn(
                'relative flex size-11 items-center justify-center rounded-2xl border text-sm font-semibold transition',
                {
                    'border-[#0f172a] bg-[#0f172a] text-white': active,
                    'border-emerald-200 bg-emerald-50 text-emerald-700':
                        !active && answered && !flagged,
                    'border-amber-200 bg-amber-50 text-amber-700':
                        !active && flagged,
                    'border-[#d8e2ea] bg-white text-slate-500':
                        !active && !answered && !flagged,
                },
            )
        "
    >
        {{ number }}
        <span
            v-if="flagged"
            class="absolute top-1 right-1 size-2 rounded-full bg-[#b91c1c]"
        />
    </div>
</template>
