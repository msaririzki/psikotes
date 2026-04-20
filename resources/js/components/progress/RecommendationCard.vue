<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, BrainCircuit, Sparkles, Target, TriangleAlert } from 'lucide-vue-next';
import type { RecommendationItem } from '@/types';

defineProps<{
    item: RecommendationItem;
}>();

const toneClass = {
    warning: 'border-amber-200 bg-amber-50 text-amber-900',
    neutral: 'border-slate-200 bg-slate-50 text-slate-900',
    success: 'border-emerald-200 bg-emerald-50 text-emerald-900',
    accent: 'border-sky-200 bg-sky-50 text-sky-900',
};

const iconMap = {
    warning: TriangleAlert,
    neutral: Target,
    success: Sparkles,
    accent: BrainCircuit,
};
</script>

<template>
    <div
        class="rounded-[1.5rem] border p-5 shadow-sm"
        :class="toneClass[item.tone]"
    >
        <div class="flex items-start gap-3">
            <component :is="iconMap[item.tone]" class="mt-0.5 size-5" />
            <div class="space-y-3">
                <div>
                    <p class="font-display text-xl font-bold tracking-tight">
                        {{ item.headline }}
                    </p>
                    <p class="mt-2 text-sm leading-6 opacity-90">
                        {{ item.description }}
                    </p>
                </div>
                <p class="text-xs opacity-80">{{ item.reason }}</p>
                <Link
                    :href="item.action_href"
                    class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-medium text-slate-950 ring-1 ring-black/5 transition hover:bg-slate-100"
                >
                    {{ item.action_label }}
                    <ArrowRight class="size-4" />
                </Link>
            </div>
        </div>
    </div>
</template>
