<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { CheckCheck, ChevronLeft, ChevronRight, Flag, Save } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
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
let timerId: number | null = null;

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

const answeredCount = computed(
    () =>
        Object.values(form.answers).filter((value) => value !== null).length,
);

const flaggedCount = computed(
    () => Object.values(form.flags).filter((value) => value).length,
);

function formatDuration(totalSeconds: number) {
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

function goToQuestion(index: number) {
    activeQuestionIndex.value = index;
}

function saveProgress() {
    form.post(`/simulations/attempts/${props.attempt.id}/progress`, {
        preserveScroll: true,
        preserveState: true,
    });
}

function submit(final = false) {
    if (final && !window.confirm('Submit final simulasi ini sekarang?')) {
        return;
    }

    form.post(`/simulations/attempts/${props.attempt.id}/submit`);
}

onMounted(() => {
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

onBeforeUnmount(() => {
    if (timerId !== null) {
        window.clearInterval(timerId);
    }
});
</script>

<template>
    <Head :title="`Simulasi ${attempt.package.title || ''}`" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <section
            class="overflow-hidden rounded-[2rem] border border-[#dbe6ee] bg-[radial-gradient(circle_at_top_right,_rgba(15,23,42,0.16),_transparent_30%),linear-gradient(135deg,_#f8fbff_0%,_#eef5fb_50%,_#ffffff_100%)] p-6 shadow-sm"
        >
            <div class="grid gap-6 xl:grid-cols-[1.1fr,0.9fr] xl:items-end">
                <div class="space-y-3">
                    <p class="text-sm font-medium text-[#b91c1c]">Sesi CAT</p>
                    <h1 class="font-display text-4xl font-bold tracking-tight text-slate-950">
                        {{ attempt.package.title }}
                    </h1>
                    <p class="text-base leading-7 text-slate-600">
                        Fokus pada akurasi dan manajemen waktu. Gunakan flag untuk soal yang ingin direview sebelum submit final.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-[1.5rem] border border-[#dfe8ef] bg-white/90 p-4">
                        <p class="text-sm text-slate-500">Timer</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-950">
                            {{ formatDuration(remainingSeconds) }}
                        </p>
                    </div>
                    <div class="rounded-[1.5rem] border border-[#dfe8ef] bg-white/90 p-4">
                        <p class="text-sm text-slate-500">Jawaban</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-950">
                            {{ answeredCount }} / {{ attempt.progress.total_questions }}
                        </p>
                    </div>
                    <div class="rounded-[1.5rem] border border-[#dfe8ef] bg-white/90 p-4">
                        <p class="text-sm text-slate-500">Flag</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-950">
                            {{ flaggedCount }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.72fr,1.28fr]">
            <div class="space-y-5">
                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle>Navigator soal</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-5 gap-3 sm:grid-cols-6">
                            <button
                                v-for="(question, index) in attempt.questions"
                                :key="question.id"
                                type="button"
                                @click="goToQuestion(index)"
                            >
                                <SimulationQuestionPill
                                    :number="question.display_order"
                                    :active="index === activeQuestionIndex"
                                    :answered="form.answers[question.id] !== null"
                                    :flagged="form.flags[question.id]"
                                />
                            </button>
                        </div>
                        <div class="rounded-2xl bg-[#f8fbff] p-4 text-sm text-slate-600">
                            {{ answeredCount }} dijawab, {{
                                attempt.progress.total_questions - answeredCount
                            }}
                            kosong, {{ flaggedCount }} ditandai ragu-ragu.
                        </div>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-[#0f172a] text-white shadow-sm">
                    <CardHeader>
                        <CardTitle>Kontrol sesi</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm text-slate-200">
                        <p>
                            Simpan progres kapan saja. Jika timer mencapai nol, sistem akan otomatis mengirim sesi ini.
                        </p>
                        <div class="flex flex-col gap-3">
                            <Button
                                type="button"
                                :disabled="form.processing"
                                class="rounded-2xl bg-white text-slate-950 hover:bg-slate-100"
                                @click="saveProgress"
                            >
                                <Save class="mr-2 size-4" />
                                Simpan progres
                            </Button>
                            <Button
                                type="button"
                                :disabled="form.processing"
                                class="rounded-2xl bg-[#b91c1c] text-white hover:bg-[#991b1b]"
                                @click="submit(true)"
                            >
                                <CheckCheck class="mr-2 size-4" />
                                Kirim final
                            </Button>
                            <Button
                                as-child
                                variant="outline"
                                class="rounded-2xl border-white/20 bg-transparent text-white hover:bg-white/10"
                            >
                                <Link href="/simulations">Kembali ke daftar</Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div>
                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardHeader class="space-y-3">
                        <p class="text-sm font-medium text-[#b91c1c]">
                            Soal {{ currentQuestion.display_order }}
                            <span v-if="currentQuestion.section_name">
                                - {{ currentQuestion.section_name }}
                            </span>
                            <span v-if="currentQuestion.code">
                                - {{ currentQuestion.code }}
                            </span>
                        </p>
                        <CardTitle class="text-2xl leading-8">
                            {{ currentQuestion.question_text }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <label
                            v-for="option in currentQuestion.options"
                            :key="option.id"
                            class="flex cursor-pointer items-start gap-3 rounded-[1.35rem] border border-[#e7edf2] bg-[#fbfdff] px-4 py-4 transition hover:border-[#cdd8e2]"
                        >
                            <input
                                v-model="form.answers[currentQuestion.id]"
                                :value="option.id"
                                type="radio"
                                class="mt-1 size-4 border-slate-300 text-[#b91c1c] focus:ring-[#b91c1c]"
                            />
                            <span class="text-sm leading-6 text-slate-700">
                                <span class="font-semibold">
                                    {{ option.option_key }}.
                                </span>
                                {{ option.option_text }}
                            </span>
                        </label>

                        <div class="flex flex-wrap items-center justify-between gap-3 pt-4">
                            <div class="flex gap-3">
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="rounded-2xl"
                                    :disabled="activeQuestionIndex === 0"
                                    @click="goToQuestion(activeQuestionIndex - 1)"
                                >
                                    <ChevronLeft class="mr-2 size-4" />
                                    Sebelumnya
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="rounded-2xl"
                                    :disabled="
                                        activeQuestionIndex ===
                                        attempt.questions.length - 1
                                    "
                                    @click="goToQuestion(activeQuestionIndex + 1)"
                                >
                                    Berikutnya
                                    <ChevronRight class="ml-2 size-4" />
                                </Button>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="rounded-2xl"
                                    @click="
                                        form.flags[currentQuestion.id] =
                                            !form.flags[currentQuestion.id]
                                    "
                                >
                                    <Flag class="mr-2 size-4" />
                                    {{
                                        form.flags[currentQuestion.id]
                                            ? 'Batalkan flag'
                                            : 'Flag soal'
                                    }}
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="rounded-2xl"
                                    @click="form.answers[currentQuestion.id] = null"
                                >
                                    Kosongkan
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </section>
    </div>
</template>

