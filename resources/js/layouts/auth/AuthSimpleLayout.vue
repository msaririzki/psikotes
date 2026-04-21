<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-slate-50 p-6 md:p-10 dark:bg-slate-950"
    >
        <!-- Background Decorative Orbs -->
        <div class="pointer-events-none absolute inset-0 z-0 flex items-center justify-center">
            <div
                class="absolute left-[-20%] top-[-10%] h-[500px] w-[500px] animate-pulse rounded-full bg-red-600/10 mix-blend-multiply blur-[120px] duration-10000 dark:bg-red-500/15 dark:mix-blend-lighten"
            ></div>
            <div
                class="absolute right-[-10%] top-[30%] h-[600px] w-[600px] rounded-full bg-blue-600/10 mix-blend-multiply blur-[120px] dark:bg-blue-500/15 dark:mix-blend-lighten"
            ></div>
        </div>

        <div class="relative z-10 w-full max-w-[26rem]">
            <Link
                :href="home()"
                class="mx-auto mb-8 flex flex-col items-center gap-3 transition-opacity hover:opacity-80"
            >
                <div
                    class="flex size-16 items-center justify-center rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 shadow-md dark:border-slate-700/50 dark:from-slate-800 dark:to-slate-900"
                >
                    <AppLogoIcon class="size-10 drop-shadow-sm" />
                </div>
            </Link>

            <div
                class="flex w-full flex-col gap-6 rounded-[2rem] border border-white/50 bg-white/70 p-8 shadow-2xl ring-1 ring-slate-900/5 backdrop-blur-2xl dark:border-slate-800/50 dark:bg-slate-900/60 sm:p-10"
            >
                <div class="mb-2 space-y-2 text-center">
                    <h1 class="font-display text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                        {{ title }}
                    </h1>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
