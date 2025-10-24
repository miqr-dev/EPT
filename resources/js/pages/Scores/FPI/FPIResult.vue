<script setup lang="ts">
import { computed } from 'vue';
import { FPI_QUESTIONS } from '../../Questions/FPIQuestions';

// Stanine positions and raw scores are provided by the parent component.
// No defaults are supplied so a missing value simply renders an empty graph.
const props = withDefaults(
  defineProps<{
    stanines?: (number | null)[];
    rohwerte?: (number | string | null)[];
    answers?: any[];
    showAnswers?: boolean;
  }>(),
  {
    showAnswers: true,
  },
);


const normalizedAnswers = computed(() => {
  if (!props.answers) return [];
  return props.answers.map((ans: any, index: number) => {
    const question = FPI_QUESTIONS.find(q => q.number === (ans.number ?? index + 1));
    return {
      number: ans.number ?? index + 1,
      question: question?.text ?? ans.question,
      user_answer: ans.user_answer ?? ans.answer,
      time_seconds: ans.time_seconds,
    };
  });
});

// Category labels and descriptions
const chartCategories = [
  { label: "Lebenszufriedenheit", commentL: "lebenszufrieden, gute Laune<br>zuversichtlich", commentR: "unzufrieden, bedrückt<br>negative Lebenseinstellung" },
  { label: "Soziale Orientierung", commentL: "sozial verantwortlich<br>hilfsbereit, mitmenschlich", commentR: "Eigenverantwortung in Not-<br>lagen betonend, selbstbe-<br>zogen unsolidarisch" },
  { label: "Leistungsorientierung", commentL: "leistungsorientiert, aktiv schnell-handelnd<br>ehrgeizig-konkurrierend", commentR: "wenig leistungsorientiert<br>oder energisch, wenig<br>ehrgeizig-konkurrierend" },
  { label: "Gehemmtheit", commentL: "gehemmt, unsicher,<br>kontaktscheu", commentR: "ungezwungen, selbstsicher,<br>kontaktbereit" },
  { label: "Erregbarkeit", commentL: "erregbar, empfindlich,<br>unbeherrscht", commentR: "ruhig, gelassen,<br>selbstbeherrscht" },
  { label: "Aggressivität", commentL: "aggressives Verhalten-spontan<br>und reaktiv,<br>sich durchsetzend", commentR: "wenig aggressiv,<br>kontrolliert,<br>zurückhaltend" },
  { label: "Beanspruchung", commentL: "angespannt, überfordert<br>sich oft „im Stress“ fühlend", commentR: "wenig beansprucht, nicht<br>überfordert, belastbar" },
  { label: "Körperliche Beschwerden", commentL: "viele Beschwerden<br>psychosomatisch gestört", commentR: "wenig Beschwerden,<br>psychosomatisch nicht gestört" },
  { label: "Gesundheitssorgen", commentL: "furcht vor Erkrankung<br>gesundheitsbewusst, sich schonend", commentR: "wenig Gesundheitssorgen<br>gesundheitlich unbekümmert,<br>robust" },
  { label: "Offenheit", commentL: "Offenes Zugeben kleiner Schwächen und<br>alltäglicher Normverletzungen, ungeniert,<br>unkonventionell", commentR: "an Umgangsnormen orientiert,<br>auf guten Eindruck bedacht,<br>mangelnde Selbstkritik,verschlossen<br>(Achtung bei Stanine 1-3)" },
  { label: "Extraversion", commentL: "extravertiert, gesellig<br>impulsiv, unternehmungslustig", commentR: "introvertiert, zurückhaltend<br>überlegt, ernst" },
  { label: "Emotionalität", commentL: "emotional, labil, empfindlich ängstlich, viele Probleme und körperliche Beschwerden", commentR: "emotional stabil, gelassen<br>selbstvertrauend,<br>lebenszufrieden" },
];

// Chart geometry constants for precise layout
const cellWidth = 37.5;
const rowHeight = 65; // Generous height for clear spacing
const gridWidth = cellWidth * 9;
const gridHeight = rowHeight * 12;
const headerHeight = 64;
const graphOffset = 85 + 230;

const getCatNum = (index: number): string => {
  if (index < 10) return (index + 1).toString();
  if (index === 10) return 'E';
  return 'N';
};

// replace both functions with a single value→x helper
function xForStanine(val: number) {
  // columns are labeled [9,8,7,6,5,4,3,2,1] from left to right
  const colIndexFromLeft = 9 - val; // 9→0, 8→1, …, 1→8
  return colIndexFromLeft * cellWidth + cellWidth / 2;
}

// keep rowY as-is
function rowY(idx: number) {
  return rowHeight * idx + rowHeight / 2;
}

const splitAfterRows = [9]; // after Offenheit

interface Pt { x: number; y: number; row: number, stanine: number }

const pointsByRow = computed<(Pt | null)[]>(() => {
  const values = props.stanines ?? [];
  return values.map((s, idx) => {
    const val = typeof s === 'string' ? Number(s) : s;
    if (!val || val < 1 || val > 9) return null;
    // use your existing x helper (xForStanine or stanineX(val-1))
    const x = typeof xForStanine === 'function' ? xForStanine(val) : (cellWidth * (val - 1) + cellWidth / 2);
    return { x, y: rowY(idx), row: idx, stanine: val };
  });
});

const outlierPoints = computed(() => {
  return pointsByRow.value.filter(pt => pt && (pt.stanine < 3 || pt.stanine > 7));
});

const drawablePoints = computed(() => {
  return pointsByRow.value.filter(pt => pt !== null);
});

const drawableOutlierPoints = computed(() => {
  return outlierPoints.value.filter(pt => pt !== null);
});

const polySegments = computed<{ x: number; y: number }[][]>(() => {
  const segs: { x: number; y: number }[][] = [];
  let cur: { x: number; y: number }[] = [];
  pointsByRow.value.forEach((pt, idx) => {
    if (pt) cur.push({ x: pt.x, y: pt.y });
    if (splitAfterRows.includes(idx) || pt === null) {
      if (cur.length) segs.push(cur);
      cur = [];
    }
  });
  if (cur.length) segs.push(cur);
  return segs;
});

const LINE_W = 2;          // same as CSS .blue-line width
const blueStanines = [7, 3]; // the two stanine columns you want to cross
const MARKER_R = 6        // was 4
const MARKER_SW = 1.75    // thicker outline

const snap = (x: number) => Math.round(x) + 0.5

// Inner bracket should span stanines 6..4
const innerLeft = computed(() => snap(xForStanine(6))) // left border at center of 6
const innerRight = computed(() => snap(xForStanine(4))) // right border at center of 4
const innerWidth = computed(() => innerRight.value - innerLeft.value)
</script>

<template>
  <div class="fpi-sheet">
    <div class="main-grid">
      <!-- Grid Headers -->
      <div class="header-cell rohwert-title">Rohwert</div>
      <div class="header-cell standardwert-title">Standardwert</div>
      <div class="header-cell graph-title">
        <div class="normstichprobe-label">Normstichprobe</div>
        <div class="prozent-numbers">
          <span v-for="p in [4, 7, 12, 17, 20, 17, 12, 7, 4]" :key="'p' + p">{{ p }}</span>
        </div>

        <div class="stanine-numbers">
          <span v-for="s in [9, 8, 7, 6, 5, 4, 3, 2, 1]" :key="'s' + s">{{ s }}</span>
        </div>
      </div>
      <div class="header-cell right-desc-title">
        <div>Prozent</div>
        <div>Stanine</div>
      </div>

      <!-- Content Rows -->
      <template v-for="(cat, i) in chartCategories" :key="i">
        <div class="cell rohwert-cell" :class="{ 'section-divider-bottom': i === 9 || i === 11 }">
          <div class="rohwert-box">{{ props.rohwerte[i] }}</div>
        </div>
        <div class="cell standardwert-cell" :class="{ 'section-divider-bottom': i === 9 || i === 11 }">
          <div class="cat-title">
            <span class="cat-num">{{ getCatNum(i) }}.</span>
            <span class="cat-label">{{ cat.label }}</span>
          </div>
          <div class="cat-desc" v-html="cat.commentL"></div>
        </div>
        <div class="cell graph-cell" :class="{ 'section-divider-bottom': i === 9 || i === 11 }">
          <div class="graph-dots">
            <span v-for="n in 9" :key="n" class="dot"></span>
          </div>
        </div>
        <div class="cell right-desc-cell" :class="{ 'section-divider-bottom': i === 9 || i === 11 }">
          <div class="cat-desc right" v-html="cat.commentR"></div>
        </div>
      </template>

      <!-- Graph Overlays -->
      <div class="graph-overlay-container" :style="{
        top: `${headerHeight}px`,
        left: `${graphOffset}px`,
        width: `${gridWidth}px`,
        height: `${gridHeight}px`,
      }">
        <div v-for="s in blueStanines" :key="`blue-${s}`" class="blue-line"
          :style="{ left: `${xForStanine(s) - LINE_W / 2}px`, width: `${LINE_W}px` }" />
        <!-- overlay markers -->
        <circle v-for="(pt, idx) in drawablePoints" :key="'dot-' + idx" :cx="pt.x" :cy="pt.y" :r="MARKER_R"
          fill="var(--overlay-fill)" stroke="var(--overlay-stroke)" :stroke-width="MARKER_SW" />
        <svg :width="gridWidth" :height="gridHeight" class="polyline-svg">
          <template v-for="(seg, sIdx) in polySegments" :key="'seg-'+sIdx">
            <polyline :points="seg.map(p => `${p.x},${p.y}`).join(' ')" fill="none" stroke="black" stroke-width="1.5" />
          </template>

          <!-- keep the dots -->
          <circle v-for="(pt, idx) in drawablePoints" :key="'dot-' + idx" :cx="pt.x" :cy="pt.y" r="4" fill="white"
            stroke="black" stroke-width="1.5" />

          <circle v-for="(pt, idx) in drawableOutlierPoints" :key="'outlier-' + idx" :cx="pt.x" :cy="pt.y" r="8"
            fill="none" stroke="red" stroke-width="1.5" />
        </svg>
        <div class="average-box avg-top" :style="{
          top: 0,
          height: `${rowHeight * 10}px`,
          left: `${innerLeft}px`,
          width: `${innerWidth}px`
        }">
          <div class="percent-label top">
            54%
            <div class="percent-connector"></div>
          </div>
        </div>

        <div class="average-box avg-bottom" :style="{
          top: `${rowHeight * 10}px`,
          height: `${rowHeight * 2}px`,
          left: `${innerLeft}px`,
          width: `${innerWidth}px`
        }">
          <div class="percent-label bottom">54%</div>
        </div>
      </div>

      <!-- Footer Row -->
      <div class="footer-cell rohwert-cell">
        <div class="rohwert-box"></div>
      </div>
      <div class="footer-cell standardwert-cell">fehlende Antworten</div>
    </div>
  </div>
    <details v-if="showAnswers" class="mt-4">
      <summary class="cursor-pointer">Antworten anzeigen</summary>
      <table class="w-full text-sm border-collapse border border-gray-300">
        <thead>
          <tr class="bg-gray-100">
            <th class="border border-gray-300 p-2">#</th>
            <th class="border border-gray-300 p-2">Frage</th>
            <th class="border border-gray-300 p-2">Ihre Antwort</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(answer, index) in normalizedAnswers" :key="index">
            <td class="border border-gray-300 p-2">{{ answer.number }}</td>
            <td class="border border-gray-300 p-2">{{ answer.question }}</td>
            <td class="border border-gray-300 p-2">{{ answer.user_answer }}</td>
          </tr>
        </tbody>
      </table>
    </details>
</template>

<style scoped>
/* =========================
   SVG overlay styling
   ========================= */
.polyline-svg {
  position: absolute;
  top: 0;
  left: 0;
  z-index: 5;
  shape-rendering: geometricPrecision
}

.polyline-svg polyline {
  stroke: var(--overlay-stroke);
  stroke-width: 1.5;
  stroke-linecap: round;
  stroke-linejoin: round;
  fill: none;
  vector-effect: non-scaling-stroke
}

.polyline-svg circle {
  paint-order: stroke fill
}

/* =========================
   Base container + tokens
   ========================= */
.fpi-sheet {
  /* Light theme defaults */
  --bg: #ffffff;
  --fg: #111111;
  --muted-fg: #444444;
  --grid: #e0e0e0;
  --border: #000000;
  --blue: #2f7dd6;
  --overlay-stroke: #000000;
  --overlay-fill: #ffffff;
  --dot-size: 4px;
  /* header label row height (where the mid-line sits) */
  --label-row-h: 22px;

  border: 1.5px solid var(--border);
  background: var(--bg);
  color: var(--fg);
  font-family: Arial, sans-serif;
  width: 100%;
  margin: 2rem auto;
  font-size: 11px;
  line-height: 1.25;
}

/* Dark mode */
@media (prefers-color-scheme:dark) {
  .fpi-sheet {
    --bg: #0f1115;
    --fg: #f1f5f9;
    --muted-fg: #cbd5e1;
    --grid: #3a3f4b;
    --border: #e2e8f0;
    --blue: #8ab4ff;
    --overlay-stroke: #f8fafc;
    --overlay-fill: #0f1115;
    --dot-size: 5px
  }
}

.dark .fpi-sheet {
  --bg: #0f1115;
  --fg: #f1f5f9;
  --muted-fg: #cbd5e1;
  --grid: #3a3f4b;
  --border: #e2e8f0;
  --blue: #8ab4ff;
  --overlay-stroke: #f8fafc;
  --overlay-fill: #0f1115;
  --dot-size: 5px
}

/* =========================
   Grid layout
   ========================= */
.main-grid {
  display: grid;
  grid-template-columns: 85px 230px 337.5px auto;
  position: relative
}

.header-cell,
.cell,
.footer-cell {
  padding: 4px;
  box-sizing: border-box
}

/* Borders */
.standardwert-title,
.standardwert-cell,
.graph-title,
.graph-cell,
.right-desc-title,
.right-desc-cell {
  border-left: 1.5px solid var(--border)
}

.header-cell {
  border-bottom: 1.5px solid var(--border)
}

.cell {
  border-bottom: 1px solid var(--grid)
}

.section-divider-bottom {
  border-bottom: 1.5px solid var(--border)
}

/* Heights */
.header-cell {
  height: v-bind('headerHeight + "px"');
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  align-items: stretch
}

.cell {
  height: v-bind('rowHeight + "px"')
}

/* =========================
   Header content (TOP)
   ========================= */
.standardwert-title {
  align-items: flex-start;
  padding-left: 8px
}

/* Center header: label + two number rows */
.graph-title {
  display: grid;
  width: 100%;
  grid-template-columns: 1fr;
  grid-template-rows: var(--label-row-h) 1fr 1fr;
  align-content: stretch;
  justify-content: stretch;
  padding: 0
}

.normstichprobe-label {
  grid-row: 1;
  display: block;
  width: 100%;
  text-align: center;
  padding: 0 8px 2px;
  /* remove per-cell underline; we'll draw a single line across the whole header */
  border-bottom: none;
  font-weight: 600;
}

.prozent-numbers,
.stanine-numbers {
  grid-row: auto;
  display: flex;
  justify-content: space-around;
  width: 100%;
  line-height: 1;
  padding-top: 0
}

/* Right header mirrors the 3-row layout */
.right-desc-title {
  display: grid;
  width: 100%;
  grid-template-columns: 1fr;
  grid-template-rows: var(--label-row-h) 1fr 1fr;
  align-content: stretch;
  justify-content: stretch;
  justify-items: end;
  padding: 0 8px
}

.right-desc-title>div:first-child {
  grid-row: 2
}

/* Prozent */
.right-desc-title>div:last-child {
  grid-row: 3
}

/* Stanine */

/* Remove the old per-cell mid-lines (if present) */
.standardwert-title::before,
.right-desc-title::before {
  content: none
}

/* One global mid-line across the whole header row */
.header-midline {
  position: absolute;
  left: 0;
  right: 0;
  top: var(--label-row-h);
  height: 1.5px;
  background: var(--border);
  z-index: 2
}

/* =========================
   Body columns
   ========================= */
.rohwert-cell {
  display: flex;
  align-items: center;
  justify-content: center
}

.rohwert-box {
  width: 48px;
  height: 30px;
  border: 1px solid var(--border);
  border-radius: 15px;
  background: var(--bg);
  color: var(--fg);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: bold;
  flex-shrink: 0
}

.standardwert-cell {
  padding: 4px 12px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start
}

.cat-title {
  font-weight: bold;
  font-size: 12.5px;
  margin-bottom: 2px
}

.cat-num {
  display: inline-block;
  width: 20px;
  text-align: right;
  margin-right: 4px
}

.cat-desc {
  color: var(--muted-fg)
}

.right-desc-cell {
  display: flex;
  align-items: center;
  padding: 4px 12px
}

/* Graph column */
.graph-cell {
  padding: 0;
  display: flex;
  align-items: center
}

.graph-dots {
  display: flex;
  justify-content: space-around;
  align-items: center;
  height: 100%;
  width: 100%
}

.dot {
  width: var(--dot-size);
  height: var(--dot-size);
  background-color: var(--fg);
  border-radius: 50%
}

/* =========================
   Overlays (lines, markers, bracket)
   ========================= */
.graph-overlay-container {
  position: absolute;
  pointer-events: none
}

.blue-line {
  position: absolute;
  top: 0;
  bottom: 0;
  width: 1.5px;
  background-color: var(--blue);
  opacity: .7;
  z-index: 4
}

.average-box {
  position: absolute;
  border: 1px solid var(--overlay-stroke);
  box-sizing: border-box;
  z-index: 2
}

.percent-label {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  font-size: 11px;
  font-weight: bold;
  background: var(--bg);
  padding: 0 2px;
  color: var(--fg);
  z-index: 10
}

.percent-label.top {
  top: -18px
}

.percent-label.bottom {
  bottom: 5px;
  width: 100%;
  text-align: center
}

.percent-connector {
  position: absolute;
  bottom: -6px;
  left: 50%;
  transform: translateX(-50%);
  width: 1px;
  height: 6px;
  background-color: var(--fg)
}

.percent-connector::before {
  content: '';
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 15px;
  height: 1px;
  background-color: var(--fg)
}

/* =========================
   Footer
   ========================= */
.footer-cell {
  height: 48px;
  display: flex;
  align-items: center;
  font-size: 12px
}

.footer-cell.standardwert-cell {
  padding-left: 8px
}

.average-box {
  position: absolute;
  border: 1px solid var(--overlay-stroke);
  box-sizing: border-box;
  z-index: 2;
}

/* let the thick row divider be visible */
.avg-top {
  border-bottom: none;
}

/* don’t cover the divider from above */
.avg-bottom {
  border-top: none;
}

/* don’t cover the divider from below */

.polyline-svg polyline {
  stroke: var(--overlay-stroke);
  stroke-width: 3;
  /* thicker so it stands over grid/blue lines */
  stroke-linecap: round;
  stroke-linejoin: round;
  fill: none;
  vector-effect: non-scaling-stroke;
}

/* reset */
.average-box {
  border: none;
}

/* upper block: only left+right borders */
.avg-top {
  border-left: 1px solid var(--overlay-stroke);
  border-right: 1px solid var(--overlay-stroke);
}

/* lower block: left+right+bottom (top already none) */
.avg-bottom {
  border-left: 1px solid var(--overlay-stroke);
  border-right: 1px solid var(--overlay-stroke);
  border-bottom: 1px solid var(--overlay-stroke);
}
</style>
