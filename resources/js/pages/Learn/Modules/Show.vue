<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookCheck,
    Lightbulb,
    Rocket,
    Target,
} from 'lucide-vue-next';
import BelajaringSectionCard from '@/components/learn/BelajaringSectionCard.vue';
import BelajarProgresBadge from '@/components/learn/BelajarProgresBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { BelajarProgres, BelajarRecentMiniQuiz } from '@/types';

type ExampleItem = {
    id: number;
    code: string | null;
    question_text: string;
    options: Array<{
        id: number;
        option_key: string;
        option_text: string | null;
        is_correct: boolean;
    }>;
    explanation_text: string | null;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Belajar', href: '/learn' },
            { title: 'Module', href: '#' },
        ],
    },
});

defineProps<{
    category: {
        name: string | null;
        slug: string | null;
    };
    subtest: {
        name: string | null;
        slug: string | null;
        instruction: string | null;
    };
    module: {
        id: number;
        title: string;
        slug: string;
        summary: string | null;
        content: string | null;
        tips: string | null;
        tricks: string | null;
        level: string | null;
        level_label: string | null;
        estimated_minutes: number | null;
        progress: BelajarProgres;
        objectives: string[];
        quiz_available: boolean;
        quiz_question_count: number;
        next_module: {
            title: string;
            slug: string;
        } | null;
    };
    examples: ExampleItem[];
    recentMiniQuizzes: BelajarRecentMiniQuiz[];
}>();
</script>

<template>
    <Head :title="module.title" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <section
            class="overflow-hidden rounded-[2rem] border border-[#dbe6ee] bg-[radial-gradient(circle_at_top_right,_rgba(188,24,24,0.12),_transparent_28%),linear-gradient(135deg,_#f8fbff_0%,_#eef5fb_50%,_#ffffff_100%)] p-6 shadow-sm"
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
                            {{ module.title }}
                        </h1>
                        <p
                            class="mt-3 max-w-3xl text-base leading-7 text-slate-600"
                        >
                            {{
                                module.summary ||
                                'Modul ini membantu user memahami subtes dari dasar sebelum masuk ke mini quiz.'
                            }}
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <BelajarProgresBadge
                            :status="module.progress.status"
                            :label="module.progress.label"
                        />
                        <span class="text-sm text-slate-500">
                            {{ module.level_label }} •
                            {{ module.estimated_minutes ?? 15 }} menit
                        </span>
                    </div>
                </div>

                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/90">
                    <CardContent class="space-y-4 p-5">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-2xl bg-[#f8fbff] p-4">
                                <p class="text-sm text-slate-500">Status</p>
                                <p
                                    class="mt-2 text-xl font-semibold text-slate-950"
                                >
                                    {{ module.progress.label }}
                                </p>
                            </div>
                            <div class="rounded-2xl bg-[#f8fbff] p-4">
                                <p class="text-sm text-slate-500">
                                    Quiz attempt
                                </p>
                                <p
                                    class="mt-2 text-xl font-semibold text-slate-950"
                                >
                                    {{ module.progress.quiz_attempts_count }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <Button
                                as-child
                                class="rounded-2xl bg-[#0f172a] text-white hover:bg-[#111827]"
                            >
                                <Link
                                    :href="`/learn/modules/${module.slug}/mini-quiz`"
                                    method="post"
                                    as="button"
                                >
                                    Mulai mini quiz
                                </Link>
                            </Button>
                            <Button
                                as-child
                                variant="outline"
                                class="rounded-2xl"
                            >
                                <Link
                                    :href="`/learn/modules/${module.slug}/complete`"
                                    method="post"
                                    as="button"
                                >
                                    Tandai selesai
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.1fr,0.9fr]">
            <div class="space-y-5">
                <BelajaringSectionCard
                    title="Pengenalan"
                    description="Ringkasan inti modul sebelum masuk ke detail pembelajaran."
                >
                    <div
                        class="text-sm leading-7 whitespace-pre-line text-slate-600"
                    >
                        {{
                            module.summary ||
                            module.content ||
                            'Konten pengenalan belum tersedia.'
                        }}
                    </div>
                </BelajaringSectionCard>

                <BelajaringSectionCard
                    title="Tujuan Belajar"
                    description="Target pemahaman yang sebaiknya sudah terbentuk setelah modul ini selesai."
                >
                    <ul class="space-y-3 text-sm leading-6 text-slate-600">
                        <li
                            v-for="objective in module.objectives"
                            :key="objective"
                            class="flex items-start gap-3"
                        >
                            <Target class="mt-1 size-4 text-[#b91c1c]" />
                            <span>{{ objective }}</span>
                        </li>
                    </ul>
                </BelajaringSectionCard>

                <BelajaringSectionCard
                    title="Cara Mengerjakan"
                    description="Panduan kerja yang konsisten dengan instruksi subtes di CMS."
                >
                    <div
                        class="text-sm leading-7 whitespace-pre-line text-slate-600"
                    >
                        {{
                            subtest.instruction ||
                            'Instruksi subtes belum diisi.'
                        }}
                    </div>
                </BelajaringSectionCard>

                <BelajaringSectionCard
                    title="Materi Inti"
                    description="Bagian utama untuk membaca konsep, pola, dan alur penyelesaian."
                >
                    <div
                        class="text-sm leading-7 whitespace-pre-line text-slate-700"
                    >
                        {{
                            module.content ||
                            'Konten modul belum diisi dari CMS.'
                        }}
                    </div>
                </BelajaringSectionCard>

                <div class="grid gap-5 lg:grid-cols-2">
                    <BelajaringSectionCard
                        title="Tips"
                        description="Saran praktis untuk mengurangi salah langkah."
                    >
                        <div class="flex items-start gap-3">
                            <Lightbulb class="mt-1 size-5 text-[#b91c1c]" />
                            <div
                                class="text-sm leading-7 whitespace-pre-line text-slate-600"
                            >
                                {{
                                    module.tips ||
                                    'Tips belum diisi pada modul ini.'
                                }}
                            </div>
                        </div>
                    </BelajaringSectionCard>

                    <BelajaringSectionCard
                        title="Trik"
                        description="Shortcut yang membantu menjawab lebih stabil dan efisien."
                    >
                        <div class="flex items-start gap-3">
                            <Rocket class="mt-1 size-5 text-[#b91c1c]" />
                            <div
                                class="text-sm leading-7 whitespace-pre-line text-slate-600"
                            >
                                {{
                                    module.tricks ||
                                    'Trik belum diisi pada modul ini.'
                                }}
                            </div>
                        </div>
                    </BelajaringSectionCard>
                </div>

                <BelajaringSectionCard
                    title="Contoh Soal"
                    description="Contoh diambil langsung dari bank soal published pada subtes yang sama."
                >
                    <div class="space-y-5">
                        <div
                            v-for="example in examples"
                            :key="example.id"
                            class="rounded-[1.5rem] border border-[#e5edf3] bg-[#fbfdff] p-5"
                        >
                            <div class="space-y-3">
                                <p class="text-sm font-medium text-[#b91c1c]">
                                    {{ example.code || 'Contoh soal' }}
                                </p>
                                <div
                                    class="text-sm leading-7 whitespace-pre-line text-slate-700"
                                >
                                    {{ example.question_text }}
                                </div>
                            </div>

                            <div class="mt-4 grid gap-3">
                                <div
                                    v-for="option in example.options"
                                    :key="option.id"
                                    class="rounded-2xl border px-4 py-3 text-sm"
                                    :class="
                                        option.is_correct
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                            : 'border-[#e7edf2] bg-white text-slate-600'
                                    "
                                >
                                    <span class="font-semibold">
                                        {{ option.option_key }}.
                                    </span>
                                    {{ option.option_text }}
                                </div>
                            </div>

                            <div
                                v-if="example.explanation_text"
                                class="mt-4 rounded-2xl bg-[#fff8f4] p-4 text-sm leading-6 text-slate-600"
                            >
                                <span class="font-semibold text-slate-900">
                                    Pembahasan:
                                </span>
                                {{ example.explanation_text }}
                            </div>
                        </div>

                        <p
                            v-if="examples.length === 0"
                            class="text-sm text-slate-500"
                        >
                            Contoh soal belum tersedia pada subtes ini.
                        </p>
                    </div>
                </BelajaringSectionCard>
            </div>

            <div class="space-y-5">
                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-[#0f172a] text-white shadow-sm"
                >
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <BookCheck class="size-5" />
                            CTA Mini Quiz
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="text-sm leading-6 text-slate-200">
                            Setelah membaca modul, ukur pemahaman lewat mini
                            quiz sederhana. Histori hasil tetap disimpan rapi
                            sebagai bagian dari learning progress.
                        </p>
                        <p class="text-sm text-slate-300">
                            {{ module.quiz_question_count }} soal siap dipakai
                            untuk mini quiz.
                        </p>
                        <Button
                            v-if="module.quiz_available"
                            as-child
                            class="w-full rounded-2xl bg-white text-slate-950 hover:bg-slate-100"
                        >
                            <Link
                                :href="`/learn/modules/${module.slug}/mini-quiz`"
                                method="post"
                                as="button"
                            >
                                Mulai mini quiz
                            </Link>
                        </Button>
                        <p v-else class="text-sm text-amber-200">
                            Mini quiz belum tersedia karena soal publish yang
                            valid belum cukup.
                        </p>
                    </CardContent>
                </Card>

                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardHeader>
                        <CardTitle>Progres modul</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm text-slate-600">
                        <div class="rounded-2xl bg-[#f8fbff] p-4">
                            <p class="text-slate-500">Status</p>
                            <p class="mt-2 font-semibold text-slate-950">
                                {{ module.progress.label }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-[#f8fbff] p-4">
                            <p class="text-slate-500">Skor quiz terakhir</p>
                            <p class="mt-2 font-semibold text-slate-950">
                                {{
                                    module.progress.last_quiz_score ??
                                    'Belum ada'
                                }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-[#f8fbff] p-4">
                            <p class="text-slate-500">Total sesi</p>
                            <p class="mt-2 font-semibold text-slate-950">
                                {{ module.progress.quiz_attempts_count }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardHeader>
                        <CardTitle>Langkah berikutnya</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <Button
                            as-child
                            variant="outline"
                            class="w-full justify-between rounded-2xl"
                        >
                            <Link
                                :href="`/learn/categories/${category.slug}/subtests/${subtest.slug}`"
                            >
                                Kembali ke subtes
                                <ArrowRight class="size-4" />
                            </Link>
                        </Button>
                        <Button
                            v-if="module.next_module"
                            as-child
                            variant="outline"
                            class="w-full justify-between rounded-2xl"
                        >
                            <Link
                                :href="`/learn/modules/${module.next_module.slug}`"
                            >
                                Lanjut ke {{ module.next_module.title }}
                                <ArrowRight class="size-4" />
                            </Link>
                        </Button>
                    </CardContent>
                </Card>

                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardHeader>
                        <CardTitle>Histori mini quiz</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            v-for="attempt in recentMiniQuizzes"
                            :key="attempt.id"
                            class="rounded-2xl border border-[#e7edf2] bg-[#fbfdff] p-4"
                        >
                            <p class="font-medium text-slate-900">
                                Skor {{ attempt.score_total ?? 0 }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                Akurasi {{ attempt.accuracy ?? 0 }}%
                            </p>
                            <a
                                :href="`/learn/mini-quizzes/${attempt.id}/result`"
                                class="mt-3 inline-flex text-sm font-medium text-[#0f172a] hover:text-[#b91c1c]"
                            >
                                Lihat hasil
                            </a>
                        </div>
                        <p
                            v-if="recentMiniQuizzes.length === 0"
                            class="text-sm text-slate-500"
                        >
                            Belum ada histori mini quiz untuk modul ini.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </section>
    </div>
</template>

