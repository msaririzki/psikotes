<script setup lang="ts">
import { cn } from '@/lib/utils';

defineProps<{
    number: number;
    active?: boolean;
    answered?: boolean;
}>();
</script>

<template>
    <div
        :class="
            cn(
                'relative flex size-10 items-center justify-center rounded-xl text-xs font-bold transition-all duration-200 select-none',
                {
                    // Soal sedang dikerjakan — hijau gelap tegas
                    'bg-gradient-to-br from-emerald-600 to-teal-500 text-white shadow-md shadow-emerald-500/30 scale-110':
                        active,
                    // Soal sudah dijawab — hijau muda subtle
                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300':
                        !active && answered,
                    // Soal belum dijawab — abu netral
                    'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-white/5 dark:text-slate-400 dark:hover:bg-white/10':
                        !active && !answered,
                },
            )
        "
    >
        {{ number }}
        <!-- Titik indikator jawaban sudah diisi -->
        <span
            v-if="answered && !active"
            class="absolute -top-0.5 -right-0.5 size-2 rounded-full bg-emerald-500 dark:bg-emerald-400 border border-white dark:border-slate-900"
        ></span>
    </div>
</template>
