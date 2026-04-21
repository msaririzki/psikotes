<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        label: string;
        tone?: 'neutral' | 'success' | 'warning' | 'danger' | 'info';
    }>(),
    {
        tone: 'neutral',
    },
);

const badgeClass = computed(() =>
    cn('rounded-full border px-3 py-1 text-xs font-medium', {
        'border-border bg-muted text-muted-foreground':
            props.tone === 'neutral',
        'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300':
            props.tone === 'success',
        'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300':
            props.tone === 'warning',
        'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300':
            props.tone === 'danger',
        'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-300':
            props.tone === 'info',
    }),
);
</script>

<template>
    <Badge :class="badgeClass">
        {{ label }}
    </Badge>
</template>
