<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

type AdminOverview = {
    users: {
        total: number;
        verified: number;
        admins: number;
    };
    content: {
        categories: number;
        subtests: number;
        modules: number;
        questions: number;
    };
    activity: {
        attempts: number;
    };
};

defineProps<{
    overview: AdminOverview;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dasbor',
                href: '/dashboard',
            },
            {
                title: 'Admin',
                href: '/admin',
            },
        ],
    },
});
</script>

<template>
    <Head title="Admin" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <section
            class="overflow-hidden rounded-[2rem] border border-[#dbd6d0] bg-[radial-gradient(circle_at_top_right,_rgba(176,34,34,0.15),_transparent_28%),linear-gradient(135deg,_#fff8f1_0%,_#fff3ef_48%,_#ffffff_100%)] p-6 shadow-sm"
        >
            <div class="space-y-3">
                <Badge
                    class="rounded-full bg-[#7f1d1d] text-white hover:bg-[#7f1d1d]"
                >
                    Admin Control Surface
                </Badge>
                <div>
                    <h1
                        class="font-display text-3xl font-bold tracking-tight text-[#111827]"
                    >
                        Fondasi administrasi sudah aktif.
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                        Pada Phase 1, panel ini berfungsi sebagai titik masuk
                        terproteksi untuk admin. CRUD konten akan dibuka di
                        Phase 2 di atas schema yang sudah disiapkan.
                    </p>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <Card class="rounded-[1.75rem] border-[#e7ddd5]">
                <CardHeader>
                    <CardDescription>Total user</CardDescription>
                    <CardTitle class="text-3xl">{{
                        overview.users.total
                    }}</CardTitle>
                </CardHeader>
            </Card>
            <Card class="rounded-[1.75rem] border-[#e7ddd5]">
                <CardHeader>
                    <CardDescription>User terverifikasi</CardDescription>
                    <CardTitle class="text-3xl">{{
                        overview.users.verified
                    }}</CardTitle>
                </CardHeader>
            </Card>
            <Card class="rounded-[1.75rem] border-[#e7ddd5]">
                <CardHeader>
                    <CardDescription>Admin aktif</CardDescription>
                    <CardTitle class="text-3xl">{{
                        overview.users.admins
                    }}</CardTitle>
                </CardHeader>
            </Card>
            <Card class="rounded-[1.75rem] border-[#e7ddd5]">
                <CardHeader>
                    <CardDescription>Attempt tersimpan</CardDescription>
                    <CardTitle class="text-3xl">{{
                        overview.activity.attempts
                    }}</CardTitle>
                </CardHeader>
            </Card>
        </section>

        <section class="grid gap-4 xl:grid-cols-[1.2fr,0.8fr]">
            <Card class="rounded-[1.75rem] border-[#e7ddd5]">
                <CardHeader>
                    <CardTitle>Content inventory</CardTitle>
                    <CardDescription>
                        Seed Phase 1 mengikuti struktur domain dari
                        implementation plan dan PDF bank soal.
                    </CardDescription>
                </CardHeader>
                <CardContent class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-3xl bg-[#fff7f3] p-4">
                        <p class="text-sm text-slate-500">Kategori</p>
                        <p class="mt-2 text-xl font-semibold text-slate-900">
                            {{ overview.content.categories }}
                        </p>
                    </div>
                    <div class="rounded-3xl bg-[#fff7f3] p-4">
                        <p class="text-sm text-slate-500">Subtes</p>
                        <p class="mt-2 text-xl font-semibold text-slate-900">
                            {{ overview.content.subtests }}
                        </p>
                    </div>
                    <div class="rounded-3xl bg-[#fff7f3] p-4">
                        <p class="text-sm text-slate-500">Modul</p>
                        <p class="mt-2 text-xl font-semibold text-slate-900">
                            {{ overview.content.modules }}
                        </p>
                    </div>
                    <div class="rounded-3xl bg-[#fff7f3] p-4">
                        <p class="text-sm text-slate-500">Soal</p>
                        <p class="mt-2 text-xl font-semibold text-slate-900">
                            {{ overview.content.questions }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <Card class="rounded-[1.75rem] border-[#e7ddd5]">
                <CardHeader>
                    <CardTitle>Phase 2 checklist</CardTitle>
                    <CardDescription>
                        Langkah implementasi berikutnya untuk membuka
                        operasional konten.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-3 text-sm text-slate-600">
                    <div
                        class="rounded-3xl border border-[#f1ddd3] bg-[#fff9f5] p-4"
                    >
                        Resource controller admin untuk kategori, subtes, modul,
                        dan soal.
                    </div>
                    <div
                        class="rounded-3xl border border-[#f1ddd3] bg-[#fff9f5] p-4"
                    >
                        Form request dan policy granular per resource.
                    </div>
                    <div
                        class="rounded-3xl border border-[#f1ddd3] bg-[#fff9f5] p-4"
                    >
                        Status draft, review, publish, dan filter pencarian.
                    </div>
                </CardContent>
            </Card>
        </section>
    </div>
</template>

