<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SubtestForm from '@/pages/Admin/Subtes/Partials/SubtestForm.vue';
import { dashboard } from '@/routes';
import type { EntityOption, SelectOption } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Admin', href: '/admin' },
            { title: 'Subtes', href: '/admin/subtests' },
            { title: 'Create', href: '/admin/subtests/create' },
        ],
    },
});

defineProps<{
    categories: EntityOption[];
    scoringTypes: SelectOption[];
}>();
</script>

<template>
    <Head title="Create Subtest" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <SubtestForm
            :categories="categories"
            :scoring-types="scoringTypes"
            submit-url="/admin/subtests"
            method="post"
            cancel-url="/admin/subtests"
            heading="Tambah subtes"
            description="Subtes akan menjadi penghubung utama antara kategori, modul, dan bank soal."
        />
    </div>
</template>

