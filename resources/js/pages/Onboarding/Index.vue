<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowRight, Compass, Target } from 'lucide-vue-next';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';

type ProfilForm = {
    education_level: string | null;
    target_exam: string;
    learning_level: string;
    target_daily_minutes: number | null;
    preferred_focus: string;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dasbor', href: dashboard() },
            { title: 'Orientasi', href: '/onboarding' },
        ],
    },
});

const props = defineProps<{
    profile: ProfilForm;
    onboarding: {
        completed: boolean;
        learning_levels: Array<{ value: string; label: string }>;
        focus_options: Array<{ value: string; label: string }>;
    };
}>();

const form = useForm({
    education_level: props.profile.education_level ?? '',
    target_exam: props.profile.target_exam,
    learning_level: props.profile.learning_level,
    target_daily_minutes: props.profile.target_daily_minutes ?? 45,
    preferred_focus: props.profile.preferred_focus,
});

const heading = computed(() =>
    props.onboarding.completed
        ? 'Perbarui arah belajar kamu'
        : 'Selesaikan onboarding singkat dulu',
);

function submit() {
    form.post('/onboarding');
}
</script>

<template>
    <Head title="Orientasi" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <section
            class="overflow-hidden rounded-[2rem] border border-[#d9e3ec] bg-[radial-gradient(circle_at_top_left,_rgba(185,28,28,0.12),_transparent_30%),linear-gradient(135deg,_#f8fbff_0%,_#eef3f8_55%,_#ffffff_100%)] p-6 shadow-sm"
        >
            <div class="grid gap-6 xl:grid-cols-[1.08fr,0.92fr] xl:items-end">
                <div class="space-y-4">
                    <div class="inline-flex items-center rounded-full bg-[#0f172a] px-4 py-2 text-xs font-semibold tracking-[0.18em] text-white uppercase">
                        First Run Setup
                    </div>
                    <div>
                        <h1 class="font-display text-4xl font-bold tracking-tight text-slate-950">
                            {{ heading }}
                        </h1>
                        <p class="mt-3 max-w-3xl text-base leading-7 text-slate-600">
                            Halaman ini dipakai untuk menandai target belajar awal user. Setelah ini dashboard, study plan, dan goal tracking akan lebih relevan.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <Button as-child variant="outline" class="rounded-2xl">
                        <Link href="/dashboard">
                            Kembali ke dashboard
                        </Link>
                    </Button>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[1.08fr,0.92fr]">
            <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                <CardHeader>
                    <CardTitle>Orientasi form</CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="space-y-5" @submit.prevent="submit">
                        <div class="grid gap-2">
                            <Label for="education_level">Latar pendidikan</Label>
                            <Input
                                id="education_level"
                                v-model="form.education_level"
                                placeholder="Contoh: SMA / SMK"
                            />
                            <InputError :message="form.errors.education_level" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="target_exam">Target ujian</Label>
                            <Input
                                id="target_exam"
                                v-model="form.target_exam"
                                placeholder="Psikotes Polri"
                            />
                            <InputError :message="form.errors.target_exam" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="learning_level">Level belajar saat ini</Label>
                            <select
                                id="learning_level"
                                v-model="form.learning_level"
                                class="flex h-11 w-full rounded-2xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            >
                                <option
                                    v-for="option in onboarding.learning_levels"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.learning_level" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="target_daily_minutes">Target belajar per hari</Label>
                            <Input
                                id="target_daily_minutes"
                                v-model="form.target_daily_minutes"
                                type="number"
                                min="15"
                                max="240"
                            />
                            <InputError :message="form.errors.target_daily_minutes" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="preferred_focus">Fokus utama</Label>
                            <select
                                id="preferred_focus"
                                v-model="form.preferred_focus"
                                class="flex h-11 w-full rounded-2xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            >
                                <option
                                    v-for="option in onboarding.focus_options"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.preferred_focus" />
                        </div>

                        <Button
                            type="submit"
                            class="rounded-2xl bg-[#b91c1c] text-white hover:bg-[#991b1b]"
                            :disabled="form.processing"
                        >
                            Simpan onboarding
                            <ArrowRight class="ml-2 size-4" />
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <div class="space-y-5">
                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-white/95 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Target class="size-5 text-[#b91c1c]" />
                            Kenapa ini penting
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3 text-sm leading-6 text-slate-600">
                        <p class="rounded-2xl bg-[#fbfdff] p-4 ring-1 ring-[#e6edf3]">
                            Study plan dan goal tracking membaca histori nyata, tetapi target awal harian tetap penting agar agenda tidak terasa random.
                        </p>
                        <p class="rounded-2xl bg-[#fbfdff] p-4 ring-1 ring-[#e6edf3]">
                            Setelah onboarding selesai, user baru bisa langsung masuk ke learn, practice, dan simulation tanpa setup tambahan.
                        </p>
                    </CardContent>
                </Card>

                <Card class="rounded-[1.75rem] border-[#dfe8ef] bg-[#0f172a] text-white shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Compass class="size-5 text-white" />
                            What changes next
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3 text-sm text-slate-200">
                        <p>Dasbor akan menampilkan target readiness yang lebih relevan.</p>
                        <p>Study plan akan lebih mudah dipahami karena fokus awal user sudah tercatat.</p>
                        <p>Flow ini sengaja singkat agar tidak menghambat local UAT.</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>

