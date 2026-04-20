<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
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

type SubtestFormData = {
    id?: number;
    category_id: number | null;
    name: string;
    slug: string;
    description: string;
    instruction: string;
    scoring_type: string;
    default_duration_minutes: number | string | null;
    sort_order: number;
    is_active: boolean;
};

const props = defineProps<{
    subtest?: SubtestFormData | null;
    categories: EntityOption[];
    scoringTypes: SelectOption[];
    submitUrl: string;
    method: 'post' | 'put';
    cancelUrl: string;
    heading: string;
    description: string;
}>();

const form = useForm({
    category_id: props.subtest?.category_id ?? props.categories[0]?.id ?? null,
    name: props.subtest?.name ?? '',
    slug: props.subtest?.slug ?? '',
    description: props.subtest?.description ?? '',
    instruction: props.subtest?.instruction ?? '',
    scoring_type:
        props.subtest?.scoring_type ??
        props.scoringTypes[0]?.value ??
        'objective',
    default_duration_minutes: props.subtest?.default_duration_minutes ?? '',
    sort_order: props.subtest?.sort_order ?? 0,
    is_active: props.subtest?.is_active ?? true,
});

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
                        <InputError :message="form.errors.category_id" />
                    </div>

                    <div class="space-y-2">
                        <Label for="scoring_type">Tipe scoring</Label>
                        <select
                            id="scoring_type"
                            v-model="form.scoring_type"
                            class="flex h-10 w-full rounded-2xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none"
                        >
                            <option
                                v-for="type in scoringTypes"
                                :key="type.value"
                                :value="type.value"
                            >
                                {{ type.label }}
                            </option>
                        </select>
                        <InputError :message="form.errors.scoring_type" />
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="name">Nama subtes</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Kemampuan Verbal"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="space-y-2">
                        <Label for="slug">Slug</Label>
                        <Input
                            id="slug"
                            v-model="form.slug"
                            placeholder="kemampuan-verbal"
                        />
                        <InputError :message="form.errors.slug" />
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="description">Deskripsi</Label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        class="flex min-h-24 w-full rounded-2xl border border-input bg-transparent px-4 py-3 text-sm shadow-xs transition outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        placeholder="Ringkasan subtes."
                    />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="space-y-2">
                    <Label for="instruction">Instruksi</Label>
                    <textarea
                        id="instruction"
                        v-model="form.instruction"
                        rows="6"
                        class="flex min-h-32 w-full rounded-2xl border border-input bg-transparent px-4 py-3 text-sm shadow-xs transition outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        placeholder="Instruksi pengerjaan subtes."
                    />
                    <InputError :message="form.errors.instruction" />
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div class="space-y-2">
                        <Label for="default_duration_minutes"
                            >Durasi default</Label
                        >
                        <Input
                            id="default_duration_minutes"
                            v-model="form.default_duration_minutes"
                            type="number"
                            min="1"
                            max="300"
                            placeholder="60"
                        />
                        <InputError
                            :message="form.errors.default_duration_minutes"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="sort_order">Urutan</Label>
                        <Input
                            id="sort_order"
                            v-model="form.sort_order"
                            type="number"
                            min="0"
                            max="9999"
                        />
                        <InputError :message="form.errors.sort_order" />
                    </div>

                    <label
                        class="flex items-start gap-3 rounded-3xl border border-[#ead8cd] bg-[#fff8f4] px-4 py-4"
                    >
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="mt-1 size-4 rounded border-slate-300 text-[#7f1d1d] focus:ring-[#7f1d1d]"
                        />
                        <span class="space-y-1">
                            <span
                                class="block text-sm font-medium text-slate-900"
                                >Subtes aktif</span
                            >
                            <span class="block text-sm text-slate-500"
                                >Konten tetap dapat diedit saat nonaktif.</span
                            >
                        </span>
                    </label>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Button
                        :disabled="form.processing"
                        class="rounded-2xl bg-[#7f1d1d] text-white hover:bg-[#6e1717]"
                    >
                        {{
                            props.method === 'put'
                                ? 'Simpan perubahan'
                                : 'Buat subtes'
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
