<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, BookOpenText, Clock3, Layers3, PlayCircle } from 'lucide-vue-next';
import SimulasiRiwayatCard from '@/components/simulation/SimulasiRiwayatCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { SimulasiRiwayatItem } from '@/types';

type CompositionItem = {
    subtest_id: number | null;
    subtest_name: string | null;
    category_name: string | null;
    question_count: number;
    available_questions: number;
    learning_modules: Array<{
        title: string;
        slug: string;
    }>;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Simulasi', href: '/simulations' },
            { title: 'Package', href: '#' },
        ],
    },
});

defineProps<{
    simulationPackage: {
        id: number;
        title: string;
        slug: string;
        description: string | null;
        instruction: string | null;
        duration_minutes: number;
        question_count: number;
        subtests_count: number;
        analytics: {
            attempts_count: number;
            best_score: number | null;
            average_accuracy: number | null;
        };
        in_progress_attempt: {
            id: number;
            answered_questions: number;
            total_questions: number;
        } | null;
    };
    composition: CompositionItem[];
    recentAttempts: SimulasiRiwayatItem[];
}>();
</script>

<template>
    <Head :title="simulationPackage.title" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <section
            class="overflow-hidden rounded-[2rem] border border-[#dbe6ee] bg-[radial-gradient(circle_at_top_right,_rgba(15,23,42,0.14),_transparent_30%),linear-gradient(135deg,_#f8fbff_0%,_#eef5fb_46%,_#ffffff_100%)] p-6 shadow-sm"
        >
            <div class="grid gap-6 xl:grid-cols-[1.1fr,0.9fr] xl:items-end">
                <div class="space-y-4">
                    <p class="text-sm font-medium text-[#b91c1c]">Simulasi Package</p>
                    <div>
                        <h1 class="font-display text-4xl font-bold tracking-tight text-slate-950">
                            {{ simulationPackage.title }}
                        </h1>
                        <p class="mt-3 max-w-3xl text-base leading-7 text-slate-600">
                            {{ simulationPackage.description }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-[1.5rem] border border-[#dfe8ef] bg-white/90 p-4">
                        <p class="text-sm text-slate-500">Durasi</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-950">
                            {{ simulationPackage.duration_minutes }} menit
                        </p>
                    </div>
                    <div class="rounded-[1.5rem] border border-[#dfe8ef] bg-white/90 p-4">
                        <p class="text-sm text-slate-500">Total soal</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-950">
                            {{ simulationPackage.question_count }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.05fr,0.95fr]">
            <div class="space-y-5">
                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <PlayCircle class="size-5 text-[#b91c1c]" />
                            Mulai simulasi
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="text-sm leading-6 text-slate-600">
                            Simulasi ini memakai timer penuh dan snapshot immutable. Setelah start, soal dan opsi review akan tetap mengikuti kondisi saat attempt dibuat.
                        </p>
                        <div class="flex flex-wrap gap-3">
                            <Button
                                as-child
                                class="rounded-2xl bg-[#0f172a] text-white hover:bg-[#111827]"
                            >
                                <Link
                                    :href="`/simulations/${simulationPackage.slug}/attempts`"
                                    method="post"
                                    as="button"
                                >
                                    {{
                                        simulationPackage.in_progress_attempt
                                            ? 'Lanjutkan / buka sesi'
                                            : 'Mulai simulasi'
                                    }}
                                </Link>
                            </Button>
                            <Button
                                v-if="simulationPackage.in_progress_attempt"
                                as-child
                                variant="outline"
                                class="rounded-2xl"
                            >
                                <Link
                                    :href="`/simulations/attempts/${simulationPackage.in_progress_attempt.id}`"
                                >
                                    Lanjutkan attempt aktif
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Layers3 class="size-5 text-[#b91c1c]" />
                            Komposisi paket
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div
                            v-for="item in composition"
                            :key="`${item.subtest_id}-${item.subtest_name}`"
                            class="rounded-[1.45rem] border border-[#e7edf2] bg-[#fbfdff] p-5"
                        >
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <p class="font-semibold text-slate-950">
                                        {{ item.subtest_name }}
                                    </p>
                                    <p class="text-sm text-slate-500">
                                        {{ item.category_name }}
                                    </p>
                                </div>
                                <div class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-[#e4ebf1]">
                                    {{ item.question_count }} soal
                                </div>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl bg-white p-4 ring-1 ring-[#e6edf3]">
                                    <p class="text-sm text-slate-500">Bank soal valid</p>
                                    <p class="mt-1 font-semibold text-slate-950">
                                        {{ item.available_questions }}
                                    </p>
                                </div>
                                <div class="rounded-2xl bg-white p-4 ring-1 ring-[#e6edf3]">
                                    <p class="text-sm text-slate-500">Materi terkait</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <Link
                                            v-for="module in item.learning_modules"
                                            :key="module.slug"
                                            :href="`/learn/modules/${module.slug}`"
                                            class="rounded-full bg-[#f8fbff] px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-[#e6edf3] hover:text-[#b91c1c]"
                                        >
                                            {{ module.title }}
                                        </Link>
                                        <span
                                            v-if="item.learning_modules.length === 0"
                                            class="text-sm text-slate-500"
                                        >
                                            Belum ada modul published.
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-5">
                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Clock3 class="size-5 text-[#b91c1c]" />
                            Aturan simulasi
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm text-slate-600">
                        <p>
                            {{ simulationPackage.instruction || 'Ikuti timer penuh, jawab semua soal sebisa mungkin, dan gunakan flag untuk menandai soal ragu-ragu.' }}
                        </p>
                        <p>
                            Setelah waktu habis, sistem akan auto-submit attempt.
                        </p>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <BookOpenText class="size-5 text-[#b91c1c]" />
                            Histori paket ini
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <SimulasiRiwayatCard
                            v-for="attempt in recentAttempts"
                            :key="attempt.id"
                            :attempt="attempt"
                        />
                        <p
                            v-if="recentAttempts.length === 0"
                            class="text-sm text-slate-500"
                        >
                            Belum ada attempt simulasi untuk paket ini.
                        </p>
                    </CardContent>
                </Card>

                <Button as-child variant="outline" class="w-full justify-between rounded-2xl">
                    <Link href="/simulations">
                        Kembali ke semua paket
                        <ArrowRight class="size-4" />
                    </Link>
                </Button>
            </div>
        </section>
    </div>
</template>


