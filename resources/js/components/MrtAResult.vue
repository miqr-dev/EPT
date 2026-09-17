<script setup lang="ts">
import MrtResultChart from '@/components/MrtResultChart.vue';
import { useMrtA } from '@/composables/useMrtA';
import { ref } from 'vue';

const props = withDefaults(
    defineProps<{
        results: any;
        showAnswers?: boolean;
        pdfMode?: boolean;
        answersOnly?: boolean;
    }>(),
    {
        showAnswers: true,
        pdfMode: false,
        answersOnly: false,
    },
);

const showDetails = ref(false);
const { mrtQuestions: mrtOptionQuestions } = useMrtA();

function formatTime(sec: number | null | undefined): string {
    const totalSeconds = Number(sec);
    if (sec == null || Number.isNaN(totalSeconds)) return '–';
    const safeSeconds = Math.max(0, Math.round(totalSeconds));
    const min = Math.floor(safeSeconds / 60);
    const seconds = safeSeconds % 60;
    return `${min}:${seconds.toString().padStart(2, '0')}`;
}

function optionText(questionIndex: number, answer: string | null | undefined): string {
    const optionIndex = ['A', 'B', 'C', 'D'].indexOf(
        String(answer ?? '')
            .trim()
            .toUpperCase(),
    );
    return optionIndex >= 0 ? (mrtOptionQuestions[questionIndex]?.options?.[optionIndex] ?? '-') : '-';
}

function correctOptionText(questionIndex: number): string {
    return (mrtQuestions[questionIndex]?.correct ?? []).map((answer) => optionText(questionIndex, answer)).join(', ') || '-';
}

// U1..U6 explanation lines
const uExplanations = [
    'U1 – Dehnung',
    'U2 – Kürzung',
    'U3 – Konsonantenverwechslung',
    'U4 – Vokalverwechslung',
    'U5 – Groß- und Kleinschreibung, Getrennt- und Zusammenschreibung',
    'U6 – Fremdwörter',
];

const mrtQuestions = [
    { number: 1, correct: ['D'] },
    { number: 2, correct: ['A'] },
    { number: 3, correct: ['C'] },
    { number: 4, correct: ['C'] },
    { number: 5, correct: ['A'] },
    { number: 6, correct: ['B'] },
    { number: 7, correct: ['C'] },
    { number: 8, correct: ['D'] },
    { number: 9, correct: ['C', 'D'] },
    { number: 10, correct: ['D'] },
    { number: 11, correct: ['D'] },
    { number: 12, correct: ['C'] },
    { number: 13, correct: ['D'] },
    { number: 14, correct: ['D'] },
    { number: 15, correct: ['B'] },
    { number: 16, correct: ['A'] },
    { number: 17, correct: ['A'] },
    { number: 18, correct: ['D'] },
    { number: 19, correct: ['B'] },
    { number: 20, correct: ['B'] },
    { number: 21, correct: ['A'] },
    { number: 22, correct: ['A'] },
    { number: 23, correct: ['C'] },
    { number: 24, correct: ['B'] },
    { number: 25, correct: ['C'] },
    { number: 26, correct: ['D'] },
    { number: 27, correct: ['D'] },
    { number: 28, correct: ['D'] },
    { number: 29, correct: ['B'] },
    { number: 30, correct: ['C'] },
    { number: 31, correct: ['C'] },
    { number: 32, correct: ['B'] },
    { number: 33, correct: ['A'] },
    { number: 34, correct: ['B'] },
    { number: 35, correct: ['D'] },
    { number: 36, correct: ['B'] },
    { number: 37, correct: ['A'] },
    { number: 38, correct: ['B'] },
    { number: 39, correct: ['D'] },
    { number: 40, correct: ['D'] },
    { number: 41, correct: ['B'] },
    { number: 42, correct: ['A'] },
    { number: 43, correct: ['A'] },
    { number: 44, correct: ['C'] },
    { number: 45, correct: ['B'] },
    { number: 46, correct: ['A'] },
    { number: 47, correct: ['B'] },
    { number: 48, correct: ['C'] },
    { number: 49, correct: ['A'] },
    { number: 50, correct: ['D'] },
    { number: 51, correct: ['D'] },
    { number: 52, correct: ['B'] },
    { number: 53, correct: ['D'] },
    { number: 54, correct: ['A', 'C'] },
    { number: 55, correct: ['A', 'C'] },
    { number: 56, correct: ['B'] },
    { number: 57, correct: ['D'] },
    { number: 58, correct: ['C'] },
    { number: 59, correct: ['D'] },
    { number: 60, correct: ['D'] },
];
</script>

<template>
    <div class="mrt-result rounded-lg border bg-background p-6" :class="{ 'mrt-result--pdf': pdfMode }">
        <h2 v-if="!answersOnly" class="mb-4 text-xl font-semibold">Test abgeschlossen!</h2>
        <div v-if="!answersOnly" class="mrt-summary mb-6 w-full max-w-md">
            <table class="w-full overflow-hidden rounded-lg border text-sm shadow">
                <tbody>
                    <tr class="bg-muted/40">
                        <td class="w-1/2 px-3 py-2 font-semibold">Rohwert</td>
                        <td class="px-3 py-2">{{ results.total_score }} von 60</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-semibold">Benötigte Zeit</td>
                        <td class="px-3 py-2">
                            <span
                                v-if="results.total_time_seconds !== null"
                                :class="results.total_time_seconds > 1800 ? 'font-bold text-red-600' : ''"
                            >
                                {{ formatTime(results.total_time_seconds) }}
                            </span>
                            <span v-else>–</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <button v-if="!answersOnly && !pdfMode && !showDetails" @click="showDetails = true" class="mb-4 rounded-lg px-4 py-2 font-semibold">
            Antworten je Aufgabe anzeigen
        </button>
        <button v-else-if="!answersOnly && !pdfMode" @click="showDetails = false" class="mb-4 rounded-lg px-4 py-2 font-semibold">
            Antworten je Aufgabe verbergen
        </button>
        <div v-if="showAnswers && (answersOnly || (!pdfMode && showDetails))">
            <h3 class="mb-2 font-bold">Antworten je Aufgabe</h3>
            <div class="overflow-x-auto">
                <table class="mrt-answer-table min-w-full rounded-lg border text-sm shadow">
                    <thead class="bg-muted/40">
                        <tr>
                            <th class="mrt-col-number px-2 py-1 text-left font-semibold">#</th>
                            <th class="px-2 py-1 text-left font-semibold">Ihre Auswahl</th>
                            <th class="px-2 py-1 text-left font-semibold">Richtige Antwort(en)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(answer, idx) in results.answers"
                            :key="idx"
                            :class="answer.is_correct ? 'bg-green-50 dark:bg-green-900/50' : 'bg-red-50 dark:bg-red-900/50'"
                        >
                            <td class="mrt-col-number px-2 py-1 font-medium text-muted-foreground">{{ answer.number }}</td>
                            <td class="px-2 py-1">
                                {{ optionText(idx, answer.user_answer) }}
                            </td>
                            <td class="px-2 py-1">
                                {{ correctOptionText(idx) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="!answersOnly">
            <div class="mrt-chart-section my-10 flex w-full flex-col items-center justify-center">
                <div class="mrt-chart-grid grid w-full max-w-[1080px] grid-cols-1 items-start gap-6 lg:grid-cols-[740px_minmax(0,1fr)]">
                    <div class="mrt-chart-panel w-full">
                        <MrtResultChart
                            :group-scores="results.group_scores"
                            :group-stanines="results.group_stanines"
                            :total-score="results.total_score"
                            :prozentrang="results.prozentrang"
                            :pdf-mode="pdfMode"
                        />
                    </div>

                    <aside class="mrt-hints-card rounded-lg border bg-white p-4 shadow-sm dark:bg-neutral-900">
                        <h4 class="mb-3 text-sm font-semibold">Hinweise</h4>
                        <ul class="space-y-2 text-sm leading-5">
                            <li v-for="(text, idx) in uExplanations" :key="'ux-' + idx" class="flex">
                                <span class="w-10 font-semibold">U{{ idx + 1 }}</span>
                                <span class="mx-2">–</span>
                                <span class="flex-1">{{ text.split('–')[1]?.trim() || text }}</span>
                            </li>
                        </ul>
                    </aside>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.mrt-result {
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
}

.mrt-result--pdf {
    border-color: #e5e7eb;
    border-radius: 8px;
    background: #ffffff;
    padding: 18px;
    box-shadow: none;
}

.mrt-result--pdf h2 {
    margin-bottom: 12px;
    font-size: 15px;
    line-height: 1.35;
}

.mrt-result--pdf .mrt-summary {
    max-width: 360px;
    margin-bottom: 16px;
}

.mrt-result--pdf .mrt-summary table {
    border-color: #e5e7eb;
    box-shadow: none;
}

.mrt-result--pdf .mrt-summary td {
    padding-top: 7px;
    padding-bottom: 7px;
}

.mrt-result--pdf .mrt-chart-section {
    align-items: stretch;
    margin-top: 16px;
    margin-bottom: 0;
}

.mrt-chart-panel {
    min-width: 0;
}

@media (min-width: 1024px) {
    .mrt-hints-card {
        margin-top: 28px;
    }
}

.mrt-result--pdf .mrt-chart-grid {
    display: grid;
    grid-template-columns: minmax(0, 620px) minmax(210px, 1fr);
    gap: 18px;
    max-width: 100%;
}

.mrt-result--pdf .mrt-chart-panel {
    position: relative;
    width: 100%;
}

.mrt-result--pdf .mrt-hints-card {
    margin-top: 24px;
    border-color: #e5e7eb;
    border-radius: 6px;
    background: #ffffff;
    padding: 14px 16px 15px;
    box-shadow: none;
}

.mrt-result--pdf .mrt-hints-card h4 {
    margin-bottom: 10px;
    font-size: 13.5px;
    line-height: 1.25;
    color: #111827;
}

.mrt-result--pdf .mrt-hints-card ul {
    display: block;
    font-size: 12.5px;
    line-height: 1.45;
    color: #111827;
}

.mrt-result--pdf .mrt-hints-card li {
    align-items: flex-start;
}

.mrt-result--pdf .mrt-hints-card li + li {
    margin-top: 7px;
}

.mrt-result--pdf .mrt-hints-card li span:first-child {
    width: 34px;
    flex: 0 0 34px;
    color: #111827;
}

.mrt-result--pdf .mrt-hints-card li span:nth-child(2) {
    margin-left: 2px;
    margin-right: 8px;
    color: #6b7280;
}

.mrt-answer-table .mrt-col-number {
    width: 2.75rem;
    min-width: 2.75rem;
    max-width: 2.75rem;
    box-sizing: border-box;
    border-right: 1px solid #d1d5db;
    text-align: center;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
}

@media print {
    .mrt-result--pdf {
        break-inside: avoid;
    }
}
</style>
