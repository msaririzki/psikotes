<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CategoryForm from '@/pages/Admin/Categories/Partials/CategoryForm.vue';
import { dashboard } from '@/routes';

type CategoryFormData = {
    id: number;
    name: string;
    slug: string;
    description: string;
    sort_order: number;
    is_active: boolean;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Admin', href: '/admin' },
            { title: 'Kategori', href: '/admin/categories' },
            { title: 'Edit', href: '#' },
        ],
    },
});

defineProps<{
    category: CategoryFormData;
}>();
</script>

<template>
    <Head title="Edit Category" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <CategoryForm
            :category="category"
            :submit-url="`/admin/categories/${category.id}`"
            method="put"
            cancel-url="/admin/categories"
            heading="Edit kategori"
            description="Perubahan kategori langsung memengaruhi opsi relasi untuk subtes dan soal."
        />
    </div>
</template>

