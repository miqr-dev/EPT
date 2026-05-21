<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        results: any;
        showAnswers?: boolean;
    }>(),
    {
        showAnswers: true,
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
    <div class="rounded-lg border bg-background p-6">
        <h2 class="mb-4 text-xl font-semibold">628 Ergebnisse</h2>
        <div class="mb-6 w-full max-w-md">
            <table class="w-full overflow-hidden rounded-lg border text-sm shadow">
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

        <div class="mb-6 w-full max-w-4xl">
            <h3 class="mb-2 font-bold">Falsche Antworten pro Seite</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full rounded-lg border text-sm shadow">
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

        <details v-if="showAnswers && detailRows.length" class="mt-4">
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
