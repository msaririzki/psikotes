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

type BelajaringModuleItem = {
    id: number;
    title: string;
    slug: string;
    summary: string | null;
    level: string;
    level_label: string;
    estimated_minutes: number | null;
    is_published: boolean;
    published_at: string | null;
    subtest: string | null;
    subtest_id: number;
    category: string | null;
    updated_at: string | null;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Admin', href: '/admin' },
            { title: 'Belajaring Modul', href: '/admin/learning-modules' },
        ],
    },
});

const props = defineProps<{
    modules: PaginatedResponse<BelajaringModuleItem>;
    filters: {
        q: string;
        category_id: number | null;
        subtest_id: number | null;
        level: string;
        publication: string;
    };
    stats: {
        total: number;
        published: number;
        draft: number;
    };
    categories: EntityOption[];
    subtests: EntityOption[];
    levels: SelectOption[];
}>();

const filterForm = reactive({
    q: props.filters.q ?? '',
    category_id: props.filters.category_id ?? '',
    subtest_id: props.filters.subtest_id ?? '',
    level: props.filters.level ?? 'all',
    publication: props.filters.publication ?? 'all',
});

const availableSubtes = computed(() =>
    props.subtests.filter(
        (subtest) =>
            !filterForm.category_id ||
            subtest.category_id === Number(filterForm.category_id),
    ),
);

const hasFilters = computed(
    () =>
        filterForm.q !== '' ||
        filterForm.category_id !== '' ||
        filterForm.subtest_id !== '' ||
        filterForm.level !== 'all' ||
        filterForm.publication !== 'all',
);

function applyFilters() {
    router.get(
        '/admin/learning-modules',
        {
            q: filterForm.q || undefined,
            category_id: filterForm.category_id || undefined,
            subtest_id: filterForm.subtest_id || undefined,
            level: filterForm.level !== 'all' ? filterForm.level : undefined,
            publication:
                filterForm.publication !== 'all'
                    ? filterForm.publication
                    : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function resetFilters() {
    filterForm.q = '';
    filterForm.category_id = '';
    filterForm.subtest_id = '';
    filterForm.level = 'all';
    filterForm.publication = 'all';
    applyFilters();
}

function togglePublication(moduleId: number) {
    router.patch(
        `/admin/learning-modules/${moduleId}/publication`,
        {},
        { preserveScroll: true },
    );
}

function destroy(moduleId: number) {
    if (!window.confirm('Hapus modul ini?')) {
        return;
    }

    router.delete(`/admin/learning-modules/${moduleId}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Belajaring Modul" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <AdminPageHero
            eyebrow="Admin CMS"
            title="Kelola modul belajar"
            description="Modul belajar diposisikan sebagai materi persiapan. Phase 2 fokus pada manajemen konten dan workflow publish, belum masuk ke engine delivery."
        >
            <Button
                as-child
                class="rounded-2xl bg-[#7f1d1d] text-white hover:bg-[#6e1717]"
            >
                <Link href="/admin/learning-modules/create">
                    <Plus class="mr-2 size-4" />
                    Tambah modul
                </Link>
            </Button>
        </AdminPageHero>

        <section class="grid gap-4 md:grid-cols-3">
            <AdminMetricCard title="Total modul" :value="stats.total" />
            <AdminMetricCard title="Sudah publish" :value="stats.published" />
            <AdminMetricCard title="Masih draft" :value="stats.draft" />
        </section>

        <Card class="rounded-[1.75rem] border-[#e7ddd5] shadow-sm">
            <CardContent class="space-y-5 p-6">
                <div
                    class="grid gap-4 2xl:grid-cols-[1.4fr,220px,220px,220px,220px,auto]"
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
                                placeholder="Cari modul..."
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
                            >Subtes</label
                        >
                        <select
                            v-model="filterForm.subtest_id"
                            class="flex h-10 w-full rounded-2xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none"
                        >
                            <option value="">Semua</option>
                            <option
                                v-for="subtest in availableSubtes"
                                :key="subtest.id"
                                :value="subtest.id"
                            >
                                {{ subtest.name }}
                            </option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700"
                            >Level</label
                        >
                        <select
                            v-model="filterForm.level"
                            class="flex h-10 w-full rounded-2xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none"
                        >
                            <option value="all">Semua</option>
                            <option
                                v-for="level in levels"
                                :key="level.value"
                                :value="level.value"
                            >
                                {{ level.label }}
                            </option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700"
                            >Publikasi</label
                        >
                        <select
                            v-model="filterForm.publication"
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
                                    <th class="px-4 py-3 font-medium">Modul</th>
                                    <th class="px-4 py-3 font-medium">Level</th>
                                    <th class="px-4 py-3 font-medium">
                                        Publikasi
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
                                    v-for="module in modules.data"
                                    :key="module.id"
                                    class="align-top"
                                >
                                    <td class="px-4 py-4">
                                        <div class="space-y-1">
                                            <p
                                                class="font-semibold text-slate-900"
                                            >
                                                {{ module.title }}
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                {{ module.category }} ·
                                                {{ module.subtest }} ·
                                                {{ module.slug }}
                                            </p>
                                            <p
                                                class="max-w-lg text-sm text-slate-600"
                                            >
                                                {{
                                                    module.summary ||
                                                    'Belum ada ringkasan.'
                                                }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-slate-600">
                                        <p>{{ module.level_label }}</p>
                                        <p>
                                            {{
                                                module.estimated_minutes
                                                    ? `${module.estimated_minutes} menit`
                                                    : 'Estimasi belum diatur'
                                            }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <AdminStatusBadge
                                            :label="
                                                module.is_published
                                                    ? 'Published'
                                                    : 'Draft'
                                            "
                                            :tone="
                                                module.is_published
                                                    ? 'success'
                                                    : 'warning'
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
                                                    :href="`/admin/learning-modules/${module.id}/edit`"
                                                    >Edit</Link
                                                >
                                            </Button>
                                            <Button
                                                variant="outline"
                                                class="rounded-2xl"
                                                @click="
                                                    togglePublication(module.id)
                                                "
                                            >
                                                {{
                                                    module.is_published
                                                        ? 'Unpublish'
                                                        : 'Publish'
                                                }}
                                            </Button>
                                            <Button
                                                variant="outline"
                                                class="rounded-2xl text-rose-600"
                                                @click="destroy(module.id)"
                                                >Hapus</Button
                                            >
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="modules.data.length === 0">
                                    <td
                                        colspan="4"
                                        class="px-4 py-10 text-center text-slate-500"
                                    >
                                        Belum ada modul yang cocok dengan filter
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
                        Menampilkan {{ modules.from ?? 0 }}-{{
                            modules.to ?? 0
                        }}
                        dari {{ modules.total }} modul.
                    </p>
                    <AdminPagination :links="modules.links" />
                </div>
            </CardContent>
        </Card>
    </div>
</template>

