<script setup lang="ts">
import { computed } from 'vue';

// Props with default values to replicate the target PDF image exactly
const props = withDefaults(defineProps<{
  stanines?: number[],
  rohwerte?: (number | string)[],
}>(), {
  stanines: () => [5, 6, 6, 5, 5, 5, 5, 5, 5, 4, 5, 4],
  rohwerte: () => ['9', '8', '12', '10', '6', '12', '12', '12', '11', '', '12', '14'],
});

// Category labels and descriptions
const chartCategories = [
  { label: "Lebenszufriedenheit", commentL: "lebenszufrieden, gute Laune<br>zuversichtlich", commentR: "unzufrieden, bedrückt<br>negative Lebenseinstellung" },
  { label: "Soziale Orientierung", commentL: "sozial verantwortlich<br>hilfsbereit, mitmenschlich", commentR: "Eigenverantwortung in Not-<br>lagen betonend, selbstbe-<br>zogen unsolidarisch" },
  { label: "Leistungsorientierung", commentL: "leistungsorientiert, aktiv<br>schnell-handelnd<br>ehrgeizig-konkurrierend", commentR: "wenig leistungsorientiert<br>oder energisch, wenig<br>ehrgeizig-konkurrierend" },
  { label: "Gehemmtheit", commentL: "gehemmt, unsicher,<br>kontaktscheu", commentR: "ungezwungen, selbstsicher,<br>kontaktbereit" },
  { label: "Erregbarkeit", commentL: "erregbar, empfindlich,<br>unbeherrscht", commentR: "ruhig, gelassen,<br>selbstbeherrscht" },
  { label: "Aggressivität", commentL: "aggressives Verhalten-<br>spontan und reaktiv,<br>sich durchsetzend", commentR: "wenig aggressiv,<br>kontrolliert,<br>zurückhaltend" },
  { label: "Beanspruchung", commentL: "angespannt, überfordert<br>sich oft „im Stress“ fühlend", commentR: "wenig beansprucht, nicht<br>überfordert, belastbar" },
  { label: "Körperliche Beschwerden", commentL: "viele Beschwerden<br>psychosomatisch gestört", commentR: "wenig Beschwerden,<br>psychosomatisch nicht gestört" },
  { label: "Gesundheitssorgen", commentL: "furcht vor Erkrankung<br>gesundheitsbewusst, sich<br>schonend", commentR: "wenig Gesundheitssorgen<br>gesundheitlich unbekümmert,<br>robust" },
  { label: "Offenheit", commentL: "Offenes Zugeben kleiner<br>Schwächen und alltäglicher<br>Normverletzungen, ungeniert,<br>unkonventionell", commentR: "an Umgangsnormen orientiert,<br>auf guten Eindruck bedacht,<br>mangelnde Selbstkritik,<br>verschlossen<br>(Achtung bei Stanine 1-3)" },
  { label: "Extraversion", commentL: "extravertiert, gesellig<br>impulsiv, unternehmungslustig", commentR: "introvertiert, zurückhaltend<br>überlegt, ernst" },
  { label: "Emotionalität", commentL: "emotional, labil, empfindlich<br>ängstlich, viele Probleme und<br>körperliche Beschwerden", commentR: "emotional stabil, gelassen<br>selbstvertrauend,<br>lebenszufrieden" },
];

// Chart geometry constants for precise layout
const cellWidth = 37.5;
const rowHeight = 65; // Generous height for clear spacing
const gridWidth = cellWidth * 9;
const gridHeight = rowHeight * 12;
const headerHeight = 48;
const graphOffset = 85 + 230;

const getCatNum = (index: number): string => {
  if (index < 10) return (index + 1).toString();
  if (index === 10) return 'E';
  return 'N';
};

interface Point { x: number; y: number }

function stanineX(idx: number) {
  return cellWidth * idx + cellWidth / 2;
}

function rowY(idx: number) {
  return rowHeight * idx + rowHeight / 2;
}

const stanineCoords = computed<Point[]>(() =>
  props.stanines
    .map((s, idx) => {
      if (!s || s < 1 || s > 9) return null;
      return { x: stanineX(s - 1), y: rowY(idx) } as Point;
    })
    .filter((p): p is Point => p !== null),
);

const staninePoints = computed(() =>
  stanineCoords.value.map((pt) => `${pt.x},${pt.y}`).join(' '),
);
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
          <span v-for="p in [4, 7, 12, 17, 20, 17, 12, 7, 4]" :key="'p'+p">{{ p }}</span>
        </div>
        <div class="stanine-numbers">
          <span v-for="s in [9, 8, 7, 6, 5, 4, 3, 2, 1]" :key="'s'+s">{{ s }}</span>
        </div>
      </div>
      <div class="header-cell right-desc-title">
        <div>Prozent</div>
        <div>Stanine</div>
      </div>

      <!-- Content Rows -->
      <template v-for="(cat, i) in chartCategories" :key="i">
        <div class="cell rohwert-cell" :class="{'section-divider-bottom': i === 9 || i === 11}">
          <div class="rohwert-box">{{ props.rohwerte[i] }}</div>
        </div>
        <div class="cell standardwert-cell" :class="{'section-divider-bottom': i === 9 || i === 11}">
          <div class="cat-title">
            <span class="cat-num">{{ getCatNum(i) }}.</span>
            <span class="cat-label">{{ cat.label }}</span>
          </div>
          <div class="cat-desc" v-html="cat.commentL"></div>
        </div>
        <div class="cell graph-cell" :class="{'section-divider-bottom': i === 9 || i === 11}">
          <div class="graph-dots">
            <span v-for="n in 9" :key="n" class="dot"></span>
          </div>
        </div>
        <div class="cell right-desc-cell" :class="{'section-divider-bottom': i === 9 || i === 11}">
          <div class="cat-desc right" v-html="cat.commentR"></div>
        </div>
      </template>

      <!-- Graph Overlays -->
      <div
        class="graph-overlay-container"
        :style="{
          top: `${headerHeight}px`,
          left: `${graphOffset}px`,
          width: `${gridWidth}px`,
          height: `${gridHeight}px`,
        }"
      >
        <div
          v-for="(pos, index) in [2.5, 6.5]"
          :key="`blue-line-${index}`"
          class="blue-line"
          :style="{ left: `${cellWidth * pos}px` }"
        ></div>
        <svg :width="gridWidth" :height="gridHeight" class="polyline-svg">
          <polyline :points="staninePoints" fill="none" stroke="black" stroke-width="1.5" />
          <circle
            v-for="(pt, idx) in stanineCoords"
            :key="idx"
            :cx="pt.x"
            :cy="pt.y"
            r="4"
            fill="white"
            stroke="black"
            stroke-width="1.5"
          />
        </svg>
        <div
          class="average-box"
          :style="{ top: 0, height: `${rowHeight * 10}px`, left: `${cellWidth * 3}px`, width: `${cellWidth * 3}px` }
          "
        >
          <div class="percent-label top">
            54%
            <div class="percent-connector"></div>
          </div>
        </div>
        <div
          class="average-box"
          :style="{ top: `${rowHeight * 10}px`, height: `${rowHeight * 2}px`, left: `${cellWidth * 3}px`, width: `${cellWidth * 3}px` }
          "
        >
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
</template>

<style scoped>
.fpi-sheet {
  border: 1.5px solid #000;
  font-family: Arial, sans-serif;
  background: #fff;
  width: 950px;
  margin: 2rem auto;
  font-size: 11px;
  line-height: 1.25;
}

/* Main Grid Layout */
.main-grid {
  display: grid;
  grid-template-columns: 85px 230px 337.5px auto;
  position: relative;
}
.header-cell, .cell, .footer-cell {
  padding: 4px;
  box-sizing: border-box;
}

/* Borders */
.standardwert-title, .standardwert-cell, .graph-title, .graph-cell, .right-desc-title, .right-desc-cell {
  border-left: 1.5px solid #000;
}
.header-cell {
  border-bottom: 1.5px solid #000;
  font-weight: bold;
  font-size: 12px;
  height: 48px;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  align-items: center;
}
.standardwert-title, .right-desc-title { align-items: flex-start; padding-left: 8px;}
.cell {
  height: v-bind(rowHeight + 'px'); /* CRITICAL: v-bind for dynamic height */
  border-bottom: 1px solid #e0e0e0;
}
.section-divider-bottom {
  border-bottom: 1.5px solid #000;
}

/* Header Specifics */
.graph-title { text-align: center; padding: 0; }
.normstichprobe-label { border-bottom: 1.5px solid #000; width: 100%; padding-bottom: 2px; }
.prozent-numbers, .stanine-numbers { display: flex; justify-content: space-around; width: 100%; padding-top: 2px; }

/* Column-specific cells */
.rohwert-cell { display: flex; align-items: center; justify-content: center; }
.rohwert-box {
  width: 48px; height: 30px;
  border: 1px solid #000;
  border-radius: 15px; /* Oval shape */
  display: flex; align-items: center; justify-content: center;
  font-size: 14px; font-weight: bold;
  flex-shrink: 0;
}

.standardwert-cell {
  padding: 4px 12px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start;
}
.cat-title { font-weight: bold; font-size: 12.5px; margin-bottom: 2px; }
.cat-num { display: inline-block; width: 20px; text-align: right; margin-right: 4px; }
.right-desc-cell { display: flex; align-items: center; padding: 4px 12px; }
.cat-desc { color: #333; }

/* Graph Cell and Overlays */
.graph-cell { padding: 0; display: flex; align-items: center; }
.graph-dots { display: flex; justify-content: space-around; align-items: center; height: 100%; width: 100%;}
.dot { width: 4px; height: 4px; background-color: #000; border-radius: 50%; }
.graph-overlay-container {
  position: absolute;
  pointer-events: none;
}
.blue-line {
  position: absolute;
  top: 0;
  bottom: 0;
  width: 1.5px;
  background-color: #4a90e2; /* Vibrant blue */
  opacity: 0.7;
  z-index: 1; /* Ensures it's visible but behind other elements */
}
.polyline-svg { position: absolute; top: 0; left: 0; z-index: 5; }
.average-box { position: absolute; border: 1px solid black; box-sizing: border-box; z-index: 2;}

.percent-label {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  font-size: 11px;
  font-weight: bold;
  background: white;
  padding: 0 2px;
  color: black;
  z-index: 10;
}
.percent-label.top {
  top: -18px;
}
.percent-label.bottom {
  position: absolute;
  bottom: 5px;
  width: 100%;
  text-align: center;
}
.percent-connector {
  position: absolute;
  bottom: -6px;
  left: 50%;
  transform: translateX(-50%);
  width: 1px;
  height: 6px;
  background-color: black;
}
.percent-connector::before {
  content: '';
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 15px;
  height: 1px;
  background-color: black;
}

/* Footer */
.footer-cell {
  height: 48px;
  display: flex;
  align-items: center;
  font-size: 12px;
}
.footer-cell.standardwert-cell { padding-left: 8px; }
</style>