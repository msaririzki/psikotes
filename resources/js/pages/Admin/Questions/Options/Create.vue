<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import QuestionOptionForm from '@/pages/Admin/Questions/Options/Partials/QuestionOptionForm.vue';
import { dashboard } from '@/routes';

type QuestionSummary = {
    id: number;
    code: string | null;
    question_text: string;
    question_type: string;
    question_type_label: string;
    status: string;
    status_label: string;
    category: string | null;
    subtest: string | null;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Admin', href: '/admin' },
            { title: 'Soal', href: '/admin/questions' },
            { title: 'Options', href: '#' },
            { title: 'Create', href: '#' },
        ],
    },
});

defineProps<{
    question: QuestionSummary;
}>();
</script>

<template>
    <Head title="Create Question Option" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <QuestionOptionForm
            :submit-url="`/admin/questions/${question.id}/options`"
            method="post"
            :cancel-url="`/admin/questions/${question.id}/options`"
            heading="Tambah opsi jawaban"
            description="Tambahkan opsi dengan key unik per soal. Gunakan weight bila subtes membutuhkan skoring berbobot."
        />
    </div>
</template>

