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
import type { EntityOption, PaginatedResponse, SelectOption } from '@/types';

type SubtestItem = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    category: string | null;
    category_id: number;
    scoring_type: string;
    scoring_type_label: string;
    default_duration_minutes: number | null;
    sort_order: number;
    is_active: boolean;
    learning_modules_count: number;
    questions_count: number;
    updated_at: string | null;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Admin', href: '/admin' },
            { title: 'Subtes', href: '/admin/subtests' },
        ],
    },
});

const props = defineProps<{
    subtests: PaginatedResponse<SubtestItem>;
    filters: {
        q: string;
        category_id: number | null;
        scoring_type: string;
        status: string;
    };
    stats: {
        total: number;
        active: number;
        objective: number;
    };
    categories: EntityOption[];
    scoringTypes: SelectOption[];
}>();

const filterForm = reactive({
    q: props.filters.q ?? '',
    category_id: props.filters.category_id ?? '',
    scoring_type: props.filters.scoring_type ?? 'all',
    status: props.filters.status ?? 'all',
});

const hasFilters = computed(
    () =>
        filterForm.q !== '' ||
        filterForm.category_id !== '' ||
        filterForm.scoring_type !== 'all' ||
        filterForm.status !== 'all',
);

function applyFilters() {
    router.get(
        '/admin/subtests',
        {
            q: filterForm.q || undefined,
            category_id: filterForm.category_id || undefined,
            scoring_type:
                filterForm.scoring_type !== 'all'
                    ? filterForm.scoring_type
                    : undefined,
            status: filterForm.status !== 'all' ? filterForm.status : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function resetFilters() {
    filterForm.q = '';
    filterForm.category_id = '';
    filterForm.scoring_type = 'all';
    filterForm.status = 'all';
    applyFilters();
}

function toggleActivity(subtestId: number) {
    router.patch(
        `/admin/subtests/${subtestId}/activity`,
        {},
        { preserveScroll: true },
    );
}

function destroy(subtestId: number) {
    if (
        !window.confirm(
            'Hapus subtes ini? Sistem akan menolak jika masih dipakai modul, soal, atau attempt.',
        )
    ) {
        return;
    }

    router.delete(`/admin/subtests/${subtestId}`, { preserveScroll: true });
}
</script>

<template>
    <Head title="Subtes" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <AdminPageHero
            eyebrow="Admin CMS"
            title="Kelola subtes"
            description="Subtes mengikat kategori ke modul belajar, bank soal, dan pada fase berikutnya ke mode practice serta simulation."
        >
            <Button
                as-child
                class="rounded-2xl bg-[#7f1d1d] text-white hover:bg-[#6e1717]"
            >
                <Link href="/admin/subtests/create">
                    <Plus class="mr-2 size-4" />
                    Tambah subtes
                </Link>
            </Button>
        </AdminPageHero>

        <section class="grid gap-4 md:grid-cols-3">
            <AdminMetricCard title="Total subtes" :value="stats.total" />
            <AdminMetricCard title="Subtes aktif" :value="stats.active" />
            <AdminMetricCard
                title="Scoring objective"
                :value="stats.objective"
            />
        </section>

        <Card class="rounded-[1.75rem] border-[#e7ddd5] shadow-sm">
            <CardContent class="space-y-5 p-6">
                <div
                    class="grid gap-4 xl:grid-cols-[1.4fr,220px,220px,220px,auto]"
                >
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
                                placeholder="Cari subtes..."
                                @keyup.enter="applyFilters"
                            />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700"
                            >Kategori</label
                        >
                        <select
                            v-model="filterForm.category_id"
                            class="flex h-10 w-full rounded-2xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none"
                        >
                            <option value="">Semua</option>
                            <option
                                v-for="category in categories"
                                :key="category.id"
                                :value="category.id"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700"
                            >Scoring</label
                        >
                        <select
                            v-model="filterForm.scoring_type"
                            class="flex h-10 w-full rounded-2xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none"
                        >
                            <option value="all">Semua</option>
                            <option
                                v-for="type in scoringTypes"
                                :key="type.value"
                                :value="type.value"
                            >
                                {{ type.label }}
                            </option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700"
                            >Status</label
                        >
                        <select
                            v-model="filterForm.status"
                            class="flex h-10 w-full rounded-2xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none"
                        >
                            <option value="all">Semua</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
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
                                    <th class="px-4 py-3 font-medium">
                                        Subtes
                                    </th>
                                    <th class="px-4 py-3 font-medium">
                                        Scoring
                                    </th>
                                    <th class="px-4 py-3 font-medium">
                                        Relasi
                                    </th>
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
                                    v-for="subtest in subtests.data"
                                    :key="subtest.id"
                                    class="align-top"
                                >
                                    <td class="px-4 py-4">
                                        <div class="space-y-1">
                                            <p
                                                class="font-semibold text-slate-900"
                                            >
                                                {{ subtest.name }}
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                {{ subtest.category }} ·
                                                {{ subtest.slug }}
                                            </p>
                                            <p
                                                class="max-w-md text-sm text-slate-600"
                                            >
                                                {{
                                                    subtest.description ||
                                                    'Tidak ada deskripsi.'
                                                }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-slate-600">
                                        <p>{{ subtest.scoring_type_label }}</p>
                                        <p>
                                            {{
                                                subtest.default_duration_minutes
                                                    ? `${subtest.default_duration_minutes} menit`
                                                    : 'Durasi belum diatur'
                                            }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-4 text-slate-600">
                                        <p>
                                            {{ subtest.learning_modules_count }}
                                            modul
                                        </p>
                                        <p>
                                            {{ subtest.questions_count }} soal
                                        </p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <AdminStatusBadge
                                            :label="
                                                subtest.is_active
                                                    ? 'Active'
                                                    : 'Inactive'
                                            "
                                            :tone="
                                                subtest.is_active
                                                    ? 'success'
                                                    : 'danger'
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
                                                    :href="`/admin/subtests/${subtest.id}/edit`"
                                                    >Edit</Link
                                                >
                                            </Button>
                                            <Button
                                                variant="outline"
                                                class="rounded-2xl"
                                                @click="
                                                    toggleActivity(subtest.id)
                                                "
                                            >
                                                {{
                                                    subtest.is_active
                                                        ? 'Nonaktifkan'
                                                        : 'Aktifkan'
                                                }}
                                            </Button>
                                            <Button
                                                variant="outline"
                                                class="rounded-2xl text-rose-600"
                                                @click="destroy(subtest.id)"
                                            >
                                                Hapus
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="subtests.data.length === 0">
                                    <td
                                        colspan="5"
                                        class="px-4 py-10 text-center text-slate-500"
                                    >
                                        Belum ada subtes yang cocok dengan
                                        filter saat ini.
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
                        Menampilkan {{ subtests.from ?? 0 }}-{{
                            subtests.to ?? 0
                        }}
                        dari {{ subtests.total }} subtes.
                    </p>
                    <AdminPagination :links="subtests.links" />
                </div>
            </CardContent>
        </Card>
    </div>
</template>

