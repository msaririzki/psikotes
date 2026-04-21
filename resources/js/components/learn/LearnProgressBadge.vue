<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

const props = defineProps<{
    status: 'not_started' | 'in_progress' | 'completed';
    label: string;
}>();

const badgeClass = computed(() =>
    cn('rounded-full border px-3 py-1 text-xs font-semibold', {
        'border-border bg-muted text-muted-foreground':
            props.status === 'not_started',
        'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300':
            props.status === 'in_progress',
        'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300':
            props.status === 'completed',
    }),
);
</script>

<template>
    <Badge :class="badgeClass">
        {{ label }}
    </Badge>
</template>
