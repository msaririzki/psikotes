<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    CheckCircle2,
    CircleSlash,
    Clock3,
    Flag,
    Layers3,
    XCircle,
} from 'lucide-vue-next';
import SimulationHistoryCard from '@/components/simulation/SimulationHistoryCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Simulasi', href: '/simulations' },
            { title: 'Hasil', href: '#' },
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
    };
    simulationPackage: {
        id?: number;
        title?: string | null;
        slug?: string | null;
        duration_minutes?: number | null;
        question_count?: number | null;
    };
    recommendation: {
        headline: string;
        description: string;
    };
    subtest_breakdown: Array<{
        subtest_name: string;
        total_questions: number;
        correct_answers: number;
        wrong_answers: number;
        blank_answers: number;
    }>;
    review: Array<{
        id: number;
        display_order: number;
        section_name: string | null;
        code: string | null;
        difficulty_label: string | null;
        question_text: string;
        question_image: string | null;
        selected_option: {
            option_key: string;
            option_text: string | null;
        } | null;
        correct_option: {
            option_key: string;
            option_text: string | null;
        } | null;
        is_correct: boolean | null;
        is_flagged: boolean;
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
    <Head :title="`Hasil Simulasi ${simulationPackage.title || ''}`" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <section
            class="overflow-hidden rounded-[2rem] border border-[#dbe6ee] bg-[radial-gradient(circle_at_top_right,_rgba(15,23,42,0.12),_transparent_30%),linear-gradient(135deg,_#f8fbff_0%,_#eef5fb_48%,_#ffffff_100%)] p-6 shadow-sm"
        >
            <div class="grid gap-6 xl:grid-cols-[1.05fr,0.95fr] xl:items-end">
                <div class="space-y-4">
                    <p class="text-sm font-medium text-[#b91c1c]">
                        {{ simulationPackage.title }}
                    </p>
                    <div>
                        <h1 class="font-display text-4xl font-bold tracking-tight text-slate-950">
                            Hasil simulasi sudah siap dilihat.
                        </h1>
                        <p class="mt-3 max-w-3xl text-base leading-7 text-slate-600">
                            {{ attempt.analysis_text }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-[1.6rem] border border-[#dfe8ef] bg-white/90 p-5">
                        <p class="text-sm text-slate-500">Nilai</p>
                        <p class="mt-2 text-4xl font-semibold text-slate-950">
                            {{ attempt.score_total }}
                        </p>
                    </div>
                    <div class="rounded-[1.6rem] border border-[#dfe8ef] bg-white/90 p-5">
                        <p class="text-sm text-slate-500">Ketepatan</p>
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
                    <Card class="rounded-[1.6rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                        <CardContent class="space-y-2 p-5">
                            <p class="flex items-center gap-2 text-sm text-slate-500">
                                <CheckCircle2 class="size-4 text-emerald-600" />
                                Benar
                            </p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ attempt.correct_answers }}
                            </p>
                        </CardContent>
                    </Card>
                    <Card class="rounded-[1.6rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                        <CardContent class="space-y-2 p-5">
                            <p class="flex items-center gap-2 text-sm text-slate-500">
                                <XCircle class="size-4 text-[#b91c1c]" />
                                Salah
                            </p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ attempt.wrong_answers }}
                            </p>
                        </CardContent>
                    </Card>
                    <Card class="rounded-[1.6rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                        <CardContent class="space-y-2 p-5">
                            <p class="flex items-center gap-2 text-sm text-slate-500">
                                <CircleSlash class="size-4 text-slate-500" />
                                Kosong
                            </p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ attempt.blank_answers }}
                            </p>
                        </CardContent>
                    </Card>
                    <Card class="rounded-[1.6rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                        <CardContent class="space-y-2 p-5">
                            <p class="flex items-center gap-2 text-sm text-slate-500">
                                <Clock3 class="size-4 text-[#b91c1c]" />
                                Durasi
                            </p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ formatDuration(attempt.duration_seconds) }}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Layers3 class="size-5 text-[#b91c1c]" />
                            Hasil per bagian soal
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-4 md:grid-cols-2">
                        <div
                            v-for="item in subtest_breakdown"
                            :key="item.subtest_name"
                            class="rounded-[1.4rem] border border-[#e7edf2] bg-[#fbfdff] p-4"
                        >
                            <p class="font-semibold text-slate-950">
                                {{ item.subtest_name }}
                            </p>
                            <div class="mt-3 grid gap-3 text-sm text-slate-600 sm:grid-cols-2">
                                <div>
                                    <p class="text-slate-500">Total soal</p>
                                    <p class="mt-1 font-semibold text-slate-950">
                                        {{ item.total_questions }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Benar</p>
                                    <p class="mt-1 font-semibold text-slate-950">
                                        {{ item.correct_answers }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Salah</p>
                                    <p class="mt-1 font-semibold text-slate-950">
                                        {{ item.wrong_answers }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Kosong</p>
                                    <p class="mt-1 font-semibold text-slate-950">
                                        {{ item.blank_answers }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle>Lihat jawaban simulasi</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <div
                            v-for="item in review"
                            :key="item.id"
                            class="rounded-[1.5rem] border border-[#e7edf2] bg-[#fbfdff] p-5"
                        >
                            <div class="space-y-3">
                                <div class="flex flex-wrap items-center gap-2 text-sm">
                                    <span class="font-semibold text-[#b91c1c]">
                                        Soal {{ item.display_order }}
                                    </span>
                                    <span v-if="item.section_name" class="text-slate-500">
                                        - {{ item.section_name }}
                                    </span>
                                    <span v-if="item.code" class="text-slate-500">
                                        - {{ item.code }}
                                    </span>
                                    <span
                                        v-if="item.difficulty_label"
                                        class="rounded-full bg-white px-2 py-1 text-xs text-slate-500 ring-1 ring-[#e5edf3]"
                                    >
                                        {{ item.difficulty_label }}
                                    </span>
                                    <span
                                        v-if="item.is_flagged"
                                        class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-700"
                                    >
                                        <Flag class="size-3" />
                                        Ditandai ragu
                                    </span>
                                </div>
                                <p class="text-base leading-7 text-slate-800">
                                    {{ item.question_text }}
                                </p>
                                <div
                                    v-if="item.question_image"
                                    class="overflow-hidden rounded-2xl border border-[#e7edf2] bg-white p-3"
                                >
                                    <img
                                        :src="item.question_image"
                                        :alt="`Gambar soal ${item.display_order}`"
                                        class="max-h-[38rem] w-full rounded-xl object-contain"
                                    />
                                </div>
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
                                    <p class="text-xs font-semibold tracking-[0.14em] uppercase">
                                        Jawaban kamu
                                    </p>
                                    <p class="mt-2 text-sm leading-6">
                                        <template v-if="item.selected_option">
                                            {{ item.selected_option.option_key }}.
                                            {{ item.selected_option.option_text }}
                                        </template>
                                        <template v-else> Tidak dijawab </template>
                                    </p>
                                </div>
                                <div class="rounded-2xl bg-white p-4 ring-1 ring-[#e6edf3]">
                                    <p class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase">
                                        Jawaban benar
                                    </p>
                                    <p class="mt-2 text-sm leading-6 text-slate-700">
                                        <template v-if="item.correct_option">
                                            {{ item.correct_option.option_key }}.
                                            {{ item.correct_option.option_text }}
                                        </template>
                                        <template v-else> Belum tersedia </template>
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
                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-[#0f172a] text-white shadow-sm">
                    <CardHeader>
                        <CardTitle>Rekomendasi berikutnya</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm text-slate-200">
                        <p class="font-semibold text-white">
                            {{ recommendation.headline }}
                        </p>
                        <p>{{ recommendation.description }}</p>
                        <div class="flex flex-col gap-3">
                            <Button
                                as-child
                                class="rounded-2xl bg-white text-slate-950 hover:bg-slate-100"
                            >
                                <Link href="/simulations">
                                    Coba paket simulasi lain
                                </Link>
                            </Button>
                            <Button
                                as-child
                                variant="outline"
                                class="rounded-2xl border-white/20 bg-transparent text-white hover:bg-white/10"
                            >
                                <Link href="/practice">
                                    Kembali ke latihan
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle>Ringkasan pengerjaan</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <SimulationHistoryCard
                            :attempt="{
                                id: attempt.id,
                                package_title: simulationPackage.title ?? null,
                                score_total: attempt.score_total,
                                accuracy: attempt.accuracy,
                                correct_answers: attempt.correct_answers,
                                wrong_answers: attempt.wrong_answers,
                                blank_answers: attempt.blank_answers,
                                duration_seconds: attempt.duration_seconds,
                                submitted_at: attempt.submitted_at,
                            }"
                        />
                    </CardContent>
                </Card>

                <Button as-child variant="outline" class="w-full justify-between rounded-2xl">
                    <Link href="/simulations">
                        Kembali ke daftar simulasi
                        <ArrowRight class="size-4" />
                    </Link>
                </Button>
            </div>
        </section>
    </div>
</template>

