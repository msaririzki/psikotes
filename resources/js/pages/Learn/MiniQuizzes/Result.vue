<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Award, BookOpenCheck, RotateCcw } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Belajar', href: '/learn' },
            { title: 'Hasil Mini Kuis', href: '#' },
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
        submitted_at: string | null;
        analysis_text: string | null;
    };
    module: {
        title: string | null;
        slug: string | null;
        subtest: string | null;
        category: string | null;
    };
    recommendation: {
        headline: string;
        description: string;
        primary_action: string;
        next_module: {
            title: string;
            slug: string;
        } | null;
    };
    answers: Array<{
        id: number;
        display_order: number;
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
</script>

<template>
    <Head :title="`Hasil - ${module.title || 'Mini Kuis'}`" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <section
            class="overflow-hidden rounded-[2rem] border border-[#dbe6ee] bg-[radial-gradient(circle_at_top_left,_rgba(188,24,24,0.12),_transparent_28%),linear-gradient(135deg,_#f8fbff_0%,_#eef5fb_50%,_#ffffff_100%)] p-6 shadow-sm"
        >
            <div class="grid gap-6 xl:grid-cols-[1.05fr,0.95fr] xl:items-end">
                <div class="space-y-4">
                    <p class="text-sm font-medium text-[#b91c1c]">
                        {{ module.category }} / {{ module.subtest }}
                    </p>
                    <div>
                        <h1
                            class="font-display text-4xl font-bold tracking-tight text-slate-950"
                        >
                            {{ recommendation.headline }}
                        </h1>
                        <p
                            class="mt-3 max-w-3xl text-base leading-7 text-slate-600"
                        >
                            {{ recommendation.description }}
                        </p>
                    </div>
                    <p class="text-sm leading-6 text-slate-500">
                        {{ attempt.analysis_text }}
                    </p>
                </div>

                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-[#0f172a] text-white"
                >
                    <CardContent class="grid gap-4 p-6 sm:grid-cols-2">
                        <div>
                            <p class="text-sm text-slate-300">Skor</p>
                            <p class="mt-2 text-4xl font-semibold">
                                {{ attempt.score_total }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-300">Akurasi</p>
                            <p class="mt-2 text-4xl font-semibold">
                                {{ attempt.accuracy }}%
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.95fr,1.05fr]">
            <div class="space-y-5">
                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Award class="size-5 text-[#b91c1c]" />
                            Ringkasan hasil
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl bg-[#f8fbff] p-4">
                            <p class="text-sm text-slate-500">Benar</p>
                            <p
                                class="mt-2 text-2xl font-semibold text-slate-950"
                            >
                                {{ attempt.correct_answers }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-[#f8fbff] p-4">
                            <p class="text-sm text-slate-500">Salah</p>
                            <p
                                class="mt-2 text-2xl font-semibold text-slate-950"
                            >
                                {{ attempt.wrong_answers }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-[#f8fbff] p-4">
                            <p class="text-sm text-slate-500">Kosong</p>
                            <p
                                class="mt-2 text-2xl font-semibold text-slate-950"
                            >
                                {{ attempt.blank_answers }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardHeader>
                        <CardTitle>Rekomendasi langkah berikutnya</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <Button
                            as-child
                            class="w-full justify-between rounded-2xl bg-[#0f172a] text-white hover:bg-[#111827]"
                        >
                            <Link :href="`/learn/modules/${module.slug}`">
                                Review modul
                                <BookOpenCheck class="size-4" />
                            </Link>
                        </Button>
                        <Button
                            as-child
                            variant="outline"
                            class="w-full justify-between rounded-2xl"
                        >
                            <Link
                                :href="`/learn/modules/${module.slug}/mini-quiz`"
                                method="post"
                                as="button"
                            >
                                Ulangi mini quiz
                                <RotateCcw class="size-4" />
                            </Link>
                        </Button>
                        <Button
                            v-if="attempt.score_total >= 80"
                            as-child
                            variant="outline"
                            class="w-full justify-between rounded-2xl"
                        >
                            <Link
                                :href="`/learn/modules/${module.slug}/complete`"
                                method="post"
                                as="button"
                            >
                                Tandai modul selesai
                                <ArrowRight class="size-4" />
                            </Link>
                        </Button>
                        <Button
                            v-if="recommendation.next_module"
                            as-child
                            variant="outline"
                            class="w-full justify-between rounded-2xl"
                        >
                            <Link
                                :href="`/learn/modules/${recommendation.next_module.slug}`"
                            >
                                Lanjut ke {{ recommendation.next_module.title }}
                                <ArrowRight class="size-4" />
                            </Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <Card
                class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
            >
                <CardHeader>
                    <CardTitle>Review jawaban</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div
                        v-for="answer in answers"
                        :key="answer.id"
                        class="rounded-[1.5rem] border p-5"
                        :class="
                            answer.is_correct === true
                                ? 'border-emerald-200 bg-emerald-50'
                                : answer.is_correct === false
                                  ? 'border-rose-200 bg-rose-50'
                                  : 'border-slate-200 bg-slate-50'
                        "
                    >
                        <div class="space-y-3">
                            <p class="text-sm font-medium text-[#b91c1c]">
                                Soal {{ answer.display_order }}
                            </p>
                            <p class="text-sm leading-7 text-slate-700">
                                {{ answer.question_text }}
                            </p>
                            <div class="text-sm text-slate-600">
                                <p>
                                    Jawaban kamu:
                                    <span class="font-semibold text-slate-900">
                                        {{
                                            answer.selected_option
                                                ? `${answer.selected_option.option_key}. ${answer.selected_option.option_text}`
                                                : 'Kosong'
                                        }}
                                    </span>
                                </p>
                                <p class="mt-1">
                                    Jawaban benar:
                                    <span class="font-semibold text-slate-900">
                                        {{
                                            answer.correct_option
                                                ? `${answer.correct_option.option_key}. ${answer.correct_option.option_text}`
                                                : 'Tidak tersedia'
                                        }}
                                    </span>
                                </p>
                            </div>
                            <div
                                v-if="answer.explanation_text"
                                class="rounded-2xl bg-white/70 p-4 text-sm leading-6 text-slate-600"
                            >
                                {{ answer.explanation_text }}
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </section>
    </div>
</template>

