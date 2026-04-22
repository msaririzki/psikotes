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
                'relative flex h-10 min-w-10 items-center justify-center rounded-xl border px-2 text-xs font-semibold transition sm:h-11 sm:min-w-11 sm:rounded-2xl sm:text-sm',
                {
                    'border-primary bg-primary text-primary-foreground': active,
                    'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300':
                        !active && answered && !flagged,
                    'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300':
                        !active && flagged,
                    'border-border bg-card text-muted-foreground':
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
