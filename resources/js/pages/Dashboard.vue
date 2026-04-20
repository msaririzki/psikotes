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
import KesiapanMilestoneCard from '@/components/study-plan/KesiapanMilestoneCard.vue';
import KesiapanSummaryCard from '@/components/study-plan/KesiapanSummaryCard.vue';
import StudyTaskCard from '@/components/study-plan/StudyTaskCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { ProgressSummary, StudyPlanPayload, SubtestAnalyticsItem } from '@/types';

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
    <Head title="Dasbor" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <section
            class="overflow-hidden rounded-[2rem] border border-[#d9e3ec] bg-[radial-gradient(circle_at_top_left,_rgba(185,28,28,0.12),_transparent_32%),linear-gradient(135deg,_#f8fbff_0%,_#eef3f8_55%,_#ffffff_100%)] p-6 shadow-sm"
        >
            <div class="grid gap-6 xl:grid-cols-[1.1fr,0.9fr] xl:items-end">
                <div class="space-y-4">
                    <div class="inline-flex items-center rounded-full bg-[#0f172a] px-4 py-2 text-xs font-semibold tracking-[0.18em] text-white uppercase">
                        Dasbor Adaptif
                    </div>
                    <div>
                        <h1 class="font-display text-4xl font-bold tracking-tight text-[#0f172a]">
                            Halo, {{ overview.user.name }}.
                        </h1>
                        <p class="mt-3 max-w-3xl text-base leading-7 text-slate-600">
                            Dasbor ini sekarang berubah dari statistik pasif menjadi arah belajar aktif. Fokus berikutnya ditentukan dari riwayat, progres, dan kesiapan Anda sendiri.
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-[1.6rem] bg-white/90 p-5 shadow-sm ring-1 ring-white/70">
                        <p class="text-sm text-slate-500">Peran</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-950">
                            {{ overview.user.role_label }}
                        </p>
                    </div>
                    <div class="rounded-[1.6rem] bg-white/90 p-5 shadow-sm ring-1 ring-white/70">
                        <p class="text-sm text-slate-500">Akurasi rata-rata</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-950">
                            {{ progressSnapshot.summary.average_accuracy ?? 0 }}%
                        </p>
                    </div>
                    <div class="rounded-[1.6rem] bg-white/90 p-5 shadow-sm ring-1 ring-white/70">
                        <p class="text-sm text-slate-500">Jatuh tempo hari ini</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-950">
                            {{ studyPlan.execution_summary.due_today }}
                        </p>
                    </div>
                    <div class="rounded-[1.6rem] bg-white/90 p-5 shadow-sm ring-1 ring-white/70">
                        <p class="text-sm text-slate-500">Terlambat</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-950">
                            {{ studyPlan.execution_summary.overdue_tasks }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section
            v-if="!overview.user.onboarding_completed"
            class="rounded-[1.75rem] border border-[#fecaca] bg-[#fff5f5] p-5 shadow-sm"
        >
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[#b91c1c]">Orientasi belum selesai</p>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-700">
                        Selesaikan orientasi singkat dulu supaya target harian, rencana belajar, dan pelacakan target lebih relevan untuk akun ini.
                    </p>
                </div>
                <Button as-child class="rounded-2xl bg-[#b91c1c] text-white hover:bg-[#991b1b]">
                    <Link href="/onboarding">Selesaikan orientasi</Link>
                </Button>
            </div>
        </section>

        <KesiapanSummaryCard :readiness="studyPlan.readiness" />

        <section class="space-y-5">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-[1.5rem] border border-[#dbe4ec] bg-white/95 p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Target sesuai jalur</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">
                        {{ studyPlan.goal_tracking.summary.on_track_goals }}
                    </p>
                </div>
                <div class="rounded-[1.5rem] border border-[#dbe4ec] bg-white/95 p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Target di luar jalur</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">
                        {{ studyPlan.goal_tracking.summary.off_track_goals }}
                    </p>
                </div>
                <div class="rounded-[1.5rem] border border-[#dbe4ec] bg-white/95 p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Target tercapai</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">
                        {{ studyPlan.goal_tracking.summary.completed_goals }}
                    </p>
                </div>
                <div class="rounded-[1.5rem] border border-[#dbe4ec] bg-[#0f172a] p-5 text-white shadow-sm">
                    <p class="text-sm text-slate-300">Target aktif</p>
                    <p class="mt-2 text-lg font-semibold">
                        {{ studyPlan.goal_tracking.summary.readiness_target }}
                    </p>
                </div>
            </div>

            <div class="grid gap-5 xl:grid-cols-2">
                <GoalProgressCard
                    v-for="goal in studyPlan.goal_tracking.active_goals"
                    :key="goal.key"
                    :goal="goal"
                    compact
                    cta-href="/study-plan"
                />
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.05fr,0.95fr]">
            <div class="space-y-5">
                <div class="flex items-center gap-2">
                    <Compass class="size-5 text-[#b91c1c]" />
                    <h2 class="font-display text-2xl font-bold tracking-tight text-slate-950">
                        Aksi terbaik berikutnya
                    </h2>
                </div>

                <StudyTaskCard
                    v-if="studyPlan.next_best_action"
                    :task="studyPlan.next_best_action"
                    actionable
                    redirect-to="/dashboard"
                />

                <div class="flex flex-wrap gap-3">
                    <Button as-child class="rounded-2xl bg-[#b91c1c] text-white hover:bg-[#991b1b]">
                        <Link href="/study-plan">
                            Buka rencana belajar
                            <ArrowRight class="ml-2 size-4" />
                        </Link>
                    </Button>
                    <Button as-child variant="outline" class="rounded-2xl">
                        <Link href="/progress">Lihat progres detail</Link>
                    </Button>
                </div>

                <Card class="rounded-[1.75rem] border-[#dbe4ec] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <AlarmClock class="size-5 text-[#b91c1c]" />
                            Agenda hari ini
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <StudyTaskCard
                            v-for="task in studyPlan.agenda.today.slice(0, 2)"
                            :key="task.id"
                            :task="task"
                            compact
                            actionable
                            redirect-to="/dashboard"
                        />
                        <p
                            v-if="studyPlan.agenda.today.length === 0"
                            class="text-sm text-slate-500"
                        >
                            Belum ada task hari ini.
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-5">
                <Card class="rounded-[1.75rem] border-[#dbe4ec] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Sparkles class="size-5 text-[#b91c1c]" />
                            Antrean prioritas
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            v-for="task in studyPlan.priority_recommendations.slice(0, 3)"
                            :key="task.id"
                            class="rounded-2xl bg-[#fbfdff] p-4 ring-1 ring-[#e6edf3]"
                        >
                            <p class="font-semibold text-slate-950">
                                {{ task.title }}
                            </p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                {{ task.reason }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.75rem] border-[#dbe4ec] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <ChartColumnBig class="size-5 text-[#b91c1c]" />
                            Area fokus
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm text-slate-600">
                        <div class="rounded-2xl bg-[#fbfdff] p-4 ring-1 ring-[#e6edf3]">
                            <p class="text-slate-500">Area terkuat</p>
                            <p class="mt-1 font-semibold text-slate-950">
                                {{
                                    progressSnapshot.insights.strongest_area?.subtest_name ??
                                    'Belum cukup data'
                                }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-[#fbfdff] p-4 ring-1 ring-[#e6edf3]">
                            <p class="text-slate-500">Area terlemah</p>
                            <p class="mt-1 font-semibold text-slate-950">
                                {{
                                    progressSnapshot.insights.weakest_area?.subtest_name ??
                                    'Belum cukup data'
                                }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.75rem] border-[#dbe4ec] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Target class="size-5 text-[#b91c1c]" />
                            Target kesiapan
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm text-slate-600">
                        <div class="rounded-2xl bg-[#fbfdff] p-4 ring-1 ring-[#e6edf3]">
                            <p class="text-slate-500">Target saat ini</p>
                            <p class="mt-1 font-semibold text-slate-950">
                                {{ studyPlan.readiness_progress.summary.target_label }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-[#fbfdff] p-4 ring-1 ring-[#e6edf3]">
                            <p class="text-slate-500">Progres eksekusi</p>
                            <p class="mt-1 font-semibold text-slate-950">
                                {{ studyPlan.readiness_progress.summary.execution_completion_rate }}%
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.75rem] border-[#dbe4ec] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <AlarmClock class="size-5 text-[#b91c1c]" />
                            Ritme review
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <StudyTaskCard
                            v-for="task in studyPlan.review_cadence.upcoming_review_queue.slice(0, 2)"
                            :key="task.id"
                            :task="task"
                            compact
                            actionable
                            redirect-to="/dashboard"
                        />
                        <p
                            v-if="studyPlan.review_cadence.upcoming_review_queue.length === 0"
                            class="text-sm text-slate-500"
                        >
                            Tidak ada ritme review yang mendesak.
                        </p>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.75rem] border-[#dbe4ec] bg-[#0f172a] text-white shadow-sm">
                    <CardHeader>
                        <CardTitle>Antrean review</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3 text-sm text-slate-200">
                        <div
                            v-for="task in studyPlan.review_queue.slice(0, 2)"
                            :key="task.id"
                            class="rounded-2xl border border-white/10 bg-white/5 p-4"
                        >
                            <p class="font-semibold text-white">
                                {{ task.title }}
                            </p>
                            <p class="mt-2">{{ task.reason }}</p>
                        </div>
                        <p
                            v-if="studyPlan.review_queue.length === 0"
                            class="text-slate-300"
                        >
                            Belum ada antrean review yang mendesak.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </section>

        <section class="grid gap-5 xl:grid-cols-2">
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <ListTodo class="size-5 text-[#b91c1c]" />
                    <h2 class="font-display text-2xl font-bold tracking-tight text-slate-950">
                        Agenda minggu ini
                    </h2>
                </div>
                <StudyTaskCard
                    v-for="task in studyPlan.agenda.this_week.slice(0, 2)"
                    :key="task.id"
                    :task="task"
                    compact
                    actionable
                    redirect-to="/dashboard"
                />
                <Card
                    v-if="studyPlan.agenda.this_week.length === 0"
                    class="rounded-[1.75rem] border-[#dbe4ec] bg-white/95 shadow-sm"
                >
                    <CardContent class="p-6 text-sm text-slate-500">
                        Belum ada agenda minggu ini di luar task hari ini.
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <Target class="size-5 text-[#b91c1c]" />
                    <h2 class="font-display text-2xl font-bold tracking-tight text-slate-950">
                        Milestone berikutnya
                    </h2>
                </div>
                <KesiapanMilestoneCard
                    v-for="milestone in studyPlan.readiness_progress.milestones.slice(0, 2)"
                    :key="milestone.id"
                    :milestone="milestone"
                />
            </div>
        </section>
    </div>
</template>



