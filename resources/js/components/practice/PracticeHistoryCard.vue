<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Clock3, Target } from 'lucide-vue-next';
import { computed } from 'vue';
import type { LatihanRiwayatItem } from '@/types';

const props = defineProps<{
    attempt: LatihanRiwayatItem;
}>();

const durationLabel = computed(() => {
    const totalSeconds = props.attempt.duration_seconds ?? 0;
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    return `${minutes}m ${String(seconds).padStart(2, '0')}s`;
});
</script>

<template>
    <div class="rounded-[1.4rem] border border-border/60 bg-muted/35 p-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="space-y-1">
                <p class="font-semibold text-foreground">
                    {{ attempt.subtest.name || 'Latihan' }}
                </p>
                <p class="text-sm text-muted-foreground">
                    {{ attempt.configuration.difficulty_label }} •
                    {{ attempt.configuration.question_count }} soal
                    <span v-if="attempt.configuration.timer_minutes">
                        • {{ attempt.configuration.timer_minutes }} menit
                    </span>
                </p>
            </div>
            <div
                class="rounded-full bg-primary px-3 py-1 text-xs font-semibold text-primary-foreground"
            >
                Skor {{ attempt.score_total ?? 0 }}
            </div>
        </div>

        <div
            class="mt-4 grid gap-3 text-sm text-muted-foreground sm:grid-cols-3"
        >
            <div class="rounded-2xl bg-card p-3 ring-1 ring-border/60">
                <p class="text-muted-foreground">Akurasi</p>
                <p class="mt-1 font-semibold text-foreground">
                    {{ attempt.accuracy ?? 0 }}%
                </p>
            </div>
            <div class="rounded-2xl bg-card p-3 ring-1 ring-border/60">
                <p class="flex items-center gap-2 text-muted-foreground">
                    <Target class="size-4 text-[#b91c1c]" />
                    Benar / Salah
                </p>
                <p class="mt-1 font-semibold text-foreground">
                    {{ attempt.correct_answers }} / {{ attempt.wrong_answers }}
                </p>
            </div>
            <div class="rounded-2xl bg-card p-3 ring-1 ring-border/60">
                <p class="flex items-center gap-2 text-muted-foreground">
                    <Clock3 class="size-4 text-[#b91c1c]" />
                    Durasi
                </p>
                <p class="mt-1 font-semibold text-foreground">
                    {{ durationLabel }}
                </p>
            </div>
        </div>

        <Link
            :href="`/practice/attempts/${attempt.id}/result`"
            class="mt-4 inline-flex text-sm font-medium text-foreground hover:text-[#b91c1c]"
        >
            Buka hasil latihan
        </Link>
    </div>
</template>
