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

type QuestionItem = {
    id: number;
    code: string | null;
    question_text: string;
    question_type: string;
    question_type_label: string;
    difficulty: string;
    difficulty_label: string;
    status: string;
    status_label: string;
    category: string | null;
    subtest: string | null;
    options_count: number;
    updated_at: string | null;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Admin', href: '/admin' },
            { title: 'Soal', href: '/admin/questions' },
        ],
    },
});

const props = defineProps<{
    questions: PaginatedResponse<QuestionItem>;
    filters: {
        q: string;
        category_id: number | null;
        subtest_id: number | null;
        question_type: string;
        difficulty: string;
        status: string;
    };
    stats: {
        total: number;
        published: number;
        draft: number;
        review: number;
    };
    categories: EntityOption[];
    subtests: EntityOption[];
    questionTypes: SelectOption[];
    difficulties: SelectOption[];
    statuses: SelectOption[];
}>();

const filterForm = reactive({
    q: props.filters.q ?? '',
    category_id: props.filters.category_id ?? '',
    subtest_id: props.filters.subtest_id ?? '',
    question_type: props.filters.question_type ?? 'all',
    difficulty: props.filters.difficulty ?? 'all',
    status: props.filters.status ?? 'all',
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
        filterForm.question_type !== 'all' ||
        filterForm.difficulty !== 'all' ||
        filterForm.status !== 'all',
);

function applyFilters() {
    router.get(
        '/admin/questions',
        {
            q: filterForm.q || undefined,
            category_id: filterForm.category_id || undefined,
            subtest_id: filterForm.subtest_id || undefined,
            question_type:
                filterForm.question_type !== 'all'
                    ? filterForm.question_type
                    : undefined,
            difficulty:
                filterForm.difficulty !== 'all'
                    ? filterForm.difficulty
                    : undefined,
            status: filterForm.status !== 'all' ? filterForm.status : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function resetFilters() {
    filterForm.q = '';
    filterForm.category_id = '';
    filterForm.subtest_id = '';
    filterForm.question_type = 'all';
    filterForm.difficulty = 'all';
    filterForm.status = 'all';
    applyFilters();
}

function destroy(questionId: number) {
    if (
        !window.confirm(
            'Hapus soal ini? Histori attempt akan melindungi data yang sudah terpakai.',
        )
    ) {
        return;
    }

    router.delete(`/admin/questions/${questionId}`, { preserveScroll: true });
}

function updateStatus(questionId: number, status: string) {
    router.patch(
        `/admin/questions/${questionId}/status`,
        { status },
        { preserveScroll: true },
    );
}

function statusTone(status: string) {
    if (status === 'published') {
        return 'success';
    }

    if (status === 'review') {
        return 'info';
    }

    if (status === 'archived') {
        return 'danger';
    }

    return 'warning';
}
</script>

<template>
    <Head title="Soal" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <AdminPageHero
            eyebrow="Admin CMS"
            title="Kelola bank soal"
            description="Question management di Phase 2 berfokus pada CRUD, validasi, relasi opsi jawaban, dan workflow status. Latihan engine belum disentuh."
        >
            <Button
                as-child
                class="rounded-2xl bg-[#7f1d1d] text-white hover:bg-[#6e1717]"
            >
                <Link href="/admin/questions/create">
                    <Plus class="mr-2 size-4" />
                    Tambah soal
                </Link>
            </Button>
        </AdminPageHero>

        <section class="grid gap-4 md:grid-cols-4">
            <AdminMetricCard title="Total soal" :value="stats.total" />
            <AdminMetricCard title="Published" :value="stats.published" />
            <AdminMetricCard title="Draft" :value="stats.draft" />
            <AdminMetricCard title="Review" :value="stats.review" />
        </section>

        <Card class="rounded-[1.75rem] border-[#e7ddd5] shadow-sm">
            <CardContent class="space-y-5 p-6">
                <div
                    class="grid gap-4 2xl:grid-cols-[1.35fr,220px,220px,220px,220px,220px,auto]"
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
                                placeholder="Cari kode, isi soal, atau source..."
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
                            >Tipe</label
                        >
                        <select
                            v-model="filterForm.question_type"
                            class="flex h-10 w-full rounded-2xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none"
                        >
                            <option value="all">Semua</option>
                            <option
                                v-for="type in questionTypes"
                                :key="type.value"
                                :value="type.value"
                            >
                                {{ type.label }}
                            </option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700"
                            >Difficulty</label
                        >
                        <select
                            v-model="filterForm.difficulty"
                            class="flex h-10 w-full rounded-2xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none"
                        >
                            <option value="all">Semua</option>
                            <option
                                v-for="difficulty in difficulties"
                                :key="difficulty.value"
                                :value="difficulty.value"
                            >
                                {{ difficulty.label }}
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
                            <option
                                v-for="status in statuses"
                                :key="status.value"
                                :value="status.value"
                            >
                                {{ status.label }}
                            </option>
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
                                    <th class="px-4 py-3 font-medium">Soal</th>
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
                                    v-for="question in questions.data"
                                    :key="question.id"
                                    class="align-top"
                                >
                                    <td class="px-4 py-4">
                                        <div class="space-y-1">
                                            <p
                                                class="font-semibold text-slate-900"
                                            >
                                                {{
                                                    question.code || 'Auto code'
                                                }}
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                {{ question.category }} ·
                                                {{ question.subtest }}
                                            </p>
                                            <p
                                                class="max-w-xl text-sm text-slate-600"
                                            >
                                                {{ question.question_text }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-slate-600">
                                        <p>
                                            {{ question.question_type_label }}
                                        </p>
                                        <p>{{ question.difficulty_label }}</p>
                                        <p>{{ question.options_count }} opsi</p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <AdminStatusBadge
                                            :label="question.status_label"
                                            :tone="statusTone(question.status)"
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
                                                    :href="`/admin/questions/${question.id}/edit`"
                                                    >Edit</Link
                                                >
                                            </Button>
                                            <Button
                                                variant="outline"
                                                class="rounded-2xl"
                                                as-child
                                            >
                                                <Link
                                                    :href="`/admin/questions/${question.id}/options`"
                                                    >Opsi</Link
                                                >
                                            </Button>
                                            <Button
                                                v-if="
                                                    question.status !==
                                                    'published'
                                                "
                                                variant="outline"
                                                class="rounded-2xl"
                                                @click="
                                                    updateStatus(
                                                        question.id,
                                                        'published',
                                                    )
                                                "
                                            >
                                                Publish
                                            </Button>
                                            <Button
                                                v-if="
                                                    question.status !== 'review'
                                                "
                                                variant="outline"
                                                class="rounded-2xl"
                                                @click="
                                                    updateStatus(
                                                        question.id,
                                                        'review',
                                                    )
                                                "
                                            >
                                                Review
                                            </Button>
                                            <Button
                                                v-if="
                                                    question.status !==
                                                    'archived'
                                                "
                                                variant="outline"
                                                class="rounded-2xl"
                                                @click="
                                                    updateStatus(
                                                        question.id,
                                                        'archived',
                                                    )
                                                "
                                            >
                                                Archive
                                            </Button>
                                            <Button
                                                variant="outline"
                                                class="rounded-2xl text-rose-600"
                                                @click="destroy(question.id)"
                                            >
                                                Hapus
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="questions.data.length === 0">
                                    <td
                                        colspan="4"
                                        class="px-4 py-10 text-center text-slate-500"
                                    >
                                        Belum ada soal yang cocok dengan filter
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
                        Menampilkan {{ questions.from ?? 0 }}-{{
                            questions.to ?? 0
                        }}
                        dari {{ questions.total }} soal.
                    </p>
                    <AdminPagination :links="questions.links" />
                </div>
            </CardContent>
        </Card>
    </div>
</template>

