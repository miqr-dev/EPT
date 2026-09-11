<script setup lang="ts">
import TestResultModal from '@/components/TestResultModal.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { Eye, Search, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

type ParticipantSuggestion = {
    id: number;
    name: string;
    firstname?: string | null;
    username?: string | null;
};

type Participant = ParticipantSuggestion & {
    test_assignments: any[];
};

const props = defineProps<{
    participants: {
        data: Participant[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    suggestions: ParticipantSuggestion[];
    filters: {
        search?: string | null;
        participant_id?: number | null;
    };
}>();

const isModalOpen = ref(false);
const selectedAssignment = ref(null);
const selectedParticipant = ref(null);
const searchQuery = ref(props.filters.search ?? '');
const selectedParticipantId = ref<number | null>(props.filters.participant_id ?? null);
const isSearching = ref(false);
let searchTimer: number | null = null;

const TEST_RESULT_ORDER = [
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

const selectedParticipants = computed(() => props.participants.data ?? []);
const participantSuggestions = computed(() => props.suggestions ?? []);
const hasSelectedParticipant = computed(() => selectedParticipantId.value !== null);
const canShowSuggestions = computed(() => !hasSelectedParticipant.value && searchQuery.value.trim().length >= 3);
const hasSyncedSearch = computed(() => (props.filters.search ?? '').trim() === searchQuery.value.trim());
const showSuggestions = computed(() => canShowSuggestions.value && hasSyncedSearch.value && participantSuggestions.value.length > 0);
const showNoSuggestions = computed(
    () => canShowSuggestions.value && hasSyncedSearch.value && participantSuggestions.value.length === 0 && !isSearching.value,
);

watch(
    () => props.filters.search,
    (value) => {
        if ((value ?? '') !== searchQuery.value) {
            searchQuery.value = value ?? '';
        }
    },
);

watch(
    () => props.filters.participant_id,
    (value) => {
        selectedParticipantId.value = value ?? null;
    },
);

function participantLabel(participant: ParticipantSuggestion) {
    return participant.name || participant.username || 'Teilnehmer';
}

function participantMeta(participant: ParticipantSuggestion) {
    return [participant.firstname, participant.username ? `@${participant.username}` : null].filter(Boolean).join(' - ');
}

function visitParticipants(params: Record<string, string | number | undefined>) {
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

function updateSearch() {
    selectedParticipantId.value = null;

    if (searchTimer !== null) {
        window.clearTimeout(searchTimer);
    }

    searchTimer = window.setTimeout(() => {
        searchTimer = null;
        const search = searchQuery.value.trim();

        visitParticipants({
            search: search || undefined,
        });
    }, 180);
}

function selectParticipant(participant: ParticipantSuggestion) {
    if (searchTimer !== null) {
        window.clearTimeout(searchTimer);
        searchTimer = null;
    }

    selectedParticipantId.value = participant.id;
    searchQuery.value = participantLabel(participant);

    visitParticipants({
        search: searchQuery.value,
        participant_id: participant.id,
    });
}

function clearSearch() {
    if (searchTimer !== null) {
        window.clearTimeout(searchTimer);
        searchTimer = null;
    }

    searchQuery.value = '';
    selectedParticipantId.value = null;
    visitParticipants({});
}

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

function testDisplayName(assignment: any) {
    return assignment?.test?.name === 'Konzentrationstest' ? '628' : assignment?.test?.name;
}

function completedAssignments(participant: any) {
    return (participant?.test_assignments ?? []).filter((assignment: any) => (assignment?.results ?? []).length > 0);
}

function testOrderIndex(assignment: any) {
    const identifiers = [assignment?.test?.name, assignment?.test?.code].filter(Boolean).map((value) => String(value).trim().toUpperCase());

    return TEST_RESULT_ORDER.findIndex((aliases) => aliases.some((alias) => identifiers.includes(alias)));
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
</script>

<template>
    <AppLayout>
        <div class="w-full px-4 py-6 sm:px-6 lg:px-8">
            <Card class="gap-0 border-slate-200 py-0 shadow-sm dark:border-slate-700">
                <CardHeader class="border-b bg-slate-50/70 px-5 py-4 sm:px-6 dark:bg-slate-900/40">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <CardTitle class="text-lg">Teilnehmer</CardTitle>
                            <p class="mt-1 text-sm text-muted-foreground">Pr&uuml;fungsergebnisse</p>
                        </div>
                        <div class="relative w-full md:w-96">
                            <label class="sr-only" for="participant-search">Teilnehmer suchen</label>
                            <Search
                                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                                aria-hidden="true"
                            />
                            <input
                                id="participant-search"
                                v-model="searchQuery"
                                type="search"
                                autocomplete="off"
                                placeholder="Teilnehmer suchen"
                                class="h-11 w-full rounded-md border border-input bg-background pr-11 pl-9 text-base shadow-sm transition-colors placeholder:text-muted-foreground focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none md:h-10 md:text-sm"
                                @input="updateSearch"
                            />
                            <button
                                v-if="searchQuery"
                                type="button"
                                class="absolute top-1/2 right-2 inline-flex size-8 -translate-y-1/2 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                aria-label="Auswahl entfernen"
                                @click="clearSearch"
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
                                    <span v-if="participantMeta(suggestion)" class="text-xs text-muted-foreground">{{
                                        participantMeta(suggestion)
                                    }}</span>
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
                                <TableRow v-if="selectedParticipants.length === 0">
                                    <TableCell colspan="2" class="py-8 text-center text-sm text-muted-foreground">
                                        <span v-if="hasSelectedParticipant">Ausgew&auml;hlter Teilnehmer nicht gefunden.</span>
                                        <span v-else>Teilnehmer suchen und ausw&auml;hlen.</span>
                                    </TableCell>
                                </TableRow>
                                <TableRow
                                    v-for="participant in selectedParticipants"
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
    </AppLayout>
</template>
