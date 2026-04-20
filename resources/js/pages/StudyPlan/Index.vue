<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    AlarmClock,
    ArrowRight,
    CalendarRange,
    Compass,
    ListTodo,
    Sparkles,
    Target,
} from 'lucide-vue-next';
import GoalProgressCard from '@/components/study-plan/GoalProgressCard.vue';
import KesiapanMilestoneCard from '@/components/study-plan/KesiapanMilestoneCard.vue';
import KesiapanSubtestCard from '@/components/study-plan/KesiapanSubtestCard.vue';
import KesiapanSummaryCard from '@/components/study-plan/KesiapanSummaryCard.vue';
import StudyTaskCard from '@/components/study-plan/StudyTaskCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { StudyPlanPayload } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Rencana Belajar', href: '/study-plan' },
        ],
    },
});

defineProps<{
    studyPlan: StudyPlanPayload;
}>();
</script>

<template>
    <Head title="Rencana Belajar" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <section
            class="overflow-hidden rounded-[2rem] border border-[#dbe6ee] bg-[radial-gradient(circle_at_top_left,_rgba(15,23,42,0.14),_transparent_30%),linear-gradient(135deg,_#f8fbff_0%,_#eef5fb_46%,_#ffffff_100%)] p-6 shadow-sm"
        >
            <div class="grid gap-6 xl:grid-cols-[1.08fr,0.92fr] xl:items-end">
                <div class="space-y-4">
                    <div class="inline-flex items-center rounded-full bg-[#0f172a] px-4 py-2 text-xs font-semibold tracking-[0.18em] text-white uppercase">
                        Perencanaan Belajar Adaptif
                    </div>
                    <div>
                        <h1 class="font-display text-4xl font-bold tracking-tight text-slate-950">
                            Rencana belajar yang menjelaskan langkah berikutnya secara konkret.
                        </h1>
                        <p class="mt-3 max-w-3xl text-base leading-7 text-slate-600">
                            Rencana belajar ini tidak dibuat secara acak. Sistem membaca area terlemah, pola stagnasi, kesiapan menuju simulasi, dan materi yang tertinggal untuk menyusun aksi berikutnya.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <Button as-child class="rounded-2xl bg-[#b91c1c] text-white hover:bg-[#991b1b]">
                        <Link href="/progress">
                            Buka progres detail
                            <ArrowRight class="ml-2 size-4" />
                        </Link>
                    </Button>
                    <Button as-child variant="outline" class="rounded-2xl">
                        <Link href="/history">Lihat riwayat</Link>
                    </Button>
                </div>
            </div>
        </section>

        <KesiapanSummaryCard :readiness="studyPlan.readiness" />

        <section class="space-y-5">
            <div class="flex items-center gap-2">
                <Target class="size-5 text-[#b91c1c]" />
                <h2 class="font-display text-2xl font-bold tracking-tight text-slate-950">
                    Pelacakan target
                </h2>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-[1.5rem] border border-[#dfe8ef] bg-white/95 p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Target sesuai jalur</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">
                        {{ studyPlan.goal_tracking.summary.on_track_goals }}
                    </p>
                </div>
                <div class="rounded-[1.5rem] border border-[#dfe8ef] bg-white/95 p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Target di luar jalur</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">
                        {{ studyPlan.goal_tracking.summary.off_track_goals }}
                    </p>
                </div>
                <div class="rounded-[1.5rem] border border-[#dfe8ef] bg-white/95 p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Target tercapai</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">
                        {{ studyPlan.goal_tracking.summary.completed_goals }}
                    </p>
                </div>
                <div class="rounded-[1.5rem] border border-[#dfe8ef] bg-white/95 p-5 shadow-sm md:col-span-2">
                    <p class="text-sm text-slate-500">Target kesiapan aktif</p>
                    <p class="mt-2 text-xl font-semibold text-slate-950">
                        {{ studyPlan.goal_tracking.summary.readiness_target }}
                    </p>
                    <p class="mt-1 text-sm text-slate-500">
                        Fokus utama saat ini: {{ studyPlan.goal_tracking.summary.primary_focus }}
                    </p>
                </div>
            </div>

            <div class="grid gap-5 xl:grid-cols-2">
                <GoalProgressCard
                    v-for="goal in studyPlan.goal_tracking.active_goals"
                    :key="goal.key"
                    :goal="goal"
                />
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-[1.5rem] border border-[#dfe8ef] bg-white/95 p-5 shadow-sm">
                <p class="text-sm text-slate-500">Task terbuka</p>
                <p class="mt-2 text-3xl font-semibold text-slate-950">
                    {{ studyPlan.execution_summary.open_tasks }}
                </p>
            </div>
            <div class="rounded-[1.5rem] border border-[#dfe8ef] bg-white/95 p-5 shadow-sm">
                <p class="text-sm text-slate-500">Jatuh tempo hari ini</p>
                <p class="mt-2 text-3xl font-semibold text-slate-950">
                    {{ studyPlan.execution_summary.due_today }}
                </p>
            </div>
            <div class="rounded-[1.5rem] border border-[#dfe8ef] bg-white/95 p-5 shadow-sm">
                <p class="text-sm text-slate-500">Terlambat</p>
                <p class="mt-2 text-3xl font-semibold text-slate-950">
                    {{ studyPlan.execution_summary.overdue_tasks }}
                </p>
            </div>
            <div class="rounded-[1.5rem] border border-[#dfe8ef] bg-white/95 p-5 shadow-sm">
                <p class="text-sm text-slate-500">Task selesai</p>
                <p class="mt-2 text-3xl font-semibold text-slate-950">
                    {{ studyPlan.readiness_progress.summary.completed_tasks }}
                </p>
            </div>
            <div class="rounded-[1.5rem] border border-[#dfe8ef] bg-[#0f172a] p-5 text-white shadow-sm">
                <p class="text-sm text-slate-300">Progres eksekusi</p>
                <p class="mt-2 text-3xl font-semibold">
                    {{ studyPlan.readiness_progress.summary.execution_completion_rate }}%
                </p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.08fr,0.92fr]">
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
                    redirect-to="/study-plan"
                />

                <Card
                    v-else
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardContent class="p-6 text-sm text-slate-500">
                        Belum ada aksi prioritas yang cukup kuat. Tambahkan lebih banyak histori belajar agar adaptive plan bisa lebih spesifik.
                    </CardContent>
                </Card>

                <div class="flex items-center gap-2">
                    <CalendarRange class="size-5 text-[#b91c1c]" />
                    <h2 class="font-display text-2xl font-bold tracking-tight text-slate-950">
                        Agenda hari ini
                    </h2>
                </div>

                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardContent class="space-y-4 p-6">
                        <StudyTaskCard
                            v-for="task in studyPlan.agenda.today"
                            :key="task.id"
                            :task="task"
                            compact
                            actionable
                            redirect-to="/study-plan"
                        />
                        <p
                            v-if="studyPlan.agenda.today.length === 0"
                            class="text-sm text-slate-500"
                        >
                            Belum ada task yang dijadwalkan untuk hari ini.
                        </p>
                    </CardContent>
                </Card>

                <div class="flex items-center gap-2">
                    <Sparkles class="size-5 text-[#b91c1c]" />
                    <h2 class="font-display text-2xl font-bold tracking-tight text-slate-950">
                        Rekomendasi prioritas
                    </h2>
                </div>

                <StudyTaskCard
                    v-for="task in studyPlan.priority_recommendations"
                    :key="task.id"
                    :task="task"
                    actionable
                    redirect-to="/study-plan"
                />
            </div>

            <div class="space-y-5">
                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle>Antrean review</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <StudyTaskCard
                            v-for="task in studyPlan.review_queue"
                            :key="task.id"
                            :task="task"
                            compact
                            actionable
                            redirect-to="/study-plan"
                        />
                        <p
                            v-if="studyPlan.review_queue.length === 0"
                            class="text-sm text-slate-500"
                        >
                            Belum ada antrean review yang mendesak.
                        </p>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <AlarmClock class="size-5 text-[#b91c1c]" />
                            Agenda minggu ini
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <StudyTaskCard
                            v-for="task in studyPlan.agenda.this_week"
                            :key="task.id"
                            :task="task"
                            compact
                            actionable
                            redirect-to="/study-plan"
                        />
                        <p
                            v-if="studyPlan.agenda.this_week.length === 0"
                            class="text-sm text-slate-500"
                        >
                            Belum ada task minggu ini di luar agenda hari ini.
                        </p>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <ListTodo class="size-5 text-[#b91c1c]" />
                            Terlambat dan prioritas tinggi
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <StudyTaskCard
                            v-for="task in [
                                ...studyPlan.agenda.overdue,
                                ...studyPlan.agenda.high_priority,
                            ].slice(0, 4)"
                            :key="task.id"
                            :task="task"
                            compact
                            actionable
                            redirect-to="/study-plan"
                        />
                        <p
                            v-if="
                                studyPlan.agenda.overdue.length === 0 &&
                                studyPlan.agenda.high_priority.length === 0
                            "
                            class="text-sm text-slate-500"
                        >
                            Tidak ada task mendesak yang perlu dibersihkan.
                        </p>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle>Ritme review</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <StudyTaskCard
                            v-for="task in studyPlan.review_cadence.upcoming_review_queue"
                            :key="task.id"
                            :task="task"
                            compact
                            actionable
                            redirect-to="/study-plan"
                        />
                        <p
                            v-if="studyPlan.review_cadence.upcoming_review_queue.length === 0"
                            class="text-sm text-slate-500"
                        >
                            Belum ada cadence review yang perlu dijaga.
                        </p>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle>Cara prioritas ditentukan</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3 text-sm leading-6 text-slate-600">
                        <div
                            v-for="line in studyPlan.transparency"
                            :key="line"
                            class="rounded-2xl bg-[#fbfdff] p-4 ring-1 ring-[#e6edf3]"
                        >
                            {{ line }}
                        </div>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-[#0f172a] text-white shadow-sm">
                    <CardHeader>
                        <CardTitle>Setelah fokus utama</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3 text-sm text-slate-200">
                        <div
                            v-for="task in studyPlan.plan_sections.upcoming"
                            :key="task.id"
                            class="rounded-2xl border border-white/10 bg-white/5 p-4"
                        >
                            <p class="font-semibold text-white">
                                {{ task.title }}
                            </p>
                            <p class="mt-2">{{ task.reason }}</p>
                        </div>
                        <p
                            v-if="studyPlan.plan_sections.upcoming.length === 0"
                            class="text-slate-300"
                        >
                            Belum ada antrian aksi lanjutan.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </section>

        <section class="space-y-5">
            <div class="flex items-center gap-2">
                <Target class="size-5 text-[#b91c1c]" />
                <h2 class="font-display text-2xl font-bold tracking-tight text-slate-950">
                    Progres kesiapan
                </h2>
            </div>

            <div class="grid gap-5 xl:grid-cols-2">
                <KesiapanMilestoneCard
                    v-for="milestone in studyPlan.readiness_progress.milestones"
                    :key="milestone.id"
                    :milestone="milestone"
                />
            </div>
        </section>

        <section class="space-y-5">
            <div class="flex items-center gap-2">
                <Compass class="size-5 text-[#b91c1c]" />
                <h2 class="font-display text-2xl font-bold tracking-tight text-slate-950">
                    Kesiapan per subtes
                </h2>
            </div>

            <div class="grid gap-5 xl:grid-cols-3">
                <KesiapanSubtestCard
                    v-for="item in studyPlan.readiness_progress.subtests"
                    :key="item.subtest_slug"
                    :item="item"
                />
            </div>
        </section>

        <section>
            <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-[#0f172a] text-white shadow-sm">
                <CardHeader>
                    <CardTitle>Baru diselesaikan</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3 text-sm text-slate-200">
                    <div
                        v-for="task in studyPlan.agenda.completed_recently"
                        :key="task.id"
                        class="rounded-2xl border border-white/10 bg-white/5 p-4"
                    >
                        <p class="font-semibold text-white">
                            {{ task.title }}
                        </p>
                        <p class="mt-2">{{ task.reason }}</p>
                    </div>
                    <p
                        v-if="studyPlan.agenda.completed_recently.length === 0"
                        class="text-slate-300"
                    >
                        Belum ada task yang ditandai selesai.
                    </p>
                </CardContent>
            </Card>
        </section>
    </div>
</template>



