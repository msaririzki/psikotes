<script setup lang="ts">
defineProps<{
    eyebrow: string;
    title: string;
    description: string;
}>();
</script>

<template>
    <section
        class="overflow-hidden rounded-[2rem] border border-border/60 bg-[radial-gradient(circle_at_top_right,_rgba(176,34,34,0.14),_transparent_28%),linear-gradient(135deg,_#fff8f1_0%,_#fff2ec_45%,_#ffffff_100%)] p-6 shadow-sm dark:bg-[radial-gradient(circle_at_top_right,_rgba(176,34,34,0.18),_transparent_28%),linear-gradient(135deg,_#1f1111_0%,_#140d0d_45%,_#09090b_100%)]"
    >
        <div class="max-w-3xl space-y-3">
            <div
                class="inline-flex items-center rounded-full bg-[#7f1d1d] px-4 py-1.5 text-xs font-semibold tracking-[0.18em] text-white uppercase"
            >
                {{ eyebrow }}
            </div>
            <div>
                <h1
                    class="font-display text-3xl font-bold tracking-tight text-foreground"
                >
                    {{ title }}
                </h1>
                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                    {{ description }}
                </p>
            </div>
        </div>
        <div class="mt-5">
            <slot />
        </div>
    </section>
</template>
