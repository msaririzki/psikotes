<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, BookOpenText, Brain, Sparkles } from 'lucide-vue-next';
import LearnProgressBadge from '@/components/learn/LearnProgressBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { BelajarProgres, BelajarRecentMiniQuiz, BelajarSummary } from '@/types';

type FeaturedModule = {
    id: number;
    title: string;
    slug: string;
    progress: BelajarProgres;
};

type BelajarSubtestCard = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    instruction_excerpt: string | null;
    progress: {
        completed: number;
        in_progress: number;
        not_started: number;
        completion_rate: number;
    };
    modules_count: number;
    featured_modules: FeaturedModule[];
};

type BelajarCategoryCard = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    progress: {
        completed: number;
        in_progress: number;
        not_started: number;
        completion_rate: number;
    };
    subtests: BelajarSubtestCard[];
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Belajar', href: '/learn' },
        ],
    },
});

defineProps<{
    summary: BelajarSummary;
    categories: BelajarCategoryCard[];
    recentMiniQuizzes: BelajarRecentMiniQuiz[];
}>();
</script>

<template>
    <Head title="Belajar" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <section
            class="overflow-hidden rounded-[2rem] border border-[#dbe6ee] bg-[radial-gradient(circle_at_top_right,_rgba(188,24,24,0.14),_transparent_30%),linear-gradient(135deg,_#f8fbff_0%,_#eef5fb_44%,_#ffffff_100%)] p-6 shadow-sm"
        >
            <div class="grid gap-6 xl:grid-cols-[1.15fr,0.85fr] xl:items-end">
                <div class="space-y-4">
                    <div
                        class="inline-flex items-center rounded-full bg-[#0f172a] px-4 py-2 text-xs font-semibold tracking-[0.18em] text-white uppercase"
                    >
                        Mode Belajar
                    </div>
                    <div>
                        <h1
                            class="font-display text-4xl font-bold tracking-tight text-slate-950"
                        >
                            Mulai belajar psikotes dari dasar yang benar.
                        </h1>
                        <p
                            class="mt-3 max-w-3xl text-base leading-7 text-slate-600"
                        >
                            Baca materi per subtes, pahami cara mengerjakan,
                            simpan progres, lalu uji pemahaman lewat mini quiz
                            sederhana yang tetap tercatat rapi.
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <Card
                        class="rounded-[1.75rem] border-[#dfe8ef] bg-white/90"
                    >
                        <CardContent class="space-y-2 p-5">
                            <p class="text-sm text-slate-500">Total modul</p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ summary.modules }}
                            </p>
                        </CardContent>
                    </Card>
                    <Card
                        class="rounded-[1.75rem] border-[#dfe8ef] bg-white/90"
                    >
                        <CardContent class="space-y-2 p-5">
                            <p class="text-sm text-slate-500">Selesai</p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ summary.completed }}
                            </p>
                        </CardContent>
                    </Card>
                    <Card
                        class="rounded-[1.75rem] border-[#dfe8ef] bg-white/90"
                    >
                        <CardContent class="space-y-2 p-5">
                            <p class="text-sm text-slate-500">
                                Sedang dipelajari
                            </p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ summary.in_progress }}
                            </p>
                        </CardContent>
                    </Card>
                    <Card
                        class="rounded-[1.75rem] border-[#dfe8ef] bg-white/90"
                    >
                        <CardContent class="space-y-2 p-5">
                            <p class="text-sm text-slate-500">
                                Tingkat penyelesaian
                            </p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ summary.completion_rate }}%
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-[1.25fr,0.75fr]">
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <BookOpenText class="size-5 text-[#b91c1c]" />
                    <h2 class="font-display text-2xl font-bold tracking-tight">
                        Jalur pembelajaran
                    </h2>
                </div>

                <div class="space-y-5">
                    <Card
                        v-for="category in categories"
                        :key="category.id"
                        class="rounded-[1.9rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                    >
                        <CardContent class="space-y-6 p-6">
                            <div
                                class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
                            >
                                <div class="space-y-2">
                                    <p
                                        class="font-display text-3xl font-bold tracking-tight text-slate-950"
                                    >
                                        {{ category.name }}
                                    </p>
                                    <p
                                        class="max-w-2xl text-sm leading-6 text-slate-600"
                                    >
                                        {{ category.description }}
                                    </p>
                                </div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <LearnProgressBadge
                                        :status="
                                            category.progress
                                                .completion_rate === 100
                                                ? 'completed'
                                                : category.progress
                                                        .in_progress > 0
                                                  ? 'in_progress'
                                                  : 'not_started'
                                        "
                                        :label="`${category.progress.completion_rate}% selesai`"
                                    />
                                    <Button
                                        as-child
                                        variant="outline"
                                        class="rounded-2xl"
                                    >
                                        <Link
                                            :href="`/learn/categories/${category.slug}`"
                                        >
                                            Buka kategori
                                            <ArrowRight class="ml-2 size-4" />
                                        </Link>
                                    </Button>
                                </div>
                            </div>

                            <div class="grid gap-4 lg:grid-cols-2">
                                <div
                                    v-for="subtest in category.subtests"
                                    :key="subtest.id"
                                    class="rounded-[1.5rem] border border-[#e8edf2] bg-[#fbfdff] p-5"
                                >
                                    <div
                                        class="flex flex-wrap items-start justify-between gap-3"
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
                                        <LearnProgressBadge
                                            :status="
                                                subtest.progress
                                                    .completion_rate === 100
                                                    ? 'completed'
                                                    : subtest.progress
                                                            .in_progress > 0
                                                      ? 'in_progress'
                                                      : 'not_started'
                                            "
                                            :label="`${subtest.progress.completion_rate}%`"
                                        />
                                    </div>

                                    <div
                                        class="mt-4 flex items-center justify-between text-sm text-slate-500"
                                    >
                                        <p>{{ subtest.modules_count }} modul</p>
                                        <Link
                                            :href="`/learn/categories/${category.slug}/subtests/${subtest.slug}`"
                                            class="font-medium text-[#0f172a] hover:text-[#b91c1c]"
                                        >
                                            Lihat detail
                                        </Link>
                                    </div>

                                    <div
                                        v-if="
                                            subtest.featured_modules.length > 0
                                        "
                                        class="mt-4 space-y-3"
                                    >
                                        <div
                                            v-for="module in subtest.featured_modules"
                                            :key="module.id"
                                            class="rounded-2xl bg-white p-4 ring-1 ring-[#edf2f7]"
                                        >
                                            <div
                                                class="flex items-start justify-between gap-3"
                                            >
                                                <div>
                                                    <p
                                                        class="font-medium text-slate-900"
                                                    >
                                                        {{ module.title }}
                                                    </p>
                                                    <p
                                                        class="mt-1 text-xs text-slate-500"
                                                    >
                                                        Percobaan kuis:
                                                        {{
                                                            module.progress
                                                                .quiz_attempts_count
                                                        }}
                                                    </p>
                                                </div>
                                                <LearnProgressBadge
                                                    :status="
                                                        module.progress.status
                                                    "
                                                    :label="
                                                        module.progress.label
                                                    "
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <div class="space-y-4">
                <Card
                    class="rounded-[1.9rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Brain class="size-5 text-[#b91c1c]" />
                            Aktivitas terbaru
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            v-for="attempt in recentMiniQuizzes"
                            :key="attempt.id"
                            class="rounded-[1.25rem] border border-[#e7edf2] bg-[#fbfdff] p-4"
                        >
                            <p class="font-medium text-slate-900">
                                {{ attempt.learning_module || 'Mini kuis' }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                Skor {{ attempt.score_total ?? 0 }} • Akurasi
                                {{ attempt.accuracy ?? 0 }}%
                            </p>
                            <Link
                                :href="`/learn/mini-quizzes/${attempt.id}/result`"
                                class="mt-3 inline-flex text-sm font-medium text-[#0f172a] hover:text-[#b91c1c]"
                            >
                                Lihat hasil
                            </Link>
                        </div>
                        <p
                            v-if="recentMiniQuizzes.length === 0"
                            class="text-sm text-slate-500"
                        >
                            Belum ada mini kuis yang dikerjakan.
                        </p>
                    </CardContent>
                </Card>

                <Card
                    class="rounded-[1.9rem] border-[#dfe8ef] bg-[#0f172a] text-white shadow-sm"
                >
                    <CardContent class="space-y-4 p-6">
                        <div
                            class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] uppercase"
                        >
                            Saran awal
                        </div>
                        <p
                            class="font-display text-2xl font-bold tracking-tight"
                        >
                            Mulai dari subtes yang paling dasar lebih dulu.
                        </p>
                        <p class="text-sm leading-6 text-slate-200">
                            Untuk user pemula, alur paling aman adalah baca
                            modul pertama, pahami instruksi subtes, lalu
                            kerjakan mini kuis sebelum lanjut ke modul
                            berikutnya.
                        </p>
                        <div class="space-y-3 text-sm text-slate-100">
                            <div class="flex items-start gap-3">
                                <Sparkles class="mt-0.5 size-4" />
                                <span
                                    >Jangan loncat ke simulasi penuh dulu.</span
                                >
                            </div>
                            <div class="flex items-start gap-3">
                                <Sparkles class="mt-0.5 size-4" />
                                <span
                                    >Gunakan hasil mini kuis untuk menentukan
                                    review.</span
                                >
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </section>
    </div>
</template>

