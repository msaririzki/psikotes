<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, FolderGit2 } from 'lucide-vue-next';
import BelajarProgresBadge from '@/components/learn/BelajarProgresBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { dashboard } from '@/routes';

type ProgressSummary = {
    completed: number;
    in_progress: number;
    not_started: number;
    completion_rate: number;
};

type SubtestItem = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    instruction_excerpt: string | null;
    progress: ProgressSummary;
    modules_count: number;
    featured_modules: Array<{
        id: number;
        title: string;
        slug: string;
        progress: {
            status: 'not_started' | 'in_progress' | 'completed';
            label: string;
        };
    }>;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Belajar', href: '/learn' },
            { title: 'Category', href: '#' },
        ],
    },
});

defineProps<{
    category: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        progress: ProgressSummary;
    };
    subtests: SubtestItem[];
}>();
</script>

<template>
    <Head :title="category.name" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <section
            class="overflow-hidden rounded-[2rem] border border-[#dbe6ee] bg-[radial-gradient(circle_at_top_left,_rgba(188,24,24,0.14),_transparent_30%),linear-gradient(135deg,_#f8fbff_0%,_#eef5fb_48%,_#ffffff_100%)] p-6 shadow-sm"
        >
            <div class="flex items-start gap-3">
                <FolderGit2 class="mt-1 size-6 text-[#b91c1c]" />
                <div class="space-y-3">
                    <div>
                        <h1
                            class="font-display text-4xl font-bold tracking-tight text-slate-950"
                        >
                            {{ category.name }}
                        </h1>
                        <p
                            class="mt-3 max-w-3xl text-base leading-7 text-slate-600"
                        >
                            {{ category.description }}
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <BelajarProgresBadge
                            :status="
                                category.progress.completion_rate === 100
                                    ? 'completed'
                                    : category.progress.in_progress > 0
                                      ? 'in_progress'
                                      : 'not_started'
                            "
                            :label="`${category.progress.completion_rate}% selesai`"
                        />
                        <span class="text-sm text-slate-500">
                            {{ category.progress.completed }} modul selesai •
                            {{ category.progress.in_progress }} sedang
                            dipelajari
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
            <Card
                v-for="subtest in subtests"
                :key="subtest.id"
                class="rounded-[1.9rem] border-[#dfe8ef] bg-white/95 shadow-sm"
            >
                <CardContent class="space-y-5 p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-2">
                            <p
                                class="font-display text-2xl font-bold tracking-tight text-slate-950"
                            >
                                {{ subtest.name }}
                            </p>
                            <p class="text-sm leading-6 text-slate-600">
                                {{
                                    subtest.description ||
                                    subtest.instruction_excerpt
                                }}
                            </p>
                        </div>
                        <BelajarProgresBadge
                            :status="
                                subtest.progress.completion_rate === 100
                                    ? 'completed'
                                    : subtest.progress.in_progress > 0
                                      ? 'in_progress'
                                      : 'not_started'
                            "
                            :label="`${subtest.progress.completion_rate}%`"
                        />
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <div
                            class="rounded-2xl bg-[#f8fbff] p-4 text-sm text-slate-600"
                        >
                            <p class="text-slate-500">Modul</p>
                            <p
                                class="mt-2 text-xl font-semibold text-slate-950"
                            >
                                {{ subtest.modules_count }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl bg-[#f8fbff] p-4 text-sm text-slate-600"
                        >
                            <p class="text-slate-500">Selesai</p>
                            <p
                                class="mt-2 text-xl font-semibold text-slate-950"
                            >
                                {{ subtest.progress.completed }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl bg-[#f8fbff] p-4 text-sm text-slate-600"
                        >
                            <p class="text-slate-500">In progress</p>
                            <p
                                class="mt-2 text-xl font-semibold text-slate-950"
                            >
                                {{ subtest.progress.in_progress }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="subtest.featured_modules.length > 0"
                        class="space-y-3"
                    >
                        <div
                            v-for="module in subtest.featured_modules"
                            :key="module.id"
                            class="rounded-2xl border border-[#e7edf2] bg-white p-4"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <p class="font-medium text-slate-900">
                                    {{ module.title }}
                                </p>
                                <BelajarProgresBadge
                                    :status="module.progress.status"
                                    :label="module.progress.label"
                                />
                            </div>
                        </div>
                    </div>

                    <Button
                        as-child
                        class="rounded-2xl bg-[#0f172a] text-white hover:bg-[#111827]"
                    >
                        <Link
                            :href="`/learn/categories/${category.slug}/subtests/${subtest.slug}`"
                        >
                            Buka subtes
                            <ArrowRight class="ml-2 size-4" />
                        </Link>
                    </Button>
                </CardContent>
            </Card>
        </section>
    </div>
</template>


