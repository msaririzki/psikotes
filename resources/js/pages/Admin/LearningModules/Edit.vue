<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LearningModuleForm from '@/pages/Admin/LearningModules/Partials/LearningModuleForm.vue';
import { dashboard } from '@/routes';
import type { EntityOption, SelectOption } from '@/types';

type LearningModuleFormData = {
    id: number;
    category_id: number | null;
    subtest_id: number | null;
    title: string;
    slug: string;
    summary: string;
    content: string;
    tips: string;
    tricks: string;
    level: string;
    estimated_minutes: number | null;
    is_published: boolean;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Admin', href: '/admin' },
            { title: 'Modul Belajar', href: '/admin/learning-modules' },
            { title: 'Ubah', href: '#' },
        ],
    },
});

defineProps<{
    learningModule: LearningModuleFormData;
    categories: EntityOption[];
    subtests: EntityOption[];
    levels: SelectOption[];
}>();
</script>

<template>
    <Head title="Ubah Modul Belajar" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <LearningModuleForm
            :learning-module="learningModule"
            :categories="categories"
            :subtests="subtests"
            :levels="levels"
            :submit-url="`/admin/learning-modules/${learningModule.id}`"
            method="put"
            cancel-url="/admin/learning-modules"
            heading="Edit modul belajar"
            description="Jaga hubungan kategori-subtes-modul tetap konsisten agar phase berikutnya tinggal memakai katalog ini."
        />
    </div>
</template>

