<script setup lang="ts">
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuBadge,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();

const isActive = (item: NavItem): boolean => {
    return item.href === page.url || item.items?.some(isActive) === true;
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Plattform</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <Collapsible v-if="item.items?.length" as-child :default-open="isActive(item)" class="group/collapsible">
                    <SidebarMenuItem>
                        <CollapsibleTrigger as-child>
                            <SidebarMenuButton :is-active="isActive(item)" :tooltip="item.title" :class="item.badges?.length ? 'pr-16' : undefined">
                                <component :is="item.icon" />
                                <span>{{ item.title }}</span>
                                <ChevronRight class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>
                        <SidebarMenuBadge v-if="item.badges?.length" class="right-2 gap-1 bg-transparent px-0">
                            <span
                                v-for="badge in item.badges"
                                :key="badge.title"
                                :class="['flex h-5 min-w-5 items-center justify-center rounded-md px-1 text-xs font-semibold tabular-nums', badge.class]"
                            >
                                {{ badge.count }}
                            </span>
                        </SidebarMenuBadge>
                        <CollapsibleContent>
                            <SidebarMenuSub>
                                <SidebarMenuSubItem v-for="subItem in item.items" :key="subItem.title">
                                    <SidebarMenuSubButton as-child :is-active="isActive(subItem)">
                                        <Link :href="subItem.href">
                                            <span>{{ subItem.title }}</span>
                                        </Link>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            </SidebarMenuSub>
                        </CollapsibleContent>
                    </SidebarMenuItem>
                </Collapsible>

                <SidebarMenuItem v-else>
                    <SidebarMenuButton as-child :is-active="isActive(item)" :tooltip="item.title" :class="item.badges?.length ? 'pr-16' : undefined">
                        <Link :href="item.href">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                    <SidebarMenuBadge v-if="item.badges?.length" class="right-2 gap-1 bg-transparent px-0">
                        <span
                            v-for="badge in item.badges"
                            :key="badge.title"
                            :class="['flex h-5 min-w-5 items-center justify-center rounded-md px-1 text-xs font-semibold tabular-nums', badge.class]"
                        >
                            {{ badge.count }}
                        </span>
                    </SidebarMenuBadge>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
