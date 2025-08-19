<script setup lang="ts">
import { ref, computed } from 'vue';
import { Line } from 'vue-chartjs';
import {
  Chart, LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Legend, Title, registerables
} from 'chart.js';
import annotationPlugin from 'chartjs-plugin-annotation';

Chart.register(LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Legend, Title)
Chart.register(...registerables, annotationPlugin)

const props = defineProps<{
  results: any;
}>();

const showDetails = ref(false);

function formatTime(sec: number | null): string {
  if (sec === null || isNaN(sec)) return "–";
  if (sec < 60) return `${sec} Sekunden`;
  const min = Math.round(sec / 60);
  return `${min} Minuten`;
}

const groupLabels = ['U1', 'U2', 'U3', 'U4', 'U5', 'U6'];

const chartData = computed(() => ({
  labels: groupLabels,
  datasets: [
    {
      label: 'SN',
      data: props.results.group_stanines,
      borderColor: '#1d4ed8',
      backgroundColor: '#1d4ed8',
      tension: 0,
      pointRadius: 6,
      pointBackgroundColor: '#1d4ed8',
      fill: false
    }
  ]
}));

const chartOptions = {
  responsive: true,
  plugins: {
    legend: { display: false },
    title: { display: false },
    annotation: {
      annotations: {
        rangeBox: {
          type: 'box',
          xMin: 4,
          xMax: 6,
          backgroundColor: 'rgba(144,238,144,0.3)',
          borderWidth: 0,
        }
      }
    }
  },
  indexAxis: 'y',
  scales: {
    x: {
      min: 1,
      max: 9,
      ticks: { stepSize: 1 }
    }
  }
};

const mrtQuestions = [
  { number: 1, correct: ["D"] }, { number: 2, correct: ["A"] }, { number: 3, correct: ["C"] },
  { number: 4, correct: ["C"] }, { number: 5, correct: ["A"] }, { number: 6, correct: ["B"] },
  { number: 7, correct: ["C"] }, { number: 8, correct: ["D"] }, { number: 9, correct: ["C", "D"] },
  { number: 10, correct: ["D"] }, { number: 11, correct: ["D"] }, { number: 12, correct: ["C"] },
  { number: 13, correct: ["D"] }, { number: 14, correct: ["D"] }, { number: 15, correct: ["B"] },
  { number: 16, correct: ["A"] }, { number: 17, correct: ["A"] }, { number: 18, correct: ["D"] },
  { number: 19, correct: ["B"] }, { number: 20, correct: ["B"] }, { number: 21, correct: ["A"] },
  { number: 22, correct: ["A"] }, { number: 23, correct: ["C"] }, { number: 24, correct: ["B"] },
  { number: 25, correct: ["C"] }, { number: 26, correct: ["D"] }, { number: 27, correct: ["D"] },
  { number: 28, correct: ["D"] }, { number: 29, correct: ["B"] }, { number: 30, correct: ["C"] },
  { number: 31, correct: ["C"] }, { number: 32, correct: ["B"] }, { number: 33, correct: ["A"] },
  { number: 34, correct: ["B"] }, { number: 35, correct: ["D"] }, { number: 36, correct: ["B"] },
  { number: 37, correct: ["A"] }, { number: 38, correct: ["B"] }, { number: 39, correct: ["D"] },
  { number: 40, correct: ["D"] }, { number: 41, correct: ["B"] }, { number: 42, correct: ["A"] },
  { number: 43, correct: ["A"] }, { number: 44, correct: ["C"] }, { number: 45, correct: ["B"] },
  { number: 46, correct: ["A"] }, { number: 47, correct: ["B"] }, { number: 48, correct: ["C"] },
  { number: 49, correct: ["A"] }, { number: 50, correct: ["D"] }, { number: 51, correct: ["D"] },
  { number: 52, correct: ["B"] }, { number: 53, correct: ["D"] }, { number: 54, correct: ["A", "C"] },
  { number: 55, correct: ["A", "C"] }, { number: 56, correct: ["B"] }, { number: 57, correct: ["D"] },
  { number: 58, correct: ["C"] }, { number: 59, correct: ["D"] }, { number: 60, correct: ["D"] }
];

</script>

<template>
  <div class="p-6 bg-background border rounded-lg">
    <h2 class="text-xl font-semibold mb-4">Test abgeschlossen!</h2>
    <div class="mb-6 w-full max-w-md">
      <table class="w-full text-sm border rounded-lg overflow-hidden shadow">
        <tbody>
          <tr class="bg-muted/40">
            <td class="font-semibold px-3 py-2 w-1/2">Rohwert</td>
            <td class="px-3 py-2">{{ results.total_score }} von 60</td>
          </tr>
          <tr>
            <td class="font-semibold px-3 py-2">Benötigte Zeit</td>
            <td class="px-3 py-2">
              <span v-if="results.total_time_seconds !== null" :class="results.total_time_seconds > 1800 ? 'text-red-600 font-bold' : ''">
                {{ formatTime(results.total_time_seconds) }}
              </span>
              <span v-else>–</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <button v-if="!showDetails" @click="showDetails = true" class="mb-4 px-4 py-2 rounded-lg font-semibold">
      Antwort- und Bearbeitungszeit je Aufgabe anzeigen
    </button>
    <button v-else @click="showDetails = false" class="mb-4 px-4 py-2 rounded-lg font-semibold">
      Antwort- und Bearbeitungszeit je Aufgabe verbergen
    </button>
    <div v-if="showDetails">
      <h3 class="font-bold mb-2">Antwort- und Bearbeitungszeit je Aufgabe</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm border rounded-lg shadow">
          <thead class="bg-muted/40">
            <tr>
              <th class="px-2 py-1 text-left font-semibold">#</th>
              <th class="px-2 py-1 text-left font-semibold">Ihre Auswahl</th>
              <th class="px-2 py-1 text-left font-semibold">Richtige Antwort(en)</th>
              <th class="px-2 py-1 text-left font-semibold">Bearbeitungszeit</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(answer, idx) in results.answers" :key="idx" :class="answer.is_correct
              ? 'bg-green-50 dark:bg-green-900/50'
              : 'bg-red-50 dark:bg-red-900/50'">
              <td class="px-2 py-1 font-medium text-muted-foreground">{{ answer.number }}</td>
              <td class="px-2 py-1">
                <span class="font-mono">
                  {{ answer.user_answer ? answer.user_answer : '–' }}
                </span>
              </td>
              <td class="px-2 py-1">
                <span class="font-mono">
                  {{ mrtQuestions[idx].correct.join(', ') }}
                </span>
              </td>
              <td class="px-2 py-1 text-right text-gray-500 dark:text-gray-400 font-mono min-w-[60px]">
                {{ formatTime(answer.time_seconds) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div>
      <div class="flex flex-col items-center justify-center my-10 w-full">
        <div class="flex flex-row items-center gap-3 mb-8">
          <span class="font-bold text-base mr-3">RW</span>
          <template v-for="(score, i) in results.group_scores" :key="'rwbox' + i">
            <div class="flex flex-col items-center">
              <div class="w-10 h-10 border-2 border-black flex items-center justify-center text-base font-bold">
                {{ score }}
              </div>
              <span class="text-xs text-gray-700 dark:text-gray-300 mt-1">U{{ i + 1 }}</span>
            </div>
          </template>
          <div class="flex flex-col items-center ml-6">
            <div class="w-10 h-10 border-2 border-black flex items-center justify-center text-base font-bold bg-blue-50 dark:bg-blue-900">
              {{ results.total_score }}
            </div>
            <span class="text-xs text-gray-700 dark:text-gray-300 mt-1 font-bold">RW GS</span>
          </div>
          <div class="flex flex-col items-center ml-6">
            <div class="w-10 h-10 border-2 border-black flex items-center justify-center text-base font-bold bg-yellow-50 dark:bg-yellow-900">
              {{ results.prozentrang }}
            </div>
            <span class="text-xs text-gray-700 dark:text-gray-300 mt-1 font-bold">PR</span>
          </div>
        </div>
        <div style="width: 480px; height: 320px;">
          <Line :data="chartData" :options="chartOptions" />
        </div>
        <div class="w-full flex justify-center mt-6">
          <div class="w-[400px] h-8 rounded-full bg-gray-200 dark:bg-gray-700 relative overflow-hidden shadow-inner">
            <div class="h-full bg-red-600 transition-all duration-700 flex items-center justify-center"
              :style="{ width: (results.prozentrang || 0) + '%' }">
              <span class="opacity-0">.</span>
            </div>
            <span class="absolute left-0 w-full h-full flex items-center justify-center text-black font-bold"
              style="top: 0;" v-if="results.prozentrang !== null">
              {{ results.prozentrang }}%
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
