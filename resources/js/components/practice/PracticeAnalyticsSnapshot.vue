<script setup lang="ts">
import { Target, TrendingUp } from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { LatihanAnalytics } from '@/types';

defineProps<{
    analytics: LatihanAnalytics;
    compact?: boolean;
}>();
</script>

<template>
    <Card
        class="rounded-[1.5rem] border-border/60 bg-card shadow-sm"
        :class="compact ? 'p-0' : ''"
    >
        <CardHeader :class="compact ? 'pb-2' : ''">
            <CardTitle class="flex items-center gap-2 text-base">
                <TrendingUp class="size-4 text-[#b91c1c]" />
                Progres latihan
            </CardTitle>
        </CardHeader>
        <CardContent class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl bg-muted/40 p-4">
                <p
                    class="text-xs font-medium tracking-[0.14em] text-muted-foreground uppercase"
                >
                    Total sesi
                </p>
                <p class="mt-2 text-2xl font-semibold text-foreground">
                    {{ analytics.attempts_count }}
                </p>
            </div>
            <div class="rounded-2xl bg-muted/40 p-4">
                <p
                    class="text-xs font-medium tracking-[0.14em] text-muted-foreground uppercase"
                >
                    Skor terbaik
                </p>
                <p class="mt-2 text-2xl font-semibold text-foreground">
                    {{ analytics.best_score ?? 'Belum ada' }}
                </p>
            </div>
            <div class="rounded-2xl bg-muted/40 p-4">
                <p
                    class="text-xs font-medium tracking-[0.14em] text-muted-foreground uppercase"
                >
                    Rata-rata akurasi
                </p>
                <p class="mt-2 text-2xl font-semibold text-foreground">
                    {{ analytics.average_accuracy ?? 0 }}%
                </p>
            </div>
            <div class="rounded-2xl bg-muted/40 p-4">
                <p
                    class="text-xs font-medium tracking-[0.14em] text-muted-foreground uppercase"
                >
                    Skor terbaru
                </p>
                <p
                    class="mt-2 flex items-center gap-2 text-2xl font-semibold text-foreground"
                >
                    <Target class="size-4 text-[#b91c1c]" />
                    {{ analytics.latest_score ?? 'Belum ada' }}
                </p>
            </div>
        </CardContent>
    </Card>
</template>
