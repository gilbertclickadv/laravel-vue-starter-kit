<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavMenu from '@/components/NavMenu.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarGroup, SidebarGroupLabel } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { 
    BookOpen, 
    Folder, 
    LayoutGrid,
    Users,
    Package,
    ShoppingCart,
    Star,
    Percent,
    FolderTree,
    Settings
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const dashboardItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
];

const coreItems: NavItem[] = [
    {
        title: 'Vendors',
        href: '/vendors',
        icon: Users,
    },
    {
        title: 'Categories',
        href: '/categories',
        icon: FolderTree,
    },
    {
        title: 'Attributes',
        href: '/attributes',
        icon: Settings,
    },
    {
        title: 'Products',
        href: '/products',
        icon: Package,
    },
    {
        title: 'Orders',
        href: '/orders',
        icon: ShoppingCart,
    },
    {
        title: 'Reviews',
        href: '/reviews',
        icon: Star,
    },
    {
        title: 'Promotions',
        href: '/promotions',
        icon: Percent,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="dashboardItems" />
            
            <SidebarGroup class="px-2 py-0">
                <SidebarGroupLabel>Core Management</SidebarGroupLabel>
                <NavMenu :items="coreItems" />
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
