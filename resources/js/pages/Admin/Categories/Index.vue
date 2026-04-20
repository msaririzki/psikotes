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

type CategoryItem = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    sort_order: number;
    is_active: boolean;
    subtests_count: number;
    questions_count: number;
    updated_at: string | null;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Admin', href: '/admin' },
            { title: 'Kategori', href: '/admin/categories' },
        ],
    },
});

const props = defineProps<{
    categories: PaginatedResponse<CategoryItem>;
    filters: {
        q: string;
        status: string;
    };
    stats: {
        total: number;
        active: number;
        inactive: number;
    };
}>();

const filterForm = reactive({
    q: props.filters.q ?? '',
    status: props.filters.status ?? 'all',
});

const hasFilters = computed(
    () => filterForm.q !== '' || filterForm.status !== 'all',
);

function applyFilters() {
    router.get(
        '/admin/categories',
        {
            q: filterForm.q || undefined,
            status: filterForm.status !== 'all' ? filterForm.status : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function resetFilters() {
    filterForm.q = '';
    filterForm.status = 'all';
    applyFilters();
}

function toggleActivity(categoryId: number) {
    router.patch(
        `/admin/categories/${categoryId}/activity`,
        {},
        { preserveScroll: true },
    );
}

function destroy(categoryId: number) {
    if (
        !window.confirm(
            'Hapus kategori ini? Data relasi yang masih dipakai akan ditolak oleh sistem.',
        )
    ) {
        return;
    }

    router.delete(`/admin/categories/${categoryId}`, { preserveScroll: true });
}
</script>

<template>
    <Head title="Kategori" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <AdminPageHero
            eyebrow="Admin CMS"
            title="Kelola kategori utama"
            description="Kategori menjadi node paling atas untuk pengelompokan subtes dan soal. Phase 2 menjaga struktur ini tetap rapi sebelum engine latihan dibuka."
        >
            <div class="flex flex-wrap items-center gap-3">
                <Button
                    as-child
                    class="rounded-2xl bg-[#7f1d1d] text-white hover:bg-[#6e1717]"
                >
                    <Link href="/admin/categories/create">
                        <Plus class="mr-2 size-4" />
                        Tambah kategori
                    </Link>
                </Button>
            </div>
        </AdminPageHero>

        <section class="grid gap-4 md:grid-cols-3">
            <AdminMetricCard title="Total kategori" :value="stats.total" />
            <AdminMetricCard title="Kategori aktif" :value="stats.active" />
            <AdminMetricCard
                title="Kategori nonaktif"
                :value="stats.inactive"
            />
        </section>

        <Card class="rounded-[1.75rem] border-[#e7ddd5] shadow-sm">
            <CardContent class="space-y-5 p-6">
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
                                placeholder="Cari nama, slug, atau deskripsi..."
                                @keyup.enter="applyFilters"
                            />
                        </div>
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
                        >
                            Terapkan
                        </Button>
                        <Button
                            v-if="hasFilters"
                            variant="outline"
                            class="rounded-2xl"
                            @click="resetFilters"
                        >
                            Reset
                        </Button>
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
                                        Kategori
                                    </th>
                                    <th class="px-4 py-3 font-medium">
                                        Relasi
                                    </th>
                                    <th class="px-4 py-3 font-medium">
                                        Status
                                    </th>
                                    <th class="px-4 py-3 font-medium">
                                        Urutan
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
                                    v-for="category in categories.data"
                                    :key="category.id"
                                    class="align-top"
                                >
                                    <td class="px-4 py-4">
                                        <div class="space-y-1">
                                            <p
                                                class="font-semibold text-slate-900"
                                            >
                                                {{ category.name }}
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                {{ category.slug }}
                                            </p>
                                            <p
                                                class="max-w-md text-sm text-slate-600"
                                            >
                                                {{
                                                    category.description ||
                                                    'Tidak ada deskripsi.'
                                                }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-slate-600">
                                        <p>
                                            {{ category.subtests_count }} subtes
                                        </p>
                                        <p>
                                            {{ category.questions_count }} soal
                                        </p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <AdminStatusBadge
                                            :label="
                                                category.is_active
                                                    ? 'Active'
                                                    : 'Inactive'
                                            "
                                            :tone="
                                                category.is_active
                                                    ? 'success'
                                                    : 'danger'
                                            "
                                        />
                                    </td>
                                    <td class="px-4 py-4 text-slate-600">
                                        {{ category.sort_order }}
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
                                                    :href="`/admin/categories/${category.id}/edit`"
                                                    >Edit</Link
                                                >
                                            </Button>
                                            <Button
                                                variant="outline"
                                                class="rounded-2xl"
                                                @click="
                                                    toggleActivity(category.id)
                                                "
                                            >
                                                {{
                                                    category.is_active
                                                        ? 'Nonaktifkan'
                                                        : 'Aktifkan'
                                                }}
                                            </Button>
                                            <Button
                                                variant="outline"
                                                class="rounded-2xl text-rose-600"
                                                @click="destroy(category.id)"
                                            >
                                                Hapus
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="categories.data.length === 0">
                                    <td
                                        colspan="5"
                                        class="px-4 py-10 text-center text-slate-500"
                                    >
                                        Belum ada kategori yang cocok dengan
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
                        Menampilkan {{ categories.from ?? 0 }}-{{
                            categories.to ?? 0
                        }}
                        dari {{ categories.total }} kategori.
                    </p>
                    <AdminPagination :links="categories.links" />
                </div>
            </CardContent>
        </Card>
    </div>
</template>

