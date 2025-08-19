<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Line } from 'vue-chartjs';
import {
  Chart,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
  Tooltip,
  Legend,
  Title,
  registerables,
} from 'chart.js';
import annotationPlugin from 'chartjs-plugin-annotation';

Chart.register(LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Legend, Title);
Chart.register(...registerables, annotationPlugin);

type MrtAResult = {
  group_scores: number[];
  group_stanines: number[];
  total_score: number;
  prozentrang: number;
  total_time_seconds?: number;
  answers: Array<{
    number: number;
    user_answer: string | null;
    correct_answers: string[];
    time_seconds: number;
    is_correct: boolean;
  }>;
};

const props = defineProps<{ result: MrtAResult | null }>();
const showDetails = ref(false);

function formatTime(sec: number | null | undefined): string {
  if (sec == null || isNaN(sec)) return '–';
  if (sec < 60) return `${sec} Sekunden`;
  const min = Math.round(sec / 60);
  return `${min} Minuten`;
}

const groupScores = computed(() => props.result?.group_scores ?? []);
const groupStanines = computed(() => props.result?.group_stanines ?? []);
const totalScore = computed(() => props.result?.total_score ?? 0);
const prValue = computed(() => props.result?.prozentrang ?? 0);

const chartData = computed(() => ({
  labels: ['U1', 'U2', 'U3', 'U4', 'U5', 'U6'],
  datasets: [
    {
      label: 'SN',
      data: groupStanines.value,
      borderColor: '#1d4ed8',
      borderWidth: 2,
      tension: 0,
      pointRadius: 6,
      pointBackgroundColor: '#1d4ed8',
      fill: false,
    },
  ],
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
        },
      },
    },
  },
  indexAxis: 'y' as const,
  scales: {
    x: {
      min: 1,
      max: 9,
      ticks: { stepSize: 1 },
    },
  },
};
</script>

<template>
  <Head title="MRT-A Ergebnis" />
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-6">MRT-A Ergebnis</h1>
    <div v-if="props.result" class="flex flex-col items-center">
      <div class="mb-6 w-full max-w-md">
        <table class="w-full text-sm border rounded-lg overflow-hidden shadow">
          <tbody>
            <tr class="bg-muted/40">
              <td class="font-semibold px-3 py-2 w-1/2">Rohwert</td>
              <td class="px-3 py-2">{{ totalScore }} von 60</td>
            </tr>
            <tr>
              <td class="font-semibold px-3 py-2">Prozentrang</td>
              <td class="px-3 py-2">{{ prValue }}</td>
            </tr>
            <tr>
              <td class="font-semibold px-3 py-2">Benötigte Zeit</td>
              <td class="px-3 py-2">{{ formatTime(props.result.total_time_seconds) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <Button v-if="!showDetails" @click="showDetails = true" class="mb-4 px-4 py-2 rounded-lg font-semibold">
        Antwort- und Bearbeitungszeit je Aufgabe anzeigen
      </Button>
      <Button v-else @click="showDetails = false" class="mb-4 px-4 py-2 rounded-lg font-semibold">
        Antwort- und Bearbeitungszeit je Aufgabe verbergen
      </Button>

      <div v-if="showDetails" class="mb-8">
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
              <tr v-for="(ans, idx) in props.result.answers" :key="idx" :class="ans.is_correct ? 'bg-green-50 dark:bg-green-900/50' : 'bg-red-50 dark:bg-red-900/50'">
                <td class="px-2 py-1 font-medium text-muted-foreground">{{ ans.number }}</td>
                <td class="px-2 py-1"><span class="font-mono">{{ ans.user_answer ?? '–' }}</span></td>
                <td class="px-2 py-1"><span class="font-mono">{{ ans.correct_answers.join(', ') }}</span></td>
                <td class="px-2 py-1 text-right text-gray-500 dark:text-gray-400 font-mono min-w-[60px]">
                  {{ formatTime(ans.time_seconds) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="flex flex-row items-center gap-3 mb-8">
        <span class="font-bold text-base mr-3">RW</span>
        <template v-for="(score, i) in groupScores" :key="'rwbox' + i">
          <div class="flex flex-col items-center">
            <div class="w-10 h-10 border-2 border-black flex items-center justify-center text-base font-bold">
              {{ score }}
            </div>
            <span class="text-xs text-gray-700 dark:text-gray-300 mt-1">U{{ i + 1 }}</span>
          </div>
        </template>
        <div class="flex flex-col items-center ml-6">
          <div class="w-10 h-10 border-2 border-black flex items-center justify-center text-base font-bold bg-blue-50 dark:bg-blue-900">
            {{ totalScore }}
          </div>
          <span class="text-xs text-gray-700 dark:text-gray-300 mt-1 font-bold">RW GS</span>
        </div>
        <div class="flex flex-col items-center ml-6">
          <div class="w-10 h-10 border-2 border-black flex items-center justify-center text-base font-bold bg-yellow-50 dark:bg-yellow-900">
            {{ prValue }}
          </div>
          <span class="text-xs text-gray-700 dark:text-gray-300 mt-1 font-bold">PR</span>
        </div>
      </div>
      <div style="width: 480px; height: 320px;">
        <Line :data="chartData" :options="chartOptions" />
      </div>
      <div class="w-full flex justify-center mt-6">
        <div class="w-[400px] h-8 rounded-full bg-gray-200 dark:bg-gray-700 relative overflow-hidden shadow-inner">
          <div class="h-full bg-red-600 transition-all duration-700 flex items-center justify-center" :style="{ width: (prValue || 0) + '%' }">
            <span class="opacity-0">.</span>
          </div>
          <span class="absolute left-0 w-full h-full flex items-center justify-center text-black font-bold" style="top: 0;" v-if="prValue !== null">
            {{ prValue }}%
          </span>
        </div>
      </div>
    </div>
    <div v-else class="text-center text-gray-600">Kein Ergebnis verfügbar.</div>
  </div>
</template>
