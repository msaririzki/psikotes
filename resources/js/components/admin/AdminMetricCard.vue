<script setup lang="ts">
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

defineProps<{
    title: string;
    value: string | number;
    description?: string;
}>();
</script>

<template>
    <Card class="rounded-[1.5rem] border-[#e7ddd5] bg-white/90 shadow-sm">
        <CardHeader class="gap-3">
            <CardDescription class="text-slate-500">
                {{ title }}
            </CardDescription>
            <CardTitle class="text-3xl font-semibold text-slate-950">
                {{ value }}
            </CardTitle>
            <p v-if="description" class="text-sm text-slate-500">
                {{ description }}
            </p>
        </CardHeader>
    </Card>
</template>
