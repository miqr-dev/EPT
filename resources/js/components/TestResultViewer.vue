<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    testResult: any;
}>();

const results = computed(() => props.testResult?.result_json || {});

function formatTime(sec: number | null): string {
  if (sec === null || isNaN(sec)) return "–";
  if (sec < 60) return `${sec} Sekunden`;
  const min = Math.round(sec / 60);
  return `${min} Minuten`;
}

function formatQuestionMark(text: string): string {
  if (text.endsWith('?')) {
    return (
      text.slice(0, -1) +
      '<span class="font-bold text-red-600 text-lg">?</span>'
    );
  }
  return text;
}

function isCorrectAnswer(userAnswer: string | undefined, validAnswers: string[]): boolean {
    if (!userAnswer) return false;
    const normalizedUser = userAnswer.trim().replace(",", ".").toLowerCase();
    const normalizedCorrectAnswers = validAnswers.map(a => a.trim().replace(",", ".").toLowerCase());
    return normalizedCorrectAnswers.includes(normalizedUser);
}
</script>

<template>
    <div v-if="results" class="p-6 bg-background border rounded-lg">
        <h2 class="text-xl font-semibold mb-4">Test abgeschlossen!</h2>
        <div class="mb-6 w-full max-w-md">
            <table class="w-full text-sm border rounded-lg overflow-hidden shadow">
                <tbody>
                    <tr class="bg-muted/40">
                        <td class="font-semibold px-3 py-2 w-1/2">Rohwert</td>
                        <td class="px-3 py-2">{{ results.rohwert }} von {{ results.answers.length }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold px-3 py-2">Prozentrang (PR)</td>
                        <td class="px-3 py-2">{{ results.prozentrang }}</td>
                    </tr>
                    <tr class="bg-muted/40">
                        <td class="font-semibold px-3 py-2">T-Wert (Normwert)</td>
                        <td class="px-3 py-2">{{ results.twert }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold px-3 py-2">Benötigte Zeit</td>
                        <td class="px-3 py-2">
                            <span v-if="results.total_time_seconds !== null"
                                :class="results.total_time_seconds > 1800 ? 'text-red-600 font-bold' : ''">
                                {{ formatTime(results.total_time_seconds) }}
                            </span>
                            <span v-else>–</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div>
            <h3 class="font-bold mb-2">Antwort- und Bearbeitungszeit je Frage</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border rounded-lg shadow">
                    <thead class="bg-muted/40">
                        <tr>
                            <th class="px-2 py-1 text-left font-semibold">#</th>
                            <th class="px-2 py-1 text-left font-semibold">Frage</th>
                            <th class="px-2 py-1 text-left font-semibold">Ihre Antwort</th>
                            <th class="px-2 py-1 text-left font-semibold">Richtige Antwort</th>
                            <th class="px-2 py-1 text-left font-semibold">Bearbeitungszeit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(answer, idx) in results.answers" :key="idx"
                            :class="answer.is_correct ? 'bg-green-50' : 'bg-red-50'">
                            <td class="px-2 py-1 font-medium text-muted-foreground">{{ idx + 1 }}</td>
                            <td class="px-2 py-1 align-top">
                                <span v-html="formatQuestionMark(answer.question)"></span>
                                <div v-if="answer.image" class="mt-1">
                                    <img :src="answer.image" alt="Fragebild" class="max-w-[90px] border rounded shadow" />
                                </div>
                            </td>
                            <td class="px-2 py-1">
                                <span class="font-mono">{{ answer.user_answer || '–' }}</span>
                            </td>
                            <td class="px-2 py-1">
                                <span class="font-mono">{{ answer.correct_answers.join(', ') }}</span>
                            </td>
                            <td class="px-2 py-1 text-right text-gray-500 font-mono min-w-[60px]">
                                {{ formatTime(answer.time_seconds) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div v-else>
        <p>Keine Ergebnisdaten gefunden.</p>
    </div>
</template>
