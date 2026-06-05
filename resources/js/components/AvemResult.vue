<script setup lang="ts">
import { AVEM_QUESTIONS } from '@/pages/Questions/AVEMQuestions';
import { CategoryScale, Chart, Legend, LinearScale, LineElement, PointElement, Title, Tooltip } from 'chart.js';
import annotationPlugin from 'chartjs-plugin-annotation';
import { computed } from 'vue';
import { Line } from 'vue-chartjs';

Chart.register(LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Legend, Title, annotationPlugin);

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

const scaleLabels = [
    'Subjektive Bedeutsamkeit der Arbeit',
    'Beruflicher Ehrgeiz',
    'Verausgabungsbereitschaft',
    'Perfektionsstreben',
    'Distanzierungsfähigkeit',
    'Resignationstendenz (bei Misserfolg)',
    'Offensive Problembewältigung',
    'Innere Ruhe/Ausgeglichenheit',
    'Erfolgserleben im Beruf',
    'Lebenszufriedenheit',
    'Erleben sozialer Unterstützung',
];

const answerLabels: Record<number, string> = {
    5: 'trifft völlig zu',
    4: 'trifft überwiegend zu',
    3: 'teils/teils',
    2: 'trifft überwiegend nicht zu',
    1: 'trifft überhaupt nicht zu',
};

// Stanine-Zelltexte (aus der Vorlage)
const NORM_INTERVALS: string[][] = [
    ['6–7', '8–9', '10–12', '13–14', '15–17', '18–19', '20–22', '23–24', '25–30'],
    ['6–10', '11–12', '13–14', '15–17', '18–19', '20–22', '23–25', '26–27', '28–30'],
    ['6–10', '11–13', '14–15', '16–17', '18–19', '20–22', '23–24', '25–27', '28–30'],
    ['6–14', '15–17', '18–19', '20–21', '22–23', '24–25', '26–27', '28–29', '30'],
    ['6–8', '9–11', '12–14', '15–17', '18–19', '20–21', '22–24', '25–26', '27–30'],
    ['6–7', '8–9', '10–11', '12–13', '14–16', '17–18', '19–20', '21–23', '24–30'],
    ['6–16', '17–18', '19', '20–21', '22–23', '24–25', '26–27', '28–29', '30'],
    ['6–12', '13–14', '15–17', '18–19', '20–21', '22–23', '24–25', '26–27', '28–30'],
    ['6–15', '16–17', '18–19', '20–21', '22–23', '24–25', '26–27', '28–29', '30'],
    ['6–14', '15–17', '18–19', '20–21', '22–23', '24–25', '26–27', '28', '29–30'],
    ['6–14', '15–17', '18–19', '20–22', '23–24', '25–26', '27–28', '29', '30'],
];

// Reverse-Items
const REVERSED = new Set([23, 56, 13, 16, 49, 60, 19, 30, 31, 54, 22, 33, 55]);

// Items je Skala (k, k+11, …, k+55)
const SCALE_ITEMS: number[][] = Array.from({ length: 11 }, (_, k) => Array.from({ length: 6 }, (__, j) => k + 1 + j * 11));

const clamp = (x: number, lo: number, hi: number) => Math.max(lo, Math.min(hi, x));

const parseInterval = (text: string | undefined | null) => {
    if (!text) return null;
    const cleaned = text.replace(/\s+/g, '').replace(/–/g, '-');
    if (!cleaned) return null;
    const [minStr, maxStr] = cleaned.split('-');
    const min = Number(minStr);
    if (!Number.isFinite(min)) return null;
    const max = maxStr ? Number(maxStr) : min;
    if (!Number.isFinite(max)) return null;
    return { min, max };
};

const stanineFromRawSum = (scaleIndex: number, rawSum: number) => {
    const intervals = NORM_INTERVALS[scaleIndex];
    if (!intervals) return 5;
    const rounded = Math.round(rawSum);
    for (let i = 0; i < intervals.length; i++) {
        const interval = parseInterval(intervals[i]);
        if (!interval) continue;
        if (rounded >= interval.min && rounded <= interval.max) return clamp(i + 1, 1, 9);
    }
    return clamp(rounded < 6 ? 1 : rounded > 30 ? 9 : 5, 1, 9);
};

const answersMap = computed<Record<number, number>>(() => {
    const map: Record<number, number> = {};
    const arr = Array.isArray(props.results?.answers) ? props.results.answers : [];
    for (const a of arr) {
        const num = Number(a.number);
        const val = Number(a.answer);
        if (num >= 1 && num <= 66 && val >= 1 && val <= 5) map[num] = val;
    }
    return map;
});

const computedStanines = computed<number[] | null>(() => {
    if (!props.results?.answers) return null;
    const out: number[] = [];
    for (let s = 0; s < 11; s++) {
        const items = SCALE_ITEMS[s];
        const vals: number[] = [];
        for (const q of items) {
            const raw = answersMap.value[q];
            if (!raw) continue;
            vals.push(REVERSED.has(q) ? 6 - raw : raw);
        }
        if (!vals.length) {
            out.push(5);
            continue;
        }
        const sum = vals.reduce((a, b) => a + b, 0);
        const normalised = vals.length ? (sum / vals.length) * 6 : 0;
        out.push(stanineFromRawSum(s, normalised));
    }
    return out;
});

const stanines = computed<number[]>(() => {
    const provided =
        (Array.isArray(props.results?.stanines) && props.results.stanines.length === 11 && props.results.stanines) ||
        (Array.isArray(props.results?.group_stanines) && props.results.group_stanines.length === 11 && props.results.group_stanines) ||
        null;
    return provided ?? computedStanines.value ?? Array(11).fill(5);
});

const chartData = computed(() => ({
    labels: scaleLabels,
    datasets: [
        {
            label: 'Stanine',
            data: stanines.value,
            borderColor: '#c1121f',
            backgroundColor: '#ffffff',
            borderWidth: props.pdfMode ? 2 : 2.8,
            clip: false,
            tension: 0,
            pointRadius: props.pdfMode ? 5 : 5.2,
            pointHoverRadius: props.pdfMode ? 6 : 6.2,
            pointBorderWidth: props.pdfMode ? 2 : 2.3,
            pointStyle: 'rectRot',
            pointBorderColor: '#c1121f',
            pointBackgroundColor: '#ffffff',
            fill: false,
        },
    ],
}));

const FRAME_PADDING = { top: 76, bottom: 84, left: 36, right: 36 };

const getFrameBounds = (chart: any) => {
    const { chartArea } = chart;
    return {
        left: chartArea.left - FRAME_PADDING.left,
        right: chartArea.right + FRAME_PADDING.right,
        top: chartArea.top - FRAME_PADDING.top,
        bottom: chartArea.bottom + FRAME_PADDING.bottom,
    };
};

/* ---------- custom draw: headers, Prozent, cell texts, point labels, frame ---------- */
const topBottomPlugin = {
    id: 'avemTopBottom',
    afterDraw(chart: any) {
        const { ctx, scales } = chart;
        const x = scales.x;
        if (!x) return;
        const frame = getFrameBounds(chart);
        const centerX = (frame.left + frame.right) / 2;

        ctx.save();
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.fillStyle = '#222';
        ctx.textAlign = 'center';

        // Title within top band
        ctx.font = `${props.pdfMode ? 18 : 16}px "Helvetica Neue", Helvetica, Arial, sans-serif`;
        ctx.textBaseline = 'top';
        ctx.fillText('Stanine-Werte', centerX, frame.top + 18);

        // Top 1..9 inside the frame band
        ctx.font = `${props.pdfMode ? 13.5 : 12}px "Helvetica Neue", Helvetica, Arial, sans-serif`;
        ctx.textBaseline = 'middle';
        const topNumY = frame.top + 48;
        for (let s = 1; s <= 9; s++) ctx.fillText(String(s), x.getPixelForValue(s), topNumY);

        // Segment labels below the numbers
        const topLblY = topNumY + 12;
        ctx.fillText('Muster S', x.getPixelForValue(2), topLblY);
        ctx.fillText('Risikomuster B', x.getPixelForValue(3.5), topLblY);
        ctx.fillText('Muster G', x.getPixelForValue(6), topLblY);
        ctx.fillText('Risikomuster A', x.getPixelForValue(8.25), topLblY);

        // Bottom Prozent row
        const perc = [4, 11, 23, 40, 60, 77, 89, 96];
        const xs = [1.5, 2.5, 3.5, 4.5, 5.5, 6.5, 7.5, 8.5];
        const baseY = frame.bottom - 40;
        for (let i = 0; i < xs.length; i++) ctx.fillText(String(perc[i]), x.getPixelForValue(xs[i]), baseY);
        ctx.fillText('Prozent', x.getPixelForValue(5), baseY + 18);

        ctx.restore();
    },
};

const intervalsAndPointLabels = {
    id: 'avemIntervalsAndPointLabels',
    afterDatasetsDraw(chart: any) {
        const { ctx, scales } = chart;
        const x = scales.x,
            y = scales.y;
        if (!x || !y) return;
        const frame = getFrameBounds(chart);

        // Cell interval strings
        ctx.save();
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.fillStyle = '#333';
        ctx.font = `${props.pdfMode ? 10.75 : 10}px "Helvetica Neue", Helvetica, Arial, sans-serif`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        for (let row = 0; row < NORM_INTERVALS.length; row++) {
            for (let s = 1; s <= 9; s++) {
                ctx.fillText(NORM_INTERVALS[row][s - 1] || '', x.getPixelForValue(s), y.getPixelForValue(row));
            }
        }
        // Left column labels inside the frame
        ctx.textAlign = 'right';
        ctx.font = `${props.pdfMode ? 12.6 : 12}px "Helvetica Neue", Helvetica, Arial, sans-serif`;
        const textX = frame.left - 12;
        for (let row = 0; row < scaleLabels.length; row++) {
            const yPos = y.getPixelForValue(row);
            const label = scaleLabels[row] ? `${row + 1}. ${scaleLabels[row]}` : `${row + 1}.`;
            ctx.fillText(label, textX, yPos);
        }

        ctx.restore();
    },
};

// draw a 1px frame around the plot
const frameBorder = {
    id: 'avemFrame',
    afterDraw(chart: any) {
        const { ctx } = chart;
        const frame = getFrameBounds(chart);
        ctx.save();
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.strokeStyle = '#2a2a2a';
        ctx.lineWidth = props.pdfMode ? 2 : 2.4;
        ctx.strokeRect(frame.left, frame.top, frame.right - frame.left, frame.bottom - frame.top);
        ctx.restore();
    },
};

const localPlugins = [topBottomPlugin, intervalsAndPointLabels, frameBorder];

/* ---------- options ---------- */
const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    animation: false as const,
    devicePixelRatio: props.pdfMode ? 4 : 6,
    indexAxis: 'y' as const,
    scales: {
        x: {
            min: 1,
            max: 9,
            ticks: { display: false, stepSize: 1 }, // hide bottom 1..9
            grid: { display: false },
            border: { display: false },
        },
        y: {
            ticks: { display: false },
            grid: {
                color: props.pdfMode ? 'rgba(0,0,0,0.14)' : 'rgba(17,24,39,0.28)',
                lineWidth: props.pdfMode ? 0.9 : 1.2, // optional: makes all horizontal lines same thickness too
            },
            border: { display: false },
        },
    },
    plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: (ctx: any) => ` Stanine: ${ctx.parsed.x}` } },
        title: { display: false },
        annotation: {
            annotations: {
                // grey band 4..6 (behind lines)
                band: {
                    type: 'box' as const,
                    xMin: 3.5,
                    xMax: 6.5,
                    yMin: -0.5,
                    yMax: 10.5,
                    backgroundColor: 'rgba(150,150,150,0.12)',
                    borderWidth: 0,
                    z: 0,
                },
                // SOLID boundaries between stanine groups
                lineBetweenSAndB: {
                    type: 'line' as const,
                    scaleID: 'x',
                    value: 3.5,
                    borderColor: '#111',
                    borderWidth: props.pdfMode ? 1.2 : 1.8,
                    z: 10,
                },
                lineBetweenGAndA: {
                    type: 'line' as const,
                    scaleID: 'x',
                    value: 6.5,
                    borderColor: '#111',
                    borderWidth: props.pdfMode ? 1.2 : 1.8,
                    z: 10,
                },
            },
        },
    },
    // padding outside data area that becomes part of the framed chart bands
    layout: {
        padding: {
            top: FRAME_PADDING.top,
            bottom: FRAME_PADDING.bottom,
            left: FRAME_PADDING.left + (props.pdfMode ? 262 : 246),
            right: FRAME_PADDING.right,
        },
    },
    elements: {
        line: {
            borderCapStyle: 'butt' as const,
            borderJoinStyle: 'miter' as const,
        },
        point: { hitRadius: 6 },
    },
    color: '#111',
}));

const detailRows = computed(() => {
    const arr = Array.isArray(props.results?.answers) ? props.results.answers : [];
    return arr.map((a: any, idx: number) => {
        const num = Number(a.number ?? idx + 1);
        const q = AVEM_QUESTIONS.find((q) => q.number === num)?.text ?? '';
        const rawAnswer = a.user_answer ?? a.answer ?? null;
        const numericAnswer = Number(rawAnswer);
        const answer =
            rawAnswer === null || rawAnswer === undefined || rawAnswer === ''
                ? '-'
                : Number.isFinite(numericAnswer)
                  ? (answerLabels[numericAnswer] ?? rawAnswer)
                  : rawAnswer;

        return { number: num, question: q, user_answer: answer };
    });
});
</script>

<template>
    <div class="avem-result rounded-lg bg-background p-6" :class="{ 'avem-result--pdf': props.pdfMode }">
        <div class="avem-chart-shell mb-6 rounded-md bg-white p-4">
            <div class="avem-chart-panel">
                <Line :data="chartData" :options="chartOptions" :plugins="localPlugins" />
            </div>
        </div>

        <details v-if="props.showAnswers && !props.pdfMode && detailRows.length" class="mt-4">
            <summary class="cursor-pointer">Antworten anzeigen</summary>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300 text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-300 p-2">#</th>
                            <th class="border border-gray-300 p-2">Frage</th>
                            <th class="border border-gray-300 p-2">Ihre Antwort</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="answer in detailRows" :key="answer.number">
                            <td class="border border-gray-300 p-2">{{ answer.number }}</td>
                            <td class="border border-gray-300 p-2">{{ answer.question }}</td>
                            <td class="border border-gray-300 p-2">{{ answer.user_answer }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </details>
    </div>
</template>

<style scoped>
.avem-result {
    color: #111827;
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
}

.avem-chart-panel {
    width: 920px;
    height: 560px;
}

.avem-chart-panel :deep(canvas) {
    width: 100% !important;
    height: 100% !important;
}

.avem-result--pdf {
    border-radius: 0;
    background: transparent;
    padding: 0;
}

.avem-result--pdf .avem-chart-shell {
    margin-bottom: 0;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #ffffff;
    padding: 12px 10px 10px;
    box-shadow: none;
}

.avem-result--pdf .avem-chart-panel {
    width: 100%;
    height: 600px;
}

@media print {
    .avem-result--pdf {
        break-inside: avoid;
    }
}
</style>
