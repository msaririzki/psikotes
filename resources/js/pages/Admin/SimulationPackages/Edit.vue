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
            { title: 'Edit', href: '#' },
        ],
    },
});

defineProps<{
    simulationPackage: {
        id: number;
        title: string;
        slug: string;
        description: string;
        instruction: string;
        duration_minutes: number;
        sort_order: number;
        is_published: boolean;
        subtests: Array<{
            subtest_id: number | null;
            question_count: number;
            sort_order: number;
        }>;
    };
    subtests: Array<{
        id: number;
        name: string;
        category: string | null;
    }>;
}>();
</script>

<template>
    <Head title="Edit Simulation Pack" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <SimulationPackageForm
            :simulation-package="simulationPackage"
            :subtests="subtests"
            :submit-url="`/admin/simulation-packages/${simulationPackage.id}`"
            method="put"
            cancel-url="/admin/simulation-packages"
            heading="Edit paket simulasi"
            description="Perubahan paket akan berlaku untuk attempt baru, sedangkan attempt lama tetap direview dari immutable snapshot."
        />
    </div>
</template>

