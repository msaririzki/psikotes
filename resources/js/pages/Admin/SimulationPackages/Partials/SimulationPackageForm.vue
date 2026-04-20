<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
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

type SubtestOption = {
    id: number;
    name: string;
    category: string | null;
};

type PackageFormData = {
    id?: number;
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

const props = defineProps<{
    simulationPackage?: PackageFormData | null;
    subtests: SubtestOption[];
    submitUrl: string;
    method: 'post' | 'put';
    cancelUrl: string;
    heading: string;
    description: string;
}>();

const form = useForm({
    title: props.simulationPackage?.title ?? '',
    slug: props.simulationPackage?.slug ?? '',
    description: props.simulationPackage?.description ?? '',
    instruction: props.simulationPackage?.instruction ?? '',
    duration_minutes: props.simulationPackage?.duration_minutes ?? 60,
    sort_order: props.simulationPackage?.sort_order ?? 0,
    is_published: props.simulationPackage?.is_published ?? false,
    subtests:
        props.simulationPackage?.subtests ?? [
            {
                subtest_id: props.subtests[0]?.id ?? null,
                question_count: 10,
                sort_order: 0,
            },
        ],
});

function addSubtestRow() {
    form.subtests.push({
        subtest_id: props.subtests[0]?.id ?? null,
        question_count: 10,
        sort_order: form.subtests.length,
    });
}

function removeSubtestRow(index: number) {
    if (form.subtests.length === 1) {
        return;
    }

    form.subtests.splice(index, 1);
}

function submit() {
    form.subtests = form.subtests.map((row, index) => ({
        ...row,
        sort_order: index,
    }));

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
                        <Label for="title">Judul paket</Label>
                        <Input
                            id="title"
                            v-model="form.title"
                            placeholder="Simulasi Dasar Akademik"
                        />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div class="space-y-2">
                        <Label for="slug">Slug</Label>
                        <Input
                            id="slug"
                            v-model="form.slug"
                            placeholder="simulasi-dasar-akademik"
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
                        class="flex min-h-24 w-full rounded-2xl border border-input bg-transparent px-4 py-3 text-sm shadow-xs outline-none"
                    />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="space-y-2">
                    <Label for="instruction">Instruksi simulasi</Label>
                    <textarea
                        id="instruction"
                        v-model="form.instruction"
                        rows="6"
                        class="flex min-h-32 w-full rounded-2xl border border-input bg-transparent px-4 py-3 text-sm shadow-xs outline-none"
                    />
                    <InputError :message="form.errors.instruction" />
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div class="space-y-2">
                        <Label for="duration_minutes">Durasi (menit)</Label>
                        <Input
                            id="duration_minutes"
                            v-model="form.duration_minutes"
                            type="number"
                            min="10"
                            max="300"
                        />
                        <InputError :message="form.errors.duration_minutes" />
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
                            v-model="form.is_published"
                            type="checkbox"
                            class="mt-1 size-4 rounded border-slate-300 text-[#7f1d1d] focus:ring-[#7f1d1d]"
                        />
                        <span class="space-y-1">
                            <span class="block text-sm font-medium text-slate-900">
                                Paket published
                            </span>
                            <span class="block text-sm text-slate-500">
                                Paket published akan tampil di halaman user.
                            </span>
                        </span>
                    </label>
                </div>

                <div class="space-y-4 rounded-[1.6rem] border border-[#eadfd6] bg-[#fffaf7] p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-slate-950">
                                Komposisi subtes
                            </p>
                            <p class="text-sm text-slate-500">
                                Tentukan subtes apa saja yang masuk ke paket dan jumlah soal per subtes.
                            </p>
                        </div>
                        <Button
                            type="button"
                            variant="outline"
                            class="rounded-2xl"
                            @click="addSubtestRow"
                        >
                            <Plus class="mr-2 size-4" />
                            Tambah subtes
                        </Button>
                    </div>

                    <div
                        v-for="(row, index) in form.subtests"
                        :key="index"
                        class="grid gap-4 rounded-[1.35rem] border border-[#eddccc] bg-white p-4 md:grid-cols-[1.5fr,160px,auto]"
                    >
                        <div class="space-y-2">
                            <Label :for="`subtest_${index}`">Subtes</Label>
                            <select
                                :id="`subtest_${index}`"
                                v-model="row.subtest_id"
                                class="flex h-10 w-full rounded-2xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none"
                            >
                                <option :value="null" disabled>
                                    Pilih subtes
                                </option>
                                <option
                                    v-for="subtest in subtests"
                                    :key="subtest.id"
                                    :value="subtest.id"
                                >
                                    {{ subtest.category }} - {{ subtest.name }}
                                </option>
                            </select>
                            <InputError
                                :message="
                                    form.errors[`subtests.${index}.subtest_id`]
                                "
                            />
                        </div>
                        <div class="space-y-2">
                            <Label :for="`question_count_${index}`">
                                Jumlah soal
                            </Label>
                            <Input
                                :id="`question_count_${index}`"
                                v-model="row.question_count"
                                type="number"
                                min="1"
                                max="200"
                            />
                            <InputError
                                :message="
                                    form.errors[
                                        `subtests.${index}.question_count`
                                    ]
                                "
                            />
                        </div>
                        <div class="flex items-end">
                            <Button
                                type="button"
                                variant="outline"
                                class="rounded-2xl text-rose-600"
                                @click="removeSubtestRow(index)"
                            >
                                <Trash2 class="mr-2 size-4" />
                                Hapus
                            </Button>
                        </div>
                    </div>

                    <InputError :message="form.errors.subtests" />
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Button
                        :disabled="form.processing"
                        class="rounded-2xl bg-[#7f1d1d] text-white hover:bg-[#6e1717]"
                    >
                        {{
                            props.method === 'put'
                                ? 'Simpan perubahan'
                                : 'Buat paket simulasi'
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
