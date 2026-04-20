<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookOpenText,
    CheckCircle2,
    CircleSlash,
    Clock3,
    Target,
    XCircle,
} from 'lucide-vue-next';
import PracticeHistoryCard from '@/components/practice/PracticeHistoryCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Latihan', href: '/practice' },
            { title: 'Result', href: '#' },
        ],
    },
});

defineProps<{
    attempt: {
        id: number;
        score_total: number;
        accuracy: number;
        correct_answers: number;
        wrong_answers: number;
        blank_answers: number;
        answered_questions: number;
        total_questions: number;
        duration_seconds: number;
        submitted_at: string | null;
        analysis_text: string | null;
        configuration: {
            difficulty: string;
            difficulty_label: string;
            question_count: number;
            timer_minutes: number | null;
            timer_enabled: boolean;
        };
    };
    category: {
        name: string | null;
        slug: string | null;
    };
    subtest: {
        name: string | null;
        slug: string | null;
    };
    recommendation: {
        headline: string;
        description: string;
        primary_action: string;
        next_difficulty?: string | null;
        related_module?: {
            title: string;
            slug: string;
        } | null;
    };
    related_modules: Array<{
        title: string;
        slug: string;
    }>;
    review: Array<{
        id: number;
        display_order: number;
        code: string | null;
        difficulty_label: string | null;
        question_text: string;
        selected_option: {
            option_key: string;
            option_text: string | null;
        } | null;
        correct_option: {
            option_key: string;
            option_text: string | null;
        } | null;
        is_correct: boolean | null;
        explanation_text: string | null;
    }>;
}>();

function formatDuration(totalSeconds: number) {
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    return `${minutes}m ${String(seconds).padStart(2, '0')}s`;
}
</script>

<template>
    <Head :title="`Hasil Latihan ${subtest.name || ''}`" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <section
            class="overflow-hidden rounded-[2rem] border border-[#dbe6ee] bg-[radial-gradient(circle_at_top_right,_rgba(15,23,42,0.12),_transparent_30%),linear-gradient(135deg,_#f8fbff_0%,_#eef5fb_48%,_#ffffff_100%)] p-6 shadow-sm"
        >
            <div class="grid gap-6 xl:grid-cols-[1.05fr,0.95fr] xl:items-end">
                <div class="space-y-4">
                    <p class="text-sm font-medium text-[#b91c1c]">
                        {{ category.name }} / {{ subtest.name }}
                    </p>
                    <div>
                        <h1
                            class="font-display text-4xl font-bold tracking-tight text-slate-950"
                        >
                            Hasil latihan sudah siap direview.
                        </h1>
                        <p
                            class="mt-3 max-w-3xl text-base leading-7 text-slate-600"
                        >
                            {{ attempt.analysis_text }}
                        </p>
                    </div>
                    <div
                        class="flex flex-wrap gap-2 text-xs font-medium text-slate-500"
                    >
                        <span
                            class="rounded-full bg-white px-3 py-1 ring-1 ring-[#e4ebf1]"
                        >
                            {{ attempt.configuration.difficulty_label }}
                        </span>
                        <span
                            class="rounded-full bg-white px-3 py-1 ring-1 ring-[#e4ebf1]"
                        >
                            {{ attempt.configuration.question_count }} soal
                        </span>
                        <span
                            v-if="attempt.configuration.timer_minutes"
                            class="rounded-full bg-white px-3 py-1 ring-1 ring-[#e4ebf1]"
                        >
                            Timer
                            {{ attempt.configuration.timer_minutes }} menit
                        </span>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div
                        class="rounded-[1.6rem] border border-[#dfe8ef] bg-white/90 p-5"
                    >
                        <p class="text-sm text-slate-500">Skor</p>
                        <p class="mt-2 text-4xl font-semibold text-slate-950">
                            {{ attempt.score_total }}
                        </p>
                    </div>
                    <div
                        class="rounded-[1.6rem] border border-[#dfe8ef] bg-white/90 p-5"
                    >
                        <p class="text-sm text-slate-500">Akurasi</p>
                        <p class="mt-2 text-4xl font-semibold text-slate-950">
                            {{ attempt.accuracy }}%
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.1fr,0.9fr]">
            <div class="space-y-5">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <Card
                        class="rounded-[1.6rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                    >
                        <CardContent class="space-y-2 p-5">
                            <p
                                class="flex items-center gap-2 text-sm text-slate-500"
                            >
                                <CheckCircle2 class="size-4 text-emerald-600" />
                                Benar
                            </p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ attempt.correct_answers }}
                            </p>
                        </CardContent>
                    </Card>
                    <Card
                        class="rounded-[1.6rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                    >
                        <CardContent class="space-y-2 p-5">
                            <p
                                class="flex items-center gap-2 text-sm text-slate-500"
                            >
                                <XCircle class="size-4 text-[#b91c1c]" />
                                Salah
                            </p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ attempt.wrong_answers }}
                            </p>
                        </CardContent>
                    </Card>
                    <Card
                        class="rounded-[1.6rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                    >
                        <CardContent class="space-y-2 p-5">
                            <p
                                class="flex items-center gap-2 text-sm text-slate-500"
                            >
                                <CircleSlash class="size-4 text-slate-500" />
                                Kosong
                            </p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ attempt.blank_answers }}
                            </p>
                        </CardContent>
                    </Card>
                    <Card
                        class="rounded-[1.6rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                    >
                        <CardContent class="space-y-2 p-5">
                            <p
                                class="flex items-center gap-2 text-sm text-slate-500"
                            >
                                <Clock3 class="size-4 text-[#b91c1c]" />
                                Durasi
                            </p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ formatDuration(attempt.duration_seconds) }}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardHeader>
                        <CardTitle>Review jawaban dan pembahasan</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <div
                            v-for="item in review"
                            :key="item.id"
                            class="rounded-[1.5rem] border border-[#e7edf2] bg-[#fbfdff] p-5"
                        >
                            <div class="space-y-3">
                                <div
                                    class="flex flex-wrap items-center gap-2 text-sm"
                                >
                                    <span class="font-semibold text-[#b91c1c]">
                                        Soal {{ item.display_order }}
                                    </span>
                                    <span
                                        v-if="item.code"
                                        class="text-slate-500"
                                    >
                                        • {{ item.code }}
                                    </span>
                                    <span
                                        v-if="item.difficulty_label"
                                        class="rounded-full bg-white px-2 py-1 text-xs text-slate-500 ring-1 ring-[#e5edf3]"
                                    >
                                        {{ item.difficulty_label }}
                                    </span>
                                </div>
                                <p class="text-base leading-7 text-slate-800">
                                    {{ item.question_text }}
                                </p>
                            </div>

                            <div class="mt-4 grid gap-3 lg:grid-cols-2">
                                <div
                                    class="rounded-2xl p-4"
                                    :class="
                                        item.is_correct
                                            ? 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200'
                                            : 'bg-rose-50 text-rose-800 ring-1 ring-rose-200'
                                    "
                                >
                                    <p
                                        class="text-xs font-semibold tracking-[0.14em] uppercase"
                                    >
                                        Jawaban kamu
                                    </p>
                                    <p class="mt-2 text-sm leading-6">
                                        <template v-if="item.selected_option">
                                            {{
                                                item.selected_option.option_key
                                            }}.
                                            {{
                                                item.selected_option.option_text
                                            }}
                                        </template>
                                        <template v-else>
                                            Tidak dijawab
                                        </template>
                                    </p>
                                </div>
                                <div
                                    class="rounded-2xl bg-white p-4 ring-1 ring-[#e6edf3]"
                                >
                                    <p
                                        class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase"
                                    >
                                        Jawaban benar
                                    </p>
                                    <p
                                        class="mt-2 text-sm leading-6 text-slate-700"
                                    >
                                        <template v-if="item.correct_option">
                                            {{
                                                item.correct_option.option_key
                                            }}.
                                            {{
                                                item.correct_option.option_text
                                            }}
                                        </template>
                                        <template v-else>
                                            Belum tersedia
                                        </template>
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="item.explanation_text"
                                class="mt-4 rounded-2xl bg-[#fff8f4] p-4 text-sm leading-6 text-slate-700"
                            >
                                <span class="font-semibold text-slate-950">
                                    Pembahasan:
                                </span>
                                {{ item.explanation_text }}
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-5">
                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-[#0f172a] text-white shadow-sm"
                >
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Target class="size-5" />
                            Langkah berikutnya
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm text-slate-200">
                        <p class="font-semibold text-white">
                            {{ recommendation.headline }}
                        </p>
                        <p>{{ recommendation.description }}</p>
                        <p v-if="recommendation.next_difficulty">
                            Tantangan berikutnya:
                            {{ recommendation.next_difficulty }}
                        </p>

                        <div class="flex flex-col gap-3">
                            <Button
                                as-child
                                class="rounded-2xl bg-white text-slate-950 hover:bg-slate-100"
                            >
                                <Link
                                    :href="`/practice/subtests/${subtest.slug}`"
                                >
                                    Mulai practice baru
                                </Link>
                            </Button>
                            <Button
                                v-if="recommendation.related_module"
                                as-child
                                variant="outline"
                                class="rounded-2xl border-white/20 bg-transparent text-white hover:bg-white/10"
                            >
                                <Link
                                    :href="`/learn/modules/${recommendation.related_module.slug}`"
                                >
                                    Review
                                    {{ recommendation.related_module.title }}
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <BookOpenText class="size-5 text-[#b91c1c]" />
                            Materi terkait
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            v-for="module in related_modules"
                            :key="module.slug"
                            class="rounded-[1.35rem] border border-[#e7edf2] bg-[#fbfdff] p-4"
                        >
                            <p class="font-semibold text-slate-950">
                                {{ module.title }}
                            </p>
                            <Link
                                :href="`/learn/modules/${module.slug}`"
                                class="mt-3 inline-flex text-sm font-medium text-[#0f172a] hover:text-[#b91c1c]"
                            >
                                Buka modul
                            </Link>
                        </div>
                        <p
                            v-if="related_modules.length === 0"
                            class="text-sm text-slate-500"
                        >
                            Belum ada modul related yang published.
                        </p>
                    </CardContent>
                </Card>

                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardHeader>
                        <CardTitle>Ringkasan histori</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <PracticeHistoryCard
                            :attempt="{
                                id: attempt.id,
                                subtest,
                                score_total: attempt.score_total,
                                accuracy: attempt.accuracy,
                                correct_answers: attempt.correct_answers,
                                wrong_answers: attempt.wrong_answers,
                                blank_answers: attempt.blank_answers,
                                duration_seconds: attempt.duration_seconds,
                                submitted_at: attempt.submitted_at,
                                configuration: {
                                    difficulty_label:
                                        attempt.configuration.difficulty_label,
                                    question_count:
                                        attempt.configuration.question_count,
                                    timer_minutes:
                                        attempt.configuration.timer_minutes,
                                },
                            }"
                        />
                    </CardContent>
                </Card>

                <Button
                    as-child
                    variant="outline"
                    class="w-full justify-between rounded-2xl"
                >
                    <Link href="/practice">
                        Kembali ke daftar practice
                        <ArrowRight class="size-4" />
                    </Link>
                </Button>
            </div>
        </section>
    </div>
</template>

