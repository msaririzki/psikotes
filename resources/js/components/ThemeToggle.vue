<script setup lang="ts">
import { computed } from 'vue';
import { Check, Laptop, Moon, Sun } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useAppearance } from '@/composables/useAppearance';
import type { Appearance } from '@/types';

withDefaults(
    defineProps<{
        compact?: boolean;
        align?: 'start' | 'end';
    }>(),
    {
        compact: false,
        align: 'end',
    },
);

const { appearance, resolvedAppearance, updateAppearance } = useAppearance();

const options: Array<{
    value: Appearance;
    label: string;
    description: string;
    icon: typeof Sun;
}> = [
    {
        value: 'light',
        label: 'Terang',
        description: 'Pakai tema terang.',
        icon: Sun,
    },
    {
        value: 'dark',
        label: 'Gelap',
        description: 'Pakai tema gelap.',
        icon: Moon,
    },
    {
        value: 'system',
        label: 'Sistem',
        description: 'Ikuti tema perangkat.',
        icon: Laptop,
    },
];

const activeOption = computed(() => {
    if (appearance.value === 'system') {
        return resolvedAppearance.value === 'dark' ? options[2] : options[2];
    }

    return (
        options.find((option) => option.value === appearance.value) ??
        options[0]
    );
});

const triggerIcon = computed(() =>
    resolvedAppearance.value === 'dark' ? Moon : Sun,
);
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger :as-child="true">
            <Button
                variant="outline"
                :size="compact ? 'icon' : 'sm'"
                class="rounded-full border-border/70 bg-background/85 text-foreground shadow-sm backdrop-blur supports-[backdrop-filter]:bg-background/70"
            >
                <component :is="triggerIcon" class="size-4" />
                <span v-if="!compact" class="ml-2">
                    {{ activeOption.label }}
                </span>
                <span class="sr-only">Ubah tampilan</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent
            :align="align"
            class="w-56 rounded-2xl border-border/70 bg-popover/95 p-2 backdrop-blur supports-[backdrop-filter]:bg-popover/90"
        >
            <DropdownMenuLabel
                class="px-2 py-1.5 text-xs tracking-[0.18em] text-muted-foreground uppercase"
            >
                Tampilan
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuRadioGroup
                :model-value="appearance"
                @update:model-value="updateAppearance($event as Appearance)"
            >
                <DropdownMenuRadioItem
                    v-for="option in options"
                    :key="option.value"
                    :value="option.value"
                    class="rounded-xl px-3 py-2.5"
                >
                    <template #indicator-icon>
                        <Check class="size-3.5" />
                    </template>
                    <component
                        :is="option.icon"
                        class="size-4 text-muted-foreground"
                    />
                    <div class="flex flex-col gap-0.5">
                        <span class="font-medium text-foreground">
                            {{ option.label }}
                        </span>
                        <span class="text-xs text-muted-foreground">
                            {{ option.description }}
                        </span>
                    </div>
                </DropdownMenuRadioItem>
            </DropdownMenuRadioGroup>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
