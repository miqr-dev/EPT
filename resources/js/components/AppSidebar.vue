<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Settings, Users } from 'lucide-vue-next'; // Added Settings
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Teilnehmer',
        href: '/participants',
        icon: Users,
    },
    {
        title: 'BRT-A',
        href: '/brt-a', // The new route for the Tests page
        icon: LayoutGrid, // Placeholder icon, can be changed later
    },
    {
        title: 'BRT-B',
        href: '/brt-b', // The new route for the Tests page
        icon: LayoutGrid, // Placeholder icon, can be changed later
    },
    {
        title: 'MRT-A',
        href: '/mrt-a', // The new route for the Tests page
        icon: LayoutGrid, // Placeholder icon, can be changed later
    },
    {
        title: 'MRT-B',
        href: '/mrt-b', // The new route for the Tests page
        icon: LayoutGrid, // Placeholder icon, can be changed later
    },
    {
        title: 'FPI-R',
        href: '/fpi-r', // The new route for the Tests page
        icon: LayoutGrid, // Placeholder icon, can be changed later
    },
    {
        title: 'LMT',
        href: '/lmt', // The new route for the Tests page
        icon: LayoutGrid, // Placeholder icon, can be changed later
    },
    {
        title: 'LPS-A',
        href: '/lps-a',
        icon: LayoutGrid,
    },
    {
        title: 'LPS-B',
        href: '/lps-b',
        icon: LayoutGrid,
    },
    {
        title: 'BIT-2',
        href: '/bit-2', // The new route for the Tests page
        icon: LayoutGrid, // Placeholder icon, can be changed later
    },
    {
        title: 'BT',
        href: '/bt',
        icon: LayoutGrid,
    },
    {
        title: 'AVEM',
        href: '/avem', // The new route for the Tests page
        icon: LayoutGrid, // Placeholder icon, can be changed later
    },
    {
        title: '628',
        href: '/konzentrationstest',
        icon: LayoutGrid,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Einstellungen',
        href: '/einstellungen', // New route
        icon: Settings, // Using the Settings icon
    },
    {
        title: 'GitHub-Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Dokumentation',
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
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
