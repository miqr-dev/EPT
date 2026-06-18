<script setup lang="ts">
import EntranceAnalysisForm from '@/components/EntranceAnalysisForm.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { emptyObservations, type ObservationFields } from '@/lib/entrance-analysis';
import { downloadPdfOrOpenPrint, openPrintPreview } from '@/lib/pdf-export';
import type { AppPageProps } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { FileDown, Loader2, Save, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    isOpen: boolean;
    participant: any;
}>();

const emit = defineEmits(['close']);
const page = usePage<AppPageProps>();
const observations = ref<ObservationFields>(emptyObservations());
const isSaving = ref(false);
const isGeneratingPdf = ref(false);

const assignments = computed(() => props.participant?.test_assignments ?? []);
const analysis = computed(() => props.participant?.entrance_analysis ?? null);
const teacherName = computed(() => {
    const teacher = analysis.value?.teacher ?? page.props.auth?.user;
    return displayName(teacher);
});
const filename = computed(() => `${sanitizeFilename(props.participant?.name ?? 'Teilnehmer')}_Eingangsanalyse.pdf`);

function displayName(user?: { firstname?: string | null; name?: string | null } | null) {
    const firstName = String(user?.firstname ?? '').trim();
    const name = String(user?.name ?? '').trim();

    if (!firstName) return name;
    if (!name) return firstName;

    const normalizedFirstName = firstName.toLocaleLowerCase('de-DE');
    const normalizedName = name.toLocaleLowerCase('de-DE');

    if (normalizedName === normalizedFirstName || normalizedName.startsWith(`${normalizedFirstName} `)) {
        return name;
    }

    return `${firstName} ${name}`.trim();
}

watch(
    () => props.participant,
    (participant) => {
        const current = participant?.entrance_analysis ?? {};
        observations.value = {
            instruction_understanding: current.instruction_understanding ?? '',
            work_method: current.work_method ?? '',
            work_speed: current.work_speed ?? '',
            group_behavior: current.group_behavior ?? '',
            remarks: current.remarks ?? '',
        };
    },
    { immediate: true },
);

function sanitizeFilename(value: string) {
    return value
        .replace(/[\\/:*?"<>|]+/g, '-')
        .replace(/\s+/g, '_')
        .trim();
}

function closeModal() {
    emit('close');
}

function saveAnalysis() {
    if (!props.participant || isSaving.value) return;
    isSaving.value = true;

    router.put(route('participants.entrance-analysis.update', { participant: props.participant.id }), observations.value, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            isSaving.value = false;
        },
    });
}

async function downloadPdf() {
    if (!props.participant || isGeneratingPdf.value) return;
    isGeneratingPdf.value = true;
    const pdfUrl = route('participants.entrance-analysis.pdf', { participant: props.participant.id });
    const printUrl = route('participants.entrance-analysis.print', { participant: props.participant.id });

    try {
        await downloadPdfOrOpenPrint(pdfUrl, printUrl, filename.value);
    } catch (error) {
        console.error('Eingangsanalyse PDF export failed, opening print preview.', error);
        openPrintPreview(printUrl);
    } finally {
        isGeneratingPdf.value = false;
    }
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
        <DialogContent class="!top-0 !left-0 !h-screen w-screen !max-w-none !translate-x-0 !translate-y-0 !gap-0 !rounded-none !p-0">
            <template #top-right>
                <DialogClose
                    aria-label="Schließen"
                    class="absolute top-3 right-4 z-20 inline-flex size-9 items-center justify-center rounded-md text-red-600 transition-colors hover:bg-red-50"
                >
                    <X class="size-5" aria-hidden="true" />
                    <span class="sr-only">Schließen</span>
                </DialogClose>
            </template>

            <DialogHeader class="sticky top-0 z-10 flex-row items-center justify-between border-b bg-background px-5 py-3 pr-16 text-left shadow-sm">
                <DialogTitle>Eingangsanalyse: {{ participant?.name }}</DialogTitle>
                <div class="flex items-center gap-2">
                    <Button type="button" variant="outline" :disabled="isSaving" @click="saveAnalysis">
                        <Loader2 v-if="isSaving" class="size-4 animate-spin" />
                        <Save v-else class="size-4" />
                        {{ isSaving ? 'Speichere...' : 'Beobachtungen speichern' }}
                    </Button>
                    <Button type="button" :disabled="isGeneratingPdf" @click="downloadPdf">
                        <Loader2 v-if="isGeneratingPdf" class="size-4 animate-spin" />
                        <FileDown v-else class="size-4" />
                        {{ isGeneratingPdf ? 'Generiere PDF...' : 'PDF herunterladen' }}
                    </Button>
                </div>
            </DialogHeader>

            <div class="min-h-0 flex-1 overflow-auto bg-slate-200 px-4 py-6 dark:bg-slate-950">
                <EntranceAnalysisForm
                    v-if="participant"
                    v-model="observations"
                    :participant="participant"
                    :assignments="assignments"
                    :teacher-name="teacherName"
                    :conducted-at="participant.latest_exam_created_at"
                    :editable="true"
                />
            </div>
        </DialogContent>
    </Dialog>
</template>
