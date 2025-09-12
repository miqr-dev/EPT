<script setup lang="ts">
import { computed } from 'vue';
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
} from 'chart.js';
import annotationPlugin from 'chartjs-plugin-annotation';

Chart.register(LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Legend, Title, annotationPlugin);

const props = defineProps<{ results: any }>();

const labels = [
  'Subjektive Bedeutsamkeit der Arbeit',
  'Beruflicher Ehrgeiz',
  'Verausgabungsbereitschaft',
  'Perfektionsstreben',
  'Distanzierungsfähigkeit',
  'Resignationstendenz',
  'Offensive Problembewältigung',
  'Innere Ruhe/Ausgeglichenheit',
  'Erfolgszuversicht im Beruf',
  'Lebenszufriedenheit',
  'Erleben sozialer Unterstützung',
];

const chartData = computed(() => ({
  labels,
  datasets: [
    {
      label: 'Stanine',
      data: props.results?.stanines || props.results?.group_stanines || [],
      borderColor: '#dc2626',
      backgroundColor: '#dc2626',
      tension: 0,
      pointRadius: 4,
      fill: false,
    },
  ],
}));

const chartOptions = {
  responsive: true,
  indexAxis: 'y' as const,
  scales: {
    x: {
      min: 1,
      max: 9,
      ticks: { stepSize: 1 },
    },
  },
  plugins: {
    legend: { display: false },
    annotation: {
      annotations: {
        overlay: {
          type: 'box',
          xMin: 4,
          xMax: 6,
          backgroundColor: 'rgba(100, 100, 100, 0.15)',
          borderWidth: 0,
        },
      },
    },
  },
};
</script>

<template>
  <div class="p-6 bg-background border rounded-lg">
    <div class="mb-6" style="width: 600px; height: 400px">
      <Line :data="chartData" :options="chartOptions" />
    </div>
    <details v-if="results?.answers">
      <summary class="cursor-pointer select-none px-2 py-1 bg-muted/40 rounded">Antworten</summary>
      <div class="overflow-x-auto mt-2">
        <table class="min-w-full text-sm border rounded shadow">
          <thead class="bg-muted/40">
            <tr>
              <th class="p-2 text-left">#</th>
              <th class="p-2 text-left">Antwort</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(ans, idx) in results.answers" :key="idx" class="border-t">
              <td class="p-2">{{ ans.number ?? idx + 1 }}</td>
              <td class="p-2">{{ ans.answer }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </details>
  </div>
</template>

