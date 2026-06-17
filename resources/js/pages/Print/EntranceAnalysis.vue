<script setup lang="ts">
import EntranceAnalysisForm from '@/components/EntranceAnalysisForm.vue';
import { emptyObservations, type ObservationFields } from '@/lib/entrance-analysis';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        participant: any;
        assignments: any[];
        analysis?: any | null;
        teacherName?: string;
        conductedAt?: string | null;
        autoPrint?: boolean;
        filename?: string;
        pdfUrl?: string | null;
    }>(),
    {
        analysis: null,
        teacherName: '',
        conductedAt: null,
        autoPrint: false,
        filename: 'Eingangsanalyse.pdf',
        pdfUrl: null,
    },
);

const observations = ref<ObservationFields>({
    ...emptyObservations(),
    instruction_understanding: props.analysis?.instruction_understanding ?? '',
    work_method: props.analysis?.work_method ?? '',
    work_speed: props.analysis?.work_speed ?? '',
    group_behavior: props.analysis?.group_behavior ?? '',
    remarks: props.analysis?.remarks ?? '',
});
const isSavingPdf = ref(false);

async function prepareForPdf() {
    (window as any).__PDF_READY__ = false;
    document.documentElement.classList.remove('dark');
    await document.fonts?.ready?.catch(() => undefined);

    await Promise.all(
        Array.from(document.images)
            .filter((image) => !image.complete)
            .map(
                (image) =>
                    new Promise<void>((resolve) => {
                        image.addEventListener('load', () => resolve(), { once: true });
                        image.addEventListener('error', () => resolve(), { once: true });
                    }),
            ),
    );

    await new Promise<void>((resolve) => requestAnimationFrame(() => requestAnimationFrame(() => resolve())));
    (window as any).__PDF_READY__ = true;

    if (props.autoPrint) window.setTimeout(() => window.print(), 100);
}

function printDocument() {
    window.print();
}

async function savePdf() {
    if (!props.pdfUrl || isSavingPdf.value) return;
    isSavingPdf.value = true;

    try {
        const response = await fetch(props.pdfUrl, { credentials: 'same-origin', headers: { Accept: 'application/pdf' } });
        if (!response.ok || !(response.headers.get('Content-Type') || '').includes('application/pdf')) {
            throw new Error(`PDF download failed with status ${response.status}`);
        }

        const blob = await response.blob();
        const objectUrl = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = objectUrl;
        link.download = props.filename;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.setTimeout(() => URL.revokeObjectURL(objectUrl), 1000);
    } catch (error) {
        console.error('PDF download failed.', error);
        window.alert('PDF konnte nicht automatisch gespeichert werden. Bitte nutzen Sie Drucken.');
    } finally {
        isSavingPdf.value = false;
    }
}

function closeWindow() {
    window.close();
    window.setTimeout(() => {
        if (window.closed) return;
        window.history.length > 1 ? window.history.back() : window.location.assign(route('participants.list'));
    }, 100);
}

onMounted(prepareForPdf);
</script>

<template>
    <Head :title="filename" />

    <main class="print-root">
        <div class="print-toolbar">
            <div class="truncate text-sm font-semibold text-gray-700">{{ filename }}</div>
            <div class="flex items-center gap-2">
                <button type="button" class="toolbar-button" @click="printDocument">Drucken</button>
                <button type="button" class="toolbar-button" :disabled="!pdfUrl || isSavingPdf" @click="savePdf">
                    {{ isSavingPdf ? 'Speichere...' : 'PDF speichern' }}
                </button>
                <button type="button" class="toolbar-button" @click="closeWindow">Schließen</button>
            </div>
        </div>

        <EntranceAnalysisForm
            v-model="observations"
            :participant="participant"
            :assignments="assignments"
            :teacher-name="teacherName"
            :conducted-at="conductedAt"
        />
    </main>
</template>

<style scoped>
:global(html),
:global(body),
:global(#app) {
    min-height: 100%;
    background: #e5e7eb;
}

@page {
    size: A4;
    margin: 0;
}

.print-root {
    min-height: 100vh;
    padding: 24px 0;
}

.print-toolbar {
    position: sticky;
    top: 0;
    z-index: 10;
    display: flex;
    width: 210mm;
    max-width: calc(100vw - 24px);
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin: 0 auto 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.96);
    padding: 10px 12px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
}

.toolbar-button {
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: #fff;
    padding: 7px 10px;
    color: #1d4ed8;
    font-size: 13px;
    font-weight: 600;
}

.toolbar-button:disabled {
    color: #9ca3af;
}

@media print {
    :global(html),
    :global(body),
    :global(#app) {
        width: 210mm;
        min-height: 297mm;
        background: #fff;
    }

    .print-root {
        padding: 0;
    }

    .print-toolbar {
        display: none;
    }
}
</style>
