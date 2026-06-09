<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { computed } from 'vue';

interface BrtAnswer {
    question?: string;
    user_answer?: string | null;
    correct_answers?: string[] | string;
    time_seconds?: number | null;
    is_correct?: boolean;
}

interface BrtResultJson {
    rohwert?: number | null;
    prozentrang?: number | string | null;
    twert?: number | string | null;
    total_time_seconds?: number | null;
    answers?: BrtAnswer[];
    [key: string]: any;
}

const props = withDefaults(
    defineProps<{
        results: BrtResultJson | null;
        showAnswers?: boolean;
        editableAnswers?: boolean;
        pdfMode?: boolean;
        answersOnly?: boolean;
    }>(),
    {
        showAnswers: true,
        editableAnswers: false,
        pdfMode: false,
        answersOnly: false,
    },
);

const answerRows = computed(() => props.results?.answers ?? []);
const totalQuestions = computed(() => answerRows.value.length || 16);

function formatTime(seconds?: number | null) {
    if (seconds == null) return '-';
    const totalSeconds = Math.max(0, Number(seconds) || 0);
    const minutes = Math.floor(totalSeconds / 60);
    const rest = totalSeconds % 60;
    return `${minutes}:${rest.toString().padStart(2, '0')}`;
}

function formatValue(value?: number | string | null) {
    if (value == null || value === '') return '-';
    return String(value);
}

function formatAnswers(value?: string[] | string) {
    if (Array.isArray(value)) return value.join(', ');
    return value ?? '-';
}

function resultLabel(isCorrect?: boolean) {
    if (isCorrect === true) return 'Richtig';
    if (isCorrect === false) return 'Falsch';
    return '-';
}
</script>

<template>
    <div v-if="results" class="brt-result" :class="{ 'brt-result--pdf': pdfMode }">
        <div v-if="!answersOnly" class="brt-summary-wrap mb-5 w-full max-w-lg">
            <table class="brt-summary-table w-full border-collapse overflow-hidden rounded-lg border text-sm shadow-sm">
                <tbody>
                    <tr class="bg-muted/40">
                        <td class="w-1/2 px-3 py-2 font-semibold">Rohwert</td>
                        <td class="px-3 py-2">{{ formatValue(results.rohwert) }} / {{ totalQuestions }}</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-semibold">Prozentrang (PR)</td>
                        <td class="px-3 py-2">{{ formatValue(results.prozentrang) }}</td>
                    </tr>
                    <tr class="bg-muted/40">
                        <td class="px-3 py-2 font-semibold">T-Wert (Normwert)</td>
                        <td class="px-3 py-2">{{ formatValue(results.twert) }}</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-semibold">Benötigte Zeit</td>
                        <td class="px-3 py-2">{{ formatTime(results.total_time_seconds) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <details v-if="showAnswers && answerRows.length" :open="answersOnly" class="brt-answer-section mt-4">
            <summary class="cursor-pointer rounded bg-muted/40 px-2 py-1 font-semibold select-none">Antworten</summary>
            <div class="overflow-x-auto">
                <table class="brt-answer-table min-w-full border-collapse rounded-lg border text-sm shadow-sm">
                    <thead class="bg-muted/40">
                        <tr>
                            <th class="brt-col-number px-2 py-1 text-left font-semibold">#</th>
                            <th class="brt-col-question px-2 py-1 text-left font-semibold">Aufgabe</th>
                            <th class="brt-col-answer px-2 py-1 text-left font-semibold">Antwort</th>
                            <th class="brt-col-correct px-2 py-1 text-left font-semibold">Richtige Antwort</th>
                            <th class="brt-col-result px-2 py-1 text-left font-semibold">Ergebnis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(answer, index) in answerRows" :key="index" :class="answer.is_correct ? 'bg-green-50' : 'bg-red-50'">
                            <td class="brt-col-number px-2 py-1 align-top font-medium text-muted-foreground">{{ index + 1 }}</td>
                            <td class="brt-col-question px-2 py-1 align-top">{{ answer.question || '-' }}</td>
                            <td class="brt-col-answer px-2 py-1 align-top">
                                <Input
                                    v-if="editableAnswers"
                                    :model-value="answer.user_answer ?? ''"
                                    class="w-28"
                                    @update:model-value="(value) => (answer.user_answer = String(value ?? ''))"
                                />
                                <span v-else>{{ answer.user_answer || '-' }}</span>
                            </td>
                            <td class="brt-col-correct px-2 py-1 align-top font-mono">{{ formatAnswers(answer.correct_answers) }}</td>
                            <td
                                class="brt-col-result px-2 py-1 align-top font-semibold"
                                :class="answer.is_correct ? 'text-green-700' : 'text-red-700'"
                            >
                                {{ resultLabel(answer.is_correct) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </details>
    </div>
</template>

<style scoped>
.brt-answer-table th,
.brt-answer-table td {
    border-bottom: 1px solid #e5e7eb;
}

.brt-answer-table tbody tr:last-child td {
    border-bottom: 0;
}

.brt-answer-table .brt-col-number {
    width: 2.75rem;
    min-width: 2.75rem;
    max-width: 2.75rem;
    box-sizing: border-box;
    border-right: 1px solid #d1d5db;
    text-align: center;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
}

.brt-answer-table .brt-col-question {
    width: 46%;
}

.brt-answer-table .brt-col-answer,
.brt-answer-table .brt-col-correct {
    width: 19%;
}

.brt-answer-table .brt-col-result {
    width: 15%;
}

.brt-result--pdf {
    color: #111827;
}

.brt-result--pdf .brt-summary-wrap {
    max-width: 100%;
    margin-bottom: 0;
}

.brt-result--pdf .brt-summary-table {
    border: 1px solid #d1d5db;
    border-radius: 0;
    box-shadow: none;
}

.brt-result--pdf .brt-summary-table tr {
    background: #ffffff;
}

.brt-result--pdf .brt-summary-table tr:nth-child(odd) {
    background: #f9fafb;
}

.brt-result--pdf .brt-summary-table td {
    border-bottom: 1px solid #e5e7eb;
    padding: 10px 14px;
    font-size: 13px;
}

.brt-result--pdf .brt-summary-table tr:last-child td {
    border-bottom: 0;
}

@media print {
    .brt-result {
        font-size: 12px;
    }

    .brt-answer-section {
        break-inside: avoid;
    }

    .brt-answer-table {
        font-size: 11px;
        line-height: 1.25;
    }

    .brt-answer-table th,
    .brt-answer-table td {
        padding: 4px 6px;
    }
}
</style>
