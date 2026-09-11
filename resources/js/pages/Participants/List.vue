<script setup lang="ts">
import EntranceAnalysisModal from '@/components/EntranceAnalysisModal.vue';
import TestResultModal from '@/components/TestResultModal.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import { downloadPdfOrOpenPrint, openPrintPreview } from '@/lib/pdf-export';
import { Link, router } from '@inertiajs/vue3';
import { ChartNoAxesCombined, Eye, FileText, Loader2, Search, X } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

type ParticipantSuggestion = {
    id: number;
    name: string;
    firstname?: string | null;
    username?: string | null;
};

type Participant = ParticipantSuggestion & {
    latest_exam_created_at?: string | null;
    test_assignments: any[];
};

const props = defineProps<{
    participants: {
        data: Participant[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    selectedParticipants: Participant[];
    suggestions: ParticipantSuggestion[];
    filters: {
        search?: string | null;
        participant_id?: number | null;
    };
}>();

const isModalOpen = ref(false);
const selectedAssignment = ref(null);
const selectedParticipant = ref(null);
const desktopSearchQuery = ref(props.filters.search ?? '');
const tabletSearchQuery = ref(props.filters.search ?? '');
const selectedParticipantId = ref<number | null>(props.filters.participant_id ?? null);
const pdfParticipant = ref<any | null>(null);
const isGeneratingPdf = ref(false);
const pdfExportMode = ref<'results' | 'answers' | null>(null);
const isEntranceAnalysisOpen = ref(false);
const entranceAnalysisParticipant = ref<any | null>(null);
const entranceAnalysisOverrides = ref<Record<number, any>>({});
const isSearching = ref(false);
const hasModalHistoryEntry = ref(false);
let searchTimer: number | null = null;

const TEST_PDF_ORDER = [
    ['LPS-B'],
    ['BRT-A'],
    ['BRT-B'],
    ['MRT-A'],
    ['MRT-B'],
    ['BT'],
    ['FPI-R'],
    ['LMT'],
    ['BIT-2'],
    ['628', '628 TEST', 'KONZENTRATIONSTEST', '628 08.03'],
    ['AVEM'],
];

const dateFormatter = computed(
    () =>
        new Intl.DateTimeFormat('de-DE', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
        }),
);

const desktopParticipants = computed(() => props.participants.data ?? []);
const tabletSelectedParticipants = computed(() => props.selectedParticipants ?? []);
const participantSuggestions = computed(() => props.suggestions ?? []);
const hasSelectedParticipant = computed(() => selectedParticipantId.value !== null);
const canShowSuggestions = computed(() => !hasSelectedParticipant.value && tabletSearchQuery.value.trim().length >= 3);
const hasSyncedTabletSearch = computed(() => (props.filters.search ?? '').trim() === tabletSearchQuery.value.trim());
const showSuggestions = computed(() => canShowSuggestions.value && hasSyncedTabletSearch.value && participantSuggestions.value.length > 0);
const showNoSuggestions = computed(
    () => canShowSuggestions.value && hasSyncedTabletSearch.value && participantSuggestions.value.length === 0 && !isSearching.value,
);

function resetTestResultModal() {
    isModalOpen.value = false;
    selectedAssignment.value = null;
    selectedParticipant.value = null;
}

function resetEntranceAnalysisModal() {
    isEntranceAnalysisOpen.value = false;
    entranceAnalysisParticipant.value = null;
}

function pushModalHistoryEntry() {
    if (typeof window === 'undefined' || hasModalHistoryEntry.value) {
        return;
    }

    window.history.pushState(
        {
            ...(window.history.state ?? {}),
            participantResultsModalOpen: true,
        },
        '',
        window.location.href,
    );

    hasModalHistoryEntry.value = true;
}

function closeModalHistoryEntry() {
    if (typeof window === 'undefined' || !hasModalHistoryEntry.value) {
        return;
    }

    hasModalHistoryEntry.value = false;
    window.history.back();
}

function handleHistoryBack() {
    if (!isModalOpen.value && !isEntranceAnalysisOpen.value) {
        return;
    }

    hasModalHistoryEntry.value = false;
    resetTestResultModal();
    resetEntranceAnalysisModal();
}

onMounted(() => {
    window.addEventListener('popstate', handleHistoryBack);
});

onBeforeUnmount(() => {
    window.removeEventListener('popstate', handleHistoryBack);
});

watch(
    () => props.filters.search,
    (value) => {
        const nextSearch = value ?? '';

        if (nextSearch !== desktopSearchQuery.value) {
            desktopSearchQuery.value = nextSearch;
        }

        if (nextSearch !== tabletSearchQuery.value) {
            tabletSearchQuery.value = nextSearch;
        }
    },
);

watch(
    () => props.filters.participant_id,
    (value) => {
        selectedParticipantId.value = value ?? null;
    },
);

watch(
    () => [props.participants.data, props.selectedParticipants],
    () => {
        if (!entranceAnalysisParticipant.value) return;

        const updatedParticipant = findVisibleParticipant(entranceAnalysisParticipant.value.id);

        if (updatedParticipant) {
            entranceAnalysisParticipant.value = updatedParticipant;
        }
    },
);

function findVisibleParticipant(participantId: number) {
    const participant = [...desktopParticipants.value, ...tabletSelectedParticipants.value].find((item) => item.id === participantId);

    return participant ? participantWithEntranceAnalysisOverride(participant) : null;
}

function participantWithEntranceAnalysisOverride(participant: any) {
    const override = entranceAnalysisOverrides.value[participant.id];

    if (!override) return participant;

    return {
        ...participant,
        entrance_analysis: {
            ...(participant.entrance_analysis ?? {}),
            ...override,
        },
    };
}

function viewTestResult(assignment: any, participant: any) {
    pushModalHistoryEntry();
    selectedAssignment.value = assignment;
    selectedParticipant.value = participant;
    isModalOpen.value = true;
}

function closeModal() {
    resetTestResultModal();
    closeModalHistoryEntry();
}

function openEntranceAnalysis(participant: any) {
    entranceAnalysisParticipant.value = participantWithEntranceAnalysisOverride(participant);
    isEntranceAnalysisOpen.value = true;
}

function closeEntranceAnalysis() {
    resetEntranceAnalysisModal();
}

function handleEntranceAnalysisSaved(payload: { participantId: number; analysis: any }) {
    entranceAnalysisOverrides.value = {
        ...entranceAnalysisOverrides.value,
        [payload.participantId]: payload.analysis,
    };

    if (entranceAnalysisParticipant.value?.id === payload.participantId) {
        entranceAnalysisParticipant.value = participantWithEntranceAnalysisOverride({
            ...entranceAnalysisParticipant.value,
            entrance_analysis: payload.analysis,
        });
    }
}

function testDisplayName(assignment: any) {
    return assignment?.test?.name === 'Konzentrationstest' ? '628' : assignment?.test?.name;
}

function completedAssignments(participant: any) {
    return (participant?.test_assignments ?? []).filter((assignment: any) => (assignment?.results ?? []).length > 0);
}

function testOrderIndex(assignment: any) {
    const identifiers = [assignment?.test?.name, assignment?.test?.code].filter(Boolean).map((value) => String(value).trim().toUpperCase());

    return TEST_PDF_ORDER.findIndex((aliases) => aliases.some((alias) => identifiers.includes(alias)));
}

function orderedPdfAssignments(participant: any) {
    return completedAssignments(participant)
        .map((assignment: any) => ({
            assignment,
            order: testOrderIndex(assignment),
        }))
        .filter(({ order }: { order: number }) => order !== -1)
        .sort((a: { order: number }, b: { order: number }) => a.order - b.order)
        .map(({ assignment }: { assignment: any }) => assignment);
}

function resultAssignments(participant: any) {
    return completedAssignments(participant)
        .map((assignment: any) => ({
            assignment,
            order: testOrderIndex(assignment),
            label: String(testDisplayName(assignment) ?? ''),
        }))
        .sort((a: { order: number; label: string }, b: { order: number; label: string }) => {
            const orderA = a.order === -1 ? Number.MAX_SAFE_INTEGER : a.order;
            const orderB = b.order === -1 ? Number.MAX_SAFE_INTEGER : b.order;

            return orderA - orderB || a.label.localeCompare(b.label, 'de-DE');
        })
        .map(({ assignment }: { assignment: any }) => assignment);
}

function sanitizeFilename(value: string) {
    return value
        .replace(/[\\/:*?"<>|]+/g, '-')
        .replace(/\s+/g, '_')
        .trim();
}

async function downloadParticipantAssignmentsPdf(participant: any, assignments: any[], filename: string, includeAnswers = false) {
    const exportableAssignments = assignments.filter((assignment) => (assignment?.results ?? []).length > 0);

    if (!participant || exportableAssignments.length === 0 || isGeneratingPdf.value) {
        return;
    }

    isGeneratingPdf.value = true;
    pdfParticipant.value = participant;
    pdfExportMode.value = includeAnswers ? 'answers' : 'results';

    const routeParameters = {
        participant: participant.id,
        ...(includeAnswers ? { include_answers: 1 } : {}),
    };
    const pdfUrl = route('participants.results.pdf', routeParameters);
    const printUrl = route('participants.results.print', routeParameters);

    try {
        await downloadPdfOrOpenPrint(pdfUrl, printUrl, filename);
    } catch (error) {
        console.error('PDF export failed, opening print preview.', error);
        openPrintPreview(printUrl);
    } finally {
        isGeneratingPdf.value = false;
        pdfParticipant.value = null;
        pdfExportMode.value = null;
    }
}

function downloadFullTestsPdf(participant: any) {
    const participantName = sanitizeFilename(participant?.name ?? 'Teilnehmer');
    downloadParticipantAssignmentsPdf(participant, orderedPdfAssignments(participant), `${participantName}_Alle_Tests.pdf`);
}

function downloadFullTestsWithAnswersPdf(participant: any) {
    const participantName = sanitizeFilename(participant?.name ?? 'Teilnehmer');
    downloadParticipantAssignmentsPdf(participant, orderedPdfAssignments(participant), `${participantName}_Alle_Tests_und_Antworten.pdf`, true);
}

function updateDesktopSearch() {
    router.get(
        route('participants.list'),
        { search: desktopSearchQuery.value || undefined },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function visitTabletParticipants(params: Record<string, string | number | undefined>) {
    router.get(route('participants.list'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onStart: () => {
            isSearching.value = true;
        },
        onFinish: () => {
            isSearching.value = false;
        },
    });
}

function updateTabletSearch() {
    selectedParticipantId.value = null;

    if (searchTimer !== null) {
        window.clearTimeout(searchTimer);
    }

    searchTimer = window.setTimeout(() => {
        searchTimer = null;
        const search = tabletSearchQuery.value.trim();

        visitTabletParticipants({
            search: search || undefined,
        });
    }, 180);
}

function participantLabel(participant: ParticipantSuggestion) {
    return participant.name || participant.username || 'Teilnehmer';
}

function participantMeta(participant: ParticipantSuggestion) {
    return [participant.firstname, participant.username ? `@${participant.username}` : null].filter(Boolean).join(' - ');
}

function selectParticipant(participant: ParticipantSuggestion) {
    if (searchTimer !== null) {
        window.clearTimeout(searchTimer);
        searchTimer = null;
    }

    selectedParticipantId.value = participant.id;
    tabletSearchQuery.value = participantLabel(participant);

    visitTabletParticipants({
        search: tabletSearchQuery.value,
        participant_id: participant.id,
    });
}

function clearTabletSearch() {
    if (searchTimer !== null) {
        window.clearTimeout(searchTimer);
        searchTimer = null;
    }

    tabletSearchQuery.value = '';
    selectedParticipantId.value = null;
    visitTabletParticipants({});
}
</script>

<template>
    <AppLayout>
        <div class="participant-results-desktop w-full px-4 py-6 sm:px-6">
            <Card class="gap-0 overflow-hidden border-slate-200 py-0 shadow-sm dark:border-slate-700">
                <CardHeader class="border-b bg-slate-50/70 px-5 py-4 sm:px-6 dark:bg-slate-900/40">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <CardTitle class="text-lg">Teilnehmer</CardTitle>
                            <p class="mt-1 text-sm text-muted-foreground">Pr&uuml;fungsergebnisse und PDF-Exporte</p>
                        </div>
                        <div class="relative w-full sm:w-72">
                            <label class="sr-only" for="desktop-participant-search">Teilnehmer suchen</label>
                            <Search
                                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                                aria-hidden="true"
                            />
                            <input
                                id="desktop-participant-search"
                                v-model="desktopSearchQuery"
                                type="search"
                                placeholder="Teilnehmer suchen"
                                class="h-9 w-full rounded-md border border-input bg-background pr-3 pl-9 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                @input="updateDesktopSearch"
                            />
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="px-0">
                    <Table class="text-sm">
                        <TableHeader class="bg-slate-50/80 dark:bg-slate-900/30">
                            <TableRow>
                                <TableHead class="h-11 w-[220px] min-w-[220px] px-5 text-sm font-semibold">Name</TableHead>
                                <TableHead class="h-11 w-[180px] min-w-[180px] px-4 text-sm font-semibold whitespace-nowrap">
                                    Pr&uuml;fung erstellt am
                                </TableHead>
                                <TableHead class="h-11 px-4 text-sm font-semibold">Tests</TableHead>
                                <TableHead class="h-11 w-[130px] min-w-[130px] px-3 text-center text-sm font-semibold"> Eingangsanalyse </TableHead>
                                <TableHead class="h-11 w-[290px] min-w-[290px] px-5 text-center text-sm font-semibold">PDF</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="desktopParticipants.length === 0">
                                <TableCell colspan="5" class="py-8 text-center text-sm text-muted-foreground">
                                    Keine Teilnehmer:innen gefunden.
                                </TableCell>
                            </TableRow>
                            <TableRow
                                v-for="participant in desktopParticipants"
                                :key="participant.id"
                                class="even:bg-slate-50/30 dark:even:bg-slate-900/20"
                            >
                                <TableCell class="px-5 py-3 align-middle font-semibold whitespace-nowrap text-foreground">
                                    {{ participant.name }}
                                </TableCell>
                                <TableCell class="px-4 py-3 align-middle whitespace-nowrap text-muted-foreground">
                                    <span v-if="participant.latest_exam_created_at">
                                        {{ dateFormatter.format(new Date(participant.latest_exam_created_at)) }}
                                    </span>
                                    <span v-else>&ndash;</span>
                                </TableCell>
                                <TableCell class="max-w-0 px-4 py-2.5 align-middle">
                                    <div v-if="participant.test_assignments.length > 0">
                                        <div class="flex flex-nowrap gap-2 overflow-x-auto py-0.5">
                                            <button
                                                v-for="assignment in participant.test_assignments"
                                                :key="assignment.id"
                                                class="inline-flex h-8 items-center rounded-md border border-blue-200 bg-blue-50/60 px-2.5 text-[13px] font-medium text-blue-700 transition-colors hover:border-blue-300 hover:bg-blue-100 disabled:cursor-not-allowed disabled:border-border disabled:bg-muted/40 disabled:text-muted-foreground dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-900/50"
                                                :disabled="assignment.results.length === 0"
                                                @click="assignment.results.length > 0 && viewTestResult(assignment, participant)"
                                            >
                                                {{ testDisplayName(assignment) }}
                                            </button>
                                        </div>
                                    </div>
                                    <div v-else class="py-1.5 text-sm text-muted-foreground">Keine Tests zugewiesen.</div>
                                </TableCell>
                                <TableCell class="px-3 py-2.5 text-center align-middle">
                                    <TooltipProvider :delay-duration="0">
                                        <Tooltip>
                                            <TooltipTrigger as-child>
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    size="icon"
                                                    class="size-8 border-blue-200 bg-blue-50/60 text-blue-700 hover:border-blue-300 hover:bg-blue-100 hover:text-blue-700 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-900/50 dark:hover:text-blue-300"
                                                    aria-label="Eingangsanalyse &ouml;ffnen"
                                                    @click="openEntranceAnalysis(participant)"
                                                >
                                                    <ChartNoAxesCombined class="size-4" aria-hidden="true" />
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent>Eingangsanalyse &ouml;ffnen</TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                </TableCell>
                                <TableCell class="px-5 py-2.5 align-middle">
                                    <div class="flex flex-nowrap justify-center gap-2">
                                        <button
                                            type="button"
                                            class="inline-flex h-8 items-center gap-2 rounded-md border border-border bg-background px-3 text-[13px] font-medium whitespace-nowrap text-foreground shadow-xs transition-colors hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 disabled:cursor-not-allowed disabled:bg-muted/40 disabled:text-muted-foreground dark:hover:bg-blue-950/40 dark:hover:text-blue-300"
                                            :disabled="orderedPdfAssignments(participant).length === 0 || isGeneratingPdf"
                                            title="Alle Tests als PDF herunterladen"
                                            @click="downloadFullTestsPdf(participant)"
                                        >
                                            <Loader2
                                                v-if="isGeneratingPdf && pdfParticipant?.id === participant.id && pdfExportMode === 'results'"
                                                class="size-4 animate-spin"
                                                aria-hidden="true"
                                            />
                                            <FileText v-else class="size-4" aria-hidden="true" />
                                            <span>Alle Tests</span>
                                        </button>
                                        <button
                                            type="button"
                                            class="inline-flex h-8 items-center gap-2 rounded-md border border-border bg-background px-3 text-[13px] font-medium whitespace-nowrap text-foreground shadow-xs transition-colors hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 disabled:cursor-not-allowed disabled:bg-muted/40 disabled:text-muted-foreground dark:hover:bg-blue-950/40 dark:hover:text-blue-300"
                                            :disabled="orderedPdfAssignments(participant).length === 0 || isGeneratingPdf"
                                            title="Alle Tests mit Antworten als PDF herunterladen"
                                            @click="downloadFullTestsWithAnswersPdf(participant)"
                                        >
                                            <Loader2
                                                v-if="isGeneratingPdf && pdfParticipant?.id === participant.id && pdfExportMode === 'answers'"
                                                class="size-4 animate-spin"
                                                aria-hidden="true"
                                            />
                                            <FileText v-else class="size-4" aria-hidden="true" />
                                            <span>Mit Antworten</span>
                                        </button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <div v-if="participants.links.length > 3" class="flex justify-center border-t bg-slate-50/50 px-4 py-3 dark:bg-slate-900/20">
                        <nav class="relative z-0 inline-flex -space-x-px rounded-md shadow-xs" aria-label="Pagination">
                            <template v-for="(link, key) in participants.links" :key="key">
                                <component
                                    :is="link.url ? Link : 'span'"
                                    v-bind="link.url ? { href: link.url } : {}"
                                    class="relative inline-flex h-9 items-center border px-3.5 text-sm font-medium"
                                    :class="{
                                        'z-10 border-blue-500 bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300': link.active,
                                        'border-border bg-background text-muted-foreground hover:bg-muted': !link.active,
                                        'rounded-l-md': key === 0,
                                        'rounded-r-md': key === participants.links.length - 1,
                                        'hidden md:inline-flex': !(
                                            link.active ||
                                            key === 0 ||
                                            key === participants.links.length - 1 ||
                                            link.label.includes('Previous') ||
                                            link.label.includes('Next')
                                        ),
                                        'cursor-default opacity-50': !link.url,
                                    }"
                                    :aria-disabled="!link.url"
                                >
                                    <span v-html="link.label" />
                                </component>
                            </template>
                        </nav>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="participant-results-tablet w-full px-4 py-6 sm:px-6">
            <Card class="gap-0 border-slate-200 py-0 shadow-sm dark:border-slate-700">
                <CardHeader class="border-b bg-slate-50/70 px-5 py-4 sm:px-6 dark:bg-slate-900/40">
                    <div class="flex flex-col gap-4">
                        <div>
                            <CardTitle class="text-lg">Teilnehmer</CardTitle>
                            <p class="mt-1 text-sm text-muted-foreground">Pr&uuml;fungsergebnisse</p>
                        </div>
                        <div class="relative w-full">
                            <label class="sr-only" for="tablet-participant-search">Teilnehmer suchen</label>
                            <Search
                                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                                aria-hidden="true"
                            />
                            <input
                                id="tablet-participant-search"
                                v-model="tabletSearchQuery"
                                type="search"
                                autocomplete="off"
                                placeholder="Teilnehmer suchen"
                                class="h-11 w-full rounded-md border border-input bg-background pr-11 pl-9 text-base shadow-sm transition-colors placeholder:text-muted-foreground focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                @input="updateTabletSearch"
                            />
                            <button
                                v-if="tabletSearchQuery"
                                type="button"
                                class="absolute top-1/2 right-2 inline-flex size-8 -translate-y-1/2 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                aria-label="Auswahl entfernen"
                                @click="clearTabletSearch"
                            >
                                <X class="size-4" aria-hidden="true" />
                            </button>

                            <div
                                v-if="showSuggestions || showNoSuggestions"
                                class="absolute top-[calc(100%+0.5rem)] left-0 z-30 w-full overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-lg"
                            >
                                <button
                                    v-for="suggestion in participantSuggestions"
                                    :key="suggestion.id"
                                    type="button"
                                    class="flex min-h-12 w-full flex-col items-start justify-center border-b px-3 py-2 text-left text-sm transition-colors last:border-b-0 hover:bg-muted focus:bg-muted focus:outline-none"
                                    @click="selectParticipant(suggestion)"
                                >
                                    <span class="font-medium text-foreground">{{ participantLabel(suggestion) }}</span>
                                    <span v-if="participantMeta(suggestion)" class="text-xs text-muted-foreground">
                                        {{ participantMeta(suggestion) }}
                                    </span>
                                </button>
                                <div v-if="showNoSuggestions" class="px-3 py-3 text-sm text-muted-foreground">
                                    Kein passender Teilnehmer gefunden.
                                </div>
                            </div>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="px-0">
                    <div class="overflow-x-auto">
                        <Table class="min-w-[560px] text-sm md:min-w-0">
                            <TableHeader class="bg-slate-50/80 dark:bg-slate-900/30">
                                <TableRow>
                                    <TableHead class="h-11 w-[240px] min-w-[220px] px-5 text-sm font-semibold">Name</TableHead>
                                    <TableHead class="h-11 px-4 text-sm font-semibold">Testergebnisse</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="tabletSelectedParticipants.length === 0">
                                    <TableCell colspan="2" class="py-8 text-center text-sm text-muted-foreground">
                                        <span v-if="hasSelectedParticipant">Ausgew&auml;hlter Teilnehmer nicht gefunden.</span>
                                        <span v-else>Teilnehmer suchen und ausw&auml;hlen.</span>
                                    </TableCell>
                                </TableRow>
                                <TableRow
                                    v-for="participant in tabletSelectedParticipants"
                                    :key="participant.id"
                                    class="even:bg-slate-50/30 dark:even:bg-slate-900/20"
                                >
                                    <TableCell class="px-5 py-4 align-top font-semibold whitespace-nowrap text-foreground">
                                        {{ participant.name }}
                                    </TableCell>
                                    <TableCell class="px-4 py-3 align-top">
                                        <div v-if="resultAssignments(participant).length > 0" class="flex max-w-sm flex-col items-stretch gap-2">
                                            <Button
                                                v-for="assignment in resultAssignments(participant)"
                                                :key="assignment.id"
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                class="h-auto min-h-10 w-full justify-start border-blue-200 bg-blue-50/60 px-3 text-blue-700 hover:border-blue-300 hover:bg-blue-100 hover:text-blue-700 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-900/50 dark:hover:text-blue-300"
                                                title="Testergebnis ansehen"
                                                @click="viewTestResult(assignment, participant)"
                                            >
                                                <Eye class="size-4" aria-hidden="true" />
                                                <span class="min-w-0 flex-1 truncate text-left">{{ testDisplayName(assignment) }}</span>
                                                <span class="text-xs text-blue-600/80 dark:text-blue-200/80">Ergebnis</span>
                                            </Button>
                                        </div>
                                        <div v-else class="py-2 text-sm text-muted-foreground">Keine Testergebnisse verf&uuml;gbar.</div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>

        <TestResultModal :isOpen="isModalOpen" :assignment="selectedAssignment" :participant="selectedParticipant" @close="closeModal" />
        <EntranceAnalysisModal
            :is-open="isEntranceAnalysisOpen"
            :participant="entranceAnalysisParticipant"
            @close="closeEntranceAnalysis"
            @saved="handleEntranceAnalysisSaved"
        />
    </AppLayout>
</template>

<style scoped>
.participant-results-desktop {
    display: none;
}

.participant-results-tablet {
    display: block;
}

@media (min-width: 40.01cm) {
    .participant-results-desktop {
        display: block;
        padding-right: 2rem;
        padding-left: 2rem;
    }

    .participant-results-tablet {
        display: none;
    }
}
</style>
