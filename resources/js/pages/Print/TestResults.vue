<script setup lang="ts">
import PdfTemplate from '@/components/PdfTemplate.vue';
import { Head } from '@inertiajs/vue3';
import { onBeforeUpdate, onMounted, onUnmounted, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        participant: any;
        assignments: any[];
        autoPrint?: boolean;
        filename?: string;
    }>(),
    {
        autoPrint: false,
        filename: 'Testergebnisse.pdf',
    },
);

const sheetRefs = ref<HTMLElement[]>([]);

function setSheetRef(el: Element | null) {
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

function fitSheets() {
    for (const sheet of sheetRefs.value) {
        const frame = sheet.querySelector<HTMLElement>('[data-print-frame]');
        const source = sheet.querySelector<HTMLElement>('[data-print-source]');

        if (!frame || !source) {
            continue;
        }

        const availableWidth = frame.clientWidth || frame.getBoundingClientRect().width;
        const availableHeight = frame.clientHeight || frame.getBoundingClientRect().height;
        const sourceWidth = source.scrollWidth || source.offsetWidth;
        const sourceHeight = source.scrollHeight || source.offsetHeight;

        if (!availableWidth || !availableHeight || !sourceWidth || !sourceHeight) {
            continue;
        }

        const scale = Math.min(availableWidth / sourceWidth, availableHeight / sourceHeight, 1);
        frame.style.setProperty('--result-scale', String(scale));
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
    fitSheets();
    await nextFrame();

    (window as any).__PDF_READY__ = true;

    if (props.autoPrint) {
        window.setTimeout(() => window.print(), 100);
    }
}

function printDocument() {
    window.print();
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
                <button type="button" class="toolbar-button" @click="printDocument">Drucken / PDF speichern</button>
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
    --result-scale: 1;
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
