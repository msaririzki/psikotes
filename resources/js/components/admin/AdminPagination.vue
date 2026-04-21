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
                class="inline-flex h-10 items-center rounded-2xl border border-dashed border-border px-4 text-sm text-muted-foreground"
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
                            : 'border-border bg-card text-muted-foreground hover:border-primary/40 hover:bg-muted/60 hover:text-foreground',
                    )
                "
            >
                {{ labelText(link.label) }}
            </Link>
        </template>
    </div>
</template>
