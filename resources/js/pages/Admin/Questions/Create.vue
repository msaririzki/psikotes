<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import QuestionForm from '@/pages/Admin/Soal/Partials/QuestionForm.vue';
import { dashboard } from '@/routes';
import type { EntityOption, SelectOption } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Admin', href: '/admin' },
            { title: 'Soal', href: '/admin/questions' },
            { title: 'Create', href: '/admin/questions/create' },
        ],
    },
});

defineProps<{
    categories: EntityOption[];
    subtests: EntityOption[];
    questionTypes: SelectOption[];
    difficulties: SelectOption[];
    statuses: SelectOption[];
}>();
</script>

<template>
    <Head title="Create Question" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <QuestionForm
            :categories="categories"
            :subtests="subtests"
            :question-types="questionTypes"
            :difficulties="difficulties"
            :statuses="statuses"
            submit-url="/admin/questions"
            method="post"
            cancel-url="/admin/questions"
            heading="Tambah soal"
            description="Soal disimpan dengan validasi relasi kategori-subtes, tipe, difficulty, dan workflow status."
        />
    </div>
</template>

