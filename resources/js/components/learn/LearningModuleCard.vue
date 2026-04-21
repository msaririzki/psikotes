<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, CheckCircle2, Clock3, PlayCircle } from 'lucide-vue-next';
import type { BelajarProgres } from '@/types/learning';

defineProps<{
    number?: number;
    title: string;
    slug: string;
    summary: string | null;
    levelLabel?: string | null;
    estimatedMinutes?: number | null;
    progress: BelajarProgres;
}>();
</script>

<template>
    <div
        class="group relative overflow-hidden rounded-[1.75rem] border border-border/50 bg-card shadow-sm transition-all duration-200 hover:shadow-md hover:border-violet-300/50 dark:bg-[#0c111d] dark:hover:border-violet-500/30"
    >
        <!-- Garis kiri status -->
        <div
            class="absolute left-0 top-0 bottom-0 w-1.5 rounded-l-[1.75rem]"
            :class="{
                'bg-emerald-500': progress.status === 'completed',
                'bg-amber-400': progress.status === 'in_progress',
                'bg-slate-200 dark:bg-slate-700': progress.status === 'not_started',
            }"
        ></div>

        <div class="pl-6 pr-5 py-5">
            <div class="flex items-start gap-4">
                <!-- Nomor / Ikon Status -->
                <div class="flex-none mt-0.5">
                    <div
                        v-if="progress.status === 'completed'"
                        class="flex size-9 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-500/15"
                    >
                        <CheckCircle2 class="size-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <div
                        v-else-if="progress.status === 'in_progress'"
                        class="flex size-9 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-500/15"
                    >
                        <PlayCircle class="size-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div
                        v-else
                        class="flex size-9 items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5 text-sm font-bold text-slate-500 dark:text-slate-400"
                    >
                        {{ number ?? '—' }}
                    </div>
                </div>

                <!-- Konten -->
                <div class="flex-1 min-w-0 space-y-2">
                    <!-- Header -->
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="font-semibold text-foreground leading-snug">{{ title }}</p>
                            <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-muted-foreground">
                                <span
                                    v-if="levelLabel"
                                    class="rounded-md bg-slate-100 dark:bg-white/5 px-2 py-0.5 font-medium"
                                >
                                    {{ levelLabel }}
                                </span>
                                <span v-if="estimatedMinutes" class="flex items-center gap-1">
                                    <Clock3 class="size-3" />
                                    {{ estimatedMinutes }} menit
                                </span>
                            </div>
                        </div>
                        <!-- Badge Status -->
                        <span
                            class="flex-none rounded-xl px-3 py-1 text-[0.7rem] font-bold"
                            :class="{
                                'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300': progress.status === 'completed',
                                'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300': progress.status === 'in_progress',
                                'bg-slate-100 text-slate-500 dark:bg-white/5 dark:text-slate-400': progress.status === 'not_started',
                            }"
                        >
                            {{
                                progress.status === 'completed' ? '✅ Selesai' :
                                progress.status === 'in_progress' ? '▶ Sedang Berjalan' :
                                '○ Belum Dimulai'
                            }}
                        </span>
                    </div>

                    <!-- Deskripsi -->
                    <p class="text-sm leading-relaxed text-muted-foreground line-clamp-2">
                        {{ summary || 'Modul ini siap dipakai untuk belajar dasar subtes terkait.' }}
                    </p>

                    <!-- Footer -->
                    <div class="flex items-center justify-between pt-1">
                        <div class="text-xs text-muted-foreground space-y-0.5">
                            <p v-if="progress.quiz_attempts_count > 0">
                                Sudah {{ progress.quiz_attempts_count }} kali mengerjakan kuis
                            </p>
                            <p v-if="progress.last_quiz_score !== null">
                                Nilai kuis terakhir: <strong>{{ progress.last_quiz_score }}</strong>
                            </p>
                        </div>

                        <Link
                            :href="`/learn/modules/${slug}`"
                            class="inline-flex items-center gap-1.5 rounded-xl text-xs font-semibold px-4 py-2 transition-all"
                            :class="{
                                'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm shadow-emerald-500/20': progress.status === 'completed',
                                'bg-amber-500 hover:bg-amber-600 text-white shadow-sm shadow-amber-500/20': progress.status === 'in_progress',
                                'bg-violet-600 hover:bg-violet-700 text-white shadow-sm shadow-violet-500/20': progress.status === 'not_started',
                            }"
                        >
                            {{
                                progress.status === 'completed' ? '📖 Buka Lagi' :
                                progress.status === 'in_progress' ? '▶ Lanjutkan' :
                                '🚀 Mulai Belajar'
                            }}
                            <ArrowRight class="size-3.5" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
