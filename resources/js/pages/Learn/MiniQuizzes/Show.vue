<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { TimerReset } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';

type AttemptQuestion = {
    id: number;
    display_order: number;
    code: string | null;
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
        started_at: string | null;
        module: {
            title: string | null;
            slug: string | null;
        };
        subtest: {
            name: string | null;
            slug: string | null;
        };
        questions: AttemptQuestion[];
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Belajar', href: '/learn' },
            { title: 'Mini Quiz', href: '#' },
        ],
    },
});

const form = useForm({
    answers: Object.fromEntries(
        props.attempt.questions.map((question) => [
            question.id,
            question.selected_option_id,
        ]),
    ) as Record<number, number | null>,
});

function submit() {
    form.post(`/learn/mini-quizzes/${props.attempt.id}/submit`);
}
</script>

<template>
    <Head :title="`Mini Quiz - ${attempt.module.title || 'Module'}`" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <section
            class="overflow-hidden rounded-[2rem] border border-[#dbe6ee] bg-[radial-gradient(circle_at_top_right,_rgba(188,24,24,0.12),_transparent_26%),linear-gradient(135deg,_#f8fbff_0%,_#eef5fb_48%,_#ffffff_100%)] p-6 shadow-sm"
        >
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
            >
                <div class="space-y-2">
                    <p class="text-sm font-medium text-[#b91c1c]">
                        {{ attempt.subtest.name }}
                    </p>
                    <h1
                        class="font-display text-4xl font-bold tracking-tight text-slate-950"
                    >
                        Mini Quiz: {{ attempt.module.title }}
                    </h1>
                    <p class="text-base leading-7 text-slate-600">
                        Jawab soal secara ringkas untuk mengecek pemahaman
                        setelah membaca modul.
                    </p>
                </div>
                <Card class="rounded-[1.5rem] border-[#dfe8ef] bg-white/90">
                    <CardContent
                        class="flex items-center gap-3 p-4 text-sm text-slate-600"
                    >
                        <TimerReset class="size-5 text-[#b91c1c]" />
                        {{ attempt.questions.length }} soal siap dikerjakan
                    </CardContent>
                </Card>
            </div>
        </section>

        <form
            class="grid gap-6 xl:grid-cols-[1.05fr,0.95fr]"
            @submit.prevent="submit"
        >
            <div class="space-y-5">
                <Card
                    v-for="question in attempt.questions"
                    :key="question.id"
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardHeader class="space-y-3">
                        <p class="text-sm font-medium text-[#b91c1c]">
                            Soal {{ question.display_order }}
                            <span v-if="question.code"
                                >• {{ question.code }}</span
                            >
                        </p>
                        <CardTitle class="text-lg leading-7">
                            {{ question.question_text }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <label
                            v-for="option in question.options"
                            :key="option.id"
                            class="flex cursor-pointer items-start gap-3 rounded-2xl border border-[#e7edf2] bg-[#fbfdff] px-4 py-4 transition hover:border-[#cfd9e2]"
                        >
                            <input
                                v-model="form.answers[question.id]"
                                :value="option.id"
                                type="radio"
                                class="mt-1 size-4 border-slate-300 text-[#b91c1c] focus:ring-[#b91c1c]"
                            />
                            <span class="text-sm leading-6 text-slate-700">
                                <span class="font-semibold"
                                    >{{ option.option_key }}.</span
                                >
                                {{ option.option_text }}
                            </span>
                        </label>
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-5">
                <Card
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-[#0f172a] text-white shadow-sm xl:sticky xl:top-6"
                >
                    <CardHeader>
                        <CardTitle>Siap kirim mini quiz?</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm text-slate-200">
                        <p>
                            Kamu boleh melewati soal yang belum yakin. Sistem
                            tetap menyimpan jawaban kosong sebagai bahan review
                            setelah hasil keluar.
                        </p>
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p>Total soal: {{ attempt.questions.length }}</p>
                        </div>
                        <div class="flex flex-col gap-3">
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-2xl bg-white text-slate-950 hover:bg-slate-100"
                            >
                                Kirim mini quiz
                            </Button>
                            <Button
                                as-child
                                variant="outline"
                                class="rounded-2xl border-white/20 bg-transparent text-white hover:bg-white/10"
                            >
                                <Link
                                    :href="`/learn/modules/${attempt.module.slug}`"
                                >
                                    Kembali ke modul
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </form>
    </div>
</template>

