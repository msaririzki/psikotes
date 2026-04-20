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

type QuestionOptionFormData = {
    id?: number;
    option_key: string;
    option_text: string;
    option_image: string;
    weight: string | number | null;
    is_correct: boolean | null;
    sort_order: number;
};

const props = defineProps<{
    option?: QuestionOptionFormData | null;
    submitUrl: string;
    method: 'post' | 'put';
    cancelUrl: string;
    heading: string;
    description: string;
}>();

const form = useForm({
    option_key: props.option?.option_key ?? '',
    option_text: props.option?.option_text ?? '',
    option_image: props.option?.option_image ?? '',
    weight: props.option?.weight ?? '',
    is_correct: props.option?.is_correct ?? false,
    sort_order: props.option?.sort_order ?? 0,
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
                <div class="grid gap-6 md:grid-cols-3">
                    <div class="space-y-2">
                        <Label for="option_key">Key</Label>
                        <Input
                            id="option_key"
                            v-model="form.option_key"
                            placeholder="A"
                        />
                        <InputError :message="form.errors.option_key" />
                    </div>

                    <div class="space-y-2">
                        <Label for="weight">Weight</Label>
                        <Input
                            id="weight"
                            v-model="form.weight"
                            type="number"
                            step="0.01"
                            min="0"
                            max="9999"
                            placeholder="1.00"
                        />
                        <InputError :message="form.errors.weight" />
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
                </div>

                <div class="space-y-2">
                    <Label for="option_text">Teks opsi</Label>
                    <textarea
                        id="option_text"
                        v-model="form.option_text"
                        rows="6"
                        class="flex min-h-32 w-full rounded-2xl border border-input bg-transparent px-4 py-3 text-sm shadow-xs transition outline-none"
                    />
                    <InputError :message="form.errors.option_text" />
                </div>

                <div class="space-y-2">
                    <Label for="option_image">URL gambar opsi</Label>
                    <Input
                        id="option_image"
                        v-model="form.option_image"
                        placeholder="https://..."
                    />
                    <InputError :message="form.errors.option_image" />
                </div>

                <label
                    class="flex items-start gap-3 rounded-3xl border border-[#ead8cd] bg-[#fff8f4] px-4 py-4"
                >
                    <input
                        v-model="form.is_correct"
                        type="checkbox"
                        class="mt-1 size-4 rounded border-slate-300 text-[#7f1d1d] focus:ring-[#7f1d1d]"
                    />
                    <span class="space-y-1">
                        <span class="block text-sm font-medium text-slate-900"
                            >Tandai sebagai jawaban benar</span
                        >
                        <span class="block text-sm text-slate-500"
                            >Untuk soal non-objektif, field ini bisa dibiarkan
                            kosong atau false sesuai kebutuhan implementasi
                            berikutnya.</span
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
                                : 'Buat opsi'
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
