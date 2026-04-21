<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 100 100"
        :class="className"
        v-bind="$attrs"
    >
        <defs>
            <linearGradient id="logo-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#b91c1c" />
                <stop offset="100%" stop-color="#7f1d1d" />
            </linearGradient>
            <filter id="shadow" x="-50%" y="-50%" width="200%" height="200%">
                <feDropShadow dx="0" dy="4" stdDeviation="4" flood-opacity="0.3" />
            </filter>
        </defs>
        <!-- Shield Background -->
        <path fill="url(#logo-grad)" filter="url(#shadow)" d="M50 5 L90 20 L90 50 C90 75 50 95 50 95 C50 95 10 75 10 50 L10 20 Z" />
      
        <!-- Outer stroke for elegance -->
        <path fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" d="M50 14 L82 26 L82 48 C82 68 50 85 50 85 C50 85 18 68 18 48 L18 26 Z" opacity="0.4"/>

        <!-- Stylized P -->
        <path fill="#ffffff" d="M42 32 H56 C62 32 65 35 65 41 C65 47 62 50 56 50 H48 V65 H42 Z M48 38 V44 H54 C56 44 57 43 57 41 C57 39 56 38 54 38 Z" />
      
        <!-- Star representing Police/Excellence -->
        <polygon fill="#facc15" points="50,22 53,28 60,29 55,34 56,40 50,37 44,40 45,34 40,29 47,28"/>
    </svg>
</template>
