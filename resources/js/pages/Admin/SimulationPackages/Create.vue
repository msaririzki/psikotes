<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SimulationPackageForm from '@/pages/Admin/SimulationPackages/Partials/SimulationPackageForm.vue';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Admin', href: '/admin' },
            {
                title: 'Simulation Packs',
                href: '/admin/simulation-packages',
            },
            {
                title: 'Create',
                href: '/admin/simulation-packages/create',
            },
        ],
    },
});

defineProps<{
    subtests: Array<{
        id: number;
        name: string;
        category: string | null;
    }>;
}>();
</script>

<template>
    <Head title="Create Simulation Pack" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <SimulationPackageForm
            :subtests="subtests"
            submit-url="/admin/simulation-packages"
            method="post"
            cancel-url="/admin/simulation-packages"
            heading="Tambah paket simulasi"
            description="Bangun paket CAT dengan komposisi subtes dan timer penuh tanpa menduplikasi bank soal."
        />
    </div>
</template>

