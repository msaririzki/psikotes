<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    AlarmClock,
    ArrowRight,
    ChartColumnBig,
    Compass,
    ListTodo,
    Sparkles,
    Target,
} from 'lucide-vue-next';
import GoalProgressCard from '@/components/study-plan/GoalProgressCard.vue';
import ReadinessMilestoneCard from '@/components/study-plan/ReadinessMilestoneCard.vue';
import ReadinessSummaryCard from '@/components/study-plan/ReadinessSummaryCard.vue';
import StudyTaskCard from '@/components/study-plan/StudyTaskCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type {
    ProgressSummary,
    StudyPlanPayload,
    SubtestAnalyticsItem,
} from '@/types';

type DasborOverview = {
    user: {
        name: string;
        role_label: string | null;
        onboarding_completed: boolean;
        email_verified_at: string | null;
    };
    catalog: {
        categories: number;
        subtests: number;
        modules: number;
        questions: number;
    };
    progress: {
        attempts: number;
        latest_attempt_started_at: string | null;
        latest_attempt_mode: string | null;
    };
};

defineProps<{
    overview: DasborOverview;
    progressSnapshot: {
        summary: ProgressSummary;
        insights: {
            strongest_area: SubtestAnalyticsItem | null;
            weakest_area: SubtestAnalyticsItem | null;
        };
    };
    studyPlan: StudyPlanPayload;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dasbor',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dasbor Premium" />

    <div class="page-shell min-h-screen bg-transparent">
        <!-- Hero Section Berteknologi Tinggi (Dark & Glassmorphism) -->
        <section
            class="page-hero group relative rounded-[1.9rem] border-slate-200/60 bg-gradient-to-br from-indigo-50/80 via-white to-sky-50/80 shadow-xl shadow-slate-200/50 transition-all duration-500 sm:rounded-[2.5rem] dark:border-white/5 dark:from-[#0c111d] dark:via-[#111827] dark:to-[#0c111d] dark:shadow-2xl dark:shadow-indigo-900/10"
        >
            <!-- Aksen Glow/Blur di background hero section -->
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
                class="relative z-10 grid gap-6 sm:gap-8 xl:grid-cols-[1.1fr,0.9fr] xl:items-end"
            >
                <div class="space-y-5 sm:space-y-6">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-indigo-200/60 bg-indigo-100/50 px-3 py-1.5 text-[0.65rem] font-semibold tracking-[0.24em] text-indigo-700 uppercase shadow-inner shadow-white/50 backdrop-blur-md sm:px-4 sm:py-2 sm:text-xs dark:border-indigo-500/30 dark:bg-indigo-400/10 dark:text-indigo-300 dark:shadow-white/5"
                    >
                        <Sparkles class="size-3.5" />
                        Dasbor Adaptif Cerdas
                    </div>
                    <div>
                        <h1
                            class="font-display text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl md:text-5xl lg:text-5xl/tight dark:text-white"
                        >
                            Halo,
                            <span
                                class="block bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-transparent sm:inline dark:from-indigo-200 dark:to-violet-200"
                            >
                                {{ overview.user.name }}
                            </span>
                        </h1>
                        <p
                            class="mt-3 max-w-2xl text-sm leading-7 text-slate-600 sm:mt-4 sm:text-base dark:text-indigo-100/70"
                        >
                            Navigator belajarmu sudah siap. Fokusmu berikutnya
                            diekstraksi secara real-time dari metrik progres,
                            riwayat latihan, dan tingkat kesiapan terkini.
                        </p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 sm:gap-4">
                    <div
                        class="group/card rounded-[1.35rem] border border-slate-200/60 bg-white/70 p-4 shadow-sm backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:border-indigo-300/60 hover:bg-white/90 hover:shadow-xl hover:shadow-indigo-900/10 sm:rounded-[1.75rem] sm:p-6 dark:border-white/10 dark:bg-card/5 dark:hover:border-indigo-400/40 dark:hover:bg-card/10 dark:hover:shadow-indigo-900/50"
                    >
                        <p
                            class="text-xs font-medium text-slate-500 transition-colors group-hover/card:text-indigo-600 sm:text-sm dark:text-slate-400 dark:group-hover/card:text-indigo-200"
                        >
                            Peran
                        </p>
                        <p
                            class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white"
                        >
                            {{ overview.user.role_label }}
                        </p>
                    </div>
                    <div
                        class="group/card rounded-[1.35rem] border border-slate-200/60 bg-white/70 p-4 shadow-sm backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:border-indigo-300/60 hover:bg-white/90 hover:shadow-xl hover:shadow-indigo-900/10 sm:rounded-[1.75rem] sm:p-6 dark:border-white/10 dark:bg-card/5 dark:hover:border-indigo-400/40 dark:hover:bg-card/10 dark:hover:shadow-indigo-900/50"
                    >
                        <p
                            class="text-xs font-medium text-slate-500 transition-colors group-hover/card:text-indigo-600 sm:text-sm dark:text-slate-400 dark:group-hover/card:text-indigo-200"
                        >
                            Akurasi rata-rata
                        </p>
                        <p
                            class="mt-2 flex items-baseline gap-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white"
                        >
                            {{ progressSnapshot.summary.average_accuracy ?? 0
                            }}<span
                                class="text-lg text-indigo-500 sm:text-xl dark:text-indigo-300"
                                >%</span
                            >
                        </p>
                    </div>
                    <div
                        class="group/card rounded-[1.35rem] border border-slate-200/60 bg-white/70 p-4 shadow-sm backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:border-amber-300/60 hover:bg-amber-50/90 hover:shadow-xl hover:shadow-amber-900/10 sm:rounded-[1.75rem] sm:p-6 dark:border-white/10 dark:bg-card/5 dark:hover:border-amber-400/40 dark:hover:bg-amber-900/20 dark:hover:shadow-amber-900/30"
                    >
                        <p
                            class="text-xs font-medium text-slate-500 transition-colors group-hover/card:text-amber-600 sm:text-sm dark:text-slate-400 dark:group-hover/card:text-amber-200"
                        >
                            Jatuh tempo hari ini
                        </p>
                        <p
                            class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white"
                        >
                            {{ studyPlan.execution_summary.due_today }}
                        </p>
                    </div>
                    <div
                        class="group/card rounded-[1.35rem] border border-slate-200/60 bg-white/70 p-4 shadow-sm backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:border-rose-300/60 hover:bg-rose-50/90 hover:shadow-xl hover:shadow-rose-900/10 sm:rounded-[1.75rem] sm:p-6 dark:border-white/10 dark:bg-card/5 dark:hover:border-rose-400/40 dark:hover:bg-rose-900/20 dark:hover:shadow-rose-900/30"
                    >
                        <p
                            class="text-xs font-medium text-slate-500 transition-colors group-hover/card:text-rose-600 sm:text-sm dark:text-slate-400 dark:group-hover/card:text-rose-200"
                        >
                            Terlambat
                        </p>
                        <p
                            class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white"
                        >
                            {{ studyPlan.execution_summary.overdue_tasks }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Orientasi Alert (Bila belum selesai) -->
        <Transition
            enter-active-class="transition duration-500 ease-out"
            enter-from-class="transform -translate-y-4 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
            leave-active-class="transition duration-400 ease-in"
            leave-from-class="transform translate-y-0 opacity-100"
            leave-to-class="transform -translate-y-4 opacity-0"
        >
            <section
                v-if="!overview.user.onboarding_completed"
                class="relative overflow-hidden rounded-[1.75rem] border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50 p-5 shadow-lg shadow-amber-100/50 sm:rounded-[2rem] sm:p-8 dark:border-amber-900/50 dark:from-amber-950/40 dark:to-orange-950/40 dark:shadow-none"
            >
                <div
                    class="absolute top-0 right-0 size-64 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCI+PHBhdGggZD0iTTIwIDAgTDIwIDQwIE0wIDIwIEw0MCAyMCIgc3Ryb2tlPSIjMDAwIiBzdHJva2Utd2lkdGg9IjIiLz48L3N2Zz4=')] opacity-10 mix-blend-overlay"
                ></div>
                <div
                    class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div>
                        <div
                            class="inline-flex items-center gap-2 text-sm font-bold tracking-wide text-amber-700 uppercase dark:text-amber-500"
                        >
                            <span class="relative flex h-3 w-3">
                                <span
                                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"
                                ></span>
                                <span
                                    class="relative inline-flex h-3 w-3 rounded-full bg-amber-500"
                                ></span>
                            </span>
                            Orientasi belum selesai
                        </div>
                        <p
                            class="mt-3 max-w-3xl text-base leading-relaxed font-medium text-amber-900/80 sm:text-lg dark:text-amber-200/70"
                        >
                            Target harian dan rekomendasi personal kami
                            bergantung pada profil belajarmu. Luangkan 2 menit
                            menyelesaikan orientasi agar algoritma bisa menyusun
                            lintasan terbaik.
                        </p>
                    </div>
                    <Button
                        as-child
                        class="h-12 w-full rounded-2xl bg-amber-600 px-6 text-sm text-white shadow-lg shadow-amber-600/30 transition-all duration-300 hover:-translate-y-0.5 hover:bg-amber-700 hover:shadow-xl hover:shadow-amber-700/40 sm:w-auto sm:px-8 sm:text-base"
                    >
                        <Link href="/onboarding">Selesaikan orientasi</Link>
                    </Button>
                </div>
            </section>
        </Transition>

        <ReadinessSummaryCard
            :readiness="studyPlan.readiness"
            class="transition-all duration-500 hover:-translate-y-1 hover:shadow-xl"
        />

        <!-- Statistik Status Target -->
        <section class="space-y-5 sm:space-y-6">
            <div class="grid gap-3 sm:gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div
                    class="group rounded-[1.35rem] border border-border/50 bg-card p-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl sm:rounded-[1.75rem] sm:p-6 dark:shadow-none"
                >
                    <div class="flex items-center justify-between">
                        <p
                            class="text-xs font-medium text-muted-foreground sm:text-sm"
                        >
                            Target sesuai jalur
                        </p>
                        <div
                            class="rounded-full bg-emerald-100 p-2 text-emerald-600 transition-transform group-hover:scale-110 dark:bg-emerald-500/20 dark:text-emerald-400"
                        >
                            <Target class="size-4" />
                        </div>
                    </div>
                    <p
                        class="mt-3 text-3xl font-extrabold tracking-tight text-foreground sm:mt-4 sm:text-4xl"
                    >
                        {{ studyPlan.goal_tracking.summary.on_track_goals }}
                    </p>
                </div>

                <div
                    class="group rounded-[1.35rem] border border-border/50 bg-card p-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl sm:rounded-[1.75rem] sm:p-6 dark:shadow-none"
                >
                    <div class="flex items-center justify-between">
                        <p
                            class="text-xs font-medium text-muted-foreground sm:text-sm"
                        >
                            Target keluar jalur
                        </p>
                        <div
                            class="rounded-full bg-rose-100 p-2 text-rose-600 transition-transform group-hover:scale-110 dark:bg-rose-500/20 dark:text-rose-400"
                        >
                            <AlarmClock class="size-4" />
                        </div>
                    </div>
                    <p
                        class="mt-3 text-3xl font-extrabold tracking-tight text-foreground sm:mt-4 sm:text-4xl"
                    >
                        {{ studyPlan.goal_tracking.summary.off_track_goals }}
                    </p>
                </div>

                <div
                    class="group rounded-[1.35rem] border border-border/50 bg-card p-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl sm:rounded-[1.75rem] sm:p-6 dark:shadow-none"
                >
                    <div class="flex items-center justify-between">
                        <p
                            class="text-xs font-medium text-muted-foreground sm:text-sm"
                        >
                            Target tercapai
                        </p>
                        <div
                            class="rounded-full bg-indigo-100 p-2 text-indigo-600 transition-transform group-hover:scale-110 dark:bg-indigo-500/20"
                        >
                            <Sparkles class="size-4" />
                        </div>
                    </div>
                    <p
                        class="mt-3 text-3xl font-extrabold tracking-tight text-foreground sm:mt-4 sm:text-4xl"
                    >
                        {{ studyPlan.goal_tracking.summary.completed_goals }}
                    </p>
                </div>

                <div
                    class="group relative overflow-hidden rounded-[1.35rem] bg-gradient-to-br from-indigo-600 to-violet-700 p-4 text-white shadow-lg shadow-indigo-600/30 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-indigo-600/40 sm:rounded-[1.75rem] sm:p-6"
                >
                    <div
                        class="absolute -top-6 -right-6 size-24 rounded-full bg-card/10 blur-[20px]"
                    ></div>
                    <div
                        class="relative z-10 flex items-center justify-between"
                    >
                        <p
                            class="text-xs font-medium text-indigo-100 sm:text-sm"
                        >
                            Target aktif
                        </p>
                        <div
                            class="rounded-full bg-card/20 p-2 text-white backdrop-blur-sm transition-transform duration-500 group-hover:rotate-12"
                        >
                            <Compass class="size-4" />
                        </div>
                    </div>
                    <p
                        class="relative z-10 mt-3 text-lg font-bold tracking-tight sm:mt-4 sm:text-2xl"
                    >
                        {{ studyPlan.goal_tracking.summary.readiness_target }}
                    </p>
                </div>
            </div>

            <div class="grid gap-4 sm:gap-6 xl:grid-cols-2">
                <GoalProgressCard
                    v-for="goal in studyPlan.goal_tracking.active_goals"
                    :key="goal.key"
                    :goal="goal"
                    compact
                    cta-href="/study-plan"
                    class="rounded-[1.75rem] border-border/50 bg-card transition-all duration-300 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-xl"
                />
            </div>
        </section>

        <!-- Area Tindakan Lanjut -->
        <section
            class="mt-2 grid gap-6 sm:mt-4 sm:gap-8 xl:grid-cols-[1.05fr,0.95fr]"
        >
            <div class="space-y-5 sm:space-y-6">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600 shadow-inner dark:bg-indigo-500/20"
                    >
                        <Compass class="size-5" />
                    </div>
                    <h2
                        class="font-display text-xl font-extrabold tracking-tight text-foreground sm:text-2xl md:text-3xl"
                    >
                        Aksi Terbaik Berikutnya
                    </h2>
                </div>

                <div
                    class="rounded-[1.75rem] transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-indigo-500/10"
                >
                    <StudyTaskCard
                        v-if="studyPlan.next_best_action"
                        :task="studyPlan.next_best_action"
                        actionable
                        redirect-to="/dashboard"
                    />
                </div>

                <div
                    class="flex flex-col gap-3 pt-1 sm:flex-row sm:flex-wrap sm:gap-4 sm:pt-2"
                >
                    <Button
                        as-child
                        class="h-11 w-full rounded-2xl bg-indigo-600 px-5 text-white shadow-lg shadow-indigo-600/30 transition-all duration-300 hover:-translate-y-0.5 hover:bg-indigo-700 hover:shadow-xl hover:shadow-indigo-700/40 sm:h-12 sm:w-auto sm:px-6"
                    >
                        <Link href="/study-plan">
                            Buka seluruh Rencana
                            <ArrowRight class="ml-2 size-4" />
                        </Link>
                    </Button>
                    <Button
                        as-child
                        variant="outline"
                        class="h-11 w-full rounded-2xl border-border/60 px-5 text-slate-700 shadow-sm transition-all duration-300 hover:bg-muted/30 hover:text-indigo-600 hover:shadow-md sm:h-12 sm:w-auto sm:px-6"
                    >
                        <Link href="/progress">Lihat progres detail</Link>
                    </Button>
                </div>

                <Card
                    class="glass-card transition-all duration-300 hover:shadow-xl"
                >
                    <CardHeader class="border-b border-border/40 pb-3">
                        <CardTitle
                            class="flex items-center gap-3 text-lg font-bold text-card-foreground"
                        >
                            <div
                                class="rounded-xl bg-orange-100 p-2 text-orange-600 dark:bg-orange-500/20 dark:text-orange-400"
                            >
                                <AlarmClock class="size-5" />
                            </div>
                            Agenda hari ini
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3 pt-4 sm:space-y-4 sm:pt-5">
                        <StudyTaskCard
                            v-for="task in studyPlan.agenda.today.slice(0, 2)"
                            :key="task.id"
                            :task="task"
                            compact
                            actionable
                            redirect-to="/dashboard"
                            class="rounded-2xl p-2 transition-all hover:bg-muted/30"
                        />
                        <div
                            v-if="studyPlan.agenda.today.length === 0"
                            class="border-borderashed rounded-xl border border-border/60 bg-muted/30 p-4 text-center text-sm font-medium text-muted-foreground sm:p-6"
                        >
                            Wah, kamu belum punya agenda task untuk hari ini.
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-5 sm:space-y-6">
                <Card
                    class="glass-card transition-all duration-300 hover:shadow-xl"
                >
                    <CardHeader class="border-b border-border/40 pb-3">
                        <CardTitle
                            class="flex items-center gap-3 text-lg font-bold text-card-foreground"
                        >
                            <div
                                class="rounded-xl bg-amber-100 p-2 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400"
                            >
                                <Sparkles class="size-5" />
                            </div>
                            Antrean Prioritas
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3 pt-4 sm:space-y-4 sm:pt-5">
                        <div
                            v-for="task in studyPlan.priority_recommendations.slice(
                                0,
                                3,
                            )"
                            :key="task.id"
                            class="group bg-muted/30/80 relative overflow-hidden rounded-2xl border border-border/40 p-4 transition-all hover:border-indigo-100 hover:bg-indigo-50/50 sm:p-5"
                        >
                            <div
                                class="absolute top-0 left-0 h-full w-1 bg-indigo-500 opacity-0 transition-opacity group-hover:opacity-100"
                            ></div>
                            <p
                                class="font-bold text-foreground transition-colors group-hover:text-indigo-900"
                            >
                                {{ task.title }}
                            </p>
                            <p
                                class="mt-2 text-sm leading-relaxed text-muted-foreground"
                            >
                                {{ task.reason }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6">
                    <Card
                        class="glass-card transition-all duration-300 hover:shadow-xl"
                    >
                        <CardHeader
                            class="flex items-center border-b border-border/40 pb-3 sm:h-[72px]"
                        >
                            <CardTitle
                                class="m-0 flex w-full items-center gap-3 text-lg font-bold text-card-foreground"
                            >
                                <div
                                    class="rounded-xl bg-emerald-100 p-2 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400"
                                >
                                    <ChartColumnBig class="size-4" />
                                </div>
                                Snapshot Area
                            </CardTitle>
                        </CardHeader>
                        <CardContent
                            class="space-y-3 pt-4 pb-4 sm:space-y-4 sm:pt-5 sm:pb-5"
                        >
                            <div
                                class="group relative overflow-hidden rounded-2xl border border-border/40 bg-muted/30 p-4"
                            >
                                <div
                                    class="absolute top-0 right-0 p-2 opacity-10 transition-opacity group-hover:opacity-20"
                                >
                                    <Target class="size-8 text-emerald-500" />
                                </div>
                                <p
                                    class="relative z-10 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Metrik Terkuat
                                </p>
                                <p
                                    class="relative z-10 mt-2 leading-tight font-bold text-foreground"
                                >
                                    {{
                                        progressSnapshot.insights.strongest_area
                                            ?.subtest_name ?? 'Belum terdeteksi'
                                    }}
                                </p>
                            </div>
                            <div
                                class="group relative overflow-hidden rounded-2xl border border-border/40 bg-muted/30 p-4"
                            >
                                <div
                                    class="absolute top-0 right-0 p-2 opacity-10 transition-opacity group-hover:opacity-20"
                                >
                                    <Target class="size-8 text-rose-500" />
                                </div>
                                <p
                                    class="relative z-10 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Area Terlemah
                                </p>
                                <p
                                    class="relative z-10 mt-2 leading-tight font-bold text-foreground"
                                >
                                    {{
                                        progressSnapshot.insights.weakest_area
                                            ?.subtest_name ?? 'Belum terdeteksi'
                                    }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card
                        class="glass-card flex flex-col transition-all duration-300 hover:shadow-xl"
                    >
                        <CardHeader
                            class="flex items-center border-b border-border/40 pb-3 sm:h-[72px]"
                        >
                            <CardTitle
                                class="m-0 flex w-full items-center gap-3 text-lg font-bold text-card-foreground"
                            >
                                <div
                                    class="rounded-xl bg-violet-100 p-2 text-violet-600 dark:bg-violet-500/20"
                                >
                                    <Target class="size-4" />
                                </div>
                                Radar Target
                            </CardTitle>
                        </CardHeader>
                        <CardContent
                            class="flex flex-1 flex-col space-y-3 pt-4 pb-4 sm:justify-around sm:space-y-4 sm:pt-5 sm:pb-5"
                        >
                            <div
                                class="rounded-2xl border border-border/40 bg-muted/30 p-4"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Kondisi Target
                                </p>
                                <p
                                    class="mt-1 flex items-center gap-2 font-bold text-foreground"
                                >
                                    <span
                                        class="inline-block size-2 rounded-full bg-violet-500"
                                    ></span>
                                    {{
                                        studyPlan.readiness_progress.summary
                                            .target_label
                                    }}
                                </p>
                            </div>
                            <div
                                class="mt-auto rounded-2xl border border-border/40 bg-muted/30 p-4"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Eksekusi
                                </p>
                                <div class="mt-3 flex items-center gap-3">
                                    <div
                                        class="h-2 w-full flex-1 overflow-hidden rounded-full bg-slate-200"
                                    >
                                        <div
                                            class="h-full rounded-full bg-violet-500 transition-all duration-1000"
                                            :style="`width: ${studyPlan.readiness_progress.summary.execution_completion_rate}%`"
                                        ></div>
                                    </div>
                                    <p
                                        class="text-lg leading-none font-extrabold text-foreground"
                                    >
                                        {{
                                            studyPlan.readiness_progress.summary
                                                .execution_completion_rate
                                        }}%
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- Milestones dan Agenda Mingguan -->
        <section
            class="mt-2 grid gap-6 pb-8 sm:mt-4 sm:gap-8 sm:pb-12 xl:grid-cols-2"
        >
            <div class="space-y-5 sm:space-y-6">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-100 text-sky-600 shadow-inner dark:bg-sky-500/20 dark:text-sky-400"
                    >
                        <ListTodo class="size-5" />
                    </div>
                    <h2
                        class="font-display text-xl font-extrabold tracking-tight text-foreground sm:text-2xl"
                    >
                        Agenda Minggu Ini
                    </h2>
                </div>
                <div class="space-y-4">
                    <StudyTaskCard
                        v-for="task in studyPlan.agenda.this_week.slice(0, 2)"
                        :key="task.id"
                        :task="task"
                        compact
                        actionable
                        redirect-to="/dashboard"
                        class="rounded-[1.75rem] border-border/50 bg-card transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:shadow-xl dark:shadow-none"
                    />
                    <Card
                        v-if="studyPlan.agenda.this_week.length === 0"
                        class="glass-card"
                    >
                        <CardContent
                            class="flex items-center justify-center p-5 text-sm font-medium text-muted-foreground sm:p-8"
                        >
                            Tak ada agenda di luar task harian. Bersantai
                            sedikit!
                        </CardContent>
                    </Card>
                </div>
            </div>

            <div class="space-y-5 sm:space-y-6">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-fuchsia-100 text-fuchsia-600 shadow-inner dark:bg-fuchsia-500/20 dark:text-fuchsia-400"
                    >
                        <Target class="size-5" />
                    </div>
                    <h2
                        class="font-display text-xl font-extrabold tracking-tight text-foreground sm:text-2xl"
                    >
                        Tonggak Milestone Berikutnya
                    </h2>
                </div>
                <div class="space-y-4">
                    <ReadinessMilestoneCard
                        v-for="milestone in studyPlan.readiness_progress.milestones.slice(
                            0,
                            2,
                        )"
                        :key="milestone.id"
                        :milestone="milestone"
                        class="group overflow-hidden rounded-[1.75rem] border-border/50 bg-card transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:shadow-xl dark:shadow-none"
                    />
                </div>
            </div>
        </section>
    </div>
</template>
