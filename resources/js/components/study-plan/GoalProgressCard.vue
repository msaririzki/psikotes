<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Gauge, Target, TrendingUp } from 'lucide-vue-next';
import type { StudyGoal } from '@/types';

withDefaults(defineProps<{
    goal: StudyGoal;
    compact?: boolean;
    ctaHref?: string;
}>(), {
    compact: false,
    ctaHref: '/study-plan',
});

const statusToneMap = {
    on_track: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
    off_track: 'bg-rose-50 text-rose-800 ring-rose-200',
    completed: 'bg-slate-100 text-slate-800 ring-slate-200',
};
</script>

<template>
    <div class="rounded-[1.6rem] border border-[#dfe8ef] bg-white/95 p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-[#0f172a] px-3 py-1 text-xs font-semibold text-white">
                        {{ goal.period_label }}
                    </span>
                    <span
                        class="rounded-full px-3 py-1 text-xs font-semibold ring-1"
                        :class="statusToneMap[goal.status]"
                    >
                        {{ goal.status_label }}
                    </span>
                    <span class="rounded-full bg-[#f8fbff] px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-[#e6edf3]">
                        {{ goal.window_label }}
                    </span>
                </div>

                <div>
                    <p class="font-display text-2xl font-bold tracking-tight text-slate-950">
                        {{ goal.title }}
                    </p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ goal.description }}
                    </p>
                </div>

                <p class="text-xs text-slate-500">
                    {{ goal.rationale }}
                </p>
            </div>

            <div class="grid min-w-[220px] gap-3 sm:grid-cols-2 lg:grid-cols-1">
                <div class="rounded-[1.25rem] bg-[#f8fbff] px-4 py-3 ring-1 ring-[#e6edf3]">
                    <p class="text-sm text-slate-500">Progres target</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-950">
                        {{ goal.progress }}%
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        Pace periode {{ goal.elapsed_progress }}%
                    </p>
                </div>
                <div class="rounded-[1.25rem] bg-[#f8fbff] px-4 py-3 ring-1 ring-[#e6edf3]">
                    <p class="text-sm text-slate-500">Focus target</p>
                    <p class="mt-1 text-sm font-semibold text-slate-950">
                        {{ goal.focus.subtest_name || goal.focus.target_readiness_label || 'Target aktif' }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ goal.pace_label }}
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-4 h-2 rounded-full bg-slate-100">
            <div
                class="h-2 rounded-full bg-[#b91c1c] transition-all"
                :style="{ width: `${Math.min(goal.progress, 100)}%` }"
            />
        </div>

        <div class="mt-5 grid gap-4 xl:grid-cols-[1.08fr,0.92fr]">
            <div class="space-y-3">
                <div class="rounded-2xl bg-[#fbfdff] p-4 ring-1 ring-[#e6edf3]">
                    <div class="flex items-center gap-2 text-sm font-semibold text-slate-900">
                        <Target class="size-4 text-[#b91c1c]" />
                        Target periode ini
                    </div>
                    <div class="mt-3 space-y-3">
                        <div
                            v-for="target in goal.targets"
                            :key="target.label"
                            class="rounded-2xl border border-[#edf2f7] bg-white px-4 py-3"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm text-slate-600">
                                    {{ target.label }}
                                </p>
                                <p class="text-sm font-semibold text-slate-950">
                                    {{ target.current }} / {{ target.target }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="!compact && goal.milestone"
                    class="rounded-2xl bg-[#fbfdff] p-4 ring-1 ring-[#e6edf3]"
                >
                    <div class="flex items-center gap-2 text-sm font-semibold text-slate-900">
                        <Gauge class="size-4 text-[#b91c1c]" />
                        Milestone kesiapan
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-950">
                        {{ goal.milestone.label }}
                    </p>
                    <p class="mt-1 text-sm text-slate-600">
                        Progres {{ goal.milestone.progress }}% menuju milestone ini.
                    </p>
                </div>
            </div>

            <div class="space-y-3">
                <div class="rounded-2xl bg-[#fbfdff] p-4 ring-1 ring-[#e6edf3]">
                    <div class="flex items-center gap-2 text-sm font-semibold text-slate-900">
                        <TrendingUp class="size-4 text-[#b91c1c]" />
                        Hasil dan keterkaitan
                    </div>
                    <div class="mt-3 space-y-3 text-sm text-slate-600">
                        <div class="rounded-2xl border border-[#edf2f7] bg-white px-4 py-3">
                            <p class="font-medium text-slate-900">
                                {{ goal.alignment.label }}
                            </p>
                            <p class="mt-1">{{ goal.alignment.description }}</p>
                        </div>
                        <div class="rounded-2xl border border-[#edf2f7] bg-white px-4 py-3">
                            <p class="font-medium text-slate-900">
                                Delta kesiapan: {{ goal.outcomes.readiness_score_delta }}
                            </p>
                            <p class="mt-1">
                                {{ goal.outcomes.baseline_readiness_label }} -> {{ goal.outcomes.current_readiness_label }}
                            </p>
                        </div>
                        <div class="rounded-2xl border border-[#edf2f7] bg-white px-4 py-3">
                            <p class="font-medium text-slate-900">
                                Accuracy delta:
                                {{ goal.outcomes.accuracy_delta !== null ? `${goal.outcomes.accuracy_delta}%` : 'Belum cukup data' }}
                            </p>
                            <p class="mt-1">
                                Supporting tasks: {{ goal.alignment.supporting_completed_tasks }} selesai,
                                {{ goal.alignment.supporting_open_tasks }} masih aktif.
                            </p>
                        </div>
                    </div>
                </div>

                <Link
                    v-if="goal.alignment.next_aligned_action"
                    :href="ctaHref"
                    class="inline-flex items-center gap-2 rounded-full bg-[#0f172a] px-4 py-2 text-sm font-medium text-white hover:bg-[#111827]"
                >
                    Buka task yang menopang goal
                    <ArrowRight class="size-4" />
                </Link>
            </div>
        </div>
    </div>
</template>


