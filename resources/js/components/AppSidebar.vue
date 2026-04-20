<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpenText,
    BookCheck,
    BrainCircuit,
    Clock3,
    Compass,
    FolderKanban,
    LayoutGrid,
    ListTree,
    Rocket,
    ScrollText,
    ShieldCheck,
    SquareLibrary,
    TrendingUp,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const page = usePage();

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dasbor',
            href: dashboard(),
            icon: LayoutGrid,
        },
        ...(! page.props.auth.user.onboarding_completed
            ? [{
                title: 'Orientasi',
                href: '/onboarding',
                icon: Rocket,
            }]
            : []),
        {
            title: 'Belajar',
            href: '/learn',
            icon: BrainCircuit,
        },
        {
            title: 'Latihan',
            href: '/practice',
            icon: BookCheck,
        },
        {
            title: 'Simulasi',
            href: '/simulations',
            icon: Clock3,
        },
        {
            title: 'Riwayat',
            href: '/history',
            icon: ScrollText,
        },
        {
            title: 'Progres',
            href: '/progress',
            icon: TrendingUp,
        },
        {
            title: 'Rencana Belajar',
            href: '/study-plan',
            icon: Compass,
        },
    ];

    if (page.props.auth.can.accessAdminArea) {
        items.push({
            title: 'Admin',
            href: '/admin',
            icon: ShieldCheck,
        });
        items.push({
            title: 'Kategori',
            href: '/admin/categories',
            icon: FolderKanban,
        });
        items.push({
            title: 'Subtes',
            href: '/admin/subtests',
            icon: ListTree,
        });
        items.push({
            title: 'Modul',
            href: '/admin/learning-modules',
            icon: BookOpenText,
        });
        items.push({
            title: 'Soal',
            href: '/admin/questions',
            icon: SquareLibrary,
        });
        items.push({
            title: 'Paket Simulasi',
            href: '/admin/simulation-packages',
            icon: Clock3,
        });
    }

    return items;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

