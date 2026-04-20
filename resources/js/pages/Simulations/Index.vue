<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    Clock3,
    Layers3,
    MonitorPlay,
    ShieldCheck,
} from 'lucide-vue-next';
import SimulasiRiwayatCard from '@/components/simulation/SimulasiRiwayatCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { SimulasiRiwayatItem, SimulasiSummary } from '@/types';

type SimulasiPackageCard = {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    duration_minutes: number;
    question_count: number;
    subtests_count: number;
    subtests: Array<{
        name: string | null;
        question_count: number;
    }>;
    in_progress_attempt: {
        id: number;
        answered_questions: number;
        total_questions: number;
    } | null;
    analytics: {
        attempts_count: number;
        best_score: number | null;
    };
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Simulasi', href: '/simulations' },
        ],
    },
});

defineProps<{
    summary: SimulasiSummary;
    packages: SimulasiPackageCard[];
    recentAttempts: SimulasiRiwayatItem[];
}>();
</script>

<template>
    <Head title="Simulasi" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <section
            class="overflow-hidden rounded-[2rem] border border-[#dbe6ee] bg-[radial-gradient(circle_at_top_right,_rgba(15,23,42,0.16),_transparent_30%),linear-gradient(135deg,_#f8fbff_0%,_#eef5fb_42%,_#ffffff_100%)] p-6 shadow-sm"
        >
            <div class="grid gap-6 xl:grid-cols-[1.15fr,0.85fr] xl:items-end">
                <div class="space-y-4">
                    <div class="inline-flex items-center rounded-full bg-[#0f172a] px-4 py-2 text-xs font-semibold tracking-[0.18em] text-white uppercase">
                        Mode Simulasi / CAT
                    </div>
                    <div>
                        <h1 class="font-display text-4xl font-bold tracking-tight text-slate-950">
                            Paket simulasi penuh untuk menguji performa secara formal.
                        </h1>
                        <p class="mt-3 max-w-3xl text-base leading-7 text-slate-600">
                            Kerjakan paket dengan timer penuh, navigator soal, flag ragu-ragu, dan review hasil yang tetap stabil walau bank soal CMS berubah.
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/90">
                        <CardContent class="space-y-2 p-5">
                            <p class="text-sm text-slate-500">Paket aktif</p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ summary.packages }}
                            </p>
                        </CardContent>
                    </Card>
                    <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/90">
                        <CardContent class="space-y-2 p-5">
                            <p class="text-sm text-slate-500">Histori simulasi</p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ summary.attempts }}
                            </p>
                        </CardContent>
                    </Card>
                    <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/90">
                        <CardContent class="space-y-2 p-5">
                            <p class="text-sm text-slate-500">Skor terbaik</p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ summary.best_score ?? 'Belum ada' }}
                            </p>
                        </CardContent>
                    </Card>
                    <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/90">
                        <CardContent class="space-y-2 p-5">
                            <p class="text-sm text-slate-500">Rata-rata akurasi</p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ summary.average_accuracy ?? 0 }}%
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
                    <h2 class="font-display text-2xl font-bold tracking-tight text-slate-950">
                        Pilih paket simulasi
                    </h2>
                </div>

                <Card
                    v-for="simulationPackage in packages"
                    :key="simulationPackage.id"
                    class="rounded-[1.9rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardContent class="space-y-5 p-6">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="space-y-2">
                                <p class="font-display text-3xl font-bold tracking-tight text-slate-950">
                                    {{ simulationPackage.title }}
                                </p>
                                <p class="max-w-3xl text-sm leading-6 text-slate-600">
                                    {{ simulationPackage.description }}
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2 text-xs font-medium text-slate-500">
                                <span class="rounded-full bg-[#f8fbff] px-3 py-1 ring-1 ring-[#e3ebf2]">
                                    {{ simulationPackage.question_count }} soal
                                </span>
                                <span class="rounded-full bg-[#f8fbff] px-3 py-1 ring-1 ring-[#e3ebf2]">
                                    {{ simulationPackage.duration_minutes }} menit
                                </span>
                                <span class="rounded-full bg-[#f8fbff] px-3 py-1 ring-1 ring-[#e3ebf2]">
                                    {{ simulationPackage.subtests_count }} subtes
                                </span>
                            </div>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-[1fr,0.9fr]">
                            <div class="rounded-[1.5rem] border border-[#e7edf2] bg-[#fbfdff] p-5">
                                <p class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase">
                                    Komposisi cepat
                                </p>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span
                                        v-for="subtest in simulationPackage.subtests"
                                        :key="`${simulationPackage.id}-${subtest.name}`"
                                        class="rounded-full bg-white px-3 py-1 text-sm text-slate-700 ring-1 ring-[#e3ebf2]"
                                    >
                                        {{ subtest.name }} - {{ subtest.question_count }}
                                    </span>
                                </div>
                            </div>

                            <div class="rounded-[1.5rem] border border-[#e7edf2] bg-[#fbfdff] p-5">
                                <p class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase">
                                    Ringkasan progres
                                </p>
                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <p class="text-sm text-slate-500">Attempt</p>
                                        <p class="mt-1 text-xl font-semibold text-slate-950">
                                            {{ simulationPackage.analytics.attempts_count }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-slate-500">Skor terbaik</p>
                                        <p class="mt-1 text-xl font-semibold text-slate-950">
                                            {{ simulationPackage.analytics.best_score ?? 'Belum ada' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="simulationPackage.in_progress_attempt"
                            class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900"
                        >
                            Sesi berjalan:
                            {{ simulationPackage.in_progress_attempt.answered_questions }}
                            / {{ simulationPackage.in_progress_attempt.total_questions }}
                            soal sudah dijawab.
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <Button
                                as-child
                                class="rounded-2xl bg-[#0f172a] text-white hover:bg-[#111827]"
                            >
                                <Link :href="`/simulations/${simulationPackage.slug}`">
                                    Buka paket
                                    <ArrowRight class="ml-2 size-4" />
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
                                    Lanjutkan sesi
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-5">
                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-[#0f172a] text-white shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <ShieldCheck class="size-5" />
                            Riwayat snapshot tetap
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm text-slate-200">
                        <p>
                            Setiap attempt simulasi menyimpan snapshot soal dan opsi saat sesi dibuat.
                        </p>
                        <p>
                            Hasil review lama tetap stabil walau CMS mengubah teks soal, opsi, atau pembahasan setelahnya.
                        </p>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <MonitorPlay class="size-5 text-[#b91c1c]" />
                            Histori simulasi
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
                            Belum ada histori simulasi.
                        </p>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardContent class="space-y-3 p-6 text-sm text-slate-600">
                        <div class="flex items-start gap-3">
                            <Clock3 class="mt-0.5 size-4 text-[#b91c1c]" />
                            <span>Gunakan latihan dulu sebelum masuk paket simulasi penuh.</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <Clock3 class="mt-0.5 size-4 text-[#b91c1c]" />
                            <span>Flag soal yang masih ragu, lalu review sebelum submit final.</span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </section>
    </div>
</template>


