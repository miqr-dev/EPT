<script setup lang="ts">
import { CategoryScale, Chart, Legend, LineElement, LinearScale, PointElement, Title, Tooltip, registerables } from 'chart.js';
import annotationPlugin from 'chartjs-plugin-annotation';
import { computed, ref } from 'vue';
import { Line } from 'vue-chartjs';

Chart.register(LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Legend, Title);
Chart.register(...registerables, annotationPlugin);

const props = withDefaults(
    defineProps<{
        results: any;
        pdfMode?: boolean;
    }>(),
    {
        pdfMode: false,
    },
);

const showDetails = ref(false);

function formatTime(sec: number | null | undefined): string {
    const totalSeconds = Number(sec);
    if (sec == null || Number.isNaN(totalSeconds)) return '–';
    const safeSeconds = Math.max(0, Math.round(totalSeconds));
    const min = Math.floor(safeSeconds / 60);
    const seconds = safeSeconds % 60;
    return `${min}:${seconds.toString().padStart(2, '0')}`;
}
const groupLabels = ['U1', 'U2', 'U3', 'U4', 'U5', 'U6'];
const percentTicks = ['4%', '11%', '23%', '40%', '60%', '77%', '89%', '96%', '100%'];

const percentBreaks = [4, 11, 23, 40, 60, 77, 89, 96, 100];

// U1..U6 explanation lines
const uExplanations = [
    'U1 – Dehnung',
    'U2 – Kürzung',
    'U3 – Konsonantenverwechslung',
    'U4 – Vokalverwechslung',
    'U5 – Groß- und Kleinschreibung, Getrennt- und Zusammenschreibung',
    'U6 – Fremdwörter',
];

// Map PR (0..100) to x in 1..9 by linear interpolation between breaks
function prToX(pr: number | null | undefined): number | null {
    if (pr == null || isNaN(pr)) return null;
    const p = Math.max(0, Math.min(100, pr));
    if (p <= percentBreaks[0]) return 1;
    if (p >= percentBreaks[percentBreaks.length - 1]) return 9;
    for (let i = 0; i < percentBreaks.length - 1; i++) {
        const a = percentBreaks[i],
            b = percentBreaks[i + 1];
        if (p >= a && p <= b) {
            const t = (p - a) / (b - a);
            return i + 1 + t; // x in [i+1, i+2]
        }
    }
    return null;
}

const labelsWithSpacer = computed(() => [...groupLabels, '']); // U1..U6 + blank row

const chartData = computed(() => {
    const stanines = props.results.group_stanines ?? [];
    const pr = props.results.prozentrang ?? null;
    const prX = prToX(pr);

    return {
        labels: labelsWithSpacer.value,
        datasets: [
            // SN line
            {
                label: 'SN',
                data: [...stanines, null],
                borderColor: '#1d4ed8',
                backgroundColor: '#1d4ed8',
                borderWidth: 3,
                tension: 0,
                pointRadius: 7,
                pointHoverRadius: 8,
                pointBackgroundColor: '#1d4ed8',
                // pointStyle: 'cross', // optional
                fill: false,
                spanGaps: false,
            },

            // PR red dot (on spacer row) using bottom xPercent axis
            {
                label: 'PR',
                xAxisID: 'xPercent',
                data: [null, null, null, null, null, null, prX],
                showLine: false,
                borderWidth: 0,
                pointRadius: 7,
                pointHoverRadius: 8,
                pointBackgroundColor: '#dc2626',
                pointBorderColor: '#dc2626',
            },
        ],
    };
});

const chartOptions: any = {
    responsive: true,
    maintainAspectRatio: false,
    devicePixelRatio: props.pdfMode ? 4 : 2,
    animation: false as const,
    indexAxis: 'y' as const,
    plugins: {
        legend: { display: false },
        title: { display: false },
        annotation: {
            annotations: {
                // Green band for SN 4..6 across all rows including the PR spacer row
                rangeBox: {
                    type: 'box',
                    xScaleID: 'x', // bind to TOP axis (1..9)
                    yScaleID: 'y', // bind to Y rows
                    xMin: 4,
                    xMax: 6,
                    yMin: -0.5, // start above U1
                    yMax: 6.5, // include spacer row (U6 is index 5; spacer is 6)
                    backgroundColor: 'rgba(34,197,94,0.18)', // soft green
                    borderWidth: 0,
                    drawTime: 'beforeDatasetsDraw',
                    clip: false,
                },
            },
        },
    },
    scales: {
        // TOP axis: 1..9
        x: {
            position: 'top',
            min: 1,
            max: 9,
            ticks: { stepSize: 1 },
            grid: { lineWidth: 1.5 },
            border: { width: 1.25 },
        },

        // BOTTOM axis: 4%, 11%, …, 100%
        xPercent: {
            position: 'bottom',
            type: 'linear',
            min: 1,
            max: 9,
            ticks: {
                stepSize: 1,
                callback: (value: number | string) => {
                    const v = Number(value);
                    return Number.isInteger(v) ? (percentTicks[v - 1] ?? '') : '';
                },
                padding: 8,
            },
            grid: { drawOnChartArea: false },
            border: { color: '#555', width: 1.25 },
        },

        // Y axis with RW - Ux and PR on spacer row
        y: {
            ticks: {
                display: true,
                padding: 10,
                color: '#333',
                font: { size: 13, weight: '600' },
                callback: (_val: string | number, index: number) => {
                    if (index < 6) {
                        const rw = props.results?.group_scores?.[index];
                        return `${rw ?? '–'} - ${groupLabels[index]}`;
                    }
                    const pr = props.results?.prozentrang;
                    const prText = pr == null || isNaN(pr) ? '–' : Math.round(pr).toString();
                    return `${prText} - PR`;
                },
            },
            grid: { drawBorder: true, lineWidth: 1.5 },
        },
    },
};

const mrtBQuestions = [
    { number: 1, correct: ['C'] },
    { number: 2, correct: ['A'] },
    { number: 3, correct: ['D'] },
    { number: 4, correct: ['A'] },
    { number: 5, correct: ['B'] },
    { number: 6, correct: ['D'] },
    { number: 7, correct: ['A'] },
    { number: 8, correct: ['D'] },
    { number: 9, correct: ['C'] },
    { number: 10, correct: ['A'] },
    { number: 11, correct: ['D'] },
    { number: 12, correct: ['B'] },
    { number: 13, correct: ['A'] },
    { number: 14, correct: ['B'] },
    { number: 15, correct: ['A'] },
    { number: 16, correct: ['D'] },
    { number: 17, correct: ['B'] },
    { number: 18, correct: ['C'] },
    { number: 19, correct: ['A'] },
    { number: 20, correct: ['A'] },
    { number: 21, correct: ['C'] },
    { number: 22, correct: ['B'] },
    { number: 23, correct: ['D'] },
    { number: 24, correct: ['A'] },
    { number: 25, correct: ['D'] },
    { number: 26, correct: ['C'] },
    { number: 27, correct: ['C'] },
    { number: 28, correct: ['D'] },
    { number: 29, correct: ['C'] },
    { number: 30, correct: ['D'] },
    { number: 31, correct: ['C'] },
    { number: 32, correct: ['A'] },
    { number: 33, correct: ['D'] },
    { number: 34, correct: ['C'] },
    { number: 35, correct: ['B'] },
    { number: 36, correct: ['D'] },
    { number: 37, correct: ['C', 'D'] },
    { number: 38, correct: ['B'] },
    { number: 39, correct: ['A', 'C'] },
    { number: 40, correct: ['C'] },
    { number: 41, correct: ['B'] },
    { number: 42, correct: ['A'] },
    { number: 43, correct: ['C'] },
    { number: 44, correct: ['A'] },
    { number: 45, correct: ['C'] },
    { number: 46, correct: ['C'] },
    { number: 47, correct: ['D'] },
    { number: 48, correct: ['B'] },
    { number: 49, correct: ['C'] },
    { number: 50, correct: ['D'] },
    { number: 51, correct: ['C'] },
    { number: 52, correct: ['D'] },
    { number: 53, correct: ['C'] },
    { number: 54, correct: ['C'] },
    { number: 55, correct: ['B'] },
    { number: 56, correct: ['A'] },
    { number: 57, correct: ['C'] },
    { number: 58, correct: ['D'] },
    { number: 59, correct: ['C'] },
    { number: 60, correct: ['D'] },
];
</script>

<template>
    <div class="mrt-result rounded-lg border bg-background p-6" :class="{ 'mrt-result--pdf': pdfMode }">
        <h2 class="mb-4 text-xl font-semibold">Test abgeschlossen!</h2>
        <div class="mrt-summary mb-6 w-full max-w-md">
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

        <button v-if="!pdfMode && !showDetails" @click="showDetails = true" class="mb-4 rounded-lg px-4 py-2 font-semibold">
            Antwort- und Bearbeitungszeit je Aufgabe anzeigen
        </button>
        <button v-else-if="!pdfMode" @click="showDetails = false" class="mb-4 rounded-lg px-4 py-2 font-semibold">
            Antwort- und Bearbeitungszeit je Aufgabe verbergen
        </button>
        <div v-if="!pdfMode && showDetails">
            <h3 class="mb-2 font-bold">Antwort- und Bearbeitungszeit je Aufgabe</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full rounded-lg border text-sm shadow">
                    <thead class="bg-muted/40">
                        <tr>
                            <th class="px-2 py-1 text-left font-semibold">#</th>
                            <th class="px-2 py-1 text-left font-semibold">Ihre Auswahl</th>
                            <th class="px-2 py-1 text-left font-semibold">Richtige Antwort(en)</th>
                            <th class="px-2 py-1 text-left font-semibold">Bearbeitungszeit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(answer, idx) in results.answers"
                            :key="idx"
                            :class="answer.is_correct ? 'bg-green-50 dark:bg-green-900/50' : 'bg-red-50 dark:bg-red-900/50'"
                        >
                            <td class="px-2 py-1 font-medium text-muted-foreground">{{ answer.number }}</td>
                            <td class="px-2 py-1">
                                <span class="font-mono">
                                    {{ answer.user_answer ? answer.user_answer : '–' }}
                                </span>
                            </td>
                            <td class="px-2 py-1">
                                <span class="font-mono">
                                    {{ mrtBQuestions[idx].correct.join(', ') }}
                                </span>
                            </td>
                            <td class="min-w-[60px] px-2 py-1 text-right font-mono text-gray-500 dark:text-gray-400">
                                {{ formatTime(answer.time_seconds) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <div class="mrt-chart-section my-10 flex w-full flex-col items-center justify-center">
                <!-- <div class="flex flex-row items-center gap-3 mb-8">
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
  <div
    class="w-10 h-10 border-2 border-black flex items-center justify-center text-base font-bold bg-blue-50 dark:bg-blue-900">
    {{ results.total_score }}
  </div>
  <span class="text-xs text-gray-700 dark:text-gray-300 mt-1 font-bold">RW GS</span>
</div>
<div class="flex flex-col items-center ml-6">
  <div
    class="w-10 h-10 border-2 border-black flex items-center justify-center text-base font-bold bg-yellow-50 dark:bg-yellow-900">
    {{ results.prozentrang }}
  </div>
  <span class="text-xs text-gray-700 dark:text-gray-300 mt-1 font-bold">PR</span>
</div>
</div> -->
                <!-- Chart + explanation side-by-side on large screens, stacked on small -->
                <div class="mrt-chart-grid grid w-full max-w-[1080px] grid-cols-1 items-start gap-6 lg:grid-cols-[740px_minmax(0,1fr)]">
                    <!-- Chart (stretches to given height because maintainAspectRatio=false) -->
                    <div class="mrt-chart-panel h-[420px] w-full">
                        <Line :data="chartData" :options="chartOptions" />
                    </div>

                    <!-- Explanation card OUTSIDE the chart -->
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
                <!-- <div class="w-full flex justify-center mt-6">
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
        </div> -->
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

.mrt-result--pdf .mrt-chart-grid {
    display: block;
    max-width: 100%;
}

.mrt-result--pdf .mrt-chart-panel {
    position: relative;
    width: 100%;
    height: 430px;
}

.mrt-result--pdf .mrt-chart-panel :deep(canvas) {
    width: 100% !important;
    height: 100% !important;
}

.mrt-result--pdf .mrt-hints-card {
    margin-top: 16px;
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
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 7px 24px;
    font-size: 12.5px;
    line-height: 1.45;
    color: #111827;
}

.mrt-result--pdf .mrt-hints-card li {
    align-items: flex-start;
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

@media print {
    .mrt-result--pdf {
        break-inside: avoid;
    }
}
</style>
