<script setup lang="ts">
import TestResultViewer from '@/components/TestResultViewer.vue';

withDefaults(
    defineProps<{
        assignment: any;
        participant: any;
        teacherName: string;
        showTeacher?: boolean;
        showAnswers?: boolean;
    }>(),
    {
        showAnswers: false,
    },
);

const testDate = new Date().toLocaleDateString('de-DE', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
});

const shortTestDate = new Date().toLocaleDateString('de-DE', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
});

function isBrtTest(assignment: any) {
    return ['BRT-A', 'BRT-B'].includes(String(assignment?.test?.name ?? '').trim());
}

function isMrtTest(assignment: any) {
    return ['MRT-A', 'MRT-B'].includes(String(assignment?.test?.name ?? '').trim());
}

function isBtTest(assignment: any) {
    return String(assignment?.test?.name ?? '').trim() === 'BT';
}

function isFpiRTest(assignment: any) {
    return [assignment?.test?.name, assignment?.test?.code].some((value) => String(value ?? '').trim() === 'FPI-R');
}

function displayDate(assignment: any) {
    return isBrtTest(assignment) ? shortTestDate : testDate;
}
</script>

<template>
    <div
        class="pdf-template bg-white p-8 font-sans text-black"
        :class="{
            'pdf-template--compact': assignment?.test?.name === 'LPS-B',
            'pdf-template--brt': isBrtTest(assignment),
            'pdf-template--mrt': isMrtTest(assignment),
            'pdf-template--bt': isBtTest(assignment),
            'pdf-template--fpi': isFpiRTest(assignment),
        }"
    >
        <!-- Header -->
        <div class="pdf-header flex items-center justify-between border-b-2 border-gray-200 pb-4">
            <div>
                <h1 class="text-3xl font-bold">Testergebnis</h1>
                <p class="text-gray-600">{{ assignment.test.name }}</p>
            </div>
            <div class="pdf-date text-right">
                <p class="text-gray-600">Datum: {{ displayDate(assignment) }}</p>
            </div>
        </div>

        <!-- Participant and Teacher Info -->
        <div class="participant-grid mt-8 grid grid-cols-2 gap-4">
            <div>
                <h2 class="mb-2 text-xl font-semibold">Teilnehmer/in</h2>
                <p v-if="participant">
                    {{ participant.name }}
                </p>
                <p v-else>N/A</p>
            </div>
            <div v-if="showTeacher">
                <h2 class="mb-2 text-xl font-semibold">Prüfer/in</h2>
                <p>{{ teacherName }}</p>
            </div>
        </div>

        <!-- Results -->
        <div class="mt-8">
            <h2 class="mb-4 text-xl font-semibold">Auswertung</h2>
            <div class="result-panel rounded-lg border bg-gray-50 p-4">
                <TestResultViewer
                    v-if="assignment && assignment.results.length > 0"
                    :test="assignment.test"
                    :model-value="assignment.results[0].result_json"
                    :participant-profile="participant?.participant_profile"
                    :manual-scores="assignment.results[0].manual_scores ?? []"
                    :show-answers="showAnswers"
                    :pdf-mode="true"
                    :force-open-answers="showAnswers && assignment.test.name === 'BT'"
                />
                <div v-else class="text-center text-gray-500">
                    <p>Keine Ergebnisse verfügbar.</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <!-- <div class="mt-12 text-center text-gray-500 text-sm">
      <p>Dieses Dokument wurde automatisch generiert.</p>
    </div> -->
    </div>
</template>

<style scoped>
.pdf-template--compact {
    padding: 18px;
}

.pdf-template--compact h1 {
    font-size: 1.5rem;
    line-height: 2rem;
}

.pdf-template--compact .mt-8 {
    margin-top: 1rem;
}

.pdf-template--compact .mb-4 {
    margin-bottom: 0.5rem;
}

.pdf-template--compact h2 {
    font-size: 1rem;
    line-height: 1.5rem;
}

.pdf-template--brt {
    padding: 22px;
}

.pdf-template--brt .pdf-header {
    align-items: flex-start;
    gap: 24px;
    padding-bottom: 14px;
}

.pdf-template--brt .pdf-date {
    flex-shrink: 0;
    min-width: max-content;
    white-space: nowrap;
}

.pdf-template--brt h1 {
    font-size: 1.625rem;
    line-height: 2rem;
}

.pdf-template--brt .mt-8 {
    margin-top: 1.25rem;
}

.pdf-template--brt .participant-grid {
    display: block;
}

.pdf-template--brt .mb-4 {
    margin-bottom: 0.75rem;
}

.pdf-template--brt h2 {
    font-size: 1.125rem;
    line-height: 1.5rem;
}

.pdf-template--brt .result-panel {
    border: 0;
    background: transparent;
    padding: 0;
}

.pdf-template--mrt {
    padding: 22px 24px;
}

.pdf-template--mrt .pdf-header {
    align-items: flex-start;
    gap: 24px;
    padding-bottom: 14px;
}

.pdf-template--mrt .pdf-date {
    flex-shrink: 0;
    min-width: max-content;
    white-space: nowrap;
}

.pdf-template--mrt h1 {
    font-size: 1.625rem;
    line-height: 2rem;
}

.pdf-template--mrt .mt-8 {
    margin-top: 1.25rem;
}

.pdf-template--mrt .participant-grid {
    display: block;
}

.pdf-template--mrt .mb-4 {
    margin-bottom: 0.75rem;
}

.pdf-template--mrt h2 {
    font-size: 1.125rem;
    line-height: 1.5rem;
}

.pdf-template--mrt .result-panel {
    border: 0;
    background: transparent;
    padding: 0;
}

.pdf-template--bt {
    padding: 20px 22px;
}

.pdf-template--bt .pdf-header {
    align-items: flex-start;
    gap: 24px;
    padding-bottom: 12px;
}

.pdf-template--bt .pdf-date {
    flex-shrink: 0;
    min-width: max-content;
    white-space: nowrap;
}

.pdf-template--bt h1 {
    font-size: 1.55rem;
    line-height: 1.95rem;
}

.pdf-template--bt .mt-8 {
    margin-top: 1rem;
}

.pdf-template--bt .participant-grid {
    display: block;
}

.pdf-template--bt .mb-4 {
    margin-bottom: 0.65rem;
}

.pdf-template--bt h2 {
    font-size: 1.075rem;
    line-height: 1.45rem;
}

.pdf-template--bt .result-panel {
    border: 0;
    background: transparent;
    padding: 0;
}

.pdf-template--fpi {
    padding: 18px 20px;
}

.pdf-template--fpi .pdf-header {
    align-items: flex-start;
    gap: 24px;
    padding-bottom: 12px;
}

.pdf-template--fpi .pdf-date {
    flex-shrink: 0;
    min-width: max-content;
    white-space: nowrap;
}

.pdf-template--fpi h1 {
    font-size: 1.55rem;
    line-height: 1.95rem;
}

.pdf-template--fpi .mt-8 {
    margin-top: 1rem;
}

.pdf-template--fpi .participant-grid {
    display: block;
}

.pdf-template--fpi .mb-4 {
    margin-bottom: 0.65rem;
}

.pdf-template--fpi h2 {
    font-size: 1.075rem;
    line-height: 1.45rem;
}

.pdf-template--fpi .result-panel {
    border: 0;
    background: transparent;
    padding: 0;
}
</style>
