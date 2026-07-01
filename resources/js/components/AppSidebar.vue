<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, BookOpenCheck, ClipboardList, LayoutGrid, MessageSquareText, TabletSmartphone, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const collaborationNotifications = computed(() => page.props.collaborationNotifications);
const collaborationBadges = computed(() =>
    [
        {
            title: 'Neue Vorschläge',
            count: collaborationNotifications.value?.suggestions ?? 0,
            class: 'bg-sky-50 text-sky-700 ring-1 ring-sky-200 dark:bg-sky-950/70 dark:text-sky-200 dark:ring-sky-800/70',
        },
        {
            title: 'Neuigkeiten & Updates',
            count: collaborationNotifications.value?.news ?? 0,
            class: 'bg-[#661421] text-white',
        },
    ].filter((badge) => badge.count > 0),
);

const testNavItems: NavItem[] = [
    {
        title: 'BRT-A',
        href: '/brt-a',
    },
    {
        title: 'BRT-B',
        href: '/brt-b',
    },
    {
        title: 'MRT-A',
        href: '/mrt-a',
    },
    {
        title: 'MRT-B',
        href: '/mrt-b',
    },
    {
        title: 'FPI-R',
        href: '/fpi-r',
    },
    {
        title: 'LMT',
        href: '/lmt',
    },
    {
        title: 'LPS-B',
        href: '/lps-b',
    },
    {
        title: 'BIT-2',
        href: '/bit-2',
    },
    {
        title: 'BT',
        href: '/bt',
    },
    {
        title: 'AVEM',
        href: '/avem',
    },
    {
        title: '628',
        href: '/konzentrationstest',
    },
];

const instructionNavItems: NavItem[] = [
    {
        title: 'Allgemeine Informationen',
        href: '/anleitungen/allgemein',
    },
    {
        title: 'BRT',
        href: '/anleitungen/brt',
    },
    {
        title: 'MRT',
        href: '/anleitungen/mrt',
    },
    {
        title: 'FPI-R',
        href: '/anleitungen/fpi-r',
    },
    {
        title: 'LMT',
        href: '/anleitungen/lmt',
    },
    {
        title: 'LPS',
        href: '/anleitungen/lps',
    },
    {
        title: 'BIT-2',
        href: '/anleitungen/bit-2',
    },
    {
        title: 'BT',
        href: '/anleitungen/bt',
    },
    {
        title: 'AVEM',
        href: '/anleitungen/avem',
    },
    {
        title: '628',
        href: '/anleitungen/628',
    },
];

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: TabletSmartphone,
        },
        {
            title: 'Prüfungsergebnisse',
            href: '/participants',
            icon: BookOpenCheck,
        },
        {
            title: 'Benutzer importieren',
            href: '/participants/import',
            icon: Users,
        },
        {
            title: 'Kollaboration',
            href: '/kollaboration',
            icon: MessageSquareText,
            badges: collaborationBadges.value,
        },
        {
            title: 'Tests',
            href: '/tests',
            icon: LayoutGrid,
            items: testNavItems,
        },
        {
            title: 'Anleitungen',
            href: '/anleitungen/allgemein',
            icon: BookOpen,
            items: instructionNavItems,
        },
    ];

    if (page.props.auth.user?.role === 'admin') {
        items.splice(1, 0, {
            title: 'Standort-Prüfungen',
            href: '/admin/pruefungen',
            icon: ClipboardList,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [];
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
