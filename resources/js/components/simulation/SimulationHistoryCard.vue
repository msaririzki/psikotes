<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Clock3, MonitorPlay } from 'lucide-vue-next';
import { computed } from 'vue';
import type { SimulationRiwayatItem } from '@/types';

const props = defineProps<{
    attempt: SimulationRiwayatItem;
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
                    {{ attempt.package_title || 'Simulasi' }}
                </p>
                <p class="text-sm text-muted-foreground">
                    Nilai {{ attempt.score_total ?? 0 }} - Ketepatan
                    {{ attempt.accuracy ?? 0 }}%
                </p>
            </div>
            <div
                class="rounded-full bg-primary px-3 py-1 text-xs font-semibold text-primary-foreground"
            >
                {{ attempt.correct_answers }} benar
            </div>
        </div>

        <div
            class="mt-4 grid gap-3 text-sm text-muted-foreground sm:grid-cols-3"
        >
            <div class="rounded-2xl bg-card p-3 ring-1 ring-border/60">
                <p class="text-muted-foreground">Salah / Kosong</p>
                <p class="mt-1 font-semibold text-foreground">
                    {{ attempt.wrong_answers }} / {{ attempt.blank_answers }}
                </p>
            </div>
            <div class="rounded-2xl bg-card p-3 ring-1 ring-border/60">
                <p class="flex items-center gap-2 text-muted-foreground">
                    <Clock3 class="size-4 text-[#b91c1c]" />
                    Lama pengerjaan
                </p>
                <p class="mt-1 font-semibold text-foreground">
                    {{ durationLabel }}
                </p>
            </div>
            <div class="rounded-2xl bg-card p-3 ring-1 ring-border/60">
                <p class="flex items-center gap-2 text-muted-foreground">
                    <MonitorPlay class="size-4 text-[#b91c1c]" />
                    Hasil
                </p>
                <Link
                    :href="`/simulations/attempts/${attempt.id}/result`"
                    class="mt-1 inline-flex font-semibold text-foreground hover:text-[#b91c1c]"
                >
                    Buka hasil
                </Link>
            </div>
        </div>
    </div>
</template>
