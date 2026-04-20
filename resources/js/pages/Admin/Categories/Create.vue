<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CategoryForm from '@/pages/Admin/Categories/Partials/CategoryForm.vue';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Admin', href: '/admin' },
            { title: 'Kategori', href: '/admin/categories' },
            { title: 'Create', href: '/admin/categories/create' },
        ],
    },
});
</script>

<template>
    <Head title="Create Category" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <CategoryForm
            submit-url="/admin/categories"
            method="post"
            cancel-url="/admin/categories"
            heading="Tambah kategori"
            description="Gunakan kategori untuk memisahkan kelompok besar materi dan bank soal."
        />
    </div>
</template>

