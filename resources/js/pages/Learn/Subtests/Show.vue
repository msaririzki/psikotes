<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { FileQuestion } from 'lucide-vue-next';
import BelajaringModuleCard from '@/components/learn/BelajaringModuleCard.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { BelajarProgres, BelajarRecentMiniQuiz } from '@/types';

type ProgressSummary = {
    completed: number;
    in_progress: number;
    not_started: number;
    completion_rate: number;
};

type ModuleItem = {
    id: number;
    title: string;
    slug: string;
    summary: string | null;
    level: string | null;
    level_label: string | null;
    estimated_minutes: number | null;
    progress: BelajarProgres;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Belajar', href: '/learn' },
            { title: 'Subtest', href: '#' },
        ],
    },
});

defineProps<{
    category: {
        name: string;
        slug: string;
    };
    subtest: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        instruction: string | null;
        progress: ProgressSummary;
    };
    modules: ModuleItem[];
    recentMiniQuizzes: BelajarRecentMiniQuiz[];
}>();
</script>

<template>
    <Head :title="subtest.name" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <section
            class="overflow-hidden rounded-[2rem] border border-[#dbe6ee] bg-[radial-gradient(circle_at_top_left,_rgba(15,23,42,0.12),_transparent_28%),linear-gradient(135deg,_#f8fbff_0%,_#eef5fb_48%,_#ffffff_100%)] p-6 shadow-sm"
        >
            <div class="space-y-4">
                <p class="text-sm font-medium text-[#b91c1c]">
                    {{ category.name }}
                </p>
                <div>
                    <h1
                        class="font-display text-4xl font-bold tracking-tight text-slate-950"
                    >
                        {{ subtest.name }}
                    </h1>
                    <p
                        class="mt-3 max-w-3xl text-base leading-7 text-slate-600"
                    >
                        {{ subtest.description }}
                    </p>
                </div>
                <div class="grid gap-4 xl:grid-cols-[1.1fr,0.9fr]">
                    <Card
                        class="rounded-[1.75rem] border-[#dfe8ef] bg-white/90"
                    >
                        <CardHeader>
                            <CardTitle>Cara mengerjakan</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div
                                class="text-sm leading-7 whitespace-pre-line text-slate-600"
                            >
                                {{
                                    subtest.instruction ||
                                    'Instruksi subtes belum diisi dari CMS.'
                                }}
                            </div>
                        </CardContent>
                    </Card>

                    <Card
                        class="rounded-[1.75rem] border-[#dfe8ef] bg-[#0f172a] text-white"
                    >
                        <CardContent class="grid gap-4 p-6 sm:grid-cols-3">
                            <div>
                                <p class="text-sm text-slate-300">Modul</p>
                                <p class="mt-2 text-3xl font-semibold">
                                    {{ modules.length }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-slate-300">Selesai</p>
                                <p class="mt-2 text-3xl font-semibold">
                                    {{ subtest.progress.completed }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-slate-300">Progres</p>
                                <p class="mt-2 text-3xl font-semibold">
                                    {{ subtest.progress.completion_rate }}%
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-[1.2fr,0.8fr]">
            <div class="space-y-4">
                <BelajaringModuleCard
                    v-for="module in modules"
                    :key="module.id"
                    :title="module.title"
                    :slug="module.slug"
                    :summary="module.summary"
                    :level-label="module.level_label"
                    :estimated-minutes="module.estimated_minutes"
                    :progress="module.progress"
                />
            </div>

            <Card
                class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
            >
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <FileQuestion class="size-5 text-[#b91c1c]" />
                        Riwayat mini quiz
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div
                        v-for="attempt in recentMiniQuizzes"
                        :key="attempt.id"
                        class="rounded-2xl border border-[#e7edf2] bg-[#fbfdff] p-4"
                    >
                        <p class="font-medium text-slate-900">
                            {{ attempt.learning_module || 'Mini quiz' }}
                        </p>
                        <p class="mt-1 text-sm text-slate-500">
                            Skor {{ attempt.score_total ?? 0 }} • Akurasi
                            {{ attempt.accuracy ?? 0 }}%
                        </p>
                        <a
                            :href="`/learn/mini-quizzes/${attempt.id}/result`"
                            class="mt-3 inline-flex text-sm font-medium text-[#0f172a] hover:text-[#b91c1c]"
                        >
                            Buka hasil
                        </a>
                    </div>
                    <p
                        v-if="recentMiniQuizzes.length === 0"
                        class="text-sm text-slate-500"
                    >
                        Belum ada histori mini quiz pada subtes ini.
                    </p>
                </CardContent>
            </Card>
        </section>
    </div>
</template>


