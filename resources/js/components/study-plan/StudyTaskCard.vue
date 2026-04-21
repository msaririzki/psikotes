<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookCheck,
    BrainCircuit,
    CheckCheck,
    Clock3,
    RefreshCcw,
    RotateCcw,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import type { StudyTask } from '@/types';

const props = withDefaults(
    defineProps<{
        task: StudyTask;
        compact?: boolean;
        actionable?: boolean;
        redirectTo?: string;
    }>(),
    {
        compact: false,
        actionable: false,
        redirectTo: '/study-plan',
    },
);

const toneMap = {
    learn: 'bg-sky-50 text-sky-800 ring-sky-200',
    practice: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
    simulation: 'bg-muted text-foreground ring-border',
    review: 'bg-amber-50 text-amber-800 ring-amber-200',
};

const iconMap = {
    learn: BrainCircuit,
    practice: BookCheck,
    simulation: Clock3,
    review: RefreshCcw,
};

const statusToneMap = {
    pending: 'bg-muted text-muted-foreground ring-border',
    snoozed: 'bg-amber-50 text-amber-800 ring-amber-200',
    rescheduled: 'bg-violet-50 text-violet-800 ring-violet-200',
    completed: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
};

const completionToneMap = {
    manual: 'bg-sky-50 text-sky-800 ring-sky-200',
    auto: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
};

const schedulerMode = ref<'snooze' | 'reschedule' | null>(null);
const selectedDate = ref(
    props.task.scheduled_for_date ??
        props.task.recommended_for_date ??
        new Date().toISOString().slice(0, 10),
);

const form = useForm({
    action: 'done' as 'done' | 'snooze' | 'reschedule',
    scheduled_for: selectedDate.value,
    redirect_to: props.redirectTo,
});

const canExecute = computed(
    () => props.actionable && props.task.status !== 'completed',
);

function runAction(action: 'done' | 'snooze' | 'reschedule') {
    form.action = action;
    form.scheduled_for = selectedDate.value;
    form.redirect_to = props.redirectTo;

    form.patch(`/study-plan/tasks/${props.task.record_id}`, {
        preserveScroll: true,
        onSuccess: () => {
            schedulerMode.value = null;
        },
    });
}

function openScheduler(mode: 'snooze' | 'reschedule') {
    schedulerMode.value = mode;
    form.clearErrors();
}
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
                        class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1"
                        :class="toneMap[task.track]"
                    >
                        <component :is="iconMap[task.track]" class="size-3.5" />
                        {{ task.track }}
                    </span>
                    <span
                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1"
                        :class="statusToneMap[task.status]"
                    >
                        {{ task.status_label }}
                    </span>
                    <span
                        class="rounded-full bg-[transparent] px-3 py-1 text-xs font-semibold text-muted-foreground ring-1 ring-[transparent]"
                    >
                        {{ task.priority_label || 'Recommended next' }}
                    </span>
                    <span class="text-xs text-muted-foreground">
                        {{ task.due_label }}
                    </span>
                </div>

                <div>
                    <p
                        class="font-display text-xl font-bold tracking-tight text-foreground sm:text-2xl"
                    >
                        {{ task.title }}
                    </p>
                    <p class="mt-2 text-sm leading-6 text-muted-foreground">
                        {{ task.description }}
                    </p>
                </div>

                <p class="text-xs text-muted-foreground">
                    Alasan: {{ task.reason }}
                </p>
                <div
                    class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground"
                >
                    <span
                        v-if="task.cadence.label"
                        class="rounded-full bg-[transparent] px-3 py-1 ring-1 ring-[transparent]"
                    >
                        {{ task.cadence.label }}
                    </span>
                    <span
                        v-if="task.completion_source"
                        class="rounded-full px-3 py-1 ring-1"
                        :class="completionToneMap[task.completion_source]"
                    >
                        {{ task.completion_source_label }}
                    </span>
                    <span
                        v-if="task.outcome_impact?.readiness_changed"
                        class="rounded-full bg-[#fff5f5] px-3 py-1 text-[var(--primary)] ring-1 ring-[#fecaca]"
                    >
                        Kesiapan berubah
                    </span>
                    <span
                        v-if="task.outcome_impact?.next_best_action_changed"
                        class="rounded-full bg-[#f0fdf4] px-3 py-1 text-emerald-700 ring-1 ring-emerald-200"
                    >
                        Aksi berikutnya berubah
                    </span>
                </div>
            </div>

            <div class="flex flex-col items-start gap-3 lg:min-w-[240px]">
                <div
                    class="rounded-[1.25rem] bg-[transparent] px-4 py-3 ring-1 ring-[transparent]"
                >
                    <p class="text-sm text-muted-foreground">Skor prioritas</p>
                    <p class="mt-1 text-2xl font-semibold text-foreground">
                        {{ task.priority_score }}
                    </p>
                </div>
                <div
                    class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap"
                >
                    <Link
                        :href="task.action_href"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-[var(--primary)] px-4 py-2 text-sm font-medium text-white hover:bg-[#111827] sm:w-auto"
                    >
                        {{ task.action_label }}
                        <ArrowRight class="size-4" />
                    </Link>
                    <Button
                        v-if="canExecute"
                        type="button"
                        class="w-full rounded-full bg-[var(--primary)] text-white hover:bg-[#991b1b] sm:w-auto"
                        :disabled="form.processing"
                        @click="runAction('done')"
                    >
                        <CheckCheck class="mr-2 size-4" />
                        Selesai
                    </Button>
                </div>
            </div>
        </div>

        <div
            v-if="canExecute && !compact"
            class="mt-5 border-t border-[transparent] pt-5"
        >
            <div
                v-if="schedulerMode"
                class="space-y-3 rounded-2xl bg-[transparent] p-3 ring-1 ring-[transparent] sm:p-4"
            >
                <p class="text-sm font-medium text-foreground">
                    {{
                        schedulerMode === 'snooze'
                            ? 'Tunda task ke tanggal baru'
                            : 'Jadwalkan ulang task'
                    }}
                </p>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input
                        v-model="selectedDate"
                        type="date"
                        class="flex h-11 w-full rounded-2xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                    />
                    <div class="flex gap-2">
                        <Button
                            type="button"
                            class="flex-1 rounded-2xl bg-[var(--primary)] text-white hover:bg-[#111827] sm:flex-none"
                            :disabled="form.processing"
                            @click="runAction(schedulerMode)"
                        >
                            Simpan
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            class="flex-1 rounded-2xl sm:flex-none"
                            @click="schedulerMode = null"
                        >
                            Batal
                        </Button>
                    </div>
                </div>
                <InputError :message="form.errors.scheduled_for" />
            </div>

            <div v-else class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                <Button
                    type="button"
                    variant="outline"
                    class="w-full rounded-2xl sm:w-auto"
                    :disabled="form.processing"
                    @click="openScheduler('snooze')"
                >
                    <Clock3 class="mr-2 size-4" />
                    Tunda
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    class="w-full rounded-2xl sm:w-auto"
                    :disabled="form.processing"
                    @click="openScheduler('reschedule')"
                >
                    <RotateCcw class="mr-2 size-4" />
                    Jadwalkan ulang
                </Button>
            </div>
        </div>

        <div
            v-if="!compact && task.history.length > 0"
            class="mt-5 border-t border-[transparent] pt-5"
        >
            <p class="text-sm font-medium text-foreground">Riwayat eksekusi</p>
            <div class="mt-3 space-y-2">
                <div
                    v-for="entry in task.history.slice(0, 3)"
                    :key="`${entry.event_type}-${entry.happened_at}`"
                    class="rounded-2xl bg-[transparent] px-3 py-3 text-sm text-muted-foreground ring-1 ring-[transparent] sm:px-4"
                >
                    <p class="font-medium text-foreground">
                        {{ entry.event_label }}
                    </p>
                    <p class="mt-1">{{ entry.description }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
