<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Search } from 'lucide-vue-next';
import { computed, reactive } from 'vue';
import AdminMetricCard from '@/components/admin/AdminMetricCard.vue';
import AdminPageHero from '@/components/admin/AdminPageHero.vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { dashboard } from '@/routes';
import type { PaginatedResponse } from '@/types';

type QuestionSummary = {
    id: number;
    code: string | null;
    question_text: string;
    question_type: string;
    question_type_label: string;
    status: string;
    status_label: string;
    category: string | null;
    subtest: string | null;
};

type QuestionOptionItem = {
    id: number;
    option_key: string;
    option_text: string | null;
    option_image: string | null;
    weight: string | number | null;
    is_correct: boolean | null;
    sort_order: number;
    updated_at: string | null;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Admin', href: '/admin' },
            { title: 'Soal', href: '/admin/questions' },
            { title: 'Options', href: '#' },
        ],
    },
});

const props = defineProps<{
    question: QuestionSummary;
    options: PaginatedResponse<QuestionOptionItem>;
    filters: {
        q: string;
        correctness: string;
    };
    stats: {
        total: number;
        correct: number;
        weighted: number;
    };
}>();

const filterForm = reactive({
    q: props.filters.q ?? '',
    correctness: props.filters.correctness ?? 'all',
});

const hasFilters = computed(
    () => filterForm.q !== '' || filterForm.correctness !== 'all',
);

function applyFilters() {
    router.get(
        `/admin/questions/${props.question.id}/options`,
        {
            q: filterForm.q || undefined,
            correctness:
                filterForm.correctness !== 'all'
                    ? filterForm.correctness
                    : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function resetFilters() {
    filterForm.q = '';
    filterForm.correctness = 'all';
    applyFilters();
}

function destroy(optionId: number) {
    if (!window.confirm('Hapus opsi jawaban ini?')) {
        return;
    }

    router.delete(`/admin/questions/${props.question.id}/options/${optionId}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Question Options" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <AdminPageHero
            eyebrow="Question Options"
            title="Kelola opsi jawaban"
            :description="`Opsi jawaban untuk ${question.code || 'soal tanpa kode'} dipisahkan dari form soal utama agar struktur relasi tetap jelas dan aman.`"
        >
            <div class="flex flex-wrap items-center gap-3">
                <Button as-child variant="outline" class="rounded-2xl">
                    <Link :href="`/admin/questions/${question.id}/edit`"
                        >Kembali ke soal</Link
                    >
                </Button>
                <Button
                    as-child
                    class="rounded-2xl bg-[#7f1d1d] text-white hover:bg-[#6e1717]"
                >
                    <Link
                        :href="`/admin/questions/${question.id}/options/create`"
                    >
                        <Plus class="mr-2 size-4" />
                        Tambah opsi
                    </Link>
                </Button>
            </div>
        </AdminPageHero>

        <section class="grid gap-4 md:grid-cols-3">
            <AdminMetricCard title="Total opsi" :value="stats.total" />
            <AdminMetricCard title="Opsi benar" :value="stats.correct" />
            <AdminMetricCard title="Weighted option" :value="stats.weighted" />
        </section>

        <Card class="rounded-[1.75rem] border-[#e7ddd5] shadow-sm">
            <CardContent class="space-y-5 p-6">
                <div
                    class="rounded-[1.5rem] border border-[#efe3db] bg-[#fffaf7] p-4 text-sm text-slate-600"
                >
                    <p class="font-semibold text-slate-900">
                        {{ question.code || 'Auto code' }}
                    </p>
                    <p class="mt-1">
                        {{ question.category }} · {{ question.subtest }} ·
                        {{ question.question_type_label }}
                    </p>
                    <p class="mt-2 max-w-4xl">{{ question.question_text }}</p>
                </div>

                <div class="grid gap-4 lg:grid-cols-[1.4fr,220px,auto]">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700"
                            >Pencarian</label
                        >
                        <div class="relative">
                            <Search
                                class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-slate-400"
                            />
                            <Input
                                v-model="filterForm.q"
                                class="rounded-2xl pl-10"
                                placeholder="Cari key atau isi opsi..."
                                @keyup.enter="applyFilters"
                            />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700"
                            >Correctness</label
                        >
                        <select
                            v-model="filterForm.correctness"
                            class="flex h-10 w-full rounded-2xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none"
                        >
                            <option value="all">Semua</option>
                            <option value="correct">Correct</option>
                            <option value="incorrect">Incorrect</option>
                        </select>
                    </div>
                    <div class="flex flex-wrap items-end gap-3">
                        <Button
                            class="rounded-2xl bg-slate-900 text-white hover:bg-slate-800"
                            @click="applyFilters"
                            >Terapkan</Button
                        >
                        <Button
                            v-if="hasFilters"
                            variant="outline"
                            class="rounded-2xl"
                            @click="resetFilters"
                            >Reset</Button
                        >
                    </div>
                </div>

                <div
                    class="overflow-hidden rounded-[1.5rem] border border-[#efe3db]"
                >
                    <div class="overflow-x-auto">
                        <table
                            class="min-w-full divide-y divide-[#efe3db] text-sm"
                        >
                            <thead
                                class="bg-[#fff9f6] text-left text-slate-500"
                            >
                                <tr>
                                    <th class="px-4 py-3 font-medium">Opsi</th>
                                    <th class="px-4 py-3 font-medium">Meta</th>
                                    <th class="px-4 py-3 font-medium">
                                        Status
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right font-medium"
                                    >
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f3e7df] bg-white">
                                <tr
                                    v-for="option in options.data"
                                    :key="option.id"
                                    class="align-top"
                                >
                                    <td class="px-4 py-4">
                                        <div class="space-y-1">
                                            <p
                                                class="font-semibold text-slate-900"
                                            >
                                                {{ option.option_key }}
                                            </p>
                                            <p
                                                class="max-w-lg text-sm text-slate-600"
                                            >
                                                {{
                                                    option.option_text ||
                                                    'Tidak ada teks opsi.'
                                                }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-slate-600">
                                        <p>
                                            Weight: {{ option.weight ?? '-' }}
                                        </p>
                                        <p>Urutan: {{ option.sort_order }}</p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <AdminStatusBadge
                                            :label="
                                                option.is_correct
                                                    ? 'Correct'
                                                    : 'Not marked'
                                            "
                                            :tone="
                                                option.is_correct
                                                    ? 'success'
                                                    : 'neutral'
                                            "
                                        />
                                    </td>
                                    <td class="px-4 py-4">
                                        <div
                                            class="flex flex-wrap justify-end gap-2"
                                        >
                                            <Button
                                                variant="outline"
                                                class="rounded-2xl"
                                                as-child
                                            >
                                                <Link
                                                    :href="`/admin/questions/${question.id}/options/${option.id}/edit`"
                                                    >Edit</Link
                                                >
                                            </Button>
                                            <Button
                                                variant="outline"
                                                class="rounded-2xl text-rose-600"
                                                @click="destroy(option.id)"
                                            >
                                                Hapus
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="options.data.length === 0">
                                    <td
                                        colspan="4"
                                        class="px-4 py-10 text-center text-slate-500"
                                    >
                                        Belum ada opsi yang cocok dengan filter
                                        saat ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                >
                    <p class="text-sm text-slate-500">
                        Menampilkan {{ options.from ?? 0 }}-{{
                            options.to ?? 0
                        }}
                        dari {{ options.total }} opsi.
                    </p>
                    <AdminPagination :links="options.links" />
                </div>
            </CardContent>
        </Card>
    </div>
</template>

