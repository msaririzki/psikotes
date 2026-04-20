<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Clock3 } from 'lucide-vue-next';
import LearnProgressBadge from '@/components/learn/LearnProgressBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import type { BelajarProgres } from '@/types/learning';

defineProps<{
    title: string;
    slug: string;
    summary: string | null;
    levelLabel?: string | null;
    estimatedMinutes?: number | null;
    progress: BelajarProgres;
}>();
</script>

<template>
    <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/90 shadow-sm">
        <CardContent class="space-y-4 p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="space-y-2">
                    <p
                        class="font-display text-2xl font-bold tracking-tight text-slate-950"
                    >
                        {{ title }}
                    </p>
                    <div
                        class="flex flex-wrap items-center gap-2 text-sm text-slate-500"
                    >
                        <span v-if="levelLabel">{{ levelLabel }}</span>
                        <span v-if="levelLabel && estimatedMinutes">•</span>
                        <span
                            v-if="estimatedMinutes"
                            class="inline-flex items-center gap-1"
                        >
                            <Clock3 class="size-4" />
                            {{ estimatedMinutes }} menit
                        </span>
                    </div>
                </div>
                <LearnProgressBadge
                    :status="progress.status"
                    :label="progress.label"
                />
            </div>

            <p class="text-sm leading-6 text-slate-600">
                {{
                    summary ||
                    'Modul ini siap dipakai untuk belajar dasar subtes terkait.'
                }}
            </p>

            <div class="flex items-center justify-between">
                <div class="text-sm text-slate-500">
                    <p>Percobaan kuis: {{ progress.quiz_attempts_count }}</p>
                    <p v-if="progress.last_quiz_score !== null">
                        Skor terakhir: {{ progress.last_quiz_score }}
                    </p>
                </div>
                <Button
                    as-child
                    class="rounded-2xl bg-[#0f172a] text-white hover:bg-[#111827]"
                >
                    <Link :href="`/learn/modules/${slug}`">
                        Buka modul
                        <ArrowRight class="ml-2 size-4" />
                    </Link>
                </Button>
            </div>
        </CardContent>
    </Card>
</template>

