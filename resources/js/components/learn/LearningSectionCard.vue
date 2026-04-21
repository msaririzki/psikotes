<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <Card class="rounded-[1.75rem] border-border/60 bg-card shadow-sm">
        <CardHeader class="space-y-2">
            <CardTitle class="font-display text-2xl tracking-tight">
                {{ title }}
            </CardTitle>
            <p
                v-if="description"
                class="text-sm leading-6 text-muted-foreground"
            >
                {{ description }}
            </p>
        </CardHeader>
        <CardContent>
            <slot />
        </CardContent>
    </Card>
</template>
