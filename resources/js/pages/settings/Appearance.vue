<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Anzeigeeinstellungen',
        href: '/settings/appearance',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Anzeigeeinstellungen" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Anzeigeeinstellungen" description="Aktualisieren Sie die Anzeigeeinstellungen Ihres Kontos" />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
