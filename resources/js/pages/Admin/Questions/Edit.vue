<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import QuestionForm from '@/pages/Admin/Soal/Partials/QuestionForm.vue';
import { dashboard } from '@/routes';
import type { EntityOption, SelectOption } from '@/types';

type QuestionFormData = {
    id: number;
    category_id: number | null;
    subtest_id: number | null;
    code: string;
    question_type: string;
    difficulty: string;
    question_text: string;
    question_image: string;
    extra_data: string;
    explanation_text: string;
    answer_key_text: string;
    status: string;
    source_reference: string;
    options_count: number;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Admin', href: '/admin' },
            { title: 'Soal', href: '/admin/questions' },
            { title: 'Edit', href: '#' },
        ],
    },
});

defineProps<{
    question: QuestionFormData;
    categories: EntityOption[];
    subtests: EntityOption[];
    questionTypes: SelectOption[];
    difficulties: SelectOption[];
    statuses: SelectOption[];
}>();
</script>

<template>
    <Head title="Edit Question" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <QuestionForm
            :question="question"
            :categories="categories"
            :subtests="subtests"
            :question-types="questionTypes"
            :difficulties="difficulties"
            :statuses="statuses"
            :submit-url="`/admin/questions/${question.id}`"
            method="put"
            cancel-url="/admin/questions"
            heading="Edit soal"
            description="Pengelolaan opsi jawaban tetap dipisah agar edit soal inti dan edit option tidak saling bertabrakan."
        />
    </div>
</template>

