<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Settings } from 'lucide-vue-next'; // Added Settings
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutGrid,
  },
  {
    title: 'BRT',
    href: '/brt', // The new route for the Tests page
    icon: LayoutGrid, // Placeholder icon, can be changed later
  },
  {
    title: 'MRT',
    href: '/mrt', // The new route for the Tests page
    icon: LayoutGrid, // Placeholder icon, can be changed later
  },
  {
    title: 'FPI',
    href: '/fpi', // The new route for the Tests page
    icon: LayoutGrid, // Placeholder icon, can be changed later
  },
  {
    title: 'LMT',
    href: '/lmt', // The new route for the Tests page
    icon: LayoutGrid, // Placeholder icon, can be changed later
  },
  {
    title: 'LMT2',
    href: '/lmt2', // The new route for the Tests page
    icon: LayoutGrid, // Placeholder icon, can be changed later
  },
];

const footerNavItems: NavItem[] = [
  {
    title: 'Einstellungen',
    href: '/einstellungen', // New route
    icon: Settings, // Using the Settings icon
  },
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
      <NavMain :items="mainNavItems" />
    </SidebarContent>

    <SidebarFooter>
      <NavFooter :items="footerNavItems" />
      <NavUser />
    </SidebarFooter>
  </Sidebar>
  <slot />
</template>
