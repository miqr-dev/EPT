<script setup lang="ts">
import PdfTemplate from '@/components/PdfTemplate.vue';
import { Head } from '@inertiajs/vue3';
import type { ComponentPublicInstance } from 'vue';
import { onBeforeUpdate, onMounted, onUnmounted, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        participant: any;
        assignments: any[];
        autoPrint?: boolean;
        filename?: string;
        pdfUrl?: string | null;
    }>(),
    {
        autoPrint: false,
        filename: 'Testergebnisse.pdf',
        pdfUrl: null,
    },
);

const sheetRefs = ref<HTMLElement[]>([]);
const isSavingPdf = ref(false);

function setSheetRef(el: Element | ComponentPublicInstance | null) {
    if (el instanceof HTMLElement) {
        sheetRefs.value.push(el);
    }
}

onBeforeUpdate(() => {
    sheetRefs.value = [];
});

function nextFrame() {
    return new Promise<void>((resolve) => requestAnimationFrame(() => resolve()));
}

function delay(ms: number) {
    return new Promise<void>((resolve) => window.setTimeout(resolve, ms));
}

function sheetDimensions(sheet: HTMLElement) {
    const frame = sheet.querySelector<HTMLElement>('[data-print-frame]');
    const source = sheet.querySelector<HTMLElement>('[data-print-source]');

    if (!frame || !source) {
        return null;
    }

    const availableWidth = frame.clientWidth || frame.getBoundingClientRect().width;
    const availableHeight = frame.clientHeight || frame.getBoundingClientRect().height;
    const sourceWidth = source.scrollWidth || source.offsetWidth;
    const sourceHeight = source.scrollHeight || source.offsetHeight;

    if (!availableWidth || !availableHeight || !sourceWidth || !sourceHeight) {
        return null;
    }

    return { availableWidth, availableHeight, sourceWidth, sourceHeight };
}

function fitSheets() {
    const measurements: string[] = [];

    for (const sheet of sheetRefs.value) {
        const frame = sheet.querySelector<HTMLElement>('[data-print-frame]');
        const dimensions = sheetDimensions(sheet);

        if (!frame || !dimensions) {
            continue;
        }

        const { availableWidth, availableHeight, sourceWidth, sourceHeight } = dimensions;
        const scale = Math.min(availableWidth / sourceWidth, availableHeight / sourceHeight, 1);
        frame.style.setProperty('--result-scale', String(scale));
        measurements.push([availableWidth, availableHeight, sourceWidth, sourceHeight, scale].map((value) => value.toFixed(3)).join(':'));
    }

    return measurements.join('|');
}

async function fitSheetsUntilStable() {
    let previousMeasurements = '';
    let stableFrames = 0;

    for (let attempt = 0; attempt < 30 && stableFrames < 3; attempt += 1) {
        await nextFrame();
        const measurements = fitSheets();

        if (measurements && measurements === previousMeasurements) {
            stableFrames += 1;
        } else {
            stableFrames = 0;
            previousMeasurements = measurements;
        }
    }
}

async function prepareForPdf() {
    (window as any).__PDF_READY__ = false;
    document.documentElement.classList.remove('dark');

    if (document.fonts?.ready) {
        await document.fonts.ready.catch(() => undefined);
    }

    await nextFrame();
    await nextFrame();
    await delay(350);
    await fitSheetsUntilStable();

    (window as any).__PDF_READY__ = true;

    if (props.autoPrint) {
        window.setTimeout(() => window.print(), 100);
    }
}

function printDocument() {
    window.print();
}

async function savePdf() {
    if (!props.pdfUrl || isSavingPdf.value) {
        return;
    }

    isSavingPdf.value = true;

    try {
        const response = await fetch(props.pdfUrl, {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/pdf',
            },
        });
        const contentType = response.headers.get('Content-Type') || '';

        if (!response.ok || !contentType.includes('application/pdf')) {
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
        window.alert('PDF konnte nicht automatisch gespeichert werden. Bitte versuchen Sie es erneut oder nutzen Sie Drucken.');
    } finally {
        isSavingPdf.value = false;
    }
}

function closeWindow() {
    window.close();

    window.setTimeout(() => {
        if (window.closed) {
            return;
        }

        if (window.history.length > 1) {
            window.history.back();
            return;
        }

        const listRoute = typeof route === 'function' ? route('participants.list') : '/participants';
        window.location.assign(listRoute);
    }, 100);
}

onMounted(() => {
    prepareForPdf();
    window.addEventListener('resize', fitSheets);
});

onUnmounted(() => {
    window.removeEventListener('resize', fitSheets);
});
</script>

<template>
    <Head :title="filename" />

    <main class="print-root">
        <div class="print-toolbar" aria-label="PDF">
            <div class="truncate text-sm font-semibold text-gray-700">{{ filename }}</div>
            <div class="flex items-center gap-2">
                <button type="button" class="toolbar-button" @click="printDocument">Drucken</button>
                <button type="button" class="toolbar-button" :disabled="!pdfUrl || isSavingPdf" @click="savePdf">
                    {{ isSavingPdf ? 'Speichere...' : 'PDF speichern' }}
                </button>
                <button type="button" class="toolbar-button" @click="closeWindow">Schliessen</button>
            </div>
        </div>

        <section v-if="assignments.length === 0" class="empty-state">Keine exportierbaren Ergebnisse gefunden.</section>

        <section
            v-for="assignment in assignments"
            :key="assignment.id"
            :ref="setSheetRef"
            class="print-sheet"
            :data-test-name="assignment.test?.name"
            :data-test-code="assignment.test?.code"
        >
            <div class="sheet-frame" data-print-frame>
                <div class="result-source" data-print-source>
                    <PdfTemplate :assignment="assignment" :participant="participant" teacher-name="" :show-teacher="false" :show-answers="false" />
                </div>
            </div>
        </section>
    </main>
</template>

<style scoped>
:global(html),
:global(body),
:global(#app) {
    min-height: 100%;
    background: #eef1f5;
}

@page {
    size: A4;
    margin: 0;
}

.print-root {
    min-height: 100vh;
    padding: 24px 0;
    background: #eef1f5;
    color: #111827;
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
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: #ffffff;
    padding: 7px 10px;
    font-size: 13px;
    font-weight: 600;
    color: #1d4ed8;
    transition:
        background-color 120ms ease,
        border-color 120ms ease;
}

.toolbar-button:hover {
    border-color: #93c5fd;
    background: #eff6ff;
}

.toolbar-button:disabled {
    cursor: not-allowed;
    border-color: #e5e7eb;
    background: #f9fafb;
    color: #9ca3af;
}

.print-sheet,
.empty-state {
    width: 210mm;
    height: 297mm;
    margin: 0 auto 18px;
    background: #ffffff;
    box-shadow: 0 14px 36px rgba(15, 23, 42, 0.16);
}

.print-sheet {
    box-sizing: border-box;
    padding: 8mm;
    break-after: page;
    page-break-after: always;
}

.print-sheet:last-child {
    break-after: auto;
    page-break-after: auto;
}

.empty-state {
    display: grid;
    place-items: center;
    padding: 24px;
    font-size: 16px;
    color: #4b5563;
}

.sheet-frame {
    --result-scale: 0.611;
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.result-source {
    width: 1200px;
    transform: scale(var(--result-scale));
    transform-origin: top left;
    background: #ffffff;
}

.print-sheet[data-test-name='LPS-B'] .result-source {
    width: 760px;
}

.print-sheet[data-test-name='LPS-B'] .sheet-frame {
    --result-scale: 0.964;
}

.print-sheet[data-test-name='BRT-A'] .result-source,
.print-sheet[data-test-name='BRT-B'] .result-source {
    width: 820px;
}

.print-sheet[data-test-name='BRT-A'] .sheet-frame,
.print-sheet[data-test-name='BRT-B'] .sheet-frame {
    --result-scale: 0.894;
}

.print-sheet[data-test-name='MRT-A'] .result-source,
.print-sheet[data-test-name='MRT-B'] .result-source {
    width: 900px;
}

.print-sheet[data-test-name='MRT-A'] .sheet-frame,
.print-sheet[data-test-name='MRT-B'] .sheet-frame {
    --result-scale: 0.814;
}

.print-sheet[data-test-name='BT'] .result-source {
    width: 920px;
}

.print-sheet[data-test-name='BT'] .sheet-frame {
    --result-scale: 0.796;
}

.print-sheet[data-test-name='FPI-R'] .result-source,
.print-sheet[data-test-code='FPI-R'] .result-source {
    width: 920px;
}

.print-sheet[data-test-name='FPI-R'] .sheet-frame,
.print-sheet[data-test-code='FPI-R'] .sheet-frame {
    --result-scale: 0.796;
}

.print-sheet[data-test-name='LMT'] .result-source,
.print-sheet[data-test-code='LMT'] .result-source {
    width: 820px;
}

.print-sheet[data-test-name='LMT'] .sheet-frame,
.print-sheet[data-test-code='LMT'] .sheet-frame {
    --result-scale: 0.894;
}

.print-sheet[data-test-name='BIT-2'] .result-source,
.print-sheet[data-test-code='BIT-2'] .result-source {
    width: 760px;
}

.print-sheet[data-test-name='BIT-2'] .sheet-frame,
.print-sheet[data-test-code='BIT-2'] .sheet-frame {
    --result-scale: 0.964;
}

.print-sheet[data-test-name='628'] .result-source,
.print-sheet[data-test-code='628'] .result-source,
.print-sheet[data-test-name='628 Test'] .result-source,
.print-sheet[data-test-code='628 Test'] .result-source,
.print-sheet[data-test-name='628 08.03'] .result-source,
.print-sheet[data-test-code='628 08.03'] .result-source,
.print-sheet[data-test-name='Konzentrationstest'] .result-source,
.print-sheet[data-test-code='Konzentrationstest'] .result-source {
    width: 820px;
}

.print-sheet[data-test-name='628'] .sheet-frame,
.print-sheet[data-test-code='628'] .sheet-frame,
.print-sheet[data-test-name='628 Test'] .sheet-frame,
.print-sheet[data-test-code='628 Test'] .sheet-frame,
.print-sheet[data-test-name='628 08.03'] .sheet-frame,
.print-sheet[data-test-code='628 08.03'] .sheet-frame,
.print-sheet[data-test-name='Konzentrationstest'] .sheet-frame,
.print-sheet[data-test-code='Konzentrationstest'] .sheet-frame {
    --result-scale: 0.894;
}

.print-sheet[data-test-name='AVEM'] .result-source,
.print-sheet[data-test-code='AVEM'] .result-source {
    width: 860px;
}

.print-sheet[data-test-name='AVEM'] .sheet-frame,
.print-sheet[data-test-code='AVEM'] .sheet-frame {
    --result-scale: 0.852;
}

@media print {
    :global(html),
    :global(body),
    :global(#app) {
        width: 210mm;
        min-height: 297mm;
        background: #ffffff;
    }

    .print-root {
        padding: 0;
        background: #ffffff;
    }

    .print-toolbar {
        display: none;
    }

    .print-sheet,
    .empty-state {
        margin: 0;
        box-shadow: none;
    }
}
</style>
