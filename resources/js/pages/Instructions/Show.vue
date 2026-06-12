<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { testInstructions } from '@/data/testInstructions';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, CheckCircle2, ClipboardCheck, Clock3, FileText, Info, ListChecks, Monitor, PlayCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    test: string;
}>();

const instruction = computed(() => testInstructions[props.test]);
const isGeneral = computed(() => props.test === 'allgemein');

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Anleitungen',
        href: '/anleitungen/allgemein',
    },
    {
        title: instruction.value.name,
        href: `/anleitungen/${props.test}`,
    },
]);

const testLinks: Record<string, Array<{ title: string; href: string }>> = {
    brt: [
        { title: 'BRT-A öffnen', href: '/brt-a' },
        { title: 'BRT-B öffnen', href: '/brt-b' },
    ],
    mrt: [
        { title: 'MRT-A öffnen', href: '/mrt-a' },
        { title: 'MRT-B öffnen', href: '/mrt-b' },
    ],
    'fpi-r': [{ title: 'FPI-R öffnen', href: '/fpi-r' }],
    lmt: [{ title: 'LMT öffnen', href: '/lmt' }],
    lps: [{ title: 'LPS-B öffnen', href: '/lps-b' }],
    'bit-2': [{ title: 'BIT-2 öffnen', href: '/bit-2' }],
    bt: [{ title: 'BT öffnen', href: '/bt' }],
    avem: [{ title: 'AVEM öffnen', href: '/avem' }],
    '628': [{ title: '628 öffnen', href: '/konzentrationstest' }],
};

const phaseCards = computed(() =>
    [
        {
            title: 'Vor dem Test',
            icon: PlayCircle,
            items: instruction.value.beforeTest,
        },
        {
            title: 'Während des Tests',
            icon: ListChecks,
            items: instruction.value.duringTest,
        },
        {
            title: 'Nach dem Test',
            icon: ClipboardCheck,
            items: instruction.value.afterTest,
        },
    ].filter((phase) => phase.items.length > 0),
);

const guideIcons = [PlayCircle, FileText, ListChecks, ClipboardCheck, Info];
const guideAnchors = ['pruefungsvorbereitung', 'vertrag-anzeigen', 'waehrend-der-pruefung', 'pruefungsergebnisse', 'kollaboration'];
const guideStyles = [
    {
        number: 'bg-blue-700 text-white',
        icon: 'text-blue-700 dark:text-blue-300',
        header: 'border-blue-100 bg-blue-50/70 dark:border-blue-900/60 dark:bg-blue-950/25',
    },
    {
        number: 'bg-cyan-700 text-white',
        icon: 'text-cyan-700 dark:text-cyan-300',
        header: 'border-cyan-100 bg-cyan-50/70 dark:border-cyan-900/60 dark:bg-cyan-950/25',
    },
    {
        number: 'bg-indigo-700 text-white',
        icon: 'text-indigo-700 dark:text-indigo-300',
        header: 'border-indigo-100 bg-indigo-50/70 dark:border-indigo-900/60 dark:bg-indigo-950/25',
    },
    {
        number: 'bg-emerald-700 text-white',
        icon: 'text-emerald-700 dark:text-emerald-300',
        header: 'border-emerald-100 bg-emerald-50/70 dark:border-emerald-900/60 dark:bg-emerald-950/25',
    },
    {
        number: 'bg-slate-700 text-white',
        icon: 'text-slate-700 dark:text-slate-300',
        header: 'border-slate-200 bg-slate-100/80 dark:border-slate-700 dark:bg-slate-800/60',
    },
];

const featureStyles = [
    'border-blue-100 bg-blue-50 text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300',
    'border-cyan-100 bg-cyan-50 text-cyan-700 dark:border-cyan-900/60 dark:bg-cyan-950/40 dark:text-cyan-300',
    'border-indigo-100 bg-indigo-50 text-indigo-700 dark:border-indigo-900/60 dark:bg-indigo-950/40 dark:text-indigo-300',
    'border-emerald-100 bg-emerald-50 text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300',
];

const jumpToSection = (anchor: string) => {
    document.getElementById(anchor)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};
</script>

<template>
    <Head :title="isGeneral ? instruction.name : `Anleitung ${instruction.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <main class="flex flex-1 flex-col gap-6 bg-slate-50/40 p-4 md:p-6 dark:bg-slate-950/20">
            <section
                class="overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-white via-slate-50/70 to-blue-50/80 shadow-sm dark:border-slate-700 dark:from-slate-900 dark:via-slate-900 dark:to-blue-950/35"
            >
                <div
                    class="grid gap-8 p-6 lg:p-8"
                    :class="
                        isGeneral
                            ? 'lg:grid-cols-[minmax(0,1fr)_minmax(260px,360px)] lg:items-start'
                            : 'lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end'
                    "
                >
                    <div class="space-y-4">
                        <Badge
                            variant="outline"
                            class="w-fit border-blue-200 bg-blue-50 px-3 py-1 text-blue-800 dark:border-blue-800 dark:bg-blue-950/50 dark:text-blue-200"
                        >
                            {{ isGeneral ? 'Leitfaden' : 'Anleitung für Lehrkräfte' }}
                        </Badge>
                        <div>
                            <h1 class="text-3xl font-bold tracking-tight text-slate-950 dark:text-white">{{ instruction.name }}</h1>
                            <p class="mt-1 text-lg font-medium text-slate-600 dark:text-slate-300">{{ instruction.fullName }}</p>
                        </div>
                        <p class="max-w-3xl leading-7 text-slate-600 dark:text-slate-300">{{ instruction.summary }}</p>
                        <div v-if="instruction.variants.length" class="flex flex-wrap gap-2">
                            <Badge
                                v-for="variant in instruction.variants"
                                :key="variant"
                                variant="outline"
                                class="border-slate-300 bg-white/80 text-slate-700 dark:border-slate-600 dark:bg-slate-900/70 dark:text-slate-200"
                            >
                                {{ variant }}
                            </Badge>
                        </div>
                    </div>

                    <nav
                        v-if="isGeneral && instruction.guideSections?.length"
                        aria-label="Seitennavigation"
                        class="rounded-xl border border-slate-200 bg-white/85 p-4 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/80"
                    >
                        <p class="mb-3 text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase dark:text-slate-400">Auf dieser Seite</p>
                        <div class="space-y-1">
                            <a
                                v-for="(section, sectionIndex) in instruction.guideSections"
                                :key="section.title"
                                :href="`#${guideAnchors[sectionIndex]}`"
                                class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-blue-50 hover:text-blue-800 dark:text-slate-200 dark:hover:bg-blue-950/50 dark:hover:text-blue-200"
                                @click.prevent="jumpToSection(guideAnchors[sectionIndex])"
                            >
                                <span
                                    class="flex size-6 shrink-0 items-center justify-center rounded-md bg-slate-100 text-xs font-bold text-slate-600 transition group-hover:bg-blue-100 group-hover:text-blue-700 dark:bg-slate-800 dark:text-slate-300 dark:group-hover:bg-blue-900/60 dark:group-hover:text-blue-200"
                                >
                                    {{ sectionIndex + 1 }}
                                </span>
                                <span class="flex-1">{{ section.title }}</span>
                                <ArrowRight class="size-4 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-blue-600" />
                            </a>
                        </div>
                    </nav>

                    <div v-else-if="testLinks[test]?.length" class="flex flex-wrap gap-2 lg:justify-end">
                        <Button
                            v-for="testLink in testLinks[test]"
                            :key="testLink.href"
                            as-child
                            class="bg-slate-900 text-white shadow-sm hover:bg-blue-800 dark:bg-blue-700 dark:hover:bg-blue-600"
                        >
                            <Link :href="testLink.href">
                                {{ testLink.title }}
                                <ArrowRight />
                            </Link>
                        </Button>
                    </div>
                </div>
            </section>

            <section v-if="instruction.features.length" class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <Card
                    v-for="(feature, index) in instruction.features"
                    :key="feature.label"
                    class="border-slate-200 shadow-sm transition-shadow hover:shadow-md dark:border-slate-700"
                >
                    <CardContent class="flex items-start gap-4">
                        <div :class="['rounded-lg border p-3', featureStyles[index] ?? featureStyles[0]]">
                            <Info v-if="index === 0" class="size-5" />
                            <Monitor v-else-if="index === 1" class="size-5" />
                            <Clock3 v-else-if="feature.label === 'Zeitlimit'" class="size-5" />
                            <FileText v-else class="size-5" />
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ feature.label }}</p>
                            <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ feature.value }}</p>
                        </div>
                    </CardContent>
                </Card>
            </section>

            <section v-if="phaseCards.length" class="grid gap-6" :class="phaseCards.length === 3 ? 'xl:grid-cols-3' : 'xl:grid-cols-2'">
                <Card v-for="phase in phaseCards" :key="phase.title" class="overflow-hidden border-slate-200 shadow-sm dark:border-slate-700">
                    <CardHeader class="border-b border-blue-100 bg-blue-50/70 dark:border-blue-900/50 dark:bg-blue-950/25">
                        <CardTitle class="flex items-center gap-2 text-slate-900 dark:text-slate-100">
                            <component :is="phase.icon" class="size-5 text-blue-700 dark:text-blue-300" />
                            {{ phase.title }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ul class="space-y-3">
                            <li v-for="item in phase.items" :key="item" class="flex gap-3 leading-6">
                                <CheckCircle2 class="mt-1 size-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                                <span>{{ item }}</span>
                            </li>
                        </ul>
                    </CardContent>
                </Card>
            </section>

            <section v-if="instruction.guideSections?.length" class="space-y-6">
                <Card
                    v-for="(section, sectionIndex) in instruction.guideSections"
                    :id="guideAnchors[sectionIndex]"
                    :key="section.title"
                    class="scroll-mt-6 overflow-hidden border-slate-200 shadow-sm dark:border-slate-700"
                >
                    <CardHeader :class="['border-b', guideStyles[sectionIndex].header]">
                        <div class="flex items-start gap-4">
                            <div
                                :class="[
                                    'flex size-11 shrink-0 items-center justify-center rounded-xl font-bold shadow-sm',
                                    guideStyles[sectionIndex].number,
                                ]"
                            >
                                {{ sectionIndex + 1 }}
                            </div>
                            <div class="space-y-1">
                                <CardTitle class="flex items-center gap-2 text-xl text-slate-950 dark:text-white">
                                    <component :is="guideIcons[sectionIndex]" :class="['size-5', guideStyles[sectionIndex].icon]" />
                                    {{ section.title }}
                                </CardTitle>
                                <p v-if="section.description" class="leading-6 text-slate-600 dark:text-slate-300">{{ section.description }}</p>
                            </div>
                        </div>
                    </CardHeader>

                    <CardContent v-if="section.blocks.length" class="grid gap-5 lg:grid-cols-2">
                        <div
                            v-for="block in section.blocks"
                            :key="block.title ?? block.description"
                            class="space-y-4 rounded-xl border border-slate-200 bg-white p-5 shadow-xs dark:border-slate-700 dark:bg-slate-900/70"
                        >
                            <div v-if="block.title || block.description">
                                <h3 v-if="block.title" class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ block.title }}</h3>
                                <p v-if="block.description" class="mt-1 leading-6 text-slate-600 dark:text-slate-300">{{ block.description }}</p>
                            </div>

                            <ol v-if="block.steps?.length" class="space-y-3">
                                <li v-for="(step, stepIndex) in block.steps" :key="step" class="flex gap-3 leading-6">
                                    <span
                                        class="flex size-6 shrink-0 items-center justify-center rounded-md bg-blue-100 text-xs font-bold text-blue-800 dark:bg-blue-900/50 dark:text-blue-200"
                                    >
                                        {{ stepIndex + 1 }}
                                    </span>
                                    <span>{{ step }}</span>
                                </li>
                            </ol>

                            <ul v-if="block.items?.length" class="space-y-3">
                                <li v-for="item in block.items" :key="`${item.label}-${item.text}`" class="flex gap-3 leading-6">
                                    <CheckCircle2 class="mt-1 size-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                                    <span>
                                        <strong v-if="item.label">{{ item.label }}:</strong>
                                        {{ item.text }}
                                    </span>
                                </li>
                            </ul>

                            <div
                                v-if="block.note"
                                class="rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm leading-6 text-blue-900 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-100"
                            >
                                {{ block.note }}
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </section>

            <section v-if="instruction.sections?.length" class="space-y-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Hinweise zu den einzelnen Aufgaben</h2>
                    <p class="mt-1 text-muted-foreground">Bedienung und Auswertung im BT</p>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <Card
                        v-for="section in instruction.sections"
                        :key="section.title"
                        class="overflow-hidden border-slate-200 shadow-sm dark:border-slate-700"
                    >
                        <CardHeader class="border-b border-blue-100 bg-blue-50/70 dark:border-blue-900/50 dark:bg-blue-950/25">
                            <CardTitle class="text-lg text-slate-900 dark:text-slate-100">{{ section.title }}</CardTitle>
                            <p v-if="section.description" class="text-sm text-muted-foreground">{{ section.description }}</p>
                        </CardHeader>
                        <CardContent>
                            <ul class="space-y-3">
                                <li v-for="item in section.items" :key="item" class="flex gap-3 leading-6">
                                    <CheckCircle2 class="mt-1 size-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                                    <span>{{ item }}</span>
                                </li>
                            </ul>
                        </CardContent>
                    </Card>
                </div>
            </section>

            <Card v-if="instruction.resultImage" class="overflow-hidden border-slate-200 shadow-sm dark:border-slate-700">
                <CardHeader class="border-b border-blue-100 bg-blue-50/70 dark:border-blue-900/50 dark:bg-blue-950/25">
                    <CardTitle class="text-slate-900 dark:text-slate-100">Manuelle Ergebniseingabe</CardTitle>
                </CardHeader>
                <CardContent>
                    <figure class="space-y-3">
                        <div class="overflow-x-auto rounded-lg border bg-white p-2">
                            <img :src="instruction.resultImage.src" :alt="instruction.resultImage.alt" class="max-w-full min-w-[680px]" />
                        </div>
                        <figcaption class="text-sm text-muted-foreground">{{ instruction.resultImage.caption }}</figcaption>
                    </figure>
                </CardContent>
            </Card>
        </main>
    </AppLayout>
</template>
