<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Gauge, Target, TrendingUp } from 'lucide-vue-next';
import type { StudyGoal } from '@/types';

withDefaults(
    defineProps<{
        goal: StudyGoal;
        compact?: boolean;
        ctaHref?: string;
    }>(),
    {
        compact: false,
        ctaHref: '/study-plan',
    },
);

const statusToneMap = {
    on_track: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
    off_track: 'bg-rose-50 text-rose-800 ring-rose-200',
    completed: 'bg-muted text-foreground ring-border',
};
</script>

<template>
    <div
        class="rounded-[1.35rem] border border-[transparent] border-border/40 bg-card p-4 sm:rounded-[1.6rem] sm:p-5"
    >
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
        >
            <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="rounded-full bg-[var(--primary)] px-3 py-1 text-[11px] font-semibold text-white sm:text-xs"
                    >
                        {{ goal.period_label }}
                    </span>
                    <span
                        class="rounded-full px-3 py-1 text-xs font-semibold ring-1"
                        :class="statusToneMap[goal.status]"
                    >
                        {{ goal.status_label }}
                    </span>
                    <span
                        class="rounded-full bg-[transparent] px-3 py-1 text-xs font-semibold text-muted-foreground ring-1 ring-[transparent]"
                    >
                        {{ goal.window_label }}
                    </span>
                </div>

                <div>
                    <p
                        class="font-display text-xl font-bold tracking-tight text-foreground sm:text-2xl"
                    >
                        {{ goal.title }}
                    </p>
                    <p class="mt-2 text-sm leading-6 text-muted-foreground">
                        {{ goal.description }}
                    </p>
                </div>

                <p class="text-xs text-muted-foreground">
                    {{ goal.rationale }}
                </p>
            </div>

            <div
                class="grid gap-3 sm:min-w-[220px] sm:grid-cols-2 lg:grid-cols-1"
            >
                <div
                    class="rounded-[1.25rem] bg-[transparent] px-3 py-3 ring-1 ring-[transparent] sm:px-4"
                >
                    <p class="text-sm text-muted-foreground">Progres target</p>
                    <p
                        class="mt-1 text-xl font-semibold text-foreground sm:text-2xl"
                    >
                        {{ goal.progress }}%
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Pace periode {{ goal.elapsed_progress }}%
                    </p>
                </div>
                <div
                    class="rounded-[1.25rem] bg-[transparent] px-3 py-3 ring-1 ring-[transparent] sm:px-4"
                >
                    <p class="text-sm text-muted-foreground">Focus target</p>
                    <p class="mt-1 text-sm font-semibold text-foreground">
                        {{
                            goal.focus.subtest_name ||
                            goal.focus.target_readiness_label ||
                            'Target aktif'
                        }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        {{ goal.pace_label }}
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-4 h-2 rounded-full bg-muted">
            <div
                class="h-2 rounded-full bg-[var(--primary)] transition-all"
                :style="{ width: `${Math.min(goal.progress, 100)}%` }"
            />
        </div>

        <div
            class="mt-4 grid gap-3 sm:mt-5 sm:gap-4 xl:grid-cols-[1.08fr,0.92fr]"
        >
            <div class="space-y-3">
                <div
                    class="rounded-2xl bg-[transparent] p-3 ring-1 ring-[transparent] sm:p-4"
                >
                    <div
                        class="flex items-center gap-2 text-sm font-semibold text-foreground"
                    >
                        <Target class="size-4 text-[var(--primary)]" />
                        Target periode ini
                    </div>
                    <div class="mt-3 space-y-3">
                        <div
                            v-for="target in goal.targets"
                            :key="target.label"
                            class="rounded-2xl border border-[transparent] bg-card px-3 py-3 sm:px-4"
                        >
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <p class="text-sm text-muted-foreground">
                                    {{ target.label }}
                                </p>
                                <p
                                    class="text-sm font-semibold text-foreground"
                                >
                                    {{ target.current }} / {{ target.target }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="!compact && goal.milestone"
                    class="rounded-2xl bg-[transparent] p-3 ring-1 ring-[transparent] sm:p-4"
                >
                    <div
                        class="flex items-center gap-2 text-sm font-semibold text-foreground"
                    >
                        <Gauge class="size-4 text-[var(--primary)]" />
                        Milestone kesiapan
                    </div>
                    <p class="mt-3 text-sm font-semibold text-foreground">
                        {{ goal.milestone.label }}
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Progres {{ goal.milestone.progress }}% menuju milestone
                        ini.
                    </p>
                </div>
            </div>

            <div class="space-y-3">
                <div
                    class="rounded-2xl bg-[transparent] p-3 ring-1 ring-[transparent] sm:p-4"
                >
                    <div
                        class="flex items-center gap-2 text-sm font-semibold text-foreground"
                    >
                        <TrendingUp class="size-4 text-[var(--primary)]" />
                        Hasil dan keterkaitan
                    </div>
                    <div class="mt-3 space-y-3 text-sm text-muted-foreground">
                        <div
                            class="rounded-2xl border border-[transparent] bg-card px-3 py-3 sm:px-4"
                        >
                            <p class="font-medium text-foreground">
                                {{ goal.alignment.label }}
                            </p>
                            <p class="mt-1">{{ goal.alignment.description }}</p>
                        </div>
                        <div
                            class="rounded-2xl border border-[transparent] bg-card px-3 py-3 sm:px-4"
                        >
                            <p class="font-medium text-foreground">
                                Delta kesiapan:
                                {{ goal.outcomes.readiness_score_delta }}
                            </p>
                            <p class="mt-1">
                                {{ goal.outcomes.baseline_readiness_label }} ->
                                {{ goal.outcomes.current_readiness_label }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-[transparent] bg-card px-3 py-3 sm:px-4"
                        >
                            <p class="font-medium text-foreground">
                                Accuracy delta:
                                {{
                                    goal.outcomes.accuracy_delta !== null
                                        ? `${goal.outcomes.accuracy_delta}%`
                                        : 'Belum cukup data'
                                }}
                            </p>
                            <p class="mt-1">
                                Supporting tasks:
                                {{
                                    goal.alignment.supporting_completed_tasks
                                }}
                                selesai,
                                {{ goal.alignment.supporting_open_tasks }} masih
                                aktif.
                            </p>
                        </div>
                    </div>
                </div>

                <Link
                    v-if="goal.alignment.next_aligned_action"
                    :href="ctaHref"
                    class="inline-flex items-center gap-2 rounded-full bg-[var(--primary)] px-4 py-2 text-sm font-medium text-white hover:bg-[#111827]"
                >
                    Buka task yang menopang goal
                    <ArrowRight class="size-4" />
                </Link>
            </div>
        </div>
    </div>
</template>
