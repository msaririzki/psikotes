<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { EntityOption, SelectOption } from '@/types';

type QuestionFormData = {
    id?: number;
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
    options_count?: number;
};

const props = defineProps<{
    question?: QuestionFormData | null;
    categories: EntityOption[];
    subtests: EntityOption[];
    questionTypes: SelectOption[];
    difficulties: SelectOption[];
    statuses: SelectOption[];
    submitUrl: string;
    method: 'post' | 'put';
    cancelUrl: string;
    heading: string;
    description: string;
}>();

const form = useForm({
    category_id: props.question?.category_id ?? props.categories[0]?.id ?? null,
    subtest_id: props.question?.subtest_id ?? props.subtests[0]?.id ?? null,
    code: props.question?.code ?? '',
    question_type:
        props.question?.question_type ??
        props.questionTypes[0]?.value ??
        'multiple_choice_single',
    difficulty:
        props.question?.difficulty ?? props.difficulties[0]?.value ?? 'easy',
    question_text: props.question?.question_text ?? '',
    question_image: props.question?.question_image ?? '',
    extra_data: props.question?.extra_data ?? '',
    explanation_text: props.question?.explanation_text ?? '',
    answer_key_text: props.question?.answer_key_text ?? '',
    status: props.question?.status ?? props.statuses[0]?.value ?? 'draft',
    source_reference: props.question?.source_reference ?? '',
});

const availableSubtes = computed(() =>
    props.subtests.filter(
        (subtest) =>
            !form.category_id ||
            subtest.category_id === Number(form.category_id),
    ),
);

watch(
    () => form.category_id,
    () => {
        const stillValid = availableSubtes.value.some(
            (subtest) => subtest.id === form.subtest_id,
        );

        if (!stillValid) {
            form.subtest_id = availableSubtes.value[0]?.id ?? null;
        }
    },
);

function submit() {
    if (props.method === 'put') {
        form.put(props.submitUrl);

        return;
    }

    form.post(props.submitUrl);
}
</script>

<template>
    <Card class="rounded-[1.75rem] border-[#e7ddd5] shadow-sm">
        <CardHeader>
            <CardTitle>{{ heading }}</CardTitle>
            <CardDescription>{{ description }}</CardDescription>
        </CardHeader>
        <CardContent>
            <form class="space-y-6" @submit.prevent="submit">
                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="category_id">Kategori</Label>
                        <select
                            id="category_id"
                            v-model="form.category_id"
                            class="flex h-10 w-full rounded-2xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none"
                        >
                            <option :value="null" disabled>
                                Pilih kategori
                            </option>
                            <option
                                v-for="category in categories"
                                :key="category.id"
                                :value="category.id"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.category_id" />
                    </div>

                    <div class="space-y-2">
                        <Label for="subtest_id">Subtes</Label>
                        <select
                            id="subtest_id"
                            v-model="form.subtest_id"
                            class="flex h-10 w-full rounded-2xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none"
                        >
                            <option :value="null" disabled>Pilih subtes</option>
                            <option
                                v-for="subtest in availableSubtes"
                                :key="subtest.id"
                                :value="subtest.id"
                            >
                                {{ subtest.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.subtest_id" />
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-4">
                    <div class="space-y-2">
                        <Label for="code">Kode soal</Label>
                        <Input
                            id="code"
                            v-model="form.code"
                            placeholder="Q-1-ABC123"
                        />
                        <InputError :message="form.errors.code" />
                    </div>

                    <div class="space-y-2">
                        <Label for="question_type">Tipe soal</Label>
                        <select
                            id="question_type"
                            v-model="form.question_type"
                            class="flex h-10 w-full rounded-2xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none"
                        >
                            <option
                                v-for="type in questionTypes"
                                :key="type.value"
                                :value="type.value"
                            >
                                {{ type.label }}
                            </option>
                        </select>
                        <InputError :message="form.errors.question_type" />
                    </div>

                    <div class="space-y-2">
                        <Label for="difficulty">Difficulty</Label>
                        <select
                            id="difficulty"
                            v-model="form.difficulty"
                            class="flex h-10 w-full rounded-2xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none"
                        >
                            <option
                                v-for="difficulty in difficulties"
                                :key="difficulty.value"
                                :value="difficulty.value"
                            >
                                {{ difficulty.label }}
                            </option>
                        </select>
                        <InputError :message="form.errors.difficulty" />
                    </div>

                    <div class="space-y-2">
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            v-model="form.status"
                            class="flex h-10 w-full rounded-2xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none"
                        >
                            <option
                                v-for="status in statuses"
                                :key="status.value"
                                :value="status.value"
                            >
                                {{ status.label }}
                            </option>
                        </select>
                        <InputError :message="form.errors.status" />
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="question_text">Teks soal</Label>
                    <textarea
                        id="question_text"
                        v-model="form.question_text"
                        rows="8"
                        class="flex min-h-40 w-full rounded-2xl border border-input bg-transparent px-4 py-3 text-sm shadow-xs transition outline-none"
                    />
                    <InputError :message="form.errors.question_text" />
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="question_image">URL gambar soal</Label>
                        <Input
                            id="question_image"
                            v-model="form.question_image"
                            placeholder="https://..."
                        />
                        <InputError :message="form.errors.question_image" />
                    </div>

                    <div class="space-y-2">
                        <Label for="source_reference">Source reference</Label>
                        <Input
                            id="source_reference"
                            v-model="form.source_reference"
                            placeholder="Bank soal Polri 2025"
                        />
                        <InputError :message="form.errors.source_reference" />
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="extra_data">Extra data JSON</Label>
                    <textarea
                        id="extra_data"
                        v-model="form.extra_data"
                        rows="6"
                        class="flex min-h-32 w-full rounded-2xl border border-input bg-transparent px-4 py-3 font-mono text-sm shadow-xs transition outline-none"
                        placeholder='{"note":"opsional"}'
                    />
                    <InputError :message="form.errors.extra_data" />
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="answer_key_text"
                            >Jawaban / answer key</Label
                        >
                        <textarea
                            id="answer_key_text"
                            v-model="form.answer_key_text"
                            rows="6"
                            class="flex min-h-32 w-full rounded-2xl border border-input bg-transparent px-4 py-3 text-sm shadow-xs transition outline-none"
                        />
                        <InputError :message="form.errors.answer_key_text" />
                    </div>

                    <div class="space-y-2">
                        <Label for="explanation_text">Pembahasan</Label>
                        <textarea
                            id="explanation_text"
                            v-model="form.explanation_text"
                            rows="6"
                            class="flex min-h-32 w-full rounded-2xl border border-input bg-transparent px-4 py-3 text-sm shadow-xs transition outline-none"
                        />
                        <InputError :message="form.errors.explanation_text" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Button
                        :disabled="form.processing"
                        class="rounded-2xl bg-[#7f1d1d] text-white hover:bg-[#6e1717]"
                    >
                        {{
                            props.method === 'put'
                                ? 'Simpan perubahan'
                                : 'Buat soal'
                        }}
                    </Button>
                    <Button as-child variant="outline" class="rounded-2xl">
                        <Link :href="cancelUrl">Kembali</Link>
                    </Button>
                    <Button
                        v-if="props.question?.id"
                        as-child
                        variant="outline"
                        class="rounded-2xl"
                    >
                        <Link
                            :href="`/admin/questions/${props.question.id}/options`"
                        >
                            Kelola opsi jawaban
                            <span
                                v-if="props.question.options_count"
                                class="ml-2 text-slate-500"
                            >
                                ({{ props.question.options_count }})
                            </span>
                        </Link>
                    </Button>
                </div>
            </form>
        </CardContent>
    </Card>
</template>

