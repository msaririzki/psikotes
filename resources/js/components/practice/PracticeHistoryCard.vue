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
    <div class="rounded-[1.4rem] border border-[#e7edf2] bg-[#fbfdff] p-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="space-y-1">
                <p class="font-semibold text-slate-950">
                    {{ attempt.subtest.name || 'Latihan' }}
                </p>
                <p class="text-sm text-slate-500">
                    {{ attempt.configuration.difficulty_label }} •
                    {{ attempt.configuration.question_count }} soal
                    <span v-if="attempt.configuration.timer_minutes">
                        • {{ attempt.configuration.timer_minutes }} menit
                    </span>
                </p>
            </div>
            <div
                class="rounded-full bg-[#0f172a] px-3 py-1 text-xs font-semibold text-white"
            >
                Skor {{ attempt.score_total ?? 0 }}
            </div>
        </div>

        <div class="mt-4 grid gap-3 text-sm text-slate-600 sm:grid-cols-3">
            <div class="rounded-2xl bg-white p-3 ring-1 ring-[#edf2f7]">
                <p class="text-slate-500">Akurasi</p>
                <p class="mt-1 font-semibold text-slate-950">
                    {{ attempt.accuracy ?? 0 }}%
                </p>
            </div>
            <div class="rounded-2xl bg-white p-3 ring-1 ring-[#edf2f7]">
                <p class="flex items-center gap-2 text-slate-500">
                    <Target class="size-4 text-[#b91c1c]" />
                    Benar / Salah
                </p>
                <p class="mt-1 font-semibold text-slate-950">
                    {{ attempt.correct_answers }} / {{ attempt.wrong_answers }}
                </p>
            </div>
            <div class="rounded-2xl bg-white p-3 ring-1 ring-[#edf2f7]">
                <p class="flex items-center gap-2 text-slate-500">
                    <Clock3 class="size-4 text-[#b91c1c]" />
                    Durasi
                </p>
                <p class="mt-1 font-semibold text-slate-950">
                    {{ durationLabel }}
                </p>
            </div>
        </div>

        <Link
            :href="`/practice/attempts/${attempt.id}/result`"
            class="mt-4 inline-flex text-sm font-medium text-[#0f172a] hover:text-[#b91c1c]"
        >
            Buka hasil latihan
        </Link>
    </div>
</template>

