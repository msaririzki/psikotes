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

type CategoryFormData = {
    id?: number;
    name: string;
    slug: string;
    description: string;
    sort_order: number;
    is_active: boolean;
};

const props = defineProps<{
    category?: CategoryFormData | null;
    submitUrl: string;
    method: 'post' | 'put';
    cancelUrl: string;
    heading: string;
    description: string;
}>();

const form = useForm({
    name: props.category?.name ?? '',
    slug: props.category?.slug ?? '',
    description: props.category?.description ?? '',
    sort_order: props.category?.sort_order ?? 0,
    is_active: props.category?.is_active ?? true,
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
                        <Label for="name">Nama kategori</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Tes Kecerdasan"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="space-y-2">
                        <Label for="slug">Slug</Label>
                        <Input
                            id="slug"
                            v-model="form.slug"
                            placeholder="tes-kecerdasan"
                        />
                        <InputError :message="form.errors.slug" />
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="description">Deskripsi</Label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="5"
                        class="flex min-h-28 w-full rounded-2xl border border-input bg-transparent px-4 py-3 text-sm shadow-xs transition outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        placeholder="Ringkasan kategori untuk admin dan future catalog."
                    />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="grid gap-6 md:grid-cols-[180px,1fr]">
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
                                >Kategori aktif</span
                            >
                            <span class="block text-sm text-slate-500">
                                Jika nonaktif, kategori tetap tersimpan tetapi
                                tidak siap dipakai untuk fase operasional
                                berikutnya.
                            </span>
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
                                : 'Buat kategori'
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
