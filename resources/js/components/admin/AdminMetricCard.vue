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
    <Card class="rounded-[1.5rem] border-border/60 bg-card shadow-sm">
        <CardHeader class="gap-3">
            <CardDescription class="text-muted-foreground">
                {{ title }}
            </CardDescription>
            <CardTitle class="text-3xl font-semibold text-foreground">
                {{ value }}
            </CardTitle>
            <p v-if="description" class="text-sm text-muted-foreground">
                {{ description }}
            </p>
        </CardHeader>
    </Card>
</template>
