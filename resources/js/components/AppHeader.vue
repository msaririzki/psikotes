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
    Menu,
    Rocket,
    ScrollText,
    ShieldCheck,
    SquareLibrary,
    TrendingUp,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { getInitials } from '@/composables/useInitials';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, NavItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);
const { isCurrentUrl, whenCurrentUrl } = useCurrentUrl();

const activeItemStyles =
    'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100';

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dasbor',
            href: dashboard(),
            icon: LayoutGrid,
        },
        ...(auth.value.user.onboarding_completed
            ? []
            : [{
                title: 'Orientasi',
                href: '/onboarding',
                icon: Rocket,
            }]),
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

    if (auth.value.can.accessAdminArea) {
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
    <div>
        <div class="border-b border-sidebar-border/80">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                <!-- Mobile Menu -->
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="mr-2 h-9 w-9"
                            >
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[300px] p-6">
                            <SheetTitle class="sr-only"
                                >Menu navigasi</SheetTitle
                            >
                            <SheetHeader class="flex justify-start text-left">
                                <AppLogoIcon
                                    class="size-6 fill-current text-black dark:text-white"
                                />
                            </SheetHeader>
                            <div
                                class="flex h-full flex-1 flex-col justify-between space-y-4 py-6"
                            >
                                <nav class="-mx-3 space-y-1">
                                    <Link
                                        v-for="item in mainNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                        :class="
                                            whenCurrentUrl(
                                                item.href,
                                                activeItemStyles,
                                            )
                                        "
                                    >
                                        <component
                                            v-if="item.icon"
                                            :is="item.icon"
                                            class="h-5 w-5"
                                        />
                                        {{ item.title }}
                                    </Link>
                                </nav>
                                <div
                                    class="rounded-3xl bg-muted/60 p-4 text-sm"
                                >
                                    <p class="font-semibold">Siap uji lokal</p>
                                    <p class="mt-1 text-muted-foreground">
                                        Demo data, onboarding, dan flow utama
                                        sudah disiapkan untuk uji lokal
                                        end-to-end.
                                    </p>
                                </div>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <Link :href="dashboard()" class="flex items-center gap-x-2">
                    <AppLogo />
                </Link>

                <!-- Desktop Menu -->
                <div class="hidden h-full lg:flex lg:flex-1">
                    <NavigationMenu class="ml-10 flex h-full items-stretch">
                        <NavigationMenuList
                            class="flex h-full items-stretch space-x-2"
                        >
                            <NavigationMenuItem
                                v-for="(item, index) in mainNavItems"
                                :key="index"
                                class="relative flex h-full items-center"
                            >
                                <Link
                                    :class="[
                                        navigationMenuTriggerStyle(),
                                        whenCurrentUrl(
                                            item.href,
                                            activeItemStyles,
                                        ),
                                        'h-9 cursor-pointer px-3',
                                    ]"
                                    :href="item.href"
                                >
                                    <component
                                        v-if="item.icon"
                                        :is="item.icon"
                                        class="mr-2 h-4 w-4"
                                    />
                                    {{ item.title }}
                                </Link>
                                <div
                                    v-if="isCurrentUrl(item.href)"
                                    class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-black dark:bg-white"
                                ></div>
                            </NavigationMenuItem>
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>

                <div class="ml-auto flex items-center space-x-2">
                    <TooltipProvider :delay-duration="0">
                        <Tooltip>
                            <TooltipTrigger :as-child="true">
                                <div
                                    class="hidden rounded-full border border-border/70 bg-card px-4 py-2 text-sm font-medium text-muted-foreground xl:block"
                                >
                                    {{
                                        auth.user.role === 'user'
                                            ? 'Peserta'
                                            : 'Area Admin'
                                    }}
                                </div>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Akses aktif: {{ auth.user.role }}</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>

                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                            >
                                <Avatar
                                    class="size-8 overflow-hidden rounded-full"
                                >
                                    <AvatarImage
                                        v-if="auth.user.avatar"
                                        :src="auth.user.avatar"
                                        :alt="auth.user.name"
                                    />
                                    <AvatarFallback
                                        class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ getInitials(auth.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>

        <div
            v-if="props.breadcrumbs.length > 1"
            class="flex w-full border-b border-sidebar-border/70"
        >
            <div
                class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl"
            >
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>
    </div>
</template>

