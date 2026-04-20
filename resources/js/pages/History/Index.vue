<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Clock3, LibraryBig, ScrollText } from 'lucide-vue-next';
import { computed, reactive } from 'vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import HistoryTimelineCard from '@/components/history/HistoryTimelineCard.vue';
import { Card, CardContent } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { HistorySummary, HistoryTimelineItem, PaginatedResponse } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Riwayat', href: '/history' },
        ],
    },
});

const props = defineProps<{
    summary: HistorySummary;
    filters: {
        type: string;
        items: Array<{
            value: string;
            label: string;
        }>;
    };
    timeline: PaginatedResponse<HistoryTimelineItem>;
}>();

const form = reactive({
    type: props.filters.type ?? 'all',
});

const totalActivities = computed(
    () =>
        props.summary.learn +
        props.summary.mini_quiz +
        props.summary.practice +
        props.summary.simulation,
);

function applyFilter() {
    router.get(
        '/history',
        {
            type: form.type !== 'all' ? form.type : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}
</script>

<template>
    <Head title="Riwayat" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <section
            class="overflow-hidden rounded-[2rem] border border-[#dbe6ee] bg-[radial-gradient(circle_at_top_left,_rgba(15,23,42,0.12),_transparent_30%),linear-gradient(135deg,_#f8fbff_0%,_#eef5fb_46%,_#ffffff_100%)] p-6 shadow-sm"
        >
            <div class="grid gap-6 xl:grid-cols-[1.15fr,0.85fr] xl:items-end">
                <div class="space-y-4">
                    <div class="inline-flex items-center rounded-full bg-[#0f172a] px-4 py-2 text-xs font-semibold tracking-[0.18em] text-white uppercase">
                        Pusat Riwayat Terpadu
                    </div>
                    <div>
                        <h1 class="font-display text-4xl font-bold tracking-tight text-slate-950">
                            Semua perjalanan belajar tersusun dalam satu tempat.
                        </h1>
                        <p class="mt-3 max-w-3xl text-base leading-7 text-slate-600">
                            Tinjau riwayat belajar, mini quiz, latihan, dan simulasi tanpa berpindah konteks. Setiap aktivitas tetap mengarah ke detail hasil aslinya.
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <Card class="rounded-[1.6rem] border-[#dfe8ef] bg-white/90">
                        <CardContent class="space-y-2 p-5">
                            <p class="text-sm text-slate-500">Total aktivitas</p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ totalActivities }}
                            </p>
                        </CardContent>
                    </Card>
                    <Card class="rounded-[1.6rem] border-[#dfe8ef] bg-white/90">
                        <CardContent class="space-y-2 p-5">
                            <p class="text-sm text-slate-500">Belajar tercatat</p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ summary.learn }}
                            </p>
                        </CardContent>
                    </Card>
                    <Card class="rounded-[1.6rem] border-[#dfe8ef] bg-white/90">
                        <CardContent class="space-y-2 p-5">
                            <p class="text-sm text-slate-500">Latihan</p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ summary.practice }}
                            </p>
                        </CardContent>
                    </Card>
                    <Card class="rounded-[1.6rem] border-[#dfe8ef] bg-white/90">
                        <CardContent class="space-y-2 p-5">
                            <p class="text-sm text-slate-500">Simulasi</p>
                            <p class="text-3xl font-semibold text-slate-950">
                                {{ summary.simulation }}
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.78fr,1.22fr]">
            <div class="space-y-5">
                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardContent class="space-y-4 p-6">
                        <div class="flex items-center gap-2">
                            <LibraryBig class="size-5 text-[#b91c1c]" />
                            <p class="font-display text-2xl font-bold tracking-tight text-slate-950">
                                Filter histori
                            </p>
                        </div>
                        <div class="space-y-3">
                            <button
                                v-for="item in filters.items"
                                :key="item.value"
                                type="button"
                                class="flex w-full items-center justify-between rounded-[1.2rem] border px-4 py-3 text-left text-sm font-medium transition"
                                :class="
                                    form.type === item.value
                                        ? 'border-[#0f172a] bg-[#0f172a] text-white'
                                        : 'border-[#dfe8ef] bg-[#fbfdff] text-slate-700 hover:bg-slate-50'
                                "
                                @click="
                                    form.type = item.value;
                                    applyFilter();
                                "
                            >
                                <span>{{ item.label }}</span>
                                <ScrollText class="size-4" />
                            </button>
                        </div>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-[#0f172a] text-white shadow-sm">
                    <CardContent class="space-y-4 p-6 text-sm text-slate-200">
                        <div class="flex items-center gap-2 font-semibold text-white">
                            <Clock3 class="size-4" />
                            Mengapa pusat riwayat ini penting
                        </div>
                        <p>
                            User pemula tidak perlu mengingat sendiri apa yang sudah dipelajari, dicoba, dan ditinjau ulang.
                        </p>
                        <p>
                            Linimasa ini menjaga nilai review attempt lama karena latihan dan simulasi sudah punya hasil yang bisa dibuka lagi kapan saja.
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-5">
                <HistoryTimelineCard
                    v-for="item in timeline.data"
                    :key="item.id"
                    :item="item"
                />

                <Card
                    v-if="timeline.data.length === 0"
                    class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm"
                >
                    <CardContent class="p-8 text-center text-sm text-slate-500">
                        Belum ada aktivitas yang cocok dengan filter ini.
                    </CardContent>
                </Card>

                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <p class="text-sm text-slate-500">
                        Menampilkan {{ timeline.from ?? 0 }}-{{ timeline.to ?? 0 }}
                        dari {{ timeline.total }} aktivitas.
                    </p>
                    <AdminPagination :links="timeline.links" />
                </div>
            </div>
        </section>
    </div>
</template>



