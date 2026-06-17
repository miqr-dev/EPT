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
import { ChartNoAxesCombined, FileText, Loader2, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    participants: {
        data: any[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: {
        search: string;
    };
}>();

const isModalOpen = ref(false);
const selectedAssignment = ref(null);
const selectedParticipant = ref(null);
const searchQuery = ref(props.filters.search ?? '');
const pdfParticipant = ref<any | null>(null);
const isGeneratingPdf = ref(false);
const pdfExportMode = ref<'results' | 'answers' | null>(null);
const isEntranceAnalysisOpen = ref(false);
const entranceAnalysisParticipant = ref<any | null>(null);

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

function viewTestResult(assignment: any, participant: any) {
    selectedAssignment.value = assignment;
    selectedParticipant.value = participant;
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    selectedAssignment.value = null;
    selectedParticipant.value = null;
}

function openEntranceAnalysis(participant: any) {
    entranceAnalysisParticipant.value = participant;
    isEntranceAnalysisOpen.value = true;
}

function closeEntranceAnalysis() {
    isEntranceAnalysisOpen.value = false;
    entranceAnalysisParticipant.value = null;
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

function updateSearch() {
    router.get(
        route('participants.list'),
        { search: searchQuery.value || undefined },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}
</script>

<template>
    <AppLayout>
        <div class="w-full px-4 py-6 sm:px-6 lg:px-8">
            <Card class="gap-0 overflow-hidden border-slate-200 py-0 shadow-sm dark:border-slate-700">
                <CardHeader class="border-b bg-slate-50/70 px-5 py-4 sm:px-6 dark:bg-slate-900/40">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <CardTitle class="text-lg">Teilnehmer</CardTitle>
                            <p class="mt-1 text-sm text-muted-foreground">Prüfungsergebnisse und PDF-Exporte</p>
                        </div>
                        <div class="relative w-full sm:w-72">
                            <label class="sr-only" for="participant-search">Teilnehmer suchen</label>
                            <Search
                                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                                aria-hidden="true"
                            />
                            <input
                                id="participant-search"
                                v-model="searchQuery"
                                type="search"
                                placeholder="Teilnehmer suchen"
                                class="h-9 w-full rounded-md border border-input bg-background pr-3 pl-9 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                @input="updateSearch"
                            />
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="px-0">
                    <Table class="text-sm">
                        <TableHeader class="bg-slate-50/80 dark:bg-slate-900/30">
                            <TableRow>
                                <TableHead class="h-11 w-[220px] min-w-[220px] px-5 text-sm font-semibold">Name</TableHead>
                                <TableHead class="h-11 w-[180px] min-w-[180px] px-4 text-sm font-semibold whitespace-nowrap"
                                    >Prüfung erstellt am</TableHead
                                >
                                <TableHead class="h-11 px-4 text-sm font-semibold">Tests</TableHead>
                                <TableHead class="h-11 w-[130px] min-w-[130px] px-3 text-center text-sm font-semibold">Eingangsanalyse</TableHead>
                                <TableHead class="h-11 w-[290px] min-w-[290px] px-5 text-center text-sm font-semibold">PDF</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="participants.data.length === 0">
                                <TableCell colspan="5" class="py-8 text-center text-sm text-muted-foreground"
                                    >Keine Teilnehmer:innen gefunden.</TableCell
                                >
                            </TableRow>
                            <TableRow
                                v-for="participant in participants.data"
                                :key="participant.id"
                                class="even:bg-slate-50/30 dark:even:bg-slate-900/20"
                            >
                                <TableCell class="px-5 py-3 align-middle font-semibold whitespace-nowrap text-foreground">{{
                                    participant.name
                                }}</TableCell>
                                <TableCell class="px-4 py-3 align-middle whitespace-nowrap text-muted-foreground">
                                    <span v-if="participant.latest_exam_created_at">
                                        {{ dateFormatter.format(new Date(participant.latest_exam_created_at)) }}
                                    </span>
                                    <span v-else>–</span>
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
                                                    aria-label="Eingangsanalyse öffnen"
                                                    @click="openEntranceAnalysis(participant)"
                                                >
                                                    <ChartNoAxesCombined class="size-4" aria-hidden="true" />
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent>Eingangsanalyse öffnen</TooltipContent>
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
        <TestResultModal :isOpen="isModalOpen" :assignment="selectedAssignment" :participant="selectedParticipant" @close="closeModal" />
        <EntranceAnalysisModal :is-open="isEntranceAnalysisOpen" :participant="entranceAnalysisParticipant" @close="closeEntranceAnalysis" />
    </AppLayout>
</template>
