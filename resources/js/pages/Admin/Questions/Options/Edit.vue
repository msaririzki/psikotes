<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import QuestionOptionForm from '@/pages/Admin/Soal/Options/Partials/QuestionOptionForm.vue';
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

type QuestionOptionFormData = {
    id: number;
    option_key: string;
    option_text: string;
    option_image: string;
    weight: string | number | null;
    is_correct: boolean | null;
    sort_order: number;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Admin', href: '/admin' },
            { title: 'Soal', href: '/admin/questions' },
            { title: 'Options', href: '#' },
            { title: 'Edit', href: '#' },
        ],
    },
});

defineProps<{
    question: QuestionSummary;
    option: QuestionOptionFormData;
}>();
</script>

<template>
    <Head title="Edit Question Option" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <QuestionOptionForm
            :option="option"
            :submit-url="`/admin/questions/${question.id}/options/${option.id}`"
            method="put"
            :cancel-url="`/admin/questions/${question.id}/options`"
            heading="Edit opsi jawaban"
            description="Perubahan opsi akan langsung memengaruhi syarat publish untuk soal objektif."
        />
    </div>
</template>

