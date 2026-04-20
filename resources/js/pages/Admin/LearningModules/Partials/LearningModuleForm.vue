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

type BelajaringModuleFormData = {
    id?: number;
    category_id: number | null;
    subtest_id: number | null;
    title: string;
    slug: string;
    summary: string;
    content: string;
    tips: string;
    tricks: string;
    level: string;
    estimated_minutes: number | string | null;
    is_published: boolean;
};

const props = defineProps<{
    learningModule?: BelajaringModuleFormData | null;
    categories: EntityOption[];
    subtests: EntityOption[];
    levels: SelectOption[];
    submitUrl: string;
    method: 'post' | 'put';
    cancelUrl: string;
    heading: string;
    description: string;
}>();

const form = useForm({
    category_id:
        props.learningModule?.category_id ?? props.categories[0]?.id ?? null,
    subtest_id:
        props.learningModule?.subtest_id ?? props.subtests[0]?.id ?? null,
    title: props.learningModule?.title ?? '',
    slug: props.learningModule?.slug ?? '',
    summary: props.learningModule?.summary ?? '',
    content: props.learningModule?.content ?? '',
    tips: props.learningModule?.tips ?? '',
    tricks: props.learningModule?.tricks ?? '',
    level: props.learningModule?.level ?? props.levels[0]?.value ?? 'basic',
    estimated_minutes: props.learningModule?.estimated_minutes ?? '',
    is_published: props.learningModule?.is_published ?? false,
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
    const payload = {
        subtest_id: form.subtest_id,
        title: form.title,
        slug: form.slug,
        summary: form.summary,
        content: form.content,
        tips: form.tips,
        tricks: form.tricks,
        level: form.level,
        estimated_minutes: form.estimated_minutes,
        is_published: form.is_published,
    };

    if (props.method === 'put') {
        form.transform(() => payload).put(props.submitUrl);

        return;
    }

    form.transform(() => payload).post(props.submitUrl);
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
                <div class="grid gap-6 md:grid-cols-2">
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

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="title">Judul modul</Label>
                        <Input
                            id="title"
                            v-model="form.title"
                            placeholder="Strategi dasar verbal"
                        />
                        <InputError :message="form.errors.title" />
                    </div>

                    <div class="space-y-2">
                        <Label for="slug">Slug</Label>
                        <Input
                            id="slug"
                            v-model="form.slug"
                            placeholder="strategi-dasar-verbal"
                        />
                        <InputError :message="form.errors.slug" />
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="level">Level</Label>
                        <select
                            id="level"
                            v-model="form.level"
                            class="flex h-10 w-full rounded-2xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none"
                        >
                            <option
                                v-for="level in levels"
                                :key="level.value"
                                :value="level.value"
                            >
                                {{ level.label }}
                            </option>
                        </select>
                        <InputError :message="form.errors.level" />
                    </div>

                    <div class="space-y-2">
                        <Label for="estimated_minutes">Estimasi menit</Label>
                        <Input
                            id="estimated_minutes"
                            v-model="form.estimated_minutes"
                            type="number"
                            min="1"
                            max="600"
                            placeholder="20"
                        />
                        <InputError :message="form.errors.estimated_minutes" />
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="summary">Ringkasan</Label>
                    <textarea
                        id="summary"
                        v-model="form.summary"
                        rows="4"
                        class="flex min-h-24 w-full rounded-2xl border border-input bg-transparent px-4 py-3 text-sm shadow-xs transition outline-none"
                    />
                    <InputError :message="form.errors.summary" />
                </div>

                <div class="space-y-2">
                    <Label for="content">Konten utama</Label>
                    <textarea
                        id="content"
                        v-model="form.content"
                        rows="10"
                        class="flex min-h-52 w-full rounded-2xl border border-input bg-transparent px-4 py-3 text-sm shadow-xs transition outline-none"
                    />
                    <InputError :message="form.errors.content" />
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="tips">Tips</Label>
                        <textarea
                            id="tips"
                            v-model="form.tips"
                            rows="6"
                            class="flex min-h-32 w-full rounded-2xl border border-input bg-transparent px-4 py-3 text-sm shadow-xs transition outline-none"
                        />
                        <InputError :message="form.errors.tips" />
                    </div>

                    <div class="space-y-2">
                        <Label for="tricks">Tricks</Label>
                        <textarea
                            id="tricks"
                            v-model="form.tricks"
                            rows="6"
                            class="flex min-h-32 w-full rounded-2xl border border-input bg-transparent px-4 py-3 text-sm shadow-xs transition outline-none"
                        />
                        <InputError :message="form.errors.tricks" />
                    </div>
                </div>

                <label
                    class="flex items-start gap-3 rounded-3xl border border-[#ead8cd] bg-[#fff8f4] px-4 py-4"
                >
                    <input
                        v-model="form.is_published"
                        type="checkbox"
                        class="mt-1 size-4 rounded border-slate-300 text-[#7f1d1d] focus:ring-[#7f1d1d]"
                    />
                    <span class="space-y-1">
                        <span class="block text-sm font-medium text-slate-900"
                            >Publish modul</span
                        >
                        <span class="block text-sm text-slate-500"
                            >Workflow publish tetap bisa diubah dari halaman
                            index.</span
                        >
                    </span>
                </label>

                <div class="flex flex-wrap items-center gap-3">
                    <Button
                        :disabled="form.processing"
                        class="rounded-2xl bg-[#7f1d1d] text-white hover:bg-[#6e1717]"
                    >
                        {{
                            props.method === 'put'
                                ? 'Simpan perubahan'
                                : 'Buat modul'
                        }}
                    </Button>
                    <Button as-child variant="outline" class="rounded-2xl">
                        <Link :href="cancelUrl">Kembali</Link>
                    </Button>
                </div>
            </form>
        </CardContent>
    </Card>
</template>

