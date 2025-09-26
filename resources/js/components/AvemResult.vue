<script setup lang="ts">
import { computed } from 'vue'
import { Line } from 'vue-chartjs'
import {
  Chart,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
  Tooltip,
  Legend,
  Title,
} from 'chart.js'
import annotationPlugin from 'chartjs-plugin-annotation'
import { AVEM_QUESTIONS } from '@/pages/Questions/AVEMQuestions'

Chart.register(LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Legend, Title, annotationPlugin)

const props = defineProps<{
  results: any;
  showAnswers: boolean;
}>();

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
]

// Stanine-Zelltexte (aus der Vorlage)
const NORM_INTERVALS: string[][] = [
  ['6–7','8–9','10–12','13–14','15–17','18–19','20–22','23–24','25–30'],
  ['6–10','11–12','13–14','15–17','18–19','20–22','23–25','26–27','28–30'],
  ['6–10','11–13','14–15','16–17','18–19','20–22','23–24','25–27','28–30'],
  ['6–14','15–17','18–19','20–21','22–23','24–25','26–27','28–29','30'],
  ['6–8','9–11','12–14','15–17','18–19','20–21','22–24','25–26','27–30'],
  ['6–7','8–9','10–11','12–13','14–16','17–18','19–20','21–23','24–30'],
  ['6–16','17–18','19','20–21','22–23','24–25','26–27','28–29','30'],
  ['6–12','13–14','15–17','18–19','20–21','22–23','24–25','26–27','28–30'],
  ['6–15','16–17','18–19','20–21','22–23','24–25','26–27','28–29','30'],
  ['6–14','15–17','18–19','20–21','22–23','24–25','26–27','28','29–30'],
  ['6–14','15–17','18–19','20–22','23–24','25–26','27–28','29','30'],
]

// Reverse-Items
const REVERSED = new Set([23,56,13,16,49,60,19,30,31,54,22,33,55])

// Items je Skala (k, k+11, …, k+55)
const SCALE_ITEMS: number[][] = Array.from({ length: 11 }, (_, k) =>
  Array.from({ length: 6 }, (__ , j) => k + 1 + j * 11)
)

const clamp = (x: number, lo: number, hi: number) => Math.max(lo, Math.min(hi, x))
const toStanineApprox = (mean15: number) => clamp(Math.round(((mean15 - 1) / 4) * 8 + 1), 1, 9)

const answersMap = computed<Record<number, number>>(() => {
  const map: Record<number, number> = {}
  const arr = Array.isArray(props.results?.answers) ? props.results.answers : []
  for (const a of arr) {
    const num = Number(a.number)
    const val = Number(a.answer)
    if (num >= 1 && num <= 66 && val >= 1 && val <= 5) map[num] = val
  }
  return map
})

const computedStanines = computed<number[] | null>(() => {
  if (!props.results?.answers) return null
  const out: number[] = []
  for (let s = 0; s < 11; s++) {
    const items = SCALE_ITEMS[s]
    const vals: number[] = []
    for (const q of items) {
      const raw = answersMap.value[q]
      if (!raw) continue
      vals.push(REVERSED.has(q) ? 6 - raw : raw)
    }
    if (!vals.length) { out.push(5); continue }
    const mean = vals.reduce((a,b)=>a+b,0)/vals.length
    out.push(toStanineApprox(mean))
  }
  return out
})

const stanines = computed<number[]>(() => {
  const provided =
    (Array.isArray(props.results?.stanines) && props.results.stanines.length === 11 && props.results.stanines) ||
    (Array.isArray(props.results?.group_stanines) && props.results.group_stanines.length === 11 && props.results.group_stanines) ||
    null
  return provided ?? (computedStanines.value ?? Array(11).fill(5))
})

const chartData = computed(() => ({
  labels: scaleLabels,
  datasets: [{
    label: 'Stanine',
    data: stanines.value,
    borderColor: '#e11d48',
    backgroundColor: '#e11d48',
    borderWidth: 2,
    tension: 0,
    pointRadius: 4,
    pointHoverRadius: 5,
    fill: false,
  }],
}))

/* ---------- custom draw: headers, Prozent, cell texts, point labels, frame ---------- */
const topBottomPlugin = {
  id: 'avemTopBottom',
  afterDraw(chart: any) {
    const { ctx, chartArea, scales } = chart
    const x = scales.x
    if (!x) return
    ctx.save()
    ctx.fillStyle = '#111'
    ctx.font = '12px system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif'
    ctx.textAlign = 'center'

    // Top 1..9 ABOVE the frame
    ctx.textBaseline = 'bottom'
    const topNumY = chartArea.top - 22
    for (let s = 1; s <= 9; s++) ctx.fillText(String(s), x.getPixelForValue(s), topNumY)

    // Segment labels
    const topLblY = chartArea.top - 4
    ctx.fillText('Muster S', x.getPixelForValue(2), topLblY)
    ctx.fillText('Risikomuster B', x.getPixelForValue(3.5), topLblY)
    ctx.fillText('Muster G', x.getPixelForValue(5), topLblY)
    ctx.fillText('Risikomuster A', x.getPixelForValue(8), topLblY)

    // Bottom Prozent row (outside the frame)
    const perc = [4, 11, 23, 40, 60, 77, 89, 96]
    const xs = [1, 2, 3, 4, 5, 6, 7, 8]
    ctx.textBaseline = 'middle'
    const baseY = chartArea.bottom + 24
    for (let i = 0; i < xs.length; i++) ctx.fillText(String(perc[i]), x.getPixelForValue(xs[i]), baseY)
    ctx.fillText('Prozent', x.getPixelForValue(5), baseY + 16)

    ctx.restore()
  },
}

const intervalsAndPointLabels = {
  id: 'avemIntervalsAndPointLabels',
  afterDatasetsDraw(chart: any) {
    const { ctx, scales } = chart
    const x = scales.x, y = scales.y
    if (!x || !y) return

    // Cell interval strings
    ctx.save()
    ctx.fillStyle = '#333'
    ctx.font = '10px system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif'
    ctx.textAlign = 'center'
    ctx.textBaseline = 'middle'
    for (let row = 0; row < NORM_INTERVALS.length; row++) {
      for (let s = 1; s <= 9; s++) {
        ctx.fillText(NORM_INTERVALS[row][s - 1] || '', x.getPixelForValue(s), y.getPixelForValue(row))
      }
    }
    ctx.restore()

    // numeric labels next to the red points
    const meta = chart.getDatasetMeta(0)
    ctx.save()
    ctx.fillStyle = '#e11d48'
    ctx.font = '11px system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif'
    meta.data.forEach((pt: any, i: number) => {
      const { x: px, y: py } = pt.getProps(['x','y'], true)
      ctx.fillText(String(chart.data.datasets[0].data[i]), px + 8, py - 6)
    })
    ctx.restore()
  },
}

// draw a 1px frame around the plot
const frameBorder = {
  id: 'avemFrame',
  afterDraw(chart: any) {
    const { ctx, chartArea } = chart
    ctx.save()
    ctx.strokeStyle = '#2a2a2a'
    ctx.lineWidth = 1
    ctx.strokeRect(chartArea.left, chartArea.top, chartArea.right - chartArea.left, chartArea.bottom - chartArea.top)
    ctx.restore()
  },
}

Chart.register(topBottomPlugin, intervalsAndPointLabels, frameBorder)

/* ---------- options ---------- */
const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  indexAxis: 'y' as const,
  scales: {
    x: {
      min: 1,
      max: 9,
      ticks: { display: false, stepSize: 1 },      // hide bottom 1..9
      grid: {
        drawTicks: false,
        color: (ctx: any) => (Number.isInteger(ctx.tick.value) ? 'rgba(0,0,0,0.20)' : 'rgba(0,0,0,0.08)'),
        lineWidth: 1,
      },
      border: { display: false },
    },
    y: {
      ticks: {
        // add more left margin between text and table
        padding: 20,
        callback: (val: any) => `${Number(val) + 1}. ${scaleLabels[Number(val)] || ''}`,
      },
      grid: { color: 'rgba(0,0,0,0.08)' },
      border: { display: false },
    },
  },
  plugins: {
    legend: { display: false },
    tooltip: { callbacks: { label: (ctx: any) => ` Stanine: ${ctx.parsed.x}` } },
    title: {
      display: true,
      text: 'Stanine-Werte',
      font: { size: 18, weight: '600' },
      padding: { bottom: 44 },                     // enough room for top 1..9 + headings
    },
    annotation: {
      annotations: {
        // grey band 4..6 (behind lines)
        band: { type: 'box', xMin: 4, xMax: 6, yMin: -0.5, yMax: 10.5, backgroundColor: 'rgba(120,120,120,0.12)', borderWidth: 0, z: 0 },
        // SOLID at 4 and 6
        line4: { type: 'line', xMin: 4, xMax: 4, borderColor: '#111', borderWidth: 1.2, z: 10 },
        line6: { type: 'line', xMin: 6, xMax: 6, borderColor: '#111', borderWidth: 1.2, z: 10 },
        // DASHED at exact midpoints 3.5 and 6.5
        mid35: { type: 'line', xMin: 3.5, xMax: 3.5, borderColor: 'rgba(0,0,0,0.55)', borderWidth: 1, borderDash: [5, 5], z: 10 },
        mid65: { type: 'line', xMin: 6.5, xMax: 6.5, borderColor: 'rgba(0,0,0,0.55)', borderWidth: 1, borderDash: [5, 5], z: 10 },
      },
    },
  },
  // padding OUTSIDE the frame: room above (numbers) & below (percent)
  layout: { padding: { top: 60, bottom: 56, left: 0, right: 0 } },
  elements: { point: { hitRadius: 6 } },
  color: '#111',
}))

const detailRows = computed(() => {
  const arr = Array.isArray(props.results?.answers) ? props.results.answers : []
  return arr.map((a: any, idx: number) => {
    const num = Number(a.number ?? idx + 1)
    const q = AVEM_QUESTIONS.find(q => q.number === num)?.text ?? ''
    const val = a.answer ?? null
    return { number: num, text: q, answer: val }
  })
})
</script>

<template>
  <div class="p-6 bg-background border rounded-lg flex flex-col items-center">
    <div class="mb-6" style="width: 920px; height: 560px">
      <Line :data="chartData" :options="chartOptions" />
    </div>

    <details v-if="showAnswers && detailRows.length" class="mt-2">
      <summary class="cursor-pointer select-none px-2 py-1 bg-muted/40 rounded">Antworten</summary>
      <div class="overflow-x-auto mt-3">
        <table class="min-w-full text-sm border rounded shadow">
          <thead class="bg-muted/40">
            <tr>
              <th class="p-2 text-right w-14">Nr.</th>
              <th class="p-2 text-left">Frage</th>
              <th class="p-2 text-center w-28">Antwort (1–5)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in detailRows" :key="row.number" class="border-t">
              <td class="p-2 text-right font-mono">{{ row.number }}.</td>
              <td class="p-2">{{ row.text }}</td>
              <td class="p-2 text-center">{{ row.answer ?? '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </details>
  </div>
</template>
