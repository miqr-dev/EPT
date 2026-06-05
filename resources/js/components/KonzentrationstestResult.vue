<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        results: any;
        showAnswers?: boolean;
        pdfMode?: boolean;
    }>(),
    {
        showAnswers: true,
        pdfMode: false,
    },
);

const formatTime = (seconds?: number | null) => {
    if (seconds == null) return '–';
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m}:${s.toString().padStart(2, '0')}`;
};

const page1Correct = ['13', '26', '133', '39', '125', '50', '480', '210', '11,25', '242'];
const page2Correct = ['3', '1', '3', '2', '2', '1', '2', '4', '1', '3'];
const page3Correct = ['1', '4', '3', '1', '3', '3', '0', '2', '2', '5', '2', '3', '2', '2', '4', '3', '3', '1', '3', '3', '0', '2'];
const page4Correct = ['12', '11', '13', '10', '11', '9', '16', '14', '13', '10', '15', '15', '12', '13'];
const page5Correct = ['7', '4', '8', '5', '7', '9', '7', '10', '10', '9'];

const correctAnswers = [page1Correct, page2Correct, page3Correct, page4Correct, page5Correct];

const pageTotals = correctAnswers.map((page) => page.length);

const wrongCounts = computed(() => {
    return correctAnswers.map((_, pageIndex) => {
        const answers = props.results[`page${pageIndex + 1}`] ?? [];
        return answers.reduce((count: number, answer: string | number, idx: number) => {
            return count + (isCorrectAnswer(pageIndex, idx, answer) ? 0 : 1);
        }, 0);
    });
});

const combinedPage45Wrong = computed(() => wrongCounts.value[3] + wrongCounts.value[4]);
const combinedPage45Total = pageTotals[3] + pageTotals[4];
const totalWrong = computed(() => wrongCounts.value.reduce((total, count) => total + count, 0));
const totalQuestions = pageTotals.reduce((total, count) => total + count, 0);

const isCorrectAnswer = (pageIndex: number, questionIndex: number, answer: string | number) => {
    const correctAnswer = correctAnswers[pageIndex]?.[questionIndex];
    return String(answer ?? '').trim() === String(correctAnswer ?? '').trim();
};

const getAnswerClass = (pageIndex: number, questionIndex: number, answer: string | number) => {
    return isCorrectAnswer(pageIndex, questionIndex, answer) ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold';
};

const detailRows = computed(() => {
    return correctAnswers.flatMap((answers, pageIndex) => {
        const userAnswers = props.results?.[`page${pageIndex + 1}`] ?? [];
        return answers.map((correctAnswer, questionIndex) => ({
            page: pageIndex + 1,
            number: questionIndex + 1,
            user_answer: userAnswers[questionIndex] ?? '',
            correct_answer: correctAnswer,
            pageIndex,
            questionIndex,
        }));
    });
});
</script>

<template>
    <div class="konz-result rounded-lg border bg-background p-6" :class="{ 'konz-result--pdf': props.pdfMode }">
        <h2 v-if="!props.pdfMode" class="mb-4 text-xl font-semibold">628 Ergebnisse</h2>
        <div class="konz-summary mb-6 w-full max-w-md">
            <table class="konz-summary-table w-full overflow-hidden rounded-lg border text-sm shadow">
                <tbody>
                    <tr class="bg-muted/40">
                        <td class="w-1/2 px-3 py-2 font-semibold">Falsche Antworten</td>
                        <td class="px-3 py-2">{{ results.wrong_count }}</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-semibold">Leistungskategorie</td>
                        <td class="px-3 py-2">{{ results.performance_category.category }} ({{ results.performance_category.range }})</td>
                    </tr>
                    <tr class="bg-muted/40">
                        <td class="px-3 py-2 font-semibold">Benötigte Zeit</td>
                        <td class="px-3 py-2">{{ formatTime(results.total_time_seconds) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="konz-page-breakdown mb-6 w-full max-w-4xl">
            <h3 class="mb-2 font-bold">Falsche Antworten pro Seite</h3>
            <div class="overflow-x-auto">
                <table class="konz-page-table min-w-full rounded-lg border text-sm shadow">
                    <thead class="bg-muted/40">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold">Seite 1</th>
                            <th class="px-3 py-2 text-left font-semibold">Seite 2</th>
                            <th class="px-3 py-2 text-left font-semibold">Seite 3</th>
                            <th class="px-3 py-2 text-left font-semibold">Seiten 4 &amp; 5</th>
                            <th class="px-3 py-2 text-left font-semibold">Gesamt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-3 py-2">
                                {{ wrongCounts[0] }} <span class="text-muted-foreground">/ {{ pageTotals[0] }}</span>
                            </td>
                            <td class="px-3 py-2">
                                {{ wrongCounts[1] }} <span class="text-muted-foreground">/ {{ pageTotals[1] }}</span>
                            </td>
                            <td class="px-3 py-2">
                                {{ wrongCounts[2] }} <span class="text-muted-foreground">/ {{ pageTotals[2] }}</span>
                            </td>
                            <td class="px-3 py-2">
                                {{ combinedPage45Wrong }} <span class="text-muted-foreground">/ {{ combinedPage45Total }}</span>
                            </td>
                            <td class="px-3 py-2">
                                {{ totalWrong }} <span class="text-muted-foreground">/ {{ totalQuestions }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <details v-if="props.showAnswers && !props.pdfMode && detailRows.length" class="mt-4">
            <summary class="cursor-pointer">Antworten anzeigen</summary>
            <div class="mt-3 overflow-x-auto">
                <table class="min-w-full rounded-lg border text-sm shadow">
                    <thead class="bg-muted/40">
                        <tr>
                            <th class="px-2 py-1 text-left font-semibold">Seite</th>
                            <th class="px-2 py-1 text-left font-semibold">Frage #</th>
                            <th class="px-2 py-1 text-left font-semibold">Ihre Antwort</th>
                            <th class="px-2 py-1 text-left font-semibold">Richtige Antwort</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="answer in detailRows" :key="`p${answer.page}-${answer.number}`">
                            <td class="px-2 py-1">{{ answer.page }}</td>
                            <td class="px-2 py-1">{{ answer.number }}</td>
                            <td class="px-2 py-1" :class="getAnswerClass(answer.pageIndex, answer.questionIndex, answer.user_answer)">
                                {{ answer.user_answer || '-' }}
                            </td>
                            <td class="px-2 py-1">{{ answer.correct_answer }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </details>
    </div>
</template>

<style scoped>
.konz-result {
    color: #111827;
}

.konz-summary {
    max-width: 700px;
}

.konz-summary-table td:first-child {
    width: 245px;
}

.konz-summary-table td:last-child {
    white-space: nowrap;
}

.konz-result--pdf {
    border: 0;
    border-radius: 0;
    background: transparent;
    padding: 0;
}

.konz-result--pdf .konz-summary {
    max-width: 700px;
    margin-bottom: 22px;
}

.konz-result--pdf .konz-page-breakdown {
    max-width: 100%;
    margin-bottom: 0;
}

.konz-result--pdf h3 {
    margin-bottom: 10px;
    color: #111827;
    font-size: 17px;
    line-height: 1.35;
    font-weight: 700;
}

.konz-result--pdf .konz-summary-table,
.konz-result--pdf .konz-page-table {
    overflow: hidden;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    border-collapse: separate;
    border-spacing: 0;
    background: #ffffff;
    box-shadow: none;
    color: #111827;
    font-size: 15px;
    line-height: 1.35;
}

.konz-result--pdf .konz-summary-table td,
.konz-result--pdf .konz-page-table th,
.konz-result--pdf .konz-page-table td {
    border-right: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
    padding: 10px 12px;
    vertical-align: middle;
}

.konz-result--pdf .konz-summary-table td:last-child,
.konz-result--pdf .konz-page-table th:last-child,
.konz-result--pdf .konz-page-table td:last-child {
    border-right: 0;
}

.konz-result--pdf .konz-summary-table tr:last-child td,
.konz-result--pdf .konz-page-table tbody tr:last-child td {
    border-bottom: 0;
}

.konz-result--pdf .konz-summary-table tr:nth-child(odd),
.konz-result--pdf .konz-page-table thead tr {
    background: #f3f6fa;
}

.konz-result--pdf .konz-summary-table td:first-child,
.konz-result--pdf .konz-page-table th {
    color: #111827;
    font-weight: 700;
}

.konz-result--pdf .konz-summary-table td:last-child,
.konz-result--pdf .konz-page-table td {
    font-weight: 600;
}

.konz-result--pdf .konz-page-table .text-muted-foreground {
    color: #64748b;
    font-weight: 500;
}
</style>
