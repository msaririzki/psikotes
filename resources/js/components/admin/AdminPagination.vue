<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';
import type { PaginationLink } from '@/types/cms';

defineProps<{
    links: PaginationLink[];
}>();

function labelText(label: string): string {
    return label
        .replaceAll('&laquo;', '«')
        .replaceAll('&raquo;', '»')
        .replace(/<[^>]+>/g, '')
        .trim();
}
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <template v-for="link in links" :key="`${link.label}-${link.url}`">
            <span
                v-if="!link.url"
                class="inline-flex h-10 items-center rounded-2xl border border-dashed border-slate-200 px-4 text-sm text-slate-400"
            >
                {{ labelText(link.label) }}
            </span>
            <Link
                v-else
                :href="link.url"
                class="inline-flex h-10 items-center rounded-2xl border px-4 text-sm transition"
                :class="
                    cn(
                        link.active
                            ? 'border-[#7f1d1d] bg-[#7f1d1d] text-white'
                            : 'border-slate-200 bg-white text-slate-600 hover:border-[#d3b4a6] hover:bg-[#fff7f3]',
                    )
                "
            >
                {{ labelText(link.label) }}
            </Link>
        </template>
    </div>
</template>
