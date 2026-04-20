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

const props = withDefaults(defineProps<{
    task: StudyTask;
    compact?: boolean;
    actionable?: boolean;
    redirectTo?: string;
}>(), {
    compact: false,
    actionable: false,
    redirectTo: '/study-plan',
});

const toneMap = {
    learn: 'bg-sky-50 text-sky-800 ring-sky-200',
    practice: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
    simulation: 'bg-slate-100 text-slate-800 ring-slate-200',
    review: 'bg-amber-50 text-amber-800 ring-amber-200',
};

const iconMap = {
    learn: BrainCircuit,
    practice: BookCheck,
    simulation: Clock3,
    review: RefreshCcw,
};

const statusToneMap = {
    pending: 'bg-slate-100 text-slate-700 ring-slate-200',
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
    <div class="rounded-[1.6rem] border border-[#dfe8ef] bg-white/95 p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
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
                    <span class="rounded-full bg-[#f8fbff] px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-[#e6edf3]">
                        {{ task.priority_label || 'Recommended next' }}
                    </span>
                    <span class="text-xs text-slate-500">
                        {{ task.due_label }}
                    </span>
                </div>

                <div>
                    <p class="font-display text-2xl font-bold tracking-tight text-slate-950">
                        {{ task.title }}
                    </p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ task.description }}
                    </p>
                </div>

                <p class="text-xs text-slate-500">
                    Alasan: {{ task.reason }}
                </p>
                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                    <span
                        v-if="task.cadence.label"
                        class="rounded-full bg-[#f8fbff] px-3 py-1 ring-1 ring-[#e6edf3]"
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
                        class="rounded-full bg-[#fff5f5] px-3 py-1 text-[#b91c1c] ring-1 ring-[#fecaca]"
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
                <div class="rounded-[1.25rem] bg-[#f8fbff] px-4 py-3 ring-1 ring-[#e6edf3]">
                    <p class="text-sm text-slate-500">Skor prioritas</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-950">
                        {{ task.priority_score }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="task.action_href"
                        class="inline-flex items-center gap-2 rounded-full bg-[#0f172a] px-4 py-2 text-sm font-medium text-white hover:bg-[#111827]"
                    >
                        {{ task.action_label }}
                        <ArrowRight class="size-4" />
                    </Link>
                    <Button
                        v-if="canExecute"
                        type="button"
                        class="rounded-full bg-[#b91c1c] text-white hover:bg-[#991b1b]"
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
            class="mt-5 border-t border-[#edf2f7] pt-5"
        >
            <div v-if="schedulerMode" class="space-y-3 rounded-2xl bg-[#fbfdff] p-4 ring-1 ring-[#e6edf3]">
                <p class="text-sm font-medium text-slate-900">
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
                            class="rounded-2xl bg-[#0f172a] text-white hover:bg-[#111827]"
                            :disabled="form.processing"
                            @click="runAction(schedulerMode)"
                        >
                            Simpan
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            class="rounded-2xl"
                            @click="schedulerMode = null"
                        >
                            Batal
                        </Button>
                    </div>
                </div>
                <InputError :message="form.errors.scheduled_for" />
            </div>

            <div v-else class="flex flex-wrap gap-2">
                <Button
                    type="button"
                    variant="outline"
                    class="rounded-2xl"
                    :disabled="form.processing"
                    @click="openScheduler('snooze')"
                >
                    <Clock3 class="mr-2 size-4" />
                    Tunda
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    class="rounded-2xl"
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
            class="mt-5 border-t border-[#edf2f7] pt-5"
        >
            <p class="text-sm font-medium text-slate-900">Riwayat eksekusi</p>
            <div class="mt-3 space-y-2">
                <div
                    v-for="entry in task.history.slice(0, 3)"
                    :key="`${entry.event_type}-${entry.happened_at}`"
                    class="rounded-2xl bg-[#fbfdff] px-4 py-3 text-sm text-slate-600 ring-1 ring-[#e6edf3]"
                >
                    <p class="font-medium text-slate-900">
                        {{ entry.event_label }}
                    </p>
                    <p class="mt-1">{{ entry.description }}</p>
                </div>
            </div>
        </div>
    </div>
</template>


