<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

defineProps<{
    items: NavItem[];
}>();

const { isCurrentOrParentUrl } = useCurrentUrl();
const { isMobile, setOpenMobile } = useSidebar();

const handleClick = () => {
    if (isMobile.value) {
        setOpenMobile(false);
    }
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title" class="mb-1">
                <SidebarMenuButton
                    as-child
                    :is-active="isCurrentOrParentUrl(item.href)"
                    :tooltip="item.title"
                    class="group relative overflow-hidden rounded-xl transition-all duration-300 hover:shadow-sm"
                    :class="{
                        'bg-indigo-50/80 dark:bg-indigo-500/15 border-l-[3px] border-indigo-600 dark:border-indigo-400': isCurrentOrParentUrl(item.href),
                        'hover:bg-slate-100/80 dark:hover:bg-white/5 border-l-[3px] border-transparent hover:border-slate-300 dark:hover:border-white/10': !isCurrentOrParentUrl(item.href)
                    }"
                    @click="handleClick"
                >
                    <Link :href="item.href" class="flex w-full items-center px-1">
                        <!-- Aksesoris Cahaya Latar Glow Jika Aktif -->
                        <span v-if="isCurrentOrParentUrl(item.href)" class="absolute inset-0 z-0 bg-gradient-to-r from-indigo-500/10 to-transparent blur-[8px]"></span>
                        
                        <div class="relative z-10 flex items-center gap-3 py-0.5">
                            <component 
                                :is="item.icon" 
                                class="transition-all duration-300 group-hover:scale-110"
                                :class="{ 
                                    'text-indigo-600 dark:text-indigo-400 drop-shadow-md': isCurrentOrParentUrl(item.href),
                                    'text-slate-500 dark:text-slate-400 group-hover:text-indigo-500 dark:group-hover:text-indigo-300': !isCurrentOrParentUrl(item.href)
                                }"
                            />
                            <span 
                                class="transition-all duration-300 group-hover:translate-x-1"
                                :class="{ 
                                    'font-bold text-indigo-900 dark:text-indigo-100': isCurrentOrParentUrl(item.href),
                                    'font-medium text-slate-700 dark:text-slate-300 group-hover:text-indigo-800 dark:group-hover:text-indigo-200': !isCurrentOrParentUrl(item.href)
                                }"
                            >
                                {{ item.title }}
                            </span>
                        </div>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
