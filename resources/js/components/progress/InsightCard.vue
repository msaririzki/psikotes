<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';

defineProps<{
    title: string;
    value: string | number;
    helper?: string | null;
}>();
</script>

<template>
    <Card class="rounded-[1.6rem] border-border/60 bg-card shadow-sm">
        <CardContent class="space-y-2 p-5">
            <p class="text-sm text-muted-foreground">{{ title }}</p>
            <p class="text-3xl font-semibold text-foreground">{{ value }}</p>
            <p v-if="helper" class="text-sm text-muted-foreground">
                {{ helper }}
            </p>
        </CardContent>
    </Card>
</template>
