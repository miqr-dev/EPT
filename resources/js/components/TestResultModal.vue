<script setup lang="ts">
import TestResultViewer from '@/components/TestResultViewer.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { generatePdfFromElement } from '@/lib/pdf';
import { useForm } from '@inertiajs/vue3';
import { X } from 'lucide-vue-next';
import { computed, nextTick, ref, watch } from 'vue';
import PdfTemplate from './PdfTemplate.vue';

const props = defineProps<{
    isOpen: boolean;
    assignment: any;
    participant: any;
}>();

const emit = defineEmits(['close']);

const form = useForm({
    answers: [] as any[],
});

const editable = ref<any | null>(null);
const viewerRef = ref<any>(null);
const pdfTemplateRef = ref<any>(null);
const isGeneratingPdf = ref(false);

const dialogTitle = computed(() => props.participant?.name || 'Testergebnis bearbeiten');

const teacherName = computed(() => {
    return props.assignment?.results?.[0]?.teacher?.name || 'N/A';
});

const isAvemTest = computed(() => props.assignment?.test?.name === 'AVEM');
const requiresMainSaveButton = computed(() => ['BRT-A', 'BRT-B'].includes(props.assignment?.test?.name));
const showTeacherInPdf = false;

const pdfContainerStyle = computed(() => ({
    zIndex: -1,
    width: '1200px',
}));

watch(
    () => props.assignment,
    (val) => {
        editable.value = val && val.results.length > 0 ? JSON.parse(JSON.stringify(val.results[0].result_json)) : null;
    },
);

function submit() {
    if (props.assignment && props.assignment.results.length > 0 && editable.value) {
        const testResultId = props.assignment.results[0].id;
        const applyUpdatedResultFromPage = (page: any) => {
            const participants = page?.props?.participants?.data;
            if (!Array.isArray(participants)) return;

            for (const participant of participants) {
                const assignments = participant?.test_assignments ?? [];
                for (const assignment of assignments) {
                    const result = (assignment?.results ?? []).find((entry: any) => entry?.id === testResultId);
                    if (!result?.result_json) continue;

                    const updatedResult = JSON.parse(JSON.stringify(result.result_json));
                    editable.value = updatedResult;
                    props.assignment.results[0].result_json = updatedResult;
                    return;
                }
            }
        };

        if (['MRT-A', 'MRT-B', 'BRT-A', 'BRT-B'].includes(props.assignment.test.name)) {
            form.answers = editable.value.answers;
            form.put(route('test-results.update', { testResult: testResultId }), {
                preserveState: true,
                preserveScroll: true,
                onSuccess: (page) => {
                    applyUpdatedResultFromPage(page);
                },
            });
        } else {
            // Keep old behavior for other tests
            const oldForm = useForm({ result_json: JSON.stringify(editable.value) });
            oldForm.put(route('test-results.update', { testResult: testResultId }), {
                preserveState: true,
                preserveScroll: true,
                onSuccess: (page) => {
                    applyUpdatedResultFromPage(page);
                },
            });
        }
    }
}

function closeModal() {
    emit('close');
}

function handleManualScoreUpdated(payload: { key: string; value: number | null }) {
    const result = props.assignment?.results?.[0];
    if (!result) return;
    const scores = (result.manual_scores ?? []) as Array<{ key: string; value: number | string | null }>;
    const existing = scores.find((entry) => entry.key === payload.key);
    if (existing) {
        existing.value = payload.value;
    } else {
        scores.push({ key: payload.key, value: payload.value });
    }
    result.manual_scores = [...scores];
}

async function downloadUnifiedPdf() {
    isGeneratingPdf.value = true;
    await nextTick(); // Wait for the v-if to render the component

    setTimeout(async () => {
        const el = pdfTemplateRef.value?.$el as HTMLElement | undefined;
        if (el) {
            const filename = `${props.participant.name}_${props.assignment.test.name}_Ergebnis.pdf`;
            await generatePdfFromElement(el, filename, { scale: isAvemTest.value ? 4 : 3 });
        } else {
            console.error('PDF template element not found.');
        }
        isGeneratingPdf.value = false;
    }, 200); // 200ms delay
}
</script>

<template>
    <Dialog
        :open="isOpen"
        @update:open="
            (open) => {
                if (!open) closeModal();
            }
        "
    >
        <DialogContent class="!top-0 !left-0 !h-screen w-screen !max-w-none !translate-x-0 !translate-y-0 !rounded-none">
            <template #top-right>
                <TooltipProvider :delay-duration="0">
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <DialogClose
                                aria-label="Schließen"
                                class="absolute top-4 right-4 inline-flex size-9 items-center justify-center rounded-md text-red-600 transition-colors hover:bg-red-50 hover:text-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-hidden disabled:pointer-events-none dark:hover:bg-red-950/30"
                            >
                                <X class="size-5" aria-hidden="true" />
                                <span class="sr-only">Schließen</span>
                            </DialogClose>
                        </TooltipTrigger>
                        <TooltipContent side="left">Schließen</TooltipContent>
                    </Tooltip>
                </TooltipProvider>
            </template>

            <DialogHeader class="items-start pr-12 text-left">
                <DialogTitle>{{ dialogTitle }}</DialogTitle>
                <div v-if="assignment" class="flex">
                    <Button variant="outline" size="sm" @click="downloadUnifiedPdf" :disabled="isGeneratingPdf">
                        {{ isGeneratingPdf ? 'Generiere PDF...' : 'Ergebnis PDF' }}
                    </Button>
                </div>
            </DialogHeader>
            <TestResultViewer
                v-if="editable"
                ref="viewerRef"
                :test="assignment?.test"
                v-model="editable"
                :participant-profile="participant?.participant_profile"
                :test-result-id="assignment?.results?.[0]?.id ?? null"
                :manual-scores="assignment?.results?.[0]?.manual_scores ?? []"
                @manual-score-updated="handleManualScoreUpdated"
                class="mb-4 flex-1 overflow-y-auto"
            />
            <DialogFooter>
                <Button v-if="requiresMainSaveButton" type="button" @click="submit">Änderungen speichern</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Hidden template for PDF generation -->
    <div v-if="isGeneratingPdf" class="fixed top-0 left-0" :style="pdfContainerStyle">
        <PdfTemplate
            v-if="assignment"
            ref="pdfTemplateRef"
            :assignment="assignment"
            :participant="participant"
            :teacherName="teacherName"
            :show-teacher="showTeacherInPdf"
        />
    </div>
</template>
