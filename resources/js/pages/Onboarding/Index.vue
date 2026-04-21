<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowRight, Compass, Sparkles, Target } from 'lucide-vue-next';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { dashboard } from '@/routes';

type ProfilForm = {
    education_level: string | null;
    target_exam: string;
    learning_level: string;
    target_daily_minutes: number | null;
    preferred_focus: string;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Orientasi', href: '/onboarding' },
        ],
    },
});

const props = defineProps<{
    profile: ProfilForm;
    onboarding: {
        completed: boolean;
        learning_levels: Array<{ value: string; label: string }>;
        focus_options: Array<{ value: string; label: string }>;
    };
}>();

const form = useForm({
    education_level: props.profile.education_level ?? '',
    target_exam: props.profile.target_exam,
    learning_level: props.profile.learning_level,
    target_daily_minutes: props.profile.target_daily_minutes ?? 45,
    preferred_focus: props.profile.preferred_focus,
});

const heading = computed(() =>
    props.onboarding.completed
        ? 'Perbarui arah belajar kamu'
        : 'Selesaikan onboarding singkat dulu',
);

function submit() {
    form.post('/onboarding');
}
</script>

<template>
    <Head title="Orientasi" />

    <div class="page-shell min-h-screen">
        <!-- Hero Section Onboarding -->
        <section
            class="page-hero group relative rounded-[1.9rem] border-border/50 bg-gradient-to-br from-indigo-50/80 via-white to-sky-50/80 shadow-xl shadow-slate-200/50 transition-all duration-500 sm:rounded-[2.5rem] dark:border-white/5 dark:from-[#0c111d] dark:via-[#111827] dark:to-[#0c111d] dark:shadow-2xl dark:shadow-indigo-900/10"
        >
            <div
                class="pointer-events-none absolute -top-20 -left-20 hidden h-64 w-64 rounded-full bg-indigo-300/40 blur-[80px] transition-all duration-1000 group-hover:bg-indigo-400/50 sm:block dark:bg-indigo-600/30 dark:group-hover:bg-indigo-500/40"
            ></div>
            <div
                class="pointer-events-none absolute -right-20 -bottom-20 hidden h-64 w-64 rounded-full bg-violet-300/40 blur-[80px] transition-all duration-1000 group-hover:bg-violet-400/50 sm:block dark:bg-violet-600/20 dark:group-hover:bg-violet-500/30"
            ></div>
            <div
                class="pointer-events-none absolute top-1/2 left-1/2 hidden h-[500px] w-[800px] -translate-x-1/2 -translate-y-1/2 rounded-[100%] border border-slate-200/50 bg-[radial-gradient(ellipse_at_center,_rgba(255,255,255,0.2)_0%,_transparent_70%)] sm:block dark:border-white/5 dark:bg-[radial-gradient(ellipse_at_center,_rgba(255,255,255,0.02)_0%,_transparent_70%)]"
            ></div>

            <div
                class="relative z-10 grid gap-6 sm:gap-8 xl:grid-cols-[1.1fr,0.9fr] xl:items-center"
            >
                <div class="space-y-5 sm:space-y-6">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-indigo-200/60 bg-indigo-100/50 px-3 py-1.5 text-[0.65rem] font-semibold tracking-[0.24em] text-indigo-700 uppercase shadow-inner shadow-white/50 backdrop-blur-md sm:px-4 sm:py-2 sm:text-xs dark:border-indigo-500/30 dark:bg-indigo-400/10 dark:text-indigo-300 dark:shadow-white/5"
                    >
                        <Compass class="size-3.5" />
                        First Run Setup
                    </div>
                    <div>
                        <h1
                            class="font-display text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl md:text-5xl lg:text-5xl/tight dark:text-white"
                        >
                            {{ heading }}
                        </h1>
                        <p
                            class="mt-3 max-w-2xl text-sm leading-7 text-slate-600 sm:mt-4 sm:text-base dark:text-indigo-100/70"
                        >
                            Halaman ini dipakai untuk menandai target belajar
                            awal kamu. Setelah proses ini, sistem algoritma kami
                            akan menyetel Rencana Belajar, Milestone, dan Dasbor
                            agar selalu relevan dengan kebutuhanmu.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 xl:justify-end xl:pb-4">
                    <Button
                        as-child
                        variant="outline"
                        class="h-11 w-full rounded-2xl border-border/60 px-5 text-slate-700 shadow-sm transition-all duration-300 hover:bg-muted/30 hover:text-indigo-600 hover:shadow-md sm:h-12 sm:w-auto sm:px-6 dark:text-slate-300 dark:hover:text-indigo-300"
                    >
                        <Link href="/dashboard"> Kembali ke dasbor </Link>
                    </Button>
                </div>
            </div>
        </section>

        <!-- Form Onboarding Container -->
        <div
            class="grid gap-6 sm:gap-8 lg:grid-cols-[1.5fr,1fr] xl:grid-cols-[1fr,400px]"
        >
            <Card
                class="glass-card transition-all duration-300 hover:shadow-xl sm:rounded-[2.5rem]"
            >
                <CardHeader
                    class="border-b border-border/40 px-5 pt-5 pb-4 sm:px-8 sm:pt-8"
                >
                    <CardTitle
                        class="flex items-center gap-3 text-xl font-bold text-card-foreground sm:text-2xl"
                    >
                        <div
                            class="rounded-xl bg-orange-100 p-2 text-orange-600 dark:bg-orange-500/20 dark:text-orange-400"
                        >
                            <Target class="size-6" />
                        </div>
                        Detail Profil Belajar
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-5 sm:p-8">
                    <form
                        class="grid gap-5 md:grid-cols-2 lg:gap-6"
                        @submit.prevent="submit"
                    >
                        <div class="grid gap-2">
                            <Label
                                for="education_level"
                                class="ml-1 text-sm font-semibold text-muted-foreground"
                                >Latar Pendidikan</Label
                            >
                            <Input
                                id="education_level"
                                v-model="form.education_level"
                                placeholder="Contoh: SMA / SMK"
                                class="h-12 rounded-2xl border-border/60 bg-muted/20 px-4 transition-all focus-visible:ring-indigo-500 dark:bg-black/20"
                            />
                            <InputError
                                :message="form.errors.education_level"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label
                                for="target_exam"
                                class="ml-1 text-sm font-semibold text-muted-foreground"
                                >Target Ujian</Label
                            >
                            <Input
                                id="target_exam"
                                v-model="form.target_exam"
                                placeholder="Psikotes Polri"
                                class="h-12 rounded-2xl border-border/60 bg-muted/20 px-4 transition-all focus-visible:ring-indigo-500 dark:bg-black/20"
                            />
                            <InputError :message="form.errors.target_exam" />
                        </div>

                        <div class="grid gap-2">
                            <Label
                                for="learning_level"
                                class="ml-1 text-sm font-semibold text-muted-foreground"
                                >Level Belajar Saat Ini</Label
                            >
                            <Select v-model="form.learning_level">
                                <SelectTrigger
                                    id="learning_level"
                                    class="h-12 w-full rounded-2xl border-border/60 bg-muted/20 px-4 text-foreground transition-all focus:ring-[3px] focus:ring-indigo-500 focus:ring-indigo-500/30 dark:bg-black/20"
                                >
                                    <SelectValue
                                        placeholder="Pilih level belajar..."
                                    />
                                </SelectTrigger>
                                <SelectContent
                                    class="rounded-xl border-border/50 bg-card/95 shadow-xl backdrop-blur-xl"
                                >
                                    <SelectGroup>
                                        <SelectItem
                                            v-for="option in onboarding.learning_levels"
                                            :key="option.value"
                                            :value="option.value"
                                            class="cursor-pointer rounded-lg py-3 transition-colors hover:bg-indigo-50 focus:bg-indigo-50 focus:text-indigo-900 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-100 dark:focus:bg-indigo-900/30 dark:focus:text-indigo-100"
                                        >
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.learning_level" />
                        </div>

                        <div class="grid gap-2">
                            <Label
                                for="target_daily_minutes"
                                class="ml-1 text-sm font-semibold text-muted-foreground"
                                >Kapasitas Waktu/Hari (Menit)</Label
                            >
                            <Input
                                id="target_daily_minutes"
                                v-model="form.target_daily_minutes"
                                type="number"
                                min="15"
                                max="240"
                                class="h-12 rounded-2xl border-border/60 bg-muted/20 px-4 transition-all focus-visible:ring-indigo-500 dark:bg-black/20"
                            />
                            <InputError
                                :message="form.errors.target_daily_minutes"
                            />
                        </div>

                        <div class="grid gap-2 md:col-span-2 xl:col-span-2">
                            <Label
                                for="preferred_focus"
                                class="ml-1 text-sm font-semibold text-muted-foreground"
                                >Fokus Utama (Prioritas)</Label
                            >
                            <Select v-model="form.preferred_focus">
                                <SelectTrigger
                                    id="preferred_focus"
                                    class="h-12 w-full rounded-2xl border-border/60 bg-muted/20 px-4 text-foreground transition-all focus:ring-[3px] focus:ring-indigo-500 focus:ring-indigo-500/30 dark:bg-black/20"
                                >
                                    <SelectValue
                                        placeholder="Pilih fokus prioritas..."
                                    />
                                </SelectTrigger>
                                <SelectContent
                                    class="rounded-xl border-border/50 bg-card/95 shadow-xl backdrop-blur-xl"
                                >
                                    <SelectGroup>
                                        <SelectItem
                                            v-for="option in onboarding.focus_options"
                                            :key="option.value"
                                            :value="option.value"
                                            class="cursor-pointer rounded-lg py-3 transition-colors hover:bg-indigo-50 focus:bg-indigo-50 focus:text-indigo-900 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-100 dark:focus:bg-indigo-900/30 dark:focus:text-indigo-100"
                                        >
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError
                                :message="form.errors.preferred_focus"
                            />
                        </div>

                        <div class="pt-2 sm:pt-4 md:col-span-2">
                            <Button
                                type="submit"
                                class="h-11 w-full rounded-2xl bg-indigo-600 px-6 text-sm text-white shadow-lg shadow-indigo-600/30 transition-all duration-300 hover:-translate-y-0.5 hover:bg-indigo-700 hover:shadow-xl hover:shadow-indigo-700/40 sm:h-12 sm:w-auto sm:px-8 sm:text-base"
                                :disabled="form.processing"
                            >
                                Simpan Profil
                                <ArrowRight class="ml-2 size-4" />
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Panel Bantuan / Setup Guide Tunggal -->
            <Card
                class="group relative flex h-fit flex-col overflow-hidden rounded-[2rem] border border-indigo-200 bg-white/60 text-card-foreground shadow-2xl backdrop-blur-xl transition-all duration-300 hover:shadow-indigo-900/10 dark:border-indigo-500/20 dark:bg-[#0c111d] dark:text-white dark:hover:shadow-indigo-900/20 lg:sticky lg:top-8"
            >
                <div
                    class="absolute -top-20 -right-20 size-64 rounded-full bg-gradient-to-br from-indigo-200 to-violet-300 opacity-60 blur-[80px] transition-opacity group-hover:opacity-80 dark:from-indigo-500 dark:to-violet-600 dark:opacity-30 dark:group-hover:opacity-50"
                ></div>
                <div
                    class="pointer-events-none absolute -bottom-10 -left-10 size-40 rounded-full bg-fuchsia-300 opacity-40 blur-[80px] transition-opacity group-hover:opacity-60 dark:bg-fuchsia-500 dark:opacity-20 dark:group-hover:opacity-40"
                ></div>
                <div
                    class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCI+PHBhdGggZD0iTTIwIDAgTDIwIDQwIE0wIDIwIEw0MCAyMCIgc3Ryb2tlPSIjZmZmIiBzdHJva2Utd2lkdGg9IjIiLz48L3N2Zz4=')] opacity-[0.08] mix-blend-overlay dark:opacity-[0.03]"
                ></div>

                <CardContent
                    class="relative z-10 flex-1 space-y-6 p-6 sm:space-y-8 sm:p-8"
                >
                    <div class="space-y-2">
                        <div
                            class="mb-2 inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-[0.65rem] font-semibold tracking-widest text-indigo-600 uppercase shadow-inner shadow-white/50 sm:text-xs dark:border-indigo-500/30 dark:bg-indigo-400/10 dark:text-indigo-300 dark:shadow-white/5"
                        >
                            <Sparkles class="size-3.5" />
                            Akselerasi Penuh
                        </div>
                        <h3
                            class="bg-gradient-to-r from-indigo-800 to-violet-700 bg-clip-text font-display text-2xl font-extrabold tracking-tight text-transparent sm:text-3xl dark:from-indigo-200 dark:to-violet-200"
                        >
                            Sinkronisasi Awal
                        </h3>
                        <p
                            class="text-sm leading-relaxed font-medium text-slate-600 dark:text-indigo-200/70"
                        >
                            Algoritma pintar kami butuh setelan kompas ini agar
                            Dasbor tidak menyodorkan agenda <i>"random"</i> pada
                            langkah pertamamu.
                        </p>
                    </div>

                    <div class="space-y-3 sm:space-y-4">
                        <div
                            class="flex items-start gap-4 rounded-2xl border border-indigo-100/50 bg-white/70 p-4 shadow-sm backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-white dark:border-white/5 dark:bg-white/5 dark:shadow-inner dark:hover:border-white/10 dark:hover:bg-white/10"
                        >
                            <div
                                class="shrink-0 rounded-xl bg-orange-100 p-2.5 text-orange-600 shadow-sm dark:bg-orange-500/20 dark:text-orange-400 dark:shadow-inner"
                            >
                                <Target class="size-5" />
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                    Target Tak Melenceng
                                </h4>
                                <p
                                    class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                                >
                                    Study Plan-mu disusun terstruktur hari demi
                                    hari agar terhindar dari materi bertele.
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex items-start gap-4 rounded-2xl border border-indigo-100/50 bg-white/70 p-4 shadow-sm backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-white dark:border-white/5 dark:bg-white/5 dark:shadow-inner dark:hover:border-white/10 dark:hover:bg-white/10"
                        >
                            <div
                                class="shrink-0 rounded-xl bg-emerald-100 p-2.5 text-emerald-600 shadow-sm dark:bg-emerald-500/20 dark:text-emerald-400 dark:shadow-inner"
                            >
                                <Compass class="size-5" />
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                    Dasbor Evolutif
                                </h4>
                                <p
                                    class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                                >
                                    Metrik <i>Readiness</i>, akurasi, dan
                                    deretan taktik belajar akan dikalibrasi
                                    presisi otomatis.
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex items-start gap-4 rounded-2xl border border-indigo-100/50 bg-white/70 p-4 shadow-sm backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-white dark:border-white/5 dark:bg-white/5 dark:shadow-inner dark:hover:border-white/10 dark:hover:bg-white/10"
                        >
                            <div
                                class="shrink-0 rounded-xl bg-violet-100 p-2.5 text-violet-600 shadow-sm dark:bg-violet-500/20 dark:text-violet-400 dark:shadow-inner"
                            >
                                <ArrowRight class="size-5" />
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                    Langsung Tancap Gas!
                                </h4>
                                <p
                                    class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                                >
                                    Diset kilat. Simpan sekali, dan kamu bisa
                                    instan melompat ke Simulasi Ujian.
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
