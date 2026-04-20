<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookOpenText,
    Clock3,
    Layers3,
    Target,
} from 'lucide-vue-next';
import { computed } from 'vue';
import LatihanAnalyticsSnapshot from '@/components/practice/LatihanAnalyticsSnapshot.vue';
import LatihanRiwayatCard from '@/components/practice/LatihanRiwayatCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { LatihanAnalytics, LatihanRiwayatItem } from '@/types';

type DifficultyOption = {
    value: string;
    label: string;
    available_questions: number;
};

type BelajaringModulePreview = {
    id: number;
    title: string;
    slug: string;
    summary: string | null;
    estimated_minutes: number | null;
    level_label: string | null;
};

const props = defineProps<{
    category: {
        name: string | null;
        slug: string | null;
    };
    subtest: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        instruction: string | null;
        default_duration_minutes: number | null;
        analytics: LatihanAnalytics;
        availability: {
            all: number;
            easy: number;
            medium: number;
            hard: number;
        };
        difficulty_options: DifficultyOption[];
        question_count_presets: number[];
        timer_options: number[];
        config_defaults: {
            difficulty: string;
            question_count: number;
            timer_minutes: number | null;
        };
        in_progress_attempt: {
            id: number;
            started_at: string | null;
            answered_questions: number;
            total_questions: number;
        } | null;
    };
    learningModul: BelajaringModulePreview[];
    recentAttempts: LatihanRiwayatItem[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Latihan', href: '/practice' },
            { title: 'Subtest', href: '#' },
        ],
    },
});

const form = useForm({
    difficulty: props.subtest.config_defaults.difficulty,
    question_count: props.subtest.config_defaults.question_count,
    timer_minutes: props.subtest.config_defaults.timer_minutes as number | null,
});

const availableForSelectedDifficulty = computed(() => {
    return (
        props.subtest.difficulty_options.find(
            (option) => option.value === form.difficulty,
        )?.available_questions ?? 0
    );
});

const questionCountOptions = computed(() => {
    const base = props.subtest.question_count_presets.filter(
        (count) => count <= availableForSelectedDifficulty.value,
    );

    if (base.length === 0 && availableForSelectedDifficulty.value > 0) {
        return [availableForSelectedDifficulty.value];
    }

    if (
        availableForSelectedDifficulty.value > 0 &&
        !base.includes(availableForSelectedDifficulty.value)
    ) {
        return [...base, availableForSelectedDifficulty.value].sort(
            (first, second) => first - second,
        );
    }

    return base;
});

const selectedDifficultyLabel = computed(
    () =>
        props.subtest.difficulty_options.find(
            (option) => option.value === form.difficulty,
        )?.label ?? 'Semua Level',
);

function ensureQuestionCount() {
    if (!questionCountOptions.value.includes(form.question_count)) {
        form.question_count = questionCountOptions.value[0] ?? 0;
    }
}

ensureQuestionCount();

function submit() {
    ensureQuestionCount();
    form.post(`/practice/subtests/${props.subtest.slug}/attempts`);
}
</script>

<template>
    <Head :title="`Latihan ${subtest.name}`" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <section
            class="overflow-hidden rounded-[2rem] border border-[#dbe6ee] bg-[radial-gradient(circle_at_top_right,_rgba(185,28,28,0.12),_transparent_28%),linear-gradient(135deg,_#f8fbff_0%,_#eef5fb_48%,_#ffffff_100%)] p-6 shadow-sm"
        >
            <div class="grid gap-6 xl:grid-cols-[1.1fr,0.9fr] xl:items-end">
                <div class="space-y-4">
                    <p class="text-sm font-medium text-[#b91c1c]">
                        {{ category.name }} / {{ subtest.name }}
                    </p>
                    <div>
                        <h1
                            class="font-display text-4xl font-bold tracking-tight text-slate-950"
                        >
                            Konfigurasi latihan terstruktur
                        </h1>
                        <p
                            class="mt-3 max-w-3xl text-base leading-7 text-slate-600"
                        >
                            {{
                                subtest.description ||
                                'Atur difficulty, jumlah soal, dan timer sesuai target belajar saat ini.'
                            }}
                        </p>
                    </div>
                    <div
                        class="flex flex-wrap gap-2 text-xs font-medium text-slate-500"
                    >
                        <span
                            class="rounded-full bg-white px-3 py-1 ring-1 ring-[#e4ebf1]"
                        >
                            {{ subtest.availability.all }} soal published siap
                            practice
                        </span>
                        <span
                            class="rounded-full bg-white px-3 py-1 ring-1 ring-[#e4ebf1]"
                        >
                            Easy {{ subtest.availability.easy }}
                        </span>
                        <span
                            class="rounded-full bg-white px-3 py-1 ring-1 ring-[#e4ebf1]"
                        >
                            Medium {{ subtest.availability.medium }}
                        </span>
                        <span
                            class="rounded-full bg-white px-3 py-1 ring-1 ring-[#e4ebf1]"
                        >
                            Hard {{ subtest.availability.hard }}
                        </span>
                    </div>
                </div>

                <LatihanAnalyticsSnapshot
                    :analytics="subtest.analytics"
                    compact
                />
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.05fr,0.95fr]">
            <div class="space-y-5">
                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Layers3 class="size-5 text-[#b91c1c]" />
                            Atur sesi practice
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form class="space-y-6" @submit.prevent="submit">
                            <div class="grid gap-6 lg:grid-cols-2">
                                <div class="space-y-2">
                                    <label
                                        class="text-sm font-medium text-slate-700"
                                    >
                                        Difficulty
                                    </label>
                                    <select
                                        v-model="form.difficulty"
                                        class="flex h-11 w-full rounded-2xl border border-input bg-white px-4 text-sm shadow-xs outline-none"
                                        @change="ensureQuestionCount"
                                    >
                                        <option
                                            v-for="option in subtest.difficulty_options"
                                            :key="option.value"
                                            :value="option.value"
                                            :disabled="
                                                option.available_questions === 0
                                            "
                                        >
                                            {{ option.label }} ({{
                                                option.available_questions
                                            }}
                                            soal)
                                        </option>
                                    </select>
                                    <p class="text-sm text-slate-500">
                                        Saat ini kamu memilih
                                        {{ selectedDifficultyLabel }}.
                                    </p>
                                    <p
                                        v-if="form.errors.difficulty"
                                        class="text-sm text-[#b91c1c]"
                                    >
                                        {{ form.errors.difficulty }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <label
                                        class="text-sm font-medium text-slate-700"
                                    >
                                        Jumlah soal
                                    </label>
                                    <select
                                        v-model="form.question_count"
                                        class="flex h-11 w-full rounded-2xl border border-input bg-white px-4 text-sm shadow-xs outline-none"
                                    >
                                        <option
                                            v-for="count in questionCountOptions"
                                            :key="count"
                                            :value="count"
                                        >
                                            {{ count }} soal
                                        </option>
                                    </select>
                                    <p class="text-sm text-slate-500">
                                        Maksimal
                                        {{
                                            availableForSelectedDifficulty
                                        }}
                                        soal tersedia untuk filter ini.
                                    </p>
                                    <p
                                        v-if="form.errors.question_count"
                                        class="text-sm text-[#b91c1c]"
                                    >
                                        {{ form.errors.question_count }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="space-y-3 rounded-[1.5rem] border border-[#e7edf2] bg-[#fbfdff] p-5"
                            >
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <div>
                                        <p
                                            class="text-sm font-medium text-slate-900"
                                        >
                                            Timer opsional
                                        </p>
                                        <p class="text-sm text-slate-500">
                                            Gunakan timer bila ingin membangun
                                            ritme kerja yang lebih formal.
                                        </p>
                                    </div>
                                    <label
                                        class="inline-flex cursor-pointer items-center gap-3 text-sm font-medium text-slate-700"
                                    >
                                        <input
                                            :checked="
                                                form.timer_minutes !== null
                                            "
                                            type="checkbox"
                                            class="size-4 rounded border-slate-300 text-[#b91c1c] focus:ring-[#b91c1c]"
                                            @change="
                                                form.timer_minutes =
                                                    form.timer_minutes === null
                                                        ? subtest.default_duration_minutes ||
                                                          subtest
                                                              .timer_options[0] ||
                                                          10
                                                        : null
                                            "
                                        />
                                        Aktifkan timer
                                    </label>
                                </div>

                                <select
                                    v-model="form.timer_minutes"
                                    :disabled="form.timer_minutes === null"
                                    class="flex h-11 w-full rounded-2xl border border-input bg-white px-4 text-sm shadow-xs outline-none disabled:cursor-not-allowed disabled:bg-slate-100"
                                >
                                    <option
                                        v-for="minutes in subtest.timer_options"
                                        :key="minutes"
                                        :value="minutes"
                                    >
                                        {{ minutes }} menit
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.timer_minutes"
                                    class="text-sm text-[#b91c1c]"
                                >
                                    {{ form.errors.timer_minutes }}
                                </p>
                            </div>

                            <div
                                class="rounded-[1.5rem] border border-[#e7edf2] bg-white p-5"
                            >
                                <p
                                    class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase"
                                >
                                    Ringkasan sesi
                                </p>
                                <div class="mt-4 grid gap-4 sm:grid-cols-3">
                                    <div>
                                        <p class="text-sm text-slate-500">
                                            Difficulty
                                        </p>
                                        <p
                                            class="mt-1 font-semibold text-slate-950"
                                        >
                                            {{ selectedDifficultyLabel }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-slate-500">
                                            Jumlah soal
                                        </p>
                                        <p
                                            class="mt-1 font-semibold text-slate-950"
                                        >
                                            {{ form.question_count }} soal
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-slate-500">
                                            Timer
                                        </p>
                                        <p
                                            class="mt-1 font-semibold text-slate-950"
                                        >
                                            {{
                                                form.timer_minutes
                                                    ? `${form.timer_minutes} menit`
                                                    : 'Tanpa timer'
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <Button
                                    :disabled="
                                        form.processing ||
                                        questionCountOptions.length === 0
                                    "
                                    class="rounded-2xl bg-[#0f172a] text-white hover:bg-[#111827]"
                                >
                                    Mulai sesi latihan
                                </Button>
                                <Button
                                    as-child
                                    variant="outline"
                                    class="rounded-2xl"
                                >
                                    <Link href="/practice"
                                        >Kembali ke daftar</Link
                                    >
                                </Button>
                                <Button
                                    v-if="subtest.in_progress_attempt"
                                    as-child
                                    variant="outline"
                                    class="rounded-2xl"
                                >
                                    <Link
                                        :href="`/practice/attempts/${subtest.in_progress_attempt.id}`"
                                    >
                                        Lanjutkan sesi aktif
                                    </Link>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardHeader>
                        <CardTitle>Instruksi subtes</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div
                            class="text-sm leading-7 whitespace-pre-line text-slate-600"
                        >
                            {{
                                subtest.instruction ||
                                'Instruksi subtes belum diisi di CMS.'
                            }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-5">
                <Card
                    v-if="subtest.in_progress_attempt"
                    class="rounded-[1.75rem] border-amber-200 bg-amber-50 shadow-sm"
                >
                    <CardHeader>
                        <CardTitle class="text-amber-900"
                            >Sesi practice aktif</CardTitle
                        >
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm text-amber-900">
                        <p>
                            Kamu masih punya sesi yang belum disubmit untuk
                            subtes ini.
                        </p>
                        <p>
                            Progres:
                            {{
                                subtest.in_progress_attempt.answered_questions
                            }}
                            /
                            {{
                                subtest.in_progress_attempt.total_questions
                            }}
                            soal.
                        </p>
                        <Button
                            as-child
                            class="rounded-2xl bg-amber-900 text-white hover:bg-amber-950"
                        >
                            <Link
                                :href="`/practice/attempts/${subtest.in_progress_attempt.id}`"
                            >
                                Lanjutkan sesi
                            </Link>
                        </Button>
                    </CardContent>
                </Card>

                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <BookOpenText class="size-5 text-[#b91c1c]" />
                            Materi penguat sebelum practice
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            v-for="module in learningModul"
                            :key="module.id"
                            class="rounded-[1.35rem] border border-[#e7edf2] bg-[#fbfdff] p-4"
                        >
                            <div class="space-y-2">
                                <p class="font-semibold text-slate-950">
                                    {{ module.title }}
                                </p>
                                <p class="text-sm leading-6 text-slate-600">
                                    {{ module.summary }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ module.level_label }} •
                                    {{ module.estimated_minutes ?? 15 }} menit
                                </p>
                            </div>
                            <Link
                                :href="`/learn/modules/${module.slug}`"
                                class="mt-3 inline-flex text-sm font-medium text-[#0f172a] hover:text-[#b91c1c]"
                            >
                                Review modul
                            </Link>
                        </div>
                        <p
                            v-if="learningModul.length === 0"
                            class="text-sm text-slate-500"
                        >
                            Belum ada modul published yang terhubung dengan
                            subtes ini.
                        </p>
                    </CardContent>
                </Card>

                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Target class="size-5 text-[#b91c1c]" />
                            Histori practice subtes ini
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <LatihanRiwayatCard
                            v-for="attempt in recentAttempts"
                            :key="attempt.id"
                            :attempt="attempt"
                        />
                        <p
                            v-if="recentAttempts.length === 0"
                            class="text-sm text-slate-500"
                        >
                            Belum ada histori practice untuk subtes ini.
                        </p>
                    </CardContent>
                </Card>

                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-[#0f172a] text-white shadow-sm"
                >
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Clock3 class="size-5" />
                            Saran mulai
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3 text-sm text-slate-200">
                        <p>
                            Mulai dari difficulty yang masih terasa nyaman, lalu
                            naikkan tantangan setelah akurasi stabil.
                        </p>
                        <Button
                            as-child
                            variant="outline"
                            class="w-full justify-between rounded-2xl border-white/20 bg-transparent text-white hover:bg-white/10"
                        >
                            <Link href="/practice">
                                Kembali ke semua subtes
                                <ArrowRight class="size-4" />
                            </Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </section>
    </div>
</template>

