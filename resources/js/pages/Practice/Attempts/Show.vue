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
let autoSaveTimer: ReturnType<typeof setTimeout> | null = null;
const isSaving = ref(false);

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
    isSaving.value = true;
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
            isSaving.value = false;
        },
        onError: () => {
            isSaving.value = false;
        },
    });
}

function triggerAutoSave() {
    if (autoSaveTimer !== null) {
        clearTimeout(autoSaveTimer);
    }
    autoSaveTimer = setTimeout(() => {
        saveAnswers();
    }, 1500);
}

function submit() {
    form.post(`/practice/attempts/${props.attempt.id}/submit`);
}

// Auto-save saat jawaban berubah (debounce 1.5 detik)
watch(
    () => form.answers,
    () => {
        lastSavedLabel.value = null;
        triggerAutoSave();
    },
    { deep: true },
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
    if (autoSaveTimer !== null) {
        clearTimeout(autoSaveTimer);
    }
});
</script>

<template>
    <Head :title="`Latihan ${attempt.subtest.name || 'Sesi'}`" />

    <div class="flex flex-1 flex-col mx-auto w-full max-w-7xl px-3 sm:px-6 lg:px-8 py-3 sm:py-6 lg:py-8 min-h-screen">
        
        <!-- MICRO FLOATING STATUS BAR -->
        <header class="sticky top-4 z-40 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 rounded-[1.5rem] sm:rounded-[2rem] border border-border/40 bg-white/70 p-3 sm:p-4 shadow-sm backdrop-blur-md dark:border-white/10 dark:bg-slate-900/80 transition-all">
            <!-- Info Kiri Atas -->
            <div class="flex w-full sm:w-auto items-center justify-between sm:justify-start gap-4 px-2 sm:px-4">
                <div class="flex flex-col">
                    <p class="text-[0.65rem] font-bold tracking-widest text-[#b91c1c] dark:text-rose-400 uppercase">
                        {{ attempt.subtest.name }}
                    </p>
                    <h1 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span>Tingkat: {{ attempt.configuration.difficulty_label }}</span>
                        <span class="size-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                    </h1>
                </div>
                <!-- Sisa Soal Indikator (Mobile Only atau nyempil) -->
                <div class="flex items-center gap-2 rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:bg-white/5 dark:text-slate-300">
                    <CheckCheck class="size-3.5 text-emerald-600 dark:text-emerald-400" />
                    {{ answeredCount }} / {{ attempt.progress.total_questions }}
                </div>
            </div>

            <!-- Panel Timer & Kontrol (Kanan) -->
            <div class="flex w-full sm:w-auto items-center justify-between sm:justify-end gap-3 px-2 sm:px-2">
                <div v-if="attempt.timer.enabled" class="flex items-center gap-2 rounded-xl border border-indigo-100 bg-indigo-50/50 px-3 py-1.5 text-sm font-bold tracking-wide text-indigo-700 dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-300">
                    <div class="relative flex size-2.5 items-center justify-center">
                        <span v-if="remainingSeconds && remainingSeconds > 0" class="absolute inline-flex h-full w-full animate-ping rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex size-1.5 rounded-full bg-indigo-500"></span>
                    </div>
                    <Clock3 class="size-4" />
                    {{ formatDuration(remainingSeconds) }}
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        type="button"
                        :disabled="form.processing"
                        variant="ghost"
                        class="h-9 rounded-xl px-3 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300"
                        @click="saveAnswers"
                    >
                        <Save class="size-4 sm:mr-2" />
                        <span class="hidden sm:inline">Simpan Jawaban</span>
                    </Button>
                    <Button
                        type="button"
                        :disabled="form.processing"
                        class="h-9 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 px-4 text-white hover:from-emerald-700 hover:to-teal-600 shadow-md shadow-emerald-500/20 border-none transition-all"
                        @click="submit"
                    >
                        <CheckCheck class="size-4 sm:mr-2" />
                        <span class="hidden sm:inline">Selesai & Kumpulkan</span>
                    </Button>
                </div>
            </div>
            
            <!-- Alert Waktu Habis -->
            <div v-if="remainingSeconds === 0 && attempt.timer.enabled" class="absolute -bottom-14 left-0 right-0 mx-auto w-11/12 sm:w-[400px] rounded-xl border border-rose-500/30 bg-rose-500/10 p-2.5 text-center text-xs font-medium text-rose-600 dark:text-rose-400 backdrop-blur-md shadow-lg flex items-center justify-center gap-2 animate-in slide-in-from-top-2">
                ⏳ Waktu habis! Periksa jawaban kamu lalu kumpulkan.
            </div>
            <!-- Indikator Status Auto-Save -->
            <div class="absolute -bottom-10 left-0 right-0 mx-auto w-fit flex items-center justify-center">
                <transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 -translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-1"
                >
                    <div
                        v-if="isSaving"
                        class="flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400 bg-white/70 dark:bg-slate-900/70 backdrop-blur-md py-1 px-3 rounded-full border border-slate-200/60 dark:border-white/10 shadow-sm"
                    >
                        <span class="size-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                        Menyimpan jawaban...
                    </div>
                    <div
                        v-else-if="lastSavedLabel"
                        class="flex items-center gap-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-white/70 dark:bg-slate-900/70 backdrop-blur-md py-1 px-3 rounded-full border border-emerald-500/20 shadow-sm"
                    >
                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                        {{ lastSavedLabel }}
                    </div>
                </transition>
            </div>
        </header>

        <!-- LAYOUT GRID UTAMA -->
        <main class="grid gap-6 xl:grid-cols-[1fr_320px]">
            
            <!-- AREA KIRI: TAMPILAN SOAL (MOBILE AKAN DIATAS KARENA ORDER DIPUTAR) -->
            <!-- PANEL PERTANYAAN INTI -->
            <section class="space-y-6 lg:min-h-[600px] flex flex-col order-1">
                <Card class="flex-1 rounded-[2rem] border-border/50 bg-card shadow-sm transition-all dark:bg-card">
                    <CardHeader class="flex flex-row items-center justify-between border-b border-border/40 bg-slate-50/50 pb-4 pt-5 px-5 sm:px-7 dark:bg-slate-900/30">
                        <div class="space-y-0.5">
                            <p class="text-xs font-bold tracking-widest text-[#b91c1c] uppercase dark:text-rose-400 flex items-center gap-2">
                                Soal ke-{{ currentQuestion.display_order }}
                                <span v-if="currentQuestion.difficulty_label" class="line-clamp-1 bg-slate-200/50 text-slate-600 dark:bg-slate-800 dark:text-slate-400 px-2 rounded-md text-[0.60rem] lowercase font-semibold tracking-normal border border-slate-300/50 dark:border-slate-700/50">
                                    {{ currentQuestion.difficulty_label }}
                                </span>
                            </p>
                            <p v-if="currentQuestion.code" class="text-[0.65rem] text-slate-400 tracking-wider">
                                ID: {{ currentQuestion.code }}
                            </p>
                        </div>
                        <Button
                            type="button"
                            variant="ghost"
                            class="h-7 text-xs rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950 px-2 border-transparent"
                            @click="form.answers[currentQuestion.id] = null"
                        >
                            Batalkan Pilihan
                        </Button>
                    </CardHeader>
                    
                    <CardContent class="p-5 sm:p-7 sm:pt-8 flex flex-col h-full gap-8">
                        <div class="text-lg sm:text-xl leading-relaxed text-slate-800 dark:text-slate-100 font-medium whitespace-pre-line">
                            {{ currentQuestion.question_text }}
                        </div>
                        
                        <!-- JAWABAN INTERAKTIF -->
                        <div class="space-y-3 mt-auto">
                            <label
                                v-for="option in currentQuestion.options"
                                :key="option.id"
                                :class="[
                                    'group relative flex w-full cursor-pointer items-start gap-4 rounded-[1.5rem] border p-4 sm:p-5 transition-all duration-200 overflow-hidden',
                                    form.answers[currentQuestion.id] === option.id
                                        ? 'border-emerald-500 bg-emerald-50/30 shadow-[0_4px_20px_-4px_rgba(16,185,129,0.15)] dark:border-emerald-500/50 dark:bg-emerald-500/10'
                                        : 'border-border/60 bg-white/40 hover:border-slate-300/80 hover:bg-white/80 dark:bg-white/5 dark:hover:bg-white/10 dark:hover:border-white/20'
                                ]"
                            >
                                <!-- Indikator Pilihan (Custom Radio Dot) -->
                                <div class="flex h-6 items-center flex-none">
                                    <div 
                                        :class="[
                                            'peer relative flex size-5 items-center justify-center rounded-full border transition-all',
                                            form.answers[currentQuestion.id] === option.id
                                                ? 'border-emerald-500 bg-emerald-500 dark:border-emerald-400 dark:bg-emerald-400' 
                                                : 'border-slate-300 bg-transparent group-hover:border-slate-400 dark:border-slate-600 dark:group-hover:border-slate-400'
                                        ]"
                                    >
                                        <!-- Titik Tengah saat Terpilih -->
                                        <div 
                                            class="absolute size-2 rounded-full bg-white transition-transform duration-200"
                                            :class="form.answers[currentQuestion.id] === option.id ? 'scale-100' : 'scale-0'"
                                        ></div>
                                    </div>
                                    <!-- Input Asli (Disembunyikan) -->
                                    <input
                                        v-model="form.answers[currentQuestion.id]"
                                        :value="option.id"
                                        type="radio"
                                        class="absolute opacity-0 w-0 h-0"
                                    />
                                </div>
                                
                                <div class="flex flex-col gap-1 w-full">
                                    <span :class="[
                                        'text-sm sm:text-base leading-relaxed',
                                        form.answers[currentQuestion.id] === option.id
                                            ? 'text-emerald-900 font-medium dark:text-emerald-100'
                                            : 'text-slate-700 dark:text-slate-200'
                                    ]">
                                        <span class="mr-1.5 font-bold opacity-70">{{ option.option_key }}.</span>
                                        {{ option.option_text }}
                                    </span>
                                </div>
                                <div 
                                    v-if="form.answers[currentQuestion.id] === option.id" 
                                    class="absolute left-0 top-0 bottom-0 w-1.5 bg-emerald-500 rounded-l-[1.5rem]"
                                ></div>
                            </label>
                        </div>

                        <!-- Footer Navigasi Bawah Layar -->
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-border/40 mt-6">
                            <Button
                                type="button"
                                variant="outline"
                                class="rounded-xl h-11 px-5 border-border/60 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-30"
                                :disabled="isFirstQuestion"
                                @click="goToQuestion(activeQuestionIndex - 1)"
                            >
                                <ChevronLeft class="mr-1.5 size-4" />
                                Soal Sebelumnya
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                class="rounded-xl h-11 px-5 border-border/60 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-30"
                                :disabled="isLastQuestion"
                                @click="goToQuestion(activeQuestionIndex + 1)"
                            >
                                Soal Berikutnya
                                <ChevronRight class="ml-1.5 size-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </section>

            <!-- AREA KANAN: PANEL NAVIGASI SOAL & INFO (Desktop di samping Kanan, Mobile turun ke Bawah) -->
            <aside class="space-y-6 order-2 mb-20 lg:mb-0">
                <Card class="rounded-[2rem] border-[1.5px] border-border/60 bg-[#fefefe] shadow-sm xl:sticky xl:top-28 dark:bg-[#0c111d] overflow-hidden">
                    <CardHeader class="pb-4 pt-5 border-b border-border/40 bg-slate-50/50 dark:bg-slate-900/30 items-center justify-between flex-row">
                         <CardTitle class="text-[0.9rem] uppercase tracking-widest text-[#b91c1c] dark:text-rose-400 font-bold flex items-center gap-2">
                             📋 Daftar Soal
                         </CardTitle>
                    </CardHeader>
                    <CardContent class="p-5 sm:p-6 space-y-5">
                        <div class="grid grid-cols-5 sm:grid-cols-6 xl:grid-cols-5 gap-2.5 sm:gap-3">
                            <button
                                v-for="(question, index) in attempt.questions"
                                :key="question.id"
                                type="button"
                                @click="goToQuestion(index)"
                                class="focus:outline-none rounded-xl"
                            >
                                <PracticeQuestionPill
                                    :number="question.display_order"
                                    :active="index === activeQuestionIndex"
                                    :answered="form.answers[question.id] !== null"
                                />
                            </button>
                        </div>
                        <div class="rounded-xl bg-slate-100/80 p-4 text-xs font-medium text-slate-600 dark:bg-white/5 dark:text-slate-400 text-center flex items-center justify-center gap-2 border border-slate-200/50 dark:border-white/5">
                            <span>Tersisa <strong>{{ attempt.progress.total_questions - answeredCount }}</strong> soal belum dijawab.</span>
                        </div>
                        <div class="pt-4 border-t border-border/40 w-full">
                            <Button
                                as-child
                                variant="ghost"
                                class="w-full text-xs text-rose-500 hover:bg-rose-50 hover:text-rose-700 h-9 rounded-xl dark:hover:bg-rose-950 dark:hover:text-rose-400"
                            >
                               <Link href="/practice">← Keluar dari Latihan</Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </aside>
            
        </main>
    </div>
</template>

