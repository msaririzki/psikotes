<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LearningModuleForm from '@/pages/Admin/LearningModules/Partials/LearningModuleForm.vue';
import { dashboard } from '@/routes';
import type { EntityOption, SelectOption } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Admin', href: '/admin' },
            { title: 'Modul Belajar', href: '/admin/learning-modules' },
            { title: 'Tambah', href: '/admin/learning-modules/create' },
        ],
    },
});

defineProps<{
    categories: EntityOption[];
    subtests: EntityOption[];
    levels: SelectOption[];
}>();
</script>

<template>
    <Head title="Tambah Modul Belajar" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <LearningModuleForm
            :categories="categories"
            :subtests="subtests"
            :levels="levels"
            submit-url="/admin/learning-modules"
            method="post"
            cancel-url="/admin/learning-modules"
            heading="Tambah modul belajar"
            description="Susun materi yang siap dipublish tanpa membawa logic engine practice/simulation ke fase ini."
        />
    </div>
</template>

