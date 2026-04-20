<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SubtestForm from '@/pages/Admin/Subtests/Partials/SubtestForm.vue';
import { dashboard } from '@/routes';
import type { EntityOption, SelectOption } from '@/types';

type SubtestFormData = {
    id: number;
    category_id: number | null;
    name: string;
    slug: string;
    description: string;
    instruction: string;
    scoring_type: string;
    default_duration_minutes: number | null;
    sort_order: number;
    is_active: boolean;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Admin', href: '/admin' },
            { title: 'Subtes', href: '/admin/subtests' },
            { title: 'Edit', href: '#' },
        ],
    },
});

defineProps<{
    subtest: SubtestFormData;
    categories: EntityOption[];
    scoringTypes: SelectOption[];
}>();
</script>

<template>
    <Head title="Edit Subtest" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <SubtestForm
            :subtest="subtest"
            :categories="categories"
            :scoring-types="scoringTypes"
            :submit-url="`/admin/subtests/${subtest.id}`"
            method="put"
            cancel-url="/admin/subtests"
            heading="Edit subtes"
            description="Pastikan nama, kategori, dan scoring type tetap konsisten dengan implementation plan."
        />
    </div>
</template>

