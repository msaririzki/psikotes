<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';

defineProps<{
    title: string;
    value: string | number;
    helper?: string | null;
}>();
</script>

<template>
    <Card class="rounded-[1.6rem] border-[#dfe8ef] bg-white/95 shadow-sm">
        <CardContent class="space-y-2 p-5">
            <p class="text-sm text-slate-500">{{ title }}</p>
            <p class="text-3xl font-semibold text-slate-950">{{ value }}</p>
            <p v-if="helper" class="text-sm text-slate-500">{{ helper }}</p>
        </CardContent>
    </Card>
</template>
