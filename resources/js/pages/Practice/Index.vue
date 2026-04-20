<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookCheck,
    Brain,
    Clock3,
    Layers3,
    Sparkles,
} from 'lucide-vue-next';
import LatihanAnalyticsSnapshot from '@/components/practice/LatihanAnalyticsSnapshot.vue';
import LatihanRiwayatCard from '@/components/practice/LatihanRiwayatCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type {
    LatihanAnalytics,
    LatihanRiwayatItem,
    LatihanSummary,
} from '@/types';

type LatihanSubtestCard = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    instruction_excerpt: string | null;
    available_questions: number;
    difficulty_breakdown: {
        easy: number;
        medium: number;
        hard: number;
    };
    learning_modules: Array<{
        title: string;
        slug: string;
    }>;
    analytics: LatihanAnalytics;
    in_progress_attempt: {
        id: number;
        answered_questions: number;
        total_questions: number;
    } | null;
};

type LatihanCategoryCard = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    subtests: LatihanSubtestCard[];
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Latihan', href: '/practice' },
        ],
    },
});

defineProps<{
    summary: LatihanSummary;
    categories: LatihanCategoryCard[];
    recentAttempts: LatihanRiwayatItem[];
}>();
</script>

<template>
    <Head title="Latihan" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <section
            class="overflow-hidden rounded-[2rem] border border-[#dbe6ee] bg-[radial-gradient(circle_at_top_right,_rgba(185,28,28,0.14),_transparent_30%),linear-gradient(135deg,_#f8fbff_0%,_#eef5fb_42%,_#ffffff_100%)] p-6 shadow-sm"
        >
            <div class="grid gap-6 xl:grid-cols-[1.15fr,0.85fr] xl:items-end">
                <div class="space-y-4">
                    <div
                        class="inline-flex items-center rounded-full bg-[#0f172a] px-4 py-2 text-xs font-semibold tracking-[0.18em] text-white uppercase"
                    >
                        Mode Latihan
                    </div>
                    <div>
                        <h1
                            class="font-display text-4xl font-bold tracking-tight text-slate-950"
                        >
                            Latihan terstruktur per subtes, dengan hasil yang
                            bisa direview.
                        </h1>
                        <p
                            class="mt-3 max-w-3xl text-base leading-7 text-slate-600"
                        >
                            Pilih subtes, tentukan tingkat kesulitan dan jumlah soal,
                            lalu kerjakan sesi latihan yang lebih formal dari
                            mini quiz. Histori, skor, dan pembahasan tetap
                            tersimpan rapi.
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <Card
                        class="rounded-[1.75rem] border-[#dfe8ef] bg-white/90"
                    >
                        <CardContent class="space-y-2 p-5">
                            <p class="text-sm text-slate-500">
                                Subtes siap latihan
                            </p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ summary.subtests }}
                            </p>
                        </CardContent>
                    </Card>
                    <Card
                        class="rounded-[1.75rem] border-[#dfe8ef] bg-white/90"
                    >
                        <CardContent class="space-y-2 p-5">
                            <p class="text-sm text-slate-500">Total sesi</p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ summary.practice_attempts }}
                            </p>
                        </CardContent>
                    </Card>
                    <Card
                        class="rounded-[1.75rem] border-[#dfe8ef] bg-white/90"
                    >
                        <CardContent class="space-y-2 p-5">
                            <p class="text-sm text-slate-500">Skor terbaik</p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ summary.best_score ?? 'Belum ada' }}
                            </p>
                        </CardContent>
                    </Card>
                    <Card
                        class="rounded-[1.75rem] border-[#dfe8ef] bg-white/90"
                    >
                        <CardContent class="space-y-2 p-5">
                            <p class="text-sm text-slate-500">Sesi berjalan</p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ summary.in_progress_sessions }}
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.15fr,0.85fr]">
            <div class="space-y-5">
                <div class="flex items-center gap-3">
                    <Layers3 class="size-5 text-[#b91c1c]" />
                    <h2
                        class="font-display text-2xl font-bold tracking-tight text-slate-950"
                    >
                        Pilih jalur latihan
                    </h2>
                </div>

                <Card
                    v-for="category in categories"
                    :key="category.id"
                    class="rounded-[1.9rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardContent class="space-y-6 p-6">
                        <div class="space-y-2">
                            <p
                                class="font-display text-3xl font-bold tracking-tight text-slate-950"
                            >
                                {{ category.name }}
                            </p>
                            <p
                                class="max-w-3xl text-sm leading-6 text-slate-600"
                            >
                                {{ category.description }}
                            </p>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-2">
                            <div
                                v-for="subtest in category.subtests"
                                :key="subtest.id"
                                class="rounded-[1.6rem] border border-[#e7edf2] bg-[#fbfdff] p-5"
                            >
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div class="space-y-2">
                                        <p
                                            class="text-xl font-semibold text-slate-950"
                                        >
                                            {{ subtest.name }}
                                        </p>
                                        <p
                                            class="text-sm leading-6 text-slate-600"
                                        >
                                            {{
                                                subtest.description ||
                                                subtest.instruction_excerpt
                                            }}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-[#e4ebf1]"
                                    >
                                        {{ subtest.available_questions }} soal
                                    </div>
                                </div>

                                <div
                                    class="mt-4 flex flex-wrap gap-2 text-xs font-medium text-slate-500"
                                >
                                    <span
                                        class="rounded-full bg-white px-3 py-1 ring-1 ring-[#e4ebf1]"
                                    >
                                        Easy
                                        {{ subtest.difficulty_breakdown.easy }}
                                    </span>
                                    <span
                                        class="rounded-full bg-white px-3 py-1 ring-1 ring-[#e4ebf1]"
                                    >
                                        Medium
                                        {{
                                            subtest.difficulty_breakdown.medium
                                        }}
                                    </span>
                                    <span
                                        class="rounded-full bg-white px-3 py-1 ring-1 ring-[#e4ebf1]"
                                    >
                                        Hard
                                        {{ subtest.difficulty_breakdown.hard }}
                                    </span>
                                </div>

                                <div
                                    v-if="subtest.learning_modules.length > 0"
                                    class="mt-4 rounded-2xl bg-white p-4 ring-1 ring-[#edf2f7]"
                                >
                                    <p
                                        class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase"
                                    >
                                        Materi terkait
                                    </p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <Link
                                            v-for="module in subtest.learning_modules"
                                            :key="module.slug"
                                            :href="`/learn/modules/${module.slug}`"
                                            class="rounded-full bg-[#f8fbff] px-3 py-1 text-sm font-medium text-slate-700 ring-1 ring-[#e6edf3] hover:text-[#b91c1c]"
                                        >
                                            {{ module.title }}
                                        </Link>
                                    </div>
                                </div>

                                <div
                                    class="mt-4 rounded-2xl bg-white p-4 ring-1 ring-[#edf2f7]"
                                >
                                    <p
                                        class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase"
                                    >
                                        Ringkasan progres
                                    </p>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <p class="text-sm text-slate-500">
                                                Attempt
                                            </p>
                                            <p
                                                class="mt-1 text-lg font-semibold text-slate-950"
                                            >
                                                {{
                                                    subtest.analytics
                                                        .attempts_count
                                                }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-slate-500">
                                                Skor terbaik
                                            </p>
                                            <p
                                                class="mt-1 text-lg font-semibold text-slate-950"
                                            >
                                                {{
                                                    subtest.analytics
                                                        .best_score ??
                                                    'Belum ada'
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-if="subtest.in_progress_attempt"
                                    class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800"
                                >
                                    Sesi berjalan:
                                    {{
                                        subtest.in_progress_attempt
                                            .answered_questions
                                    }}
                                    /
                                    {{
                                        subtest.in_progress_attempt
                                            .total_questions
                                    }}
                                    soal sudah dijawab.
                                </div>

                                <div class="mt-5 flex flex-wrap gap-3">
                                    <Button
                                        as-child
                                        class="rounded-2xl bg-[#0f172a] text-white hover:bg-[#111827]"
                                    >
                                        <Link
                                            :href="`/practice/subtests/${subtest.slug}`"
                                        >
                                            {{
                                                subtest.in_progress_attempt
                                                    ? 'Atur atau lanjutkan latihan'
                                                    : 'Buka konfigurasi'
                                            }}
                                            <ArrowRight class="ml-2 size-4" />
                                        </Link>
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
                                            Lanjutkan sesi
                                        </Link>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-5">
                <LatihanAnalyticsSnapshot
                    :analytics="{
                        attempts_count: summary.practice_attempts,
                        best_score: summary.best_score,
                        average_accuracy: summary.average_accuracy,
                        latest_score: recentAttempts[0]?.score_total ?? null,
                        latest_accuracy: recentAttempts[0]?.accuracy ?? null,
                        last_submitted_at:
                            recentAttempts[0]?.submitted_at ?? null,
                    }"
                />

                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-[#0f172a] text-white shadow-sm"
                >
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Brain class="size-5" />
                            Cara memakai latihan
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm text-slate-200">
                        <div class="flex items-start gap-3">
                            <BookCheck class="mt-0.5 size-4" />
                            <span>Pilih subtes yang ingin dikuatkan dulu.</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <Clock3 class="mt-0.5 size-4" />
                            <span
                                >Mulai tanpa timer bila masih membangun ritme
                                dasar.</span
                            >
                        </div>
                        <div class="flex items-start gap-3">
                            <Sparkles class="mt-0.5 size-4" />
                            <span
                                >Review hasil dan pembahasan sebelum mengulang
                                sesi.</span
                            >
                        </div>
                    </CardContent>
                </Card>

                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardHeader>
                        <CardTitle>Histori terbaru</CardTitle>
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
                            Belum ada latihan yang dikirim.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </section>
    </div>
</template>


