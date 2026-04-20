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
        'border-slate-200 bg-slate-100 text-slate-700':
            props.tone === 'neutral',
        'border-emerald-200 bg-emerald-100 text-emerald-700':
            props.tone === 'success',
        'border-amber-200 bg-amber-100 text-amber-700':
            props.tone === 'warning',
        'border-rose-200 bg-rose-100 text-rose-700': props.tone === 'danger',
        'border-sky-200 bg-sky-100 text-sky-700': props.tone === 'info',
    }),
);
</script>

<template>
    <Badge :class="badgeClass">
        {{ label }}
    </Badge>
</template>
