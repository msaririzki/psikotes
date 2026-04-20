<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-9 items-center justify-center rounded-2xl bg-sidebar-primary text-sidebar-primary-foreground shadow-sm"
    >
        <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
    </div>
    <div class="ml-2 grid flex-1 text-left text-sm">
        <span class="truncate font-display text-sm font-bold tracking-tight">
            Prikotes
        </span>
        <span class="truncate text-xs text-muted-foreground">
            Simulasi Psikotes Polri
        </span>
    </div>
</template>
