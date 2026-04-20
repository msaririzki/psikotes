<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BookCheck, BrainCircuit, Clock3, Sparkles, Target } from 'lucide-vue-next';
import { computed } from 'vue';
import type { HistoryTimelineItem } from '@/types';

const props = defineProps<{
    item: HistoryTimelineItem;
}>();

const icon = computed(() =>
    ({
        learn: BrainCircuit,
        mini_quiz: Sparkles,
        practice: BookCheck,
        simulation: Clock3,
    })[props.item.kind],
);

const toneClass = computed(() =>
    ({
        learn: 'bg-sky-50 text-sky-700 ring-sky-200',
        mini_quiz: 'bg-amber-50 text-amber-700 ring-amber-200',
        practice: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        simulation: 'bg-slate-100 text-slate-800 ring-slate-200',
    })[props.item.kind],
);
</script>

<template>
    <div class="rounded-[1.6rem] border border-[#dfe8ef] bg-white/95 p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1"
                        :class="toneClass"
                    >
                        <component :is="icon" class="size-3.5" />
                        {{ item.kind_label }}
                    </span>
                    <span class="text-xs text-slate-500">
                        {{ item.status_label }}
                    </span>
                </div>
                <div>
                    <p class="font-display text-2xl font-bold tracking-tight text-slate-950">
                        {{ item.title }}
                    </p>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ item.subtitle }}
                        <template v-if="item.category_name">
                            · {{ item.category_name }}
                        </template>
                    </p>
                </div>
                <p class="max-w-3xl text-sm leading-6 text-slate-600">
                    {{ item.description }}
                </p>
            </div>

            <div class="flex flex-col items-start gap-3 rounded-[1.4rem] bg-[#f8fbff] p-4 lg:min-w-[220px]">
                <div class="inline-flex items-center gap-2 text-sm font-semibold text-slate-900">
                    <Target class="size-4 text-[#b91c1c]" />
                    {{ item.metric_label }}
                </div>
                <p class="text-xs text-slate-500">
                    {{ new Date(item.occurred_at).toLocaleString('id-ID') }}
                </p>
                <Link
                    :href="item.href"
                    class="inline-flex rounded-full bg-[#0f172a] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#111827]"
                >
                    Buka detail
                </Link>
            </div>
        </div>
    </div>
</template>


