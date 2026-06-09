<script setup lang="ts">
import { LMT_QUESTIONS } from '@/pages/Questions/LMTQuestions';

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

function formatTime(sec: number | null | undefined): string {
    if (sec == null || isNaN(Number(sec))) return '-';
    const totalSeconds = Math.max(0, Number(sec) || 0);
    const minutes = Math.floor(totalSeconds / 60);
    const rest = totalSeconds % 60;
    return `${minutes}:${rest.toString().padStart(2, '0')}`;
}

function formatValue(value: number | string | null | undefined): string {
    if (value == null || value === '') return '-';
    return String(value);
}

const scales = [
    { key: 'L1', label: 'L1' },
    { key: 'L2', label: 'L2' },
    { key: 'F-', label: 'F-' },
    { key: 'F+', label: 'F+' },
];
</script>

<template>
    <div
        v-if="props.results && !props.answersOnly"
        class="lmt-result rounded-lg border bg-background p-6"
        :class="{ 'lmt-result--pdf': props.pdfMode }"
    >
        <h2 v-if="!props.pdfMode" class="mb-4 text-xl font-semibold">Test abgeschlossen!</h2>

        <div class="lmt-time-wrap mb-6 w-full max-w-md">
            <table class="lmt-summary-table w-full overflow-hidden rounded-lg border text-sm shadow-sm">
                <tbody>
                    <tr class="bg-muted/40">
                        <td class="w-1/2 px-3 py-2 font-semibold">Benötigte Zeit</td>
                        <td class="px-3 py-2">{{ formatTime(props.results.total_time_seconds) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="lmt-score-wrap mb-6 overflow-x-auto">
            <table class="lmt-score-table min-w-full rounded-lg border text-sm shadow-sm">
                <thead class="bg-muted/40">
                    <tr>
                        <th class="p-2 text-left"></th>
                        <th v-for="scale in scales" :key="scale.key" class="p-2 text-left">
                            {{ scale.label }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t">
                        <td class="p-2 font-semibold">Rohwert</td>
                        <td v-for="scale in scales" :key="scale.key" class="p-2">
                            {{ formatValue(props.results.group_scores?.[scale.key]) }}
                        </td>
                    </tr>
                    <tr class="border-t">
                        <td class="p-2 font-semibold">T-Wert</td>
                        <td v-for="scale in scales" :key="scale.key" class="p-2">
                            {{ formatValue(props.results.group_t_values?.[scale.key]) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <details
        v-if="props.showAnswers && (!props.pdfMode || props.answersOnly) && props.results?.answers"
        :open="props.answersOnly"
        class="lmt-answer-section mb-6"
    >
        <summary class="cursor-pointer rounded bg-muted/40 px-2 py-1 select-none">Antworten</summary>
        <div class="mt-2 overflow-x-auto">
            <table class="lmt-answer-table min-w-full rounded border text-sm shadow-sm">
                <thead class="bg-muted/40">
                    <tr>
                        <th class="lmt-col-number p-2 text-left">#</th>
                        <th class="lmt-col-question p-2 text-left">Frage</th>
                        <th class="lmt-col-answer p-2 text-left">Antwort</th>
                        <th class="lmt-col-group p-2 text-left">Gruppe</th>
                        <th class="lmt-col-points p-2 text-left">Punkte</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(ans, idx) in props.results.answers" :key="idx" class="border-t">
                        <td class="lmt-col-number p-2">{{ ans.number }}</td>
                        <td class="lmt-col-question p-2">{{ LMT_QUESTIONS[idx].text }}</td>
                        <td class="lmt-col-answer p-2">{{ ans.selected_category ?? '-' }}</td>
                        <td class="lmt-col-group p-2">{{ ans.selected_group ?? '-' }}</td>
                        <td class="lmt-col-points p-2">{{ ans.points ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </details>
</template>

<style scoped>
.lmt-score-table th,
.lmt-score-table td,
.lmt-summary-table td {
    border-bottom: 1px solid #e5e7eb;
}

.lmt-score-table tbody tr:last-child td,
.lmt-summary-table tr:last-child td {
    border-bottom: 0;
}

.lmt-answer-table .lmt-col-number {
    width: 2.75rem;
    min-width: 2.75rem;
    max-width: 2.75rem;
    box-sizing: border-box;
    border-right: 1px solid #d1d5db;
    text-align: center;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
}

.lmt-answer-table .lmt-col-question {
    width: 52%;
}

.lmt-answer-table .lmt-col-answer {
    width: 27%;
}

.lmt-answer-table .lmt-col-group,
.lmt-answer-table .lmt-col-points {
    width: 10%;
}

.lmt-result--pdf {
    border: 0;
    border-radius: 0;
    background: transparent;
    padding: 0;
    color: #111827;
}

.lmt-result--pdf .lmt-time-wrap,
.lmt-result--pdf .lmt-score-wrap {
    max-width: 100%;
    margin-bottom: 16px;
}

.lmt-result--pdf .lmt-time-wrap {
    max-width: 420px;
}

.lmt-result--pdf .lmt-summary-table,
.lmt-result--pdf .lmt-score-table {
    overflow: hidden;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    box-shadow: none;
}

.lmt-result--pdf .lmt-summary-table tr,
.lmt-result--pdf .lmt-score-table tr {
    background: #ffffff;
}

.lmt-result--pdf .lmt-summary-table tr:nth-child(odd),
.lmt-result--pdf .lmt-score-table tbody tr:nth-child(odd) {
    background: #f9fafb;
}

.lmt-result--pdf .lmt-score-table thead tr {
    background: #f3f6fa;
}

.lmt-result--pdf .lmt-summary-table td,
.lmt-result--pdf .lmt-score-table th,
.lmt-result--pdf .lmt-score-table td {
    border-bottom: 1px solid #e5e7eb;
    padding: 12px 16px;
    font-size: 13.5px;
    line-height: 1.35;
}

.lmt-result--pdf .lmt-summary-table td:first-child,
.lmt-result--pdf .lmt-score-table td:first-child,
.lmt-result--pdf .lmt-score-table th {
    color: #111827;
    font-weight: 700;
}

.lmt-result--pdf .lmt-summary-table td:last-child,
.lmt-result--pdf .lmt-score-table td:not(:first-child) {
    color: #1f2937;
    font-weight: 600;
}

.lmt-result--pdf .lmt-score-table tbody tr:last-child td {
    border-bottom: 0;
}

@media print {
    .lmt-result--pdf {
        font-size: 12px;
    }
}
</style>
