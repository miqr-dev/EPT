import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
    items?: NavItem[];
    badges?: NavItemBadge[];
}

export interface NavItemBadge {
    title: string;
    count: number;
    class?: string;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    collaborationNotifications: {
        news: number;
        suggestions: number;
        total: number;
    };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    firstname?: string | null;
    username?: string | null;
    email: string;
    role: 'participant' | 'teacher' | 'admin';
    city_id?: number | null;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
