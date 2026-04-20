<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    CheckCheck,
    ChevronLeft,
    ChevronRight,
    Clock3,
    Save,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import PracticeQuestionPill from '@/components/practice/PracticeQuestionPill.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';

type LatihanQuestion = {
    id: number;
    display_order: number;
    code: string | null;
    difficulty: string | null;
    difficulty_label: string | null;
    question_text: string;
    options: Array<{
        id: number;
        option_key: string;
        option_text: string | null;
    }>;
    selected_option_id: number | null;
};

const props = defineProps<{
    attempt: {
        id: number;
        status: string;
        started_at: string | null;
        category: {
            name: string | null;
            slug: string | null;
        };
        subtest: {
            name: string | null;
            slug: string | null;
        };
        configuration: {
            difficulty: string;
            difficulty_label: string;
            question_count: number;
            timer_minutes: number | null;
            timer_enabled: boolean;
        };
        progress: {
            answered_questions: number;
            total_questions: number;
            remaining_questions: number;
        };
        timer: {
            enabled: boolean;
            minutes: number | null;
            remaining_seconds: number | null;
            deadline: string | null;
        };
        questions: LatihanQuestion[];
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Latihan', href: '/practice' },
            { title: 'Sesi', href: '#' },
        ],
    },
});

const activeQuestionIndex = ref(0);
const remainingSeconds = ref<number | null>(
    props.attempt.timer.remaining_seconds,
);
const lastSavedLabel = ref<string | null>(null);
let timerId: number | null = null;

const form = useForm({
    answers: Object.fromEntries(
        props.attempt.questions.map((question) => [
            question.id,
            question.selected_option_id,
        ]),
    ) as Record<number, number | null>,
});

const answeredCount = computed(
    () => Object.values(form.answers).filter((value) => value !== null).length,
);

const currentQuestion = computed(
    () => props.attempt.questions[activeQuestionIndex.value],
);

const isLastQuestion = computed(
    () => activeQuestionIndex.value === props.attempt.questions.length - 1,
);

const isFirstQuestion = computed(() => activeQuestionIndex.value === 0);

function formatDuration(totalSeconds: number | null) {
    if (totalSeconds === null) {
        return 'Tanpa timer';
    }

    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

function goToQuestion(index: number) {
    activeQuestionIndex.value = index;
}

function saveAnswers() {
    form.post(`/practice/attempts/${props.attempt.id}/answers`, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            lastSavedLabel.value = `Tersimpan ${new Date().toLocaleTimeString(
                'id-ID',
                {
                    hour: '2-digit',
                    minute: '2-digit',
                },
            )}`;
        },
    });
}

function submit() {
    form.post(`/practice/attempts/${props.attempt.id}/submit`);
}

watch(
    () => currentQuestion.value?.id,
    () => {
        lastSavedLabel.value = null;
    },
);

onMounted(() => {
    if (remainingSeconds.value === null) {
        return;
    }

    timerId = window.setInterval(() => {
        if (remainingSeconds.value === null || remainingSeconds.value <= 0) {
            if (timerId !== null) {
                window.clearInterval(timerId);
            }

            remainingSeconds.value = 0;

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
    <Head :title="`Latihan ${attempt.subtest.name || 'Sesi'}`" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <section
            class="overflow-hidden rounded-[2rem] border border-[#dbe6ee] bg-[radial-gradient(circle_at_top_right,_rgba(15,23,42,0.12),_transparent_28%),linear-gradient(135deg,_#f8fbff_0%,_#eef5fb_52%,_#ffffff_100%)] p-6 shadow-sm"
        >
            <div class="grid gap-6 xl:grid-cols-[1.1fr,0.9fr] xl:items-end">
                <div class="space-y-3">
                    <p class="text-sm font-medium text-[#b91c1c]">
                        {{ attempt.category.name }} / {{ attempt.subtest.name }}
                    </p>
                    <h1
                        class="font-display text-4xl font-bold tracking-tight text-slate-950"
                    >
                        Sesi latihan terstruktur
                    </h1>
                    <p class="text-base leading-7 text-slate-600">
                        Fokus pada ritme kerja yang stabil. Jawaban bisa
                        disimpan selama sesi, lalu submit saat semua review
                        selesai.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div
                        class="rounded-[1.5rem] border border-[#dfe8ef] bg-white/90 p-4"
                    >
                        <p class="text-sm text-slate-500">Kesulitan</p>
                        <p class="mt-2 text-xl font-semibold text-slate-950">
                            {{ attempt.configuration.difficulty_label }}
                        </p>
                    </div>
                    <div
                        class="rounded-[1.5rem] border border-[#dfe8ef] bg-white/90 p-4"
                    >
                        <p class="text-sm text-slate-500">Jawaban</p>
                        <p class="mt-2 text-xl font-semibold text-slate-950">
                            {{ answeredCount }} /
                            {{ attempt.progress.total_questions }}
                        </p>
                    </div>
                    <div
                        class="rounded-[1.5rem] border border-[#dfe8ef] bg-white/90 p-4"
                    >
                        <p
                            class="flex items-center gap-2 text-sm text-slate-500"
                        >
                            <Clock3 class="size-4 text-[#b91c1c]" />
                            Timer
                        </p>
                        <p class="mt-2 text-xl font-semibold text-slate-950">
                            {{ formatDuration(remainingSeconds) }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.72fr,1.28fr]">
            <div class="space-y-5">
                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardHeader>
                        <CardTitle>Navigasi soal</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-5 gap-3 sm:grid-cols-6">
                            <button
                                v-for="(question, index) in attempt.questions"
                                :key="question.id"
                                type="button"
                                @click="goToQuestion(index)"
                            >
                                <PracticeQuestionPill
                                    :number="question.display_order"
                                    :active="index === activeQuestionIndex"
                                    :answered="
                                        form.answers[question.id] !== null
                                    "
                                />
                            </button>
                        </div>
                        <div
                            class="rounded-2xl bg-[#f8fbff] p-4 text-sm text-slate-600"
                        >
                            {{ answeredCount }} soal sudah dijawab,
                            {{
                                attempt.progress.total_questions - answeredCount
                            }}
                            soal masih kosong.
                        </div>
                    </CardContent>
                </Card>

                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-[#0f172a] text-white shadow-sm"
                >
                    <CardHeader>
                        <CardTitle>Kontrol sesi</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm text-slate-200">
                        <p>
                            Simpan jawaban kapan saja bila ingin menjaga progres
                            tetap aman.
                        </p>
                        <div
                            v-if="
                                remainingSeconds === 0 && attempt.timer.enabled
                            "
                            class="rounded-2xl border border-red-400/40 bg-red-500/10 p-4 text-red-100"
                        >
                            Waktu hitung mundur sudah habis. Review cepat lalu
                            kirim latihan ini.
                        </div>
                        <p
                            v-if="lastSavedLabel"
                            class="text-sm text-emerald-200"
                        >
                            {{ lastSavedLabel }}
                        </p>
                        <div class="flex flex-col gap-3">
                            <Button
                                type="button"
                                :disabled="form.processing"
                                class="rounded-2xl bg-white text-slate-950 hover:bg-slate-100"
                                @click="saveAnswers"
                            >
                                <Save class="mr-2 size-4" />
                                Simpan jawaban
                            </Button>
                            <Button
                                type="button"
                                :disabled="form.processing"
                                class="rounded-2xl bg-[#b91c1c] text-white hover:bg-[#991b1b]"
                                @click="submit"
                            >
                                <CheckCheck class="mr-2 size-4" />
                                Submit latihan
                            </Button>
                            <Button
                                as-child
                                variant="outline"
                                class="rounded-2xl border-white/20 bg-transparent text-white hover:bg-white/10"
                            >
                                <Link href="/practice"
                                    >Kembali ke latihan</Link
                                >
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div>
                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardHeader class="space-y-3">
                        <p class="text-sm font-medium text-[#b91c1c]">
                            Soal {{ currentQuestion.display_order }}
                            <span v-if="currentQuestion.code">
                                • {{ currentQuestion.code }}
                            </span>
                            <span v-if="currentQuestion.difficulty_label">
                                • {{ currentQuestion.difficulty_label }}
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

                        <div
                            class="flex flex-wrap items-center justify-between gap-3 pt-4"
                        >
                            <div class="flex gap-3">
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="rounded-2xl"
                                    :disabled="isFirstQuestion"
                                    @click="
                                        goToQuestion(activeQuestionIndex - 1)
                                    "
                                >
                                    <ChevronLeft class="mr-2 size-4" />
                                    Sebelumnya
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="rounded-2xl"
                                    :disabled="isLastQuestion"
                                    @click="
                                        goToQuestion(activeQuestionIndex + 1)
                                    "
                                >
                                    Berikutnya
                                    <ChevronRight class="ml-2 size-4" />
                                </Button>
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                class="rounded-2xl"
                                @click="form.answers[currentQuestion.id] = null"
                            >
                                Kosongkan jawaban
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </section>
    </div>
</template>

