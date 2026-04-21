<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookOpenText,
    Clock3,
    Layers3,
    Target,
} from 'lucide-vue-next';
import { computed } from 'vue';
import PracticeAnalyticsSnapshot from '@/components/practice/PracticeAnalyticsSnapshot.vue';
import PracticeHistoryCard from '@/components/practice/PracticeHistoryCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { dashboard } from '@/routes';
import type { LatihanAnalytics, LatihanRiwayatItem } from '@/types';

type DifficultyOption = {
    value: string;
    label: string;
    available_questions: number;
};

type LearningModulePreview = {
    id: number;
    title: string;
    slug: string;
    summary: string | null;
    estimated_minutes: number | null;
    level_label: string | null;
};

const props = defineProps<{
    category: {
        name: string | null;
        slug: string | null;
    };
    subtest: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        instruction: string | null;
        default_duration_minutes: number | null;
        analytics: LatihanAnalytics;
        availability: {
            all: number;
            easy: number;
            medium: number;
            hard: number;
        };
        difficulty_options: DifficultyOption[];
        question_count_presets: number[];
        timer_options: number[];
        config_defaults: {
            difficulty: string;
            question_count: number;
            timer_minutes: number | null;
        };
        in_progress_attempt: {
            id: number;
            started_at: string | null;
            answered_questions: number;
            total_questions: number;
        } | null;
    };
    learningModul: LearningModulePreview[];
    recentAttempts: LatihanRiwayatItem[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Latihan', href: '/practice' },
            { title: 'Subtes', href: '#' },
        ],
    },
});

const form = useForm({
    difficulty: props.subtest.config_defaults.difficulty,
    question_count: props.subtest.config_defaults.question_count,
    timer_minutes: props.subtest.config_defaults.timer_minutes as number | null,
});

const availableForSelectedDifficulty = computed(() => {
    return (
        props.subtest.difficulty_options.find(
            (option) => option.value === form.difficulty,
        )?.available_questions ?? 0
    );
});

const questionCountOptions = computed(() => {
    const base = props.subtest.question_count_presets.filter(
        (count) => count <= availableForSelectedDifficulty.value,
    );

    if (base.length === 0 && availableForSelectedDifficulty.value > 0) {
        return [availableForSelectedDifficulty.value];
    }

    if (
        availableForSelectedDifficulty.value > 0 &&
        !base.includes(availableForSelectedDifficulty.value)
    ) {
        return [...base, availableForSelectedDifficulty.value].sort(
            (first, second) => first - second,
        );
    }

    return base;
});

const selectedDifficultyLabel = computed(
    () =>
        props.subtest.difficulty_options.find(
            (option) => option.value === form.difficulty,
        )?.label ?? 'Semua Level',
);

function ensureQuestionCount() {
    if (!questionCountOptions.value.includes(form.question_count)) {
        form.question_count = questionCountOptions.value[0] ?? 0;
    }
}

ensureQuestionCount();

function submit() {
    ensureQuestionCount();
    form.post(`/practice/subtests/${props.subtest.slug}/attempts`);
}
</script>

<template>
    <Head :title="`Latihan ${subtest.name}`" />

    <div class="flex flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8 max-w-[1400px] mx-auto w-full">
        <!-- Modern Hero Banner Config -->
        <section class="relative overflow-hidden rounded-[2rem] border border-border/50 bg-card shadow-sm transition-all dark:bg-[#0c111d]">
            <div class="absolute -top-32 -right-32 size-96 rounded-full bg-gradient-to-br from-emerald-500/20 to-teal-500/20 blur-[100px] pointer-events-none"></div>
            <div class="absolute -bottom-20 -left-20 size-64 rounded-full bg-emerald-500/10 blur-[80px] pointer-events-none"></div>
            
            <div class="relative p-6 sm:p-8 lg:p-10 z-10 grid gap-8 xl:grid-cols-[1fr,0.85fr] xl:items-center">
                <div class="space-y-5">
                    <p class="text-xs font-bold tracking-widest text-emerald-600 uppercase dark:text-emerald-400">
                        {{ category.name }} / {{ subtest.name }}
                    </p>
                    <div>
                        <h1 class="font-display text-4xl font-bold tracking-tight text-foreground sm:text-5xl lg:text-4xl">
                            Mau Latihan Apa Hari Ini?
                        </h1>
                        <p class="mt-3 max-w-2xl text-base leading-relaxed text-muted-foreground">
                            {{
                                subtest.description ||
                                'Yuk atur tingkatan soalnya, jumlah pertanyaannya, dan waktunya sesuai yang kamu rasa nyaman.'
                            }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs font-semibold pt-1">
                        <span class="inline-flex items-center rounded-xl bg-slate-100 px-3 py-1.5 text-slate-700 ring-1 ring-border/50 dark:bg-white/5 dark:text-slate-300 shadow-sm">
                            Tersedia: {{ subtest.availability.all }} Soal
                        </span>
                        <span class="inline-flex items-center rounded-xl bg-green-50 px-3 py-1.5 text-green-700 ring-1 ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-400/20 shadow-sm">
                            Mudah: {{ subtest.availability.easy }}
                        </span>
                        <span class="inline-flex items-center rounded-xl bg-orange-50 px-3 py-1.5 text-orange-700 ring-1 ring-orange-600/20 dark:bg-orange-500/10 dark:text-orange-400 dark:ring-orange-400/20 shadow-sm">
                            Sedang: {{ subtest.availability.medium }}
                        </span>
                        <span class="inline-flex items-center rounded-xl bg-red-50 px-3 py-1.5 text-red-700 ring-1 ring-red-600/20 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-400/20 shadow-sm">
                            Sulit: {{ subtest.availability.hard }}
                        </span>
                    </div>
                </div>

                <div class="bg-white/50 backdrop-blur-md dark:bg-white/5 p-4 rounded-3xl border border-border/50 shadow-sm transition-all sm:p-5">
                    <PracticeAnalyticsSnapshot
                        :analytics="subtest.analytics"
                        compact
                    />
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.05fr,0.95fr] pb-10">
            <div class="space-y-6">
                <!-- Setting Card -->
                <Card class="rounded-[2rem] border-border/50 bg-card shadow-sm transition-all overflow-hidden">
                    <CardHeader class="border-b border-border/40 bg-muted/10 pb-5 pt-6 px-6 sm:px-8">
                        <CardTitle class="flex items-center gap-2 text-foreground">
                            <Layers3 class="size-5 text-emerald-600 dark:text-emerald-400" />
                            Kustomisasi Tiket Belajarmu
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-6 sm:p-8">
                        <form class="space-y-6 sm:space-y-8" @submit.prevent="submit">
                            <div class="grid gap-6 lg:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold tracking-wide text-foreground uppercase opacity-80">
                                        Tingkat Kerumitan
                                    </label>
                                    <Select
                                        v-model="form.difficulty"
                                        @update:model-value="ensureQuestionCount"
                                    >
                                        <SelectTrigger class="flex h-12 w-full rounded-xl border border-input bg-background px-4 text-sm shadow-sm transition-colors outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                                            <SelectValue placeholder="Pilih kerumitan..." />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="option in subtest.difficulty_options"
                                                :key="option.value"
                                                :value="option.value"
                                                :disabled="option.available_questions === 0"
                                            >
                                                {{ option.label }} (Ada {{ option.available_questions }} soal)
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p class="text-xs text-muted-foreground pt-1">
                                        Saat ini kamu di mode: <strong>{{ selectedDifficultyLabel }}</strong>.
                                    </p>
                                    <p v-if="form.errors.difficulty" class="text-sm text-red-500">
                                        {{ form.errors.difficulty }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-semibold tracking-wide text-foreground uppercase opacity-80">
                                        Tantang Berapa Soal?
                                    </label>
                                    <Select
                                        :model-value="form.question_count.toString()"
                                        @update:model-value="val => form.question_count = Number(val)"
                                    >
                                        <SelectTrigger class="flex h-12 w-full rounded-xl border border-input bg-background px-4 text-sm shadow-sm transition-colors outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                                            <SelectValue placeholder="Berapa amunisi?" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="count in questionCountOptions"
                                                :key="count"
                                                :value="count.toString()"
                                            >
                                                {{ count }} soal sajah
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p class="text-xs text-muted-foreground pt-1">
                                        Stok soal mentok di {{ availableForSelectedDifficulty }} biji.
                                    </p>
                                    <p v-if="form.errors.question_count" class="text-sm text-red-500">
                                        {{ form.errors.question_count }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4 rounded-2xl border border-indigo-200/50 bg-indigo-50/50 p-5 sm:p-6 dark:border-indigo-500/20 dark:bg-indigo-500/10">
                                <div class="flex items-start justify-between gap-4 flex-col sm:flex-row sm:items-center">
                                    <div>
                                        <p class="text-[0.95rem] font-bold text-indigo-900 dark:text-indigo-200">
                                            Penanda Waktu (Opsional)
                                        </p>
                                        <p class="text-xs text-indigo-700/80 dark:text-indigo-300 mt-1">
                                            Coba aktifkan kalau mau ngetes mental di kondisi ujian riil!
                                        </p>
                                    </div>
                                    <label class="inline-flex cursor-pointer items-center gap-3 text-sm font-bold text-indigo-800 dark:text-indigo-300 bg-white/50 px-4 py-2.5 rounded-xl border border-indigo-200/50 hover:bg-white dark:bg-black/20 dark:border-white/10 dark:hover:bg-black/40 transition-colors">
                                        <input
                                            :checked="form.timer_minutes !== null"
                                            type="checkbox"
                                            class="size-4.5 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-600 dark:border-slate-600 dark:bg-slate-900"
                                            @change="form.timer_minutes = form.timer_minutes === null ? subtest.default_duration_minutes || subtest.timer_options[0] || 10 : null"
                                        />
                                        Pakai Timer
                                    </label>
                                </div>

                                <Select
                                    :model-value="form.timer_minutes ? form.timer_minutes.toString() : ''"
                                    @update:model-value="val => form.timer_minutes = Number(val)"
                                    :disabled="form.timer_minutes === null"
                                >
                                    <SelectTrigger class="flex h-12 w-full rounded-xl border border-indigo-200 bg-white/70 px-4 text-sm shadow-sm outline-none disabled:cursor-not-allowed disabled:bg-slate-50/50 disabled:opacity-50 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:border-white/10 dark:bg-black/20 dark:text-indigo-100">
                                        <SelectValue placeholder="Pilih timer durasi..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="minutes in subtest.timer_options"
                                            :key="minutes"
                                            :value="minutes.toString()"
                                            class="text-slate-900"
                                        >
                                            Dalam {{ minutes }} Menit
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.timer_minutes" class="text-sm text-red-500">
                                    {{ form.errors.timer_minutes }}
                                </p>
                            </div>

                            <!-- Summary Card Minimalis -->
                            <div class="rounded-2xl border border-border/60 bg-muted/20 p-5 sm:p-6 dark:bg-card">
                                <p class="text-[0.65rem] font-bold tracking-[0.16em] text-muted-foreground uppercase opacity-80 mb-4">
                                    Catatan Konfigurasi
                                </p>
                                <div class="grid gap-4 sm:grid-cols-3">
                                    <div class="bg-card border border-border/50 rounded-xl p-3 sm:p-4 shadow-sm">
                                        <p class="text-[0.65rem] text-muted-foreground uppercase tracking-widest">Tipe</p>
                                        <p class="mt-1 text-sm font-bold text-foreground">Level {{ selectedDifficultyLabel }}</p>
                                    </div>
                                    <div class="bg-card border border-border/50 rounded-xl p-3 sm:p-4 shadow-sm">
                                        <p class="text-[0.65rem] text-muted-foreground uppercase tracking-widest">Amunisi</p>
                                        <p class="mt-1 text-sm font-bold text-foreground">{{ form.question_count }} Soal</p>
                                    </div>
                                    <div class="bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 rounded-xl p-3 sm:p-4 shadow-sm">
                                        <p class="text-[0.65rem] text-indigo-600/70 dark:text-indigo-300/70 uppercase tracking-widest">Waktu</p>
                                        <p class="mt-1 text-sm font-bold text-indigo-900 dark:text-indigo-200">
                                            {{ form.timer_minutes ? `${form.timer_minutes} Menit` : 'Dibebaskan' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                                <Button
                                    :disabled="form.processing || questionCountOptions.length === 0"
                                    class="rounded-xl h-[3.25rem] bg-emerald-600 text-white hover:bg-emerald-700 shadow-md shadow-emerald-600/20 transition-all font-semibold flex-1"
                                >
                                    Sikat Latihannya!
                                </Button>
                                <Button
                                    as-child
                                    variant="outline"
                                    class="rounded-xl h-[3.25rem] flex-none border-border/60 dark:bg-transparent"
                                >
                                    <Link href="/practice">Mundur Sesat</Link>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card class="rounded-[2rem] border-border/50 bg-card shadow-sm transition-all overflow-hidden">
                    <CardHeader class="border-b border-border/40 bg-muted/10 pb-4 pt-5 px-6 sm:px-8">
                        <CardTitle class="text-[0.9rem] uppercase tracking-widest text-muted-foreground/80">Informasi / Petunjuk Hal</CardTitle>
                    </CardHeader>
                    <CardContent class="p-6 sm:p-8">
                        <div class="text-sm leading-relaxed whitespace-pre-line text-muted-foreground">
                            {{
                                subtest.instruction ||
                                'Belum ada petunjuk dari sistem perihal materi ini, tancap gas aja!'
                            }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-6 lg:sticky lg:top-8 h-fit">
                <Card v-if="subtest.in_progress_attempt" class="rounded-[2rem] border-amber-200 bg-amber-50 shadow-md dark:border-amber-500/20 dark:bg-amber-500/10 overflow-hidden">
                    <CardHeader class="bg-amber-100/50 dark:bg-white/5 border-b border-amber-200/50 dark:border-white/5 pb-4">
                        <CardTitle class="text-amber-900 dark:text-amber-200 flex items-center gap-2">
                            <Clock3 class="size-5" />
                            Hei, Masih Ada PR Latihan!
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm text-amber-900 p-5 pt-5 sm:p-6 sm:pt-6 dark:text-amber-100/80">
                        <p class="leading-relaxed">
                            Kamu kemarin nyisain sesi yang belum kelar buat latihan ini.
                        </p>
                        <div class="rounded-xl border border-amber-200/50 bg-white/50 p-4 dark:border-white/10 dark:bg-black/20">
                            Terjawab <strong class="text-amber-800 dark:text-amber-300 text-lg">{{ subtest.in_progress_attempt.answered_questions }}</strong> dari <strong class="text-amber-800 dark:text-amber-300">{{ subtest.in_progress_attempt.total_questions }}</strong> pertanyaan.
                        </div>
                        <Button
                            as-child
                            class="rounded-xl h-11 w-full bg-amber-600 text-white hover:bg-amber-700 shadow-sm border-transparent"
                        >
                            <Link :href="`/practice/attempts/${subtest.in_progress_attempt.id}`">
                                Nyambung Belajar Lagi
                            </Link>
                        </Button>
                    </CardContent>
                </Card>

                <Card class="rounded-[2rem] border-border/50 bg-card shadow-sm transition-all overflow-hidden">
                    <CardHeader class="pb-3 border-b border-border/40 bg-muted/10">
                        <CardTitle class="flex items-center gap-2 text-base text-foreground">
                            <BookOpenText class="size-5 text-emerald-600 dark:text-emerald-400" />
                            Bahan Modul Bacaan
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3 p-5 sm:p-6">
                        <div
                            v-for="module in learningModul"
                            :key="module.id"
                            class="rounded-2xl border border-border/40 bg-muted/20 p-4 transition-all hover:border-emerald-500/30 hover:bg-muted/40 dark:bg-black/10 dark:hover:bg-white/5"
                        >
                            <div class="space-y-2">
                                <p class="font-bold text-foreground">
                                    {{ module.title }}
                                </p>
                                <p class="text-sm leading-relaxed text-muted-foreground line-clamp-2">
                                    {{ module.summary }}
                                </p>
                                <p class="text-xs font-semibold text-muted-foreground">
                                    {{ module.level_label }} • Estimasi {{ module.estimated_minutes ?? 15 }} menitan
                                </p>
                            </div>
                            <Link
                                :href="`/learn/modules/${module.slug}`"
                                class="mt-4 inline-flex items-center justify-center w-full rounded-xl bg-background border border-border/60 py-2.5 text-sm font-semibold text-foreground hover:text-emerald-600 hover:border-emerald-200 transition-colors shadow-sm dark:hover:text-emerald-400 dark:hover:border-emerald-500/30"
                            >
                                Buka Bacaannya Baru Eksekusi Latihan
                            </Link>
                        </div>
                        <p v-if="learningModul.length === 0" class="text-sm italic text-muted-foreground text-center py-6 border border-dashed border-border/60 rounded-2xl">
                            Belok ada buku materi khusus yang disematkan ke rute belajar ini.
                        </p>
                    </CardContent>
                </Card>

                <Card class="rounded-[2rem] border-border/50 bg-card shadow-sm transition-all overflow-hidden">
                    <CardHeader class="pb-3 border-b border-border/40 bg-muted/10">
                        <CardTitle class="flex items-center gap-2 text-base text-foreground">
                            <Target class="size-5 text-emerald-600 dark:text-emerald-400" />
                            Riwayat Latihan Ini
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3 p-5 sm:p-6">
                        <PracticeHistoryCard
                            v-for="attempt in recentAttempts"
                            :key="attempt.id"
                            :attempt="attempt"
                        />
                        <p v-if="recentAttempts.length === 0" class="text-sm italic text-muted-foreground text-center py-4">
                            Kosong, kamu belum punya rekam jejak penyelesaian di tipe soal ini.
                        </p>
                    </CardContent>
                </Card>

                <Card class="relative overflow-hidden rounded-[2rem] border border-emerald-500/20 bg-[#0c111d] text-white shadow-xl">
                    <div class="absolute -right-10 -top-10 opacity-20 size-32 bg-emerald-500 blur-3xl rounded-full pointer-events-none"></div>
                    <CardHeader class="relative z-10 pb-2">
                        <CardTitle class="flex items-center gap-2 text-emerald-100">
                            <Clock3 class="size-5 text-emerald-400" />
                            Tips Bijak
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="relative z-10 space-y-4 text-sm text-emerald-100/80 p-5 pt-3 sm:p-6 pb-6">
                        <p class="leading-relaxed">
                            Mulai aja dulu dari kesulitan yang paling enteng. Habis itu asah nyalimu dengan numpahin waktu ekstra tanpa batas sampai akurasimu bagus, barulah naik kelas.
                        </p>
                        <Button
                            as-child
                            variant="outline"
                            class="w-full justify-between rounded-xl border-white/20 bg-transparent text-white hover:bg-white/10 h-11"
                        >
                            <Link href="/practice">
                                Gak jadi, mau lihat latihan lain
                                <ArrowRight class="size-4" />
                            </Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </section>
    </div>
</template>

