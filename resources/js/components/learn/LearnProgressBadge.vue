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
        'border-slate-200 bg-slate-100 text-slate-700':
            props.status === 'not_started',
        'border-amber-200 bg-amber-100 text-amber-700':
            props.status === 'in_progress',
        'border-emerald-200 bg-emerald-100 text-emerald-700':
            props.status === 'completed',
    }),
);
</script>

<template>
    <Badge :class="badgeClass">
        {{ label }}
    </Badge>
</template>
