<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-10 items-center justify-center rounded-xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 shadow-sm dark:border-slate-700 dark:from-slate-800 dark:to-slate-900"
    >
        <AppLogoIcon class="size-6 drop-shadow-sm" />
    </div>
    <div class="ml-3 grid flex-1 text-left">
        <span class="truncate font-display text-base font-bold tracking-tight text-slate-900 dark:text-white">
            Prikotes
        </span>
        <span class="truncate text-[0.65rem] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Psikotes Polri
        </span>
    </div>
</template>
