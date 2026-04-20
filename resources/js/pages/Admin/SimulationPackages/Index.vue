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

type PackageItem = {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    duration_minutes: number;
    question_count: number;
    subtests_count: number;
    sort_order: number;
    is_published: boolean;
    updated_at: string | null;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Admin', href: '/admin' },
            { title: 'Simulation Packs', href: '/admin/simulation-packages' },
        ],
    },
});

const props = defineProps<{
    packages: PaginatedResponse<PackageItem>;
    filters: {
        q: string;
        status: string;
    };
    stats: {
        total: number;
        published: number;
        draft: number;
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
        '/admin/simulation-packages',
        {
            q: filterForm.q || undefined,
            status: filterForm.status !== 'all' ? filterForm.status : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function resetFilters() {
    filterForm.q = '';
    filterForm.status = 'all';
    applyFilters();
}

function togglePublication(packageId: number) {
    router.patch(
        `/admin/simulation-packages/${packageId}/publication`,
        {},
        { preserveScroll: true },
    );
}

function destroy(packageId: number) {
    if (
        !window.confirm(
            'Hapus paket simulasi ini? Sistem akan menolak jika sudah dipakai histori attempt.',
        )
    ) {
        return;
    }

    router.delete(`/admin/simulation-packages/${packageId}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Simulation Packs" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <AdminPageHero
            eyebrow="Admin CMS"
            title="Kelola paket simulasi"
            description="Paket simulasi menentukan komposisi subtes, durasi penuh, dan pengalaman CAT yang akan dihadapi user."
        >
            <Button
                as-child
                class="rounded-2xl bg-[#7f1d1d] text-white hover:bg-[#6e1717]"
            >
                <Link href="/admin/simulation-packages/create">
                    <Plus class="mr-2 size-4" />
                    Tambah paket
                </Link>
            </Button>
        </AdminPageHero>

        <section class="grid gap-4 md:grid-cols-3">
            <AdminMetricCard title="Total paket" :value="stats.total" />
            <AdminMetricCard title="Published" :value="stats.published" />
            <AdminMetricCard title="Draft" :value="stats.draft" />
        </section>

        <Card class="rounded-[1.75rem] border-[#e7ddd5] shadow-sm">
            <CardContent class="space-y-5 p-6">
                <div class="grid gap-4 xl:grid-cols-[1.4fr,220px,auto]">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700">
                            Pencarian
                        </label>
                        <div class="relative">
                            <Search
                                class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-slate-400"
                            />
                            <Input
                                v-model="filterForm.q"
                                class="rounded-2xl pl-10"
                                placeholder="Cari paket simulasi..."
                                @keyup.enter="applyFilters"
                            />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700">
                            Status
                        </label>
                        <select
                            v-model="filterForm.status"
                            class="flex h-10 w-full rounded-2xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none"
                        >
                            <option value="all">Semua</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
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

                <div class="overflow-hidden rounded-[1.5rem] border border-[#efe3db]">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[#efe3db] text-sm">
                            <thead class="bg-[#fff9f6] text-left text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Paket</th>
                                    <th class="px-4 py-3 font-medium">Komposisi</th>
                                    <th class="px-4 py-3 font-medium">Status</th>
                                    <th class="px-4 py-3 text-right font-medium">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f3e7df] bg-white">
                                <tr
                                    v-for="simulationPackage in packages.data"
                                    :key="simulationPackage.id"
                                    class="align-top"
                                >
                                    <td class="px-4 py-4">
                                        <div class="space-y-1">
                                            <p class="font-semibold text-slate-900">
                                                {{ simulationPackage.title }}
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                {{ simulationPackage.slug }}
                                            </p>
                                            <p class="max-w-md text-sm text-slate-600">
                                                {{
                                                    simulationPackage.description ||
                                                    'Tidak ada deskripsi.'
                                                }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-slate-600">
                                        <p>
                                            {{ simulationPackage.subtests_count }}
                                            subtes
                                        </p>
                                        <p>
                                            {{ simulationPackage.question_count }}
                                            soal
                                        </p>
                                        <p>
                                            {{
                                                simulationPackage.duration_minutes
                                            }}
                                            menit
                                        </p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <AdminStatusBadge
                                            :label="
                                                simulationPackage.is_published
                                                    ? 'Published'
                                                    : 'Draft'
                                            "
                                            :tone="
                                                simulationPackage.is_published
                                                    ? 'success'
                                                    : 'warning'
                                            "
                                        />
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <Button
                                                variant="outline"
                                                class="rounded-2xl"
                                                as-child
                                            >
                                                <Link
                                                    :href="`/admin/simulation-packages/${simulationPackage.id}/edit`"
                                                >
                                                    Edit
                                                </Link>
                                            </Button>
                                            <Button
                                                variant="outline"
                                                class="rounded-2xl"
                                                @click="
                                                    togglePublication(
                                                        simulationPackage.id,
                                                    )
                                                "
                                            >
                                                {{
                                                    simulationPackage.is_published
                                                        ? 'Unpublish'
                                                        : 'Publish'
                                                }}
                                            </Button>
                                            <Button
                                                variant="outline"
                                                class="rounded-2xl text-rose-600"
                                                @click="destroy(simulationPackage.id)"
                                            >
                                                Hapus
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="packages.data.length === 0">
                                    <td
                                        colspan="4"
                                        class="px-4 py-10 text-center text-slate-500"
                                    >
                                        Belum ada paket simulasi yang cocok dengan filter.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <p class="text-sm text-slate-500">
                        Menampilkan {{ packages.from ?? 0 }}-{{
                            packages.to ?? 0
                        }}
                        dari {{ packages.total }} paket.
                    </p>
                    <AdminPagination :links="packages.links" />
                </div>
            </CardContent>
        </Card>
    </div>
</template>

