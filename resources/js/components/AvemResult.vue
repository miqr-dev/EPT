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

const parseInterval = (text: string | undefined | null) => {
  if (!text) return null
  const cleaned = text.replace(/\s+/g, '').replace(/–/g, '-')
  if (!cleaned) return null
  const [minStr, maxStr] = cleaned.split('-')
  const min = Number(minStr)
  if (!Number.isFinite(min)) return null
  const max = maxStr ? Number(maxStr) : min
  if (!Number.isFinite(max)) return null
  return { min, max }
}

const stanineFromRawSum = (scaleIndex: number, rawSum: number) => {
  const intervals = NORM_INTERVALS[scaleIndex]
  if (!intervals) return 5
  const rounded = Math.round(rawSum)
  for (let i = 0; i < intervals.length; i++) {
    const interval = parseInterval(intervals[i])
    if (!interval) continue
    if (rounded >= interval.min && rounded <= interval.max) return clamp(i + 1, 1, 9)
  }
  return clamp(rounded < 6 ? 1 : rounded > 30 ? 9 : 5, 1, 9)
}

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
    const sum = vals.reduce((a, b) => a + b, 0)
    const normalised = vals.length ? (sum / vals.length) * 6 : 0
    out.push(stanineFromRawSum(s, normalised))
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
    borderColor: '#c1121f',
    backgroundColor: '#ffffff',
    borderWidth: 1.4,
    tension: 0,
    pointRadius: 4,
    pointHoverRadius: 5,
    pointBorderWidth: 1.6,
    pointStyle: 'rectRot',
    pointBorderColor: '#c1121f',
    pointBackgroundColor: '#ffffff',
    fill: false,
  }],
}))

const FRAME_PADDING = { top: 76, bottom: 84, left: 36, right: 36 }
const LABEL_GUTTER = 224
const LAYOUT_PADDING = {
  top: FRAME_PADDING.top,
  bottom: FRAME_PADDING.bottom,
  left: FRAME_PADDING.left + LABEL_GUTTER,
  right: FRAME_PADDING.right,
}

const getFrameBounds = (chart: any) => {
  const { chartArea } = chart
  return {
    left: chartArea.left - FRAME_PADDING.left,
    right: chartArea.right + FRAME_PADDING.right,
    top: chartArea.top - FRAME_PADDING.top,
    bottom: chartArea.bottom + FRAME_PADDING.bottom,
  }
}

/* ---------- custom draw: headers, Prozent, cell texts, point labels, frame ---------- */
const topBottomPlugin = {
  id: 'avemTopBottom',
  afterDraw(chart: any) {
    const { ctx, scales } = chart
    const x = scales.x
    if (!x) return
    const frame = getFrameBounds(chart)
    const centerX = (frame.left + frame.right) / 2

    ctx.save()
    ctx.fillStyle = '#222'
    ctx.textAlign = 'center'

    // Title within top band
    ctx.font = '16px "Helvetica Neue", Helvetica, Arial, sans-serif'
    ctx.textBaseline = 'top'
    ctx.fillText('Stanine-Werte', centerX, frame.top + 18)

    // Top 1..9 inside the frame band
    ctx.font = '12px "Helvetica Neue", Helvetica, Arial, sans-serif'
    ctx.textBaseline = 'middle'
    const topNumY = frame.top + 48
    for (let s = 1; s <= 9; s++) ctx.fillText(String(s), x.getPixelForValue(s), topNumY)

    // Segment labels below the numbers
    const topLblY = topNumY + 22
    ctx.fillText('Muster S', x.getPixelForValue(2), topLblY)
    ctx.fillText('Risikomuster B', x.getPixelForValue(3.5), topLblY)
    ctx.fillText('Muster G', x.getPixelForValue(5), topLblY)
    ctx.fillText('Risikomuster A', x.getPixelForValue(8), topLblY)

    // Bottom Prozent row
    const perc = [4, 11, 23, 40, 60, 77, 89, 96]
    const xs = [1, 2, 3, 4, 5, 6, 7, 8]
    const baseY = frame.bottom - 40
    for (let i = 0; i < xs.length; i++) ctx.fillText(String(perc[i]), x.getPixelForValue(xs[i]), baseY)
    ctx.fillText('Prozent', x.getPixelForValue(5), baseY + 18)

    ctx.restore()
  },
}

const intervalsAndPointLabels = {
  id: 'avemIntervalsAndPointLabels',
  afterDatasetsDraw(chart: any) {
    const { ctx, scales } = chart
    const x = scales.x, y = scales.y
    if (!x || !y) return
    const frame = getFrameBounds(chart)

    // Cell interval strings
    ctx.save()
    ctx.fillStyle = '#333'
    ctx.font = '10px "Helvetica Neue", Helvetica, Arial, sans-serif'
    ctx.textAlign = 'center'
    ctx.textBaseline = 'middle'
    for (let row = 0; row < NORM_INTERVALS.length; row++) {
      for (let s = 1; s <= 9; s++) {
        ctx.fillText(NORM_INTERVALS[row][s - 1] || '', x.getPixelForValue(s), y.getPixelForValue(row))
      }
    }
    // Left column labels inside the frame
    ctx.textAlign = 'right'
    ctx.font = '12px "Helvetica Neue", Helvetica, Arial, sans-serif'
    const textX = frame.left - 12
    for (let row = 0; row < scaleLabels.length; row++) {
      const yPos = y.getPixelForValue(row)
      const label = scaleLabels[row] ? `${row + 1}. ${scaleLabels[row]}` : `${row + 1}.`
      ctx.fillText(label, textX, yPos)
    }

    ctx.restore()
  },
}

// draw a 1px frame around the plot
const frameBorder = {
  id: 'avemFrame',
  afterDraw(chart: any) {
    const { ctx } = chart
    const frame = getFrameBounds(chart)
    ctx.save()
    ctx.strokeStyle = '#2a2a2a'
    ctx.lineWidth = 1.2
    ctx.strokeRect(frame.left, frame.top, frame.right - frame.left, frame.bottom - frame.top)
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
        color: (ctx: any) => {
          const val = Number(ctx.tick.value)
          if (!Number.isFinite(val)) return 'rgba(0,0,0,0.08)'
          if (val === 4 || val === 6) return '#111'
          return Number.isInteger(val) ? 'rgba(0,0,0,0.32)' : 'rgba(0,0,0,0.1)'
        },
        lineWidth: (ctx: any) => {
          const val = Number(ctx.tick.value)
          if (val === 4 || val === 6) return 1.4
          return Number.isInteger(val) ? 1 : 0.6
        },
      },
      border: { display: false },
    },
    y: {
      ticks: { display: false },
      grid: {
        color: (ctx: any) => (ctx.index === 0 || ctx.index === 10 ? '#111' : 'rgba(0,0,0,0.12)'),
        lineWidth: (ctx: any) => (ctx.index === 0 || ctx.index === 10 ? 1.2 : 0.8),
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
        band: { type: 'box', xMin: 4, xMax: 6, yMin: -0.5, yMax: 10.5, backgroundColor: 'rgba(150,150,150,0.12)', borderWidth: 0, z: 0 },
        // SOLID at 4 and 6
        line4: { type: 'line', scaleID: 'x', value: 4, borderColor: '#111', borderWidth: 1.2, z: 10 },
        line6: { type: 'line', scaleID: 'x', value: 6, borderColor: '#111', borderWidth: 1.2, z: 10 },
      },
    },
  },
  // padding outside data area that becomes part of the framed chart bands
  layout: { padding: LAYOUT_PADDING },
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
  <div class="p-6 bg-background rounded-lg">
    <div class="mb-6 rounded-md bg-white p-4">
      <div style="width: 920px; height: 560px">
        <Line :data="chartData" :options="chartOptions" />
      </div>
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
