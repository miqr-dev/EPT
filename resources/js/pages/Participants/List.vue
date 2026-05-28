<script setup lang="ts">
import PdfTemplate from '@/components/PdfTemplate.vue';
import TestResultModal from '@/components/TestResultModal.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { generatePdfFromElements } from '@/lib/pdf';
import { Link, router } from '@inertiajs/vue3';
import { FileText, Loader2 } from 'lucide-vue-next';
import { computed, nextTick, ref } from 'vue';

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
const pdfAssignments = ref<any[]>([]);
const pdfPageRefs = ref<HTMLElement[]>([]);
const pdfFilename = ref('');
const isGeneratingPdf = ref(false);

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
            hour: '2-digit',
            minute: '2-digit',
        }),
);

function viewTestResult(assignment, participant) {
    selectedAssignment.value = assignment;
    selectedParticipant.value = participant;
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    selectedAssignment.value = null;
    selectedParticipant.value = null;
}

const pdfContainerStyle = computed(() => ({
    zIndex: -1,
    width: '1200px',
}));

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
        .filter(({ order }) => order !== -1)
        .sort((a, b) => a.order - b.order)
        .map(({ assignment }) => assignment);
}

function sanitizeFilename(value: string) {
    return value
        .replace(/[\\/:*?"<>|]+/g, '-')
        .replace(/\s+/g, '_')
        .trim();
}

function setPdfPageRef(el: Element | null) {
    if (el instanceof HTMLElement) {
        pdfPageRefs.value.push(el);
    }
}

async function downloadParticipantAssignmentsPdf(participant: any, assignments: any[], filename: string) {
    const exportableAssignments = assignments.filter((assignment) => (assignment?.results ?? []).length > 0);

    if (!participant || exportableAssignments.length === 0 || isGeneratingPdf.value) {
        return;
    }

    isGeneratingPdf.value = true;
    pdfParticipant.value = participant;
    pdfAssignments.value = exportableAssignments;
    pdfFilename.value = filename;
    pdfPageRefs.value = [];

    await nextTick();

    setTimeout(async () => {
        try {
            await generatePdfFromElements(pdfPageRefs.value, pdfFilename.value, { scale: 3 });
        } finally {
            isGeneratingPdf.value = false;
            pdfParticipant.value = null;
            pdfAssignments.value = [];
            pdfFilename.value = '';
            pdfPageRefs.value = [];
        }
    }, 200);
}

function downloadFullTestsPdf(participant: any) {
    const participantName = sanitizeFilename(participant?.name ?? 'Teilnehmer');
    downloadParticipantAssignmentsPdf(participant, orderedPdfAssignments(participant), `${participantName}_Alle_Tests.pdf`);
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
        <div class="container mx-auto px-4 py-8 sm:px-6 lg:px-8">
            <Card>
                <CardHeader>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <CardTitle>Teilnehmer</CardTitle>
                        <div class="w-full sm:w-72">
                            <label class="sr-only" for="participant-search">Teilnehmer suchen</label>
                            <input
                                id="participant-search"
                                v-model="searchQuery"
                                type="search"
                                placeholder="Teilnehmer suchen"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                @input="updateSearch"
                            />
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Prüfung erstellt am</TableHead>
                                <TableHead>Tests</TableHead>
                                <TableHead class="text-center">PDF</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="participants.data.length === 0">
                                <TableCell colspan="4" class="py-4 text-center text-gray-500">Keine Teilnehmer:innen gefunden.</TableCell>
                            </TableRow>
                            <TableRow v-for="participant in participants.data" :key="participant.id">
                                <TableCell>{{ participant.name }}</TableCell>
                                <TableCell>
                                    <span v-if="participant.latest_exam_created_at">
                                        {{ dateFormatter.format(new Date(participant.latest_exam_created_at)) }}
                                    </span>
                                    <span v-else>–</span>
                                </TableCell>
                                <TableCell>
                                    <div v-if="participant.test_assignments.length > 0">
                                        <div class="flex flex-wrap gap-3">
                                            <button
                                                v-for="assignment in participant.test_assignments"
                                                :key="assignment.id"
                                                class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-blue-700 shadow-sm transition hover:bg-blue-50 disabled:cursor-not-allowed disabled:border-gray-100 disabled:text-gray-400"
                                                :disabled="assignment.results.length === 0"
                                                @click="assignment.results.length > 0 && viewTestResult(assignment, participant)"
                                            >
                                                {{ assignment.test.name }}
                                            </button>
                                        </div>
                                    </div>
                                    <div v-else>Keine Tests zugewiesen.</div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex justify-center">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-2 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-blue-700 shadow-sm transition hover:bg-blue-50 disabled:cursor-not-allowed disabled:border-gray-100 disabled:text-gray-400"
                                            :disabled="orderedPdfAssignments(participant).length === 0 || isGeneratingPdf"
                                            title="Alle Tests als PDF herunterladen"
                                            @click="downloadFullTestsPdf(participant)"
                                        >
                                            <Loader2
                                                v-if="isGeneratingPdf && pdfParticipant?.id === participant.id"
                                                class="size-4 animate-spin"
                                                aria-hidden="true"
                                            />
                                            <FileText v-else class="size-4" aria-hidden="true" />
                                            <span>Alle Tests</span>
                                        </button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <div v-if="participants.links.length > 3" class="mt-6 flex justify-center">
                        <nav class="relative z-0 inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                            <template v-for="(link, key) in participants.links" :key="key">
                                <component
                                    :is="link.url ? Link : 'span'"
                                    v-bind="link.url ? { href: link.url } : {}"
                                    class="relative inline-flex items-center border px-4 py-2 text-sm font-medium"
                                    :class="{
                                        'z-10 border-indigo-500 bg-indigo-50 text-indigo-600': link.active,
                                        'border-gray-300 bg-white text-gray-500 hover:bg-gray-50': !link.active,
                                        'rounded-l-md': key === 0,
                                        'rounded-r-md': key === participants.links.length - 1,
                                        'hidden md:inline-flex': !(
                                            link.active ||
                                            key === 0 ||
                                            key === participants.links.length - 1 ||
                                            link.label.includes('Previous') ||
                                            link.label.includes('Next')
                                        ),
                                        'cursor-default text-gray-400': !link.url,
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

        <div v-if="isGeneratingPdf" class="fixed top-0 left-0" :style="pdfContainerStyle" aria-hidden="true">
            <div v-for="assignment in pdfAssignments" :key="assignment.id" :ref="setPdfPageRef" class="bg-white">
                <PdfTemplate :assignment="assignment" :participant="pdfParticipant" teacher-name="" :show-teacher="false" :show-answers="false" />
            </div>
        </div>
    </AppLayout>
</template>
