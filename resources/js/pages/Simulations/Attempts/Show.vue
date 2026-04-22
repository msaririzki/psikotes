<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    CheckCheck,
    ChevronLeft,
    ChevronRight,
    Clock3,
    Flag,
    MonitorPlay,
    Save,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import SimulationQuestionPill from '@/components/simulation/SimulationQuestionPill.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';

type SimulationQuestion = {
    id: number;
    display_order: number;
    section_name: string | null;
    code: string | null;
    difficulty_label: string | null;
    question_text: string;
    question_image: string | null;
    options: Array<{
        id: number;
        option_key: string;
        option_text: string | null;
    }>;
    selected_option_id: number | null;
    is_flagged: boolean;
};

const props = defineProps<{
    attempt: {
        submitted: boolean;
        id: number;
        started_at: string | null;
        package: {
            title: string | null;
            slug: string | null;
            duration_minutes: number | null;
            question_count: number;
        };
        timer: {
            remaining_seconds: number;
            deadline: string | null;
        };
        progress: {
            answered_questions: number;
            total_questions: number;
            flagged_questions: number;
        };
        questions: SimulationQuestion[];
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Simulasi', href: '/simulations' },
            { title: 'Sesi', href: '#' },
        ],
    },
});

const activeQuestionIndex = ref(0);
const remainingSeconds = ref<number>(props.attempt.timer.remaining_seconds);
const autoSubmitted = ref(false);
const isSubmittingFinal = ref(false);
const lastSavedLabel = ref<string | null>(null);
const saveState = ref<'idle' | 'saving' | 'saved' | 'error'>('idle');
const saveMessage = ref('Jawaban dan tanda ragu tersimpan otomatis.');
let timerId: number | null = null;
let autosaveTimerId: number | null = null;
let autosavePending = false;
let autosaveRequest: Promise<boolean> | null = null;

const form = useForm({
    answers: Object.fromEntries(
        props.attempt.questions.map((question) => [
            question.id,
            question.selected_option_id,
        ]),
    ) as Record<number, number | null>,
    flags: Object.fromEntries(
        props.attempt.questions.map((question) => [
            question.id,
            question.is_flagged,
        ]),
    ) as Record<number, boolean>,
});

const currentQuestion = computed(
    () => props.attempt.questions[activeQuestionIndex.value],
);

const isMissingPatternQuestion = computed(() =>
    currentQuestion.value?.question_image?.includes('/missing') ?? false,
);

const missingPatternValues = computed(() => {
    if (! isMissingPatternQuestion.value) {
        return [];
    }

    const match = currentQuestion.value?.question_text.match(
        /Urutan data:\s*([^.]+)\./i,
    );

    return match?.[1]?.trim().split(/\s+/).filter(Boolean) ?? [];
});

const answeredCount = computed(
    () => Object.values(form.answers).filter((value) => value !== null).length,
);

const flaggedCount = computed(
    () => Object.values(form.flags).filter((value) => value).length,
);

const isAutosaving = computed(() => saveState.value === 'saving');

function formatDuration(totalSeconds: number) {
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

function formatSavedTime(date = new Date()) {
    return date.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
}

function csrfToken() {
    return document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');
}

function goToQuestion(index: number) {
    activeQuestionIndex.value = index;
}

function progressPayload(silent = true) {
    return {
        answers: { ...form.answers },
        flags: { ...form.flags },
        silent,
    };
}

function scheduleAutosave(delay = 700) {
    if (autoSubmitted.value || isSubmittingFinal.value) {
        return;
    }

    if (autosaveTimerId !== null) {
        window.clearTimeout(autosaveTimerId);
    }

    autosaveTimerId = window.setTimeout(() => {
        void persistProgress({ silent: true });
    }, delay);
}

async function persistProgress(options?: {
    silent?: boolean;
    showFeedback?: boolean;
    keepalive?: boolean;
}) {
    const silent = options?.silent ?? true;
    const showFeedback = options?.showFeedback ?? false;
    const keepalive = options?.keepalive ?? false;

    if (autoSubmitted.value || isSubmittingFinal.value) {
        return false;
    }

    if (!keepalive && autosaveRequest) {
        autosavePending = true;
        return false;
    }

    if (autosaveTimerId !== null) {
        window.clearTimeout(autosaveTimerId);
        autosaveTimerId = null;
    }

    if (!keepalive) {
        saveState.value = 'saving';
        saveMessage.value = showFeedback
            ? 'Menyimpan jawaban sementara...'
            : 'Menyimpan perubahan terakhir...';
        autosavePending = false;
    }

    const request = fetch(
        `/simulations/attempts/${props.attempt.id}/progress`,
        {
            method: 'POST',
            credentials: 'same-origin',
            keepalive,
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken() ?? '',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(progressPayload(silent)),
        },
    )
        .then(async (response) => {
            const result = await response.json().catch(() => null);

            if (!response.ok) {
                throw new Error(
                    result?.message ?? 'Gagal menyimpan jawaban simulasi.',
                );
            }

            if (!keepalive) {
                saveState.value = 'saved';
                saveMessage.value = showFeedback
                    ? 'Jawaban sementara tersimpan.'
                    : 'Semua perubahan terakhir sudah aman.';
                lastSavedLabel.value = `Tersimpan ${formatSavedTime()}`;
            }

            if (result?.submitted && result.redirect_url) {
                window.location.assign(result.redirect_url);
            }

            return true;
        })
        .catch(() => {
            if (!keepalive) {
                saveState.value = 'error';
                saveMessage.value = showFeedback
                    ? 'Gagal menyimpan jawaban sementara.'
                    : 'Penyimpanan otomatis gagal. Perubahan terakhir belum tersimpan.';
            }

            return false;
        })
        .finally(() => {
            if (!keepalive) {
                autosaveRequest = null;

                if (autosavePending && !isSubmittingFinal.value) {
                    autosavePending = false;
                    scheduleAutosave(250);
                }
            }
        });

    if (!keepalive) {
        autosaveRequest = request;
    }

    return request;
}

function saveProgress() {
    void persistProgress({
        silent: false,
        showFeedback: true,
    });
}

function flushProgressOnExit() {
    if (autoSubmitted.value || isSubmittingFinal.value) {
        return;
    }

    void persistProgress({
        silent: true,
        keepalive: true,
    });
}

function submit(final = false) {
    if (final && !window.confirm('Kumpulkan jawaban sekarang?')) {
        return;
    }

    isSubmittingFinal.value = true;

    if (autosaveTimerId !== null) {
        window.clearTimeout(autosaveTimerId);
        autosaveTimerId = null;
    }

    form.post(`/simulations/attempts/${props.attempt.id}/submit`, {
        onFinish: () => {
            isSubmittingFinal.value = false;
        },
    });
}

onMounted(() => {
    window.addEventListener('pagehide', flushProgressOnExit);

    timerId = window.setInterval(() => {
        if (remainingSeconds.value <= 0) {
            if (timerId !== null) {
                window.clearInterval(timerId);
            }

            if (!autoSubmitted.value) {
                autoSubmitted.value = true;
                submit(false);
            }

            return;
        }

        remainingSeconds.value -= 1;
    }, 1000);
});

watch(
    [() => form.answers, () => form.flags],
    () => {
        lastSavedLabel.value = null;
        scheduleAutosave();
    },
    { deep: true },
);

onBeforeUnmount(() => {
    if (timerId !== null) {
        window.clearInterval(timerId);
    }

    if (autosaveTimerId !== null) {
        window.clearTimeout(autosaveTimerId);
    }

    window.removeEventListener('pagehide', flushProgressOnExit);
});
</script>

<template>
    <Head :title="`Simulasi ${attempt.package.title || ''}`" />

    <div
        class="mx-auto flex w-full max-w-[1440px] flex-1 flex-col gap-3 p-2 sm:gap-6 sm:p-5"
    >
        <!-- Floating Top Status Bar -->
        <section
            class="relative z-10 flex flex-col justify-between gap-3 overflow-hidden rounded-[1.25rem] border border-border/40 bg-white p-3 shadow-md shadow-slate-200/30 sm:gap-5 sm:rounded-[1.5rem] md:bg-white/80 md:backdrop-blur-xl sm:p-5 md:flex-row md:items-center dark:border-slate-800/60 dark:bg-slate-900 dark:md:bg-slate-900/60 dark:shadow-none"
        >
            <div
                class="pointer-events-none absolute inset-0 bg-gradient-to-r from-indigo-500/10 via-transparent to-transparent opacity-50 dark:opacity-20"
            ></div>

            <div class="relative z-10 flex flex-col gap-1.5">
                <div
                    class="mb-1 inline-flex w-fit items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-[0.65rem] font-bold tracking-widest text-indigo-700 uppercase dark:border-indigo-500/30 dark:bg-indigo-900/40 dark:text-indigo-300"
                >
                    <MonitorPlay class="size-3" />
                    Sesi latihan sedang berjalan
                </div>
                <h1
                    class="line-clamp-2 max-w-2xl pr-1 font-display text-base font-bold tracking-tight text-foreground sm:line-clamp-1 sm:pr-4 sm:text-2xl"
                >
                    {{ attempt.package.title }}
                </h1>
            </div>

            <!-- Mobile Grid friendly Status Metrik -->
            <div
                class="relative z-10 mt-1 grid w-full shrink-0 grid-cols-2 gap-2 sm:gap-3 md:mt-0 md:flex md:w-auto md:flex-nowrap"
            >
                <div
                    class="flex items-center gap-2.5 rounded-xl border border-border/40 bg-muted/30 px-3 py-2 sm:gap-3 sm:rounded-2xl sm:px-4 sm:py-2.5 dark:bg-black/20"
                >
                    <div
                        class="shrink-0 rounded-full bg-slate-200/50 p-1.5 text-slate-500 sm:p-2 dark:bg-white/10 dark:text-slate-400"
                    >
                        <CheckCheck class="size-3.5 sm:size-4" />
                    </div>
                    <div>
                        <p
                            class="text-[0.6rem] font-medium tracking-wider text-muted-foreground uppercase sm:text-[0.65rem]"
                        >
                            Terjawab
                        </p>
                        <p class="text-xs font-bold text-foreground sm:text-sm">
                            {{ answeredCount }}
                            <span
                                class="text-[10px] font-normal text-muted-foreground sm:text-xs"
                                >/ {{ attempt.progress.total_questions }}</span
                            >
                        </p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-2.5 rounded-xl border border-border/40 bg-muted/30 px-3 py-2 sm:gap-3 sm:rounded-2xl sm:px-4 sm:py-2.5 dark:bg-black/20"
                >
                    <div
                        class="shrink-0 rounded-full bg-amber-100 p-1.5 text-amber-600 sm:p-2 dark:bg-amber-500/20 dark:text-amber-400"
                    >
                        <Flag class="size-3.5 sm:size-4" />
                    </div>
                    <div>
                        <p
                            class="text-[0.6rem] font-medium tracking-wider text-muted-foreground uppercase sm:text-[0.65rem]"
                        >
                            Ragu
                        </p>
                        <p class="text-xs font-bold text-foreground sm:text-sm">
                            {{ flaggedCount }}
                        </p>
                    </div>
                </div>
                <div
                    class="col-span-2 flex items-center justify-between gap-3 rounded-xl border border-indigo-200/60 bg-indigo-50/50 px-3 py-2 shadow-sm sm:rounded-2xl sm:px-4 sm:py-2.5 md:col-span-1 md:justify-start dark:border-indigo-500/20 dark:bg-indigo-500/10"
                >
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        <div
                            class="relative flex shrink-0 items-center justify-center"
                        >
                            <span
                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-indigo-400 opacity-20"
                            ></span>
                            <div
                                class="relative rounded-full bg-indigo-100 p-1.5 text-indigo-600 sm:p-2 dark:bg-indigo-500/30 dark:text-indigo-400"
                            >
                                <Clock3 class="size-3.5 sm:size-4" />
                            </div>
                        </div>
                        <p
                            class="text-[0.6rem] font-medium tracking-wider text-indigo-600/70 uppercase sm:text-[0.65rem] dark:text-indigo-300/70"
                        >
                            Waktu Tersisa
                        </p>
                    </div>
                    <p
                        class="pr-1 font-mono text-sm font-bold tracking-tight text-indigo-700 sm:text-base dark:text-indigo-300"
                    >
                        {{ formatDuration(remainingSeconds) }}
                    </p>
                </div>
            </div>
        </section>

        <section
            class="grid items-start gap-4 pb-12 sm:gap-5 xl:grid-cols-[minmax(0,1fr)_320px]"
        >
            <!-- Main Question Area -->
            <div class="order-1 space-y-4 sm:space-y-5 xl:order-none">
                <Card
                    class="overflow-hidden rounded-2xl border-border/50 bg-card shadow-sm transition-all sm:rounded-[2rem]"
                >
                    <CardHeader
                        class="space-y-4 border-b border-border/40 bg-muted/10 px-4 pt-5 pb-5 sm:px-8 sm:pt-6"
                    >
                        <div
                            class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between"
                        >
                            <div
                                class="inline-flex items-center gap-2 rounded-xl border border-orange-200/50 bg-orange-100 px-3 py-1.5 text-xs font-semibold text-orange-800 shadow-sm dark:border-orange-500/20 dark:bg-orange-900/30 dark:text-orange-300"
                            >
                                <span
                                    class="size-2 rounded-full bg-orange-500 shadow-sm"
                                ></span>
                                Soal No. {{ currentQuestion.display_order }}
                            </div>
                            <div
                                class="flex max-w-full flex-wrap items-center gap-2 text-[0.65rem] font-medium tracking-widest text-muted-foreground uppercase sm:text-[0.7rem]"
                            >
                                <span
                                    v-if="currentQuestion.section_name"
                                    class="max-w-full rounded-lg border border-border/60 bg-card px-2.5 py-1.5 shadow-sm"
                                    >{{ currentQuestion.section_name }}</span
                                >
                                <span
                                    v-if="currentQuestion.code"
                                    class="max-w-full rounded-lg border border-border/60 bg-card px-2.5 py-1.5 shadow-sm"
                                    >{{ currentQuestion.code }}</span
                                >
                            </div>
                        </div>
                        <CardTitle
                            class="question-text pt-2 text-left text-[1rem] leading-[1.75] font-medium text-slate-800 sm:pt-3 sm:text-[1.2rem] lg:text-[1.28rem] dark:text-slate-200"
                        >
                            {{ currentQuestion.question_text }}
                        </CardTitle>
                        <div
                            v-if="missingPatternValues.length > 0"
                            class="rounded-[1.25rem] border border-indigo-100 bg-white p-4 shadow-sm sm:p-5 dark:border-indigo-500/20 dark:bg-slate-950"
                        >
                            <p
                                class="text-xs font-bold tracking-widest text-slate-500 uppercase dark:text-slate-400"
                            >
                                Urutan data
                            </p>
                            <div class="mt-4 flex flex-wrap gap-2 sm:gap-3">
                                <span
                                    v-for="(value, index) in missingPatternValues"
                                    :key="`${currentQuestion.id}-${index}-${value}`"
                                    class="inline-flex min-h-12 min-w-12 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 font-mono text-xl font-bold text-slate-900 shadow-sm sm:min-h-14 sm:min-w-14 sm:text-2xl dark:border-white/10 dark:bg-white/5 dark:text-white"
                                >
                                    {{ value }}
                                </span>
                            </div>
                        </div>
                        <div
                            v-if="currentQuestion.question_image"
                            :class="[
                                'rounded-[1.25rem] border border-border/50 bg-white shadow-sm dark:bg-slate-950',
                                isMissingPatternQuestion
                                    ? 'overflow-x-auto'
                                    : 'overflow-hidden',
                            ]"
                        >
                            <!-- Scroll hint untuk layar mobile -->
                            <div
                                v-if="isMissingPatternQuestion"
                                class="flex items-center justify-end gap-1 border-b border-border/30 px-3 py-1.5 text-[0.6rem] font-medium tracking-wider text-muted-foreground/60 uppercase sm:hidden"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                Geser untuk melihat gambar
                            </div>
                            <div
                                :class="[
                                    isMissingPatternQuestion
                                        ? 'p-3 sm:p-5'
                                        : 'p-2 sm:p-3',
                                ]"
                            >
                                <img
                                    :src="currentQuestion.question_image"
                                    :alt="`Soal ${currentQuestion.display_order}`"
                                    :class="[
                                        'rounded-xl object-contain',
                                        isMissingPatternQuestion
                                            ? 'h-auto w-auto max-h-[22rem] min-w-[480px] max-w-none sm:min-w-[600px] sm:max-h-[28rem]'
                                            : 'mx-auto max-h-[65vh] min-w-0 max-w-full sm:max-h-[38rem]',
                                    ]"
                                />
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent
                        class="space-y-5 bg-slate-50/50 p-4 sm:p-8 dark:bg-transparent"
                    >
                        <!-- Custom Interactive Radio Answers -->
                        <div
                            class="relative z-10 mx-auto w-full max-w-5xl space-y-2.5 sm:space-y-3 xl:mx-0"
                        >
                            <label
                                v-for="option in currentQuestion.options"
                                :key="option.id"
                                :class="[
                                    'group relative flex min-w-0 cursor-pointer items-start gap-3 rounded-[1.15rem] border p-3 transition-colors duration-200 outline-none select-none sm:gap-4 sm:rounded-[1.25rem] sm:p-5',
                                    form.answers[currentQuestion.id] ===
                                    option.id
                                        ? 'scale-[1.01] border-indigo-400 bg-indigo-50 shadow-md shadow-indigo-100/50 ring-1 ring-indigo-500/30 dark:border-indigo-500/50 dark:bg-indigo-900/30 dark:shadow-indigo-900/20'
                                        : 'border-border/60 bg-white shadow-sm hover:border-indigo-300 hover:bg-slate-50 hover:shadow-md dark:border-white/10 dark:bg-card/50 dark:hover:border-white/20 dark:hover:bg-card/80',
                                ]"
                            >
                                <div
                                    class="relative flex shrink-0 items-center justify-center pt-1"
                                >
                                    <input
                                        v-model="
                                            form.answers[currentQuestion.id]
                                        "
                                        :value="option.id"
                                        type="radio"
                                        class="peer sr-only"
                                    />
                                    <div
                                        :class="[
                                            'flex size-5 items-center justify-center rounded-full border-2 transition-all duration-300 ease-out sm:size-6',
                                            form.answers[currentQuestion.id] ===
                                            option.id
                                                ? 'scale-100 border-indigo-600 bg-indigo-600 shadow-sm shadow-indigo-200 dark:border-indigo-400 dark:bg-indigo-400 dark:shadow-none'
                                                : 'border-slate-300 bg-white group-hover:scale-105 group-hover:border-indigo-400 dark:border-slate-600 dark:bg-transparent dark:group-hover:border-indigo-400',
                                        ]"
                                    >
                                        <div
                                            :class="[
                                                'size-1.5 rounded-full bg-white transition-transform duration-300 sm:size-2 dark:bg-slate-900',
                                                form.answers[
                                                    currentQuestion.id
                                                ] === option.id
                                                    ? 'scale-100'
                                                    : 'scale-0',
                                            ]"
                                        ></div>
                                    </div>
                                </div>
                                <span
                                    :class="[
                                        'min-w-0 flex-1 break-words text-sm leading-[1.7] sm:text-[1.05rem]',
                                        form.answers[currentQuestion.id] ===
                                        option.id
                                            ? 'font-medium text-indigo-950 dark:text-indigo-100'
                                            : 'font-normal text-slate-700 dark:text-slate-300',
                                    ]"
                                >
                                    <span
                                        class="inline-block min-w-6 font-bold text-indigo-700 dark:text-indigo-400"
                                    >
                                        {{ option.option_key }}.
                                    </span>
                                    <span class="option-text">{{ option.option_text }}</span>
                                </span>
                            </label>
                        </div>
                    </CardContent>

                    <!-- Modern Action Nav Base -->
                    <div
                        class="mt-4 border-t border-border/40 bg-muted/10 p-4 sm:p-6 sm:px-8"
                    >
                        <div
                            class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                        >
                            <div class="grid grid-cols-2 gap-2.5 sm:flex sm:gap-3">
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="h-12 rounded-[1rem] border-border/60 bg-card px-4 font-semibold shadow-sm hover:bg-muted sm:flex-none dark:bg-transparent"
                                    :disabled="activeQuestionIndex === 0"
                                    @click="
                                        goToQuestion(activeQuestionIndex - 1)
                                    "
                                >
                                    <ChevronLeft class="mr-1.5 size-4" />
                                    <span class="xs:inline hidden"
                                        >Sebelumnya</span
                                    >
                                </Button>
                                <Button
                                    type="button"
                                    class="h-12 rounded-[1rem] bg-indigo-600 px-6 font-semibold text-white shadow-md shadow-indigo-500/25 transition-all hover:-translate-y-0.5 hover:bg-indigo-700 hover:shadow-indigo-500/40 sm:flex-none dark:bg-indigo-600 dark:hover:bg-indigo-500"
                                    :disabled="
                                        activeQuestionIndex ===
                                        attempt.questions.length - 1
                                    "
                                    @click="
                                        goToQuestion(activeQuestionIndex + 1)
                                    "
                                >
                                    <span class="xs:inline hidden"
                                        >Selanjutnya</span
                                    >
                                    <span class="xs:hidden">Berikutnya</span>
                                    <ChevronRight class="ml-1.5 size-4" />
                                </Button>
                            </div>
                            <div
                                class="grid grid-cols-2 gap-2.5 sm:mt-0 sm:flex sm:justify-end sm:gap-3"
                            >
                                <Button
                                    v-if="
                                        form.answers[currentQuestion.id] !==
                                        null
                                    "
                                    type="button"
                                    variant="ghost"
                                    class="h-11 rounded-[1rem] px-4 text-slate-500 transition-colors hover:bg-slate-200/50 hover:text-slate-700 dark:hover:bg-white/10 dark:hover:text-slate-200"
                                    @click="
                                        form.answers[currentQuestion.id] = null
                                    "
                                >
                                    Kosongkan
                                </Button>
                                <Button
                                    type="button"
                                    :variant="
                                        form.flags[currentQuestion.id]
                                            ? 'default'
                                            : 'outline'
                                    "
                                    :class="[
                                        'h-12 rounded-[1rem] px-4 font-semibold shadow-sm transition-all duration-300 sm:px-5',
                                        form.flags[currentQuestion.id]
                                            ? 'border-amber-500 bg-amber-500 text-amber-950 hover:bg-amber-600 dark:bg-amber-500 dark:hover:bg-amber-600'
                                            : 'border-amber-200 bg-amber-50/50 text-amber-700 hover:bg-amber-100/80 hover:text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-400 dark:hover:bg-amber-500/20',
                                    ]"
                                    @click="
                                        form.flags[currentQuestion.id] =
                                            !form.flags[currentQuestion.id]
                                    "
                                >
                                    <Flag
                                        class="mr-2 size-4 transition-transform duration-300"
                                        :class="{
                                            'scale-110 fill-amber-950 dark:fill-amber-950':
                                                form.flags[currentQuestion.id],
                                        }"
                                    />
                                    {{
                                        form.flags[currentQuestion.id]
                                            ? 'Ditandai Ragu'
                                            : 'Tandai Ragu'
                                    }}
                                </Button>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Sidebar Navigator Area -->
            <div
                class="order-2 h-fit space-y-4 sm:space-y-5 lg:sticky lg:top-4 xl:order-none"
            >
                <Card
                    class="flex flex-col overflow-hidden rounded-2xl border-border/50 bg-card shadow-sm sm:rounded-[1.75rem] dark:bg-[#0c111d]"
                >
                    <CardHeader
                        class="shrinks-0 border-b border-border/40 bg-muted/10 px-4 pt-4 pb-3 sm:px-6 sm:pt-5 sm:pb-4"
                    >
                        <CardTitle
                            class="flex items-center justify-between text-[0.8rem] tracking-widest text-muted-foreground/80 uppercase sm:text-[0.9rem]"
                        >
                            Daftar soal
                            <span
                                class="text-xs font-normal tracking-normal lowercase"
                                >{{ answeredCount }}/{{
                                    attempt.progress.total_questions
                                }}
                                terjawab</span
                            >
                        </CardTitle>
                    </CardHeader>
                    <CardContent
                        class="custom-scroll max-h-[190px] w-full flex-1 overflow-y-auto bg-slate-50/50 p-3 sm:max-h-[300px] sm:p-5 xl:max-h-[50vh] dark:bg-transparent"
                    >
                        <div
                            class="grid grid-cols-[repeat(auto-fill,minmax(2.35rem,1fr))] gap-2 sm:gap-2.5 xl:grid-cols-5"
                        >
                            <button
                                v-for="(question, index) in attempt.questions"
                                :key="question.id"
                                type="button"
                                class="rounded-xl transition-transform hover:scale-105 focus:ring-2 focus:ring-indigo-500 focus:outline-none active:scale-95"
                                @click="goToQuestion(index)"
                            >
                                <SimulationQuestionPill
                                    :number="question.display_order"
                                    :active="index === activeQuestionIndex"
                                    :answered="
                                        form.answers[question.id] !== null
                                    "
                                    :flagged="form.flags[question.id]"
                                />
                            </button>
                        </div>
                    </CardContent>
                </Card>

                <Card
                    class="group overflow-hidden rounded-2xl border border-indigo-200/60 bg-indigo-50/40 shadow-sm transition-all focus-within:border-indigo-300 hover:border-indigo-300 hover:bg-indigo-50/60 sm:rounded-[1.75rem] dark:border-indigo-500/20 dark:bg-[#0c111d] dark:hover:border-indigo-500/30"
                >
                    <div
                        class="pointer-events-none absolute -right-10 -bottom-10 size-32 rounded-full bg-indigo-500 opacity-20 blur-2xl transition-opacity group-hover:opacity-30 sm:opacity-30 sm:blur-3xl sm:group-hover:opacity-40"
                    ></div>
                    <CardHeader
                        class="relative z-10 space-y-0.5 border-b border-indigo-200/40 px-4 pt-4 pb-3 sm:px-5 sm:pt-5 dark:border-white/5"
                    >
                        <CardTitle
                            class="text-[0.8rem] tracking-widest text-indigo-950 uppercase sm:text-[0.9rem] dark:text-indigo-200/80"
                            >Kumpulkan Jawaban</CardTitle
                        >
                        <p
                            class="pt-1 text-[0.7rem] leading-relaxed font-medium text-muted-foreground/80 sm:text-[0.75rem]"
                        >
                            Jawaban tersimpan otomatis. Kalau keluar dari sesi,
                            jawaban terakhir tetap tersimpan.
                        </p>
                    </CardHeader>
                    <CardContent class="relative z-10 p-5">
                        <div
                            class="mb-4 rounded-2xl border px-4 py-3 text-sm"
                            :class="{
                                'border-indigo-200 bg-indigo-50/80 text-indigo-700 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-200':
                                    saveState === 'idle' ||
                                    saveState === 'saved',
                                'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200':
                                    saveState === 'saving',
                                'border-red-200 bg-red-50 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200':
                                    saveState === 'error',
                            }"
                        >
                            <p class="font-medium">
                                {{ saveMessage }}
                            </p>
                            <p
                                v-if="lastSavedLabel"
                                class="mt-1 text-xs opacity-80"
                            >
                                {{ lastSavedLabel }}
                            </p>
                        </div>
                        <div class="flex flex-col gap-3">
                            <Button
                                type="button"
                                :disabled="form.processing || isAutosaving"
                                class="h-[3.25rem] w-full rounded-xl border border-indigo-200 bg-white text-indigo-700 shadow-sm hover:bg-indigo-50 hover:text-indigo-800 sm:h-12 dark:border-white/10 dark:bg-white/5 dark:text-indigo-300 dark:hover:bg-white/10"
                                @click="saveProgress"
                            >
                                <Save class="mr-2 size-4" />
                                Simpan Sekarang
                            </Button>
                            <Button
                                type="button"
                                :disabled="form.processing"
                                class="h-[3.25rem] w-full rounded-xl bg-indigo-600 font-semibold text-white shadow-md shadow-indigo-600/20 transition-all hover:bg-indigo-700 sm:h-12 dark:bg-indigo-600"
                                @click="submit(true)"
                            >
                                <CheckCheck class="mr-2 size-4" />
                                Kumpulkan & Lihat Nilai
                            </Button>
                            <Button
                                as-child
                                variant="ghost"
                                class="mt-1 h-11 rounded-xl text-[0.8rem] text-slate-500 transition-all hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-white/5 dark:hover:text-white"
                            >
                                <Link href="/simulations"
                                    >Simpan Dulu & Kembali ke Daftar</Link
                                >
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </section>
    </div>
</template>

<style scoped>
.question-text,
.option-text {
    overflow-wrap: anywhere;
    word-break: normal;
}

.question-text {
    white-space: pre-line;
}

.option-text {
    white-space: pre-wrap;
}
</style>
