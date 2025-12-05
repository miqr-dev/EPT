<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import TimeRemainingAlerts from '@/components/TimeRemainingAlerts.vue';
import { useTeacherForceFinish } from '@/composables/useTeacherForceFinish';
import { getLpsDataset, type LpsPage1Solution } from '@/pages/Questions/LPSPage1';

type LpsPage1ResponseRow = { col1: boolean[]; col2: boolean[]; col3: boolean[]; col4: boolean[]; col5: boolean[] };

type ColumnStatus = 'locked' | 'ready' | 'active' | 'finished';

type LpsColumnState = { status: ColumnStatus; remaining: number };

const COLUMN_DURATION_SECONDS = [120, 120, 60, 60, 60];

const props = defineProps<{
  pausedTestResult?: {
    pageIndex?: number;
    total_time_seconds?: number;
    page1?: LpsPage1ResponseRow[];
    columnStates?: LpsColumnState[];
  };
  timeRemainingSeconds?: number | null;
  testName?: string;
}>();

const emit = defineEmits(['complete', 'update:answers']);

const { rows: lpsRows, solutions: lpsSolutions } = getLpsDataset(props.testName);

const showTest = ref(false);
const pageIndex = ref(0);
const elapsedSecondsBeforeResume = ref(props.pausedTestResult?.total_time_seconds ?? 0);
const runningElapsedSeconds = ref(0);
const timerHandle = ref<number | null>(null);

function buildSelection(length: number, saved?: boolean[]) {
  const base = Array.from({ length }, () => false);
  if (!saved) return base;
  if (saved.length === base.length) return saved;
  return base.map((_, idx) => saved[idx] ?? false);
}

const page1Responses = ref<LpsPage1ResponseRow[]>(
  lpsRows.map((row, idx) => {
    const pausedRow = props.pausedTestResult?.page1?.[idx];
    return {
      col1: buildSelection(row.column1.length, pausedRow?.col1),
      col2: buildSelection(row.column2.length, pausedRow?.col2),
      col3: buildSelection(row.column3?.length ?? 0, pausedRow?.col3),
      col4: buildSelection(row.column4.length, pausedRow?.col4),
      col5: buildSelection(row.column5.length, pausedRow?.col5),
    };
  }),
);

const columnStates = ref<LpsColumnState[]>(
  props.pausedTestResult?.columnStates?.length === 5
    ? props.pausedTestResult.columnStates
    : createInitialColumnStates(),
);

const activeColumnIndex = computed(() => columnStates.value.findIndex((c) => c.status === 'active'));
const isAnyColumnActive = computed(() => activeColumnIndex.value !== -1);
const columnTimerHandle = ref<number | null>(null);

if (props.pausedTestResult) {
  if (typeof props.pausedTestResult.pageIndex === 'number') {
    pageIndex.value = props.pausedTestResult.pageIndex;
  }
  showTest.value = true;
  startTimer();
  if (activeColumnIndex.value !== -1) {
    beginColumnCountdown(activeColumnIndex.value);
  }
}

const totalElapsed = computed(() => elapsedSecondsBeforeResume.value + runningElapsedSeconds.value);

const endConfirmOpen = ref(false);
const testLabel = computed(() => props.testName ?? 'LPS');
const { isForcedFinish, forcedFinishCountdown, clearForcedFinish } = useTeacherForceFinish({
  isActive: () => showTest.value,
  onStart: () => {
    endConfirmOpen.value = true;
    window.dispatchEvent(new Event('start-finish'));
  },
  onCountdownFinished: () => {
    confirmEnd();
  },
  onCancel: () => {
    if (endConfirmOpen.value) {
      window.dispatchEvent(new Event('cancel-finish'));
      endConfirmOpen.value = false;
    }
  },
});

function startTimer() {
  stopTimer();
  timerHandle.value = window.setInterval(() => {
    runningElapsedSeconds.value += 1;
  }, 1000);
}

function stopTimer() {
  if (timerHandle.value) {
    clearInterval(timerHandle.value);
    timerHandle.value = null;
  }
}

onBeforeUnmount(() => stopTimer());
onBeforeUnmount(() => stopColumnTimer());

watch(
  [page1Responses, pageIndex, totalElapsed, columnStates],
  () => {
    emit('update:answers', {
      pageIndex: pageIndex.value,
      total_time_seconds: totalElapsed.value,
      page1: page1Responses.value,
      columnStates: columnStates.value,
      page1_score: page1Score.value,
      page1_max_score: page1MaxScore.value,
    });
  },
  { deep: true },
);

watch(
  page1Responses,
  () => {
    maybeReactivateColumn1FromColumn2();
  },
  { deep: true },
);

function startTest() {
  showTest.value = true;
  pageIndex.value = 0;
  elapsedSecondsBeforeResume.value = 0;
  runningElapsedSeconds.value = 0;
  resetColumns();
  startTimer();
}

function nextPage() {
  pageIndex.value = Math.min(pageIndex.value + 1, 2);
}

function prevPage() {
  pageIndex.value = Math.max(pageIndex.value - 1, 0);
}

function finishTest() {
  window.dispatchEvent(new Event('start-finish'));
  endConfirmOpen.value = true;
}

function cancelEnd() {
  if (isForcedFinish.value) return;
  endConfirmOpen.value = false;
  window.dispatchEvent(new Event('cancel-finish'));
  clearForcedFinish(false);
}

function confirmEnd() {
  clearForcedFinish(false);
  endConfirmOpen.value = false;
  stopTimer();
  stopColumnTimer();
  emit('complete', {
    pageIndex: pageIndex.value,
    total_time_seconds: totalElapsed.value,
    page1: page1Responses.value,
    columnStates: columnStates.value,
    page1_score: page1Score.value,
    page1_max_score: page1MaxScore.value,
  });
}

function formatTime(seconds: number) {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins}:${secs.toString().padStart(2, '0')}`;
}

const COLUMN_INDEX_BY_KEY: Record<keyof LpsPage1ResponseRow, number> = {
  col1: 0,
  col2: 1,
  col3: 2,
  col4: 3,
  col5: 4,
};

function toggleSelection(rowIdx: number, column: keyof LpsPage1ResponseRow, charIdx: number) {
  if (!isColumnInteractive(column)) return;
  const row = page1Responses.value[rowIdx];
  if (!row || !row[column]?.length) return;
  const currentlySelected = row[column][charIdx];
  row[column] = row[column].map((_, idx) => (idx === charIdx ? !currentlySelected : false));
}

function resetColumns() {
  stopColumnTimer();
  columnStates.value = createInitialColumnStates();
}

function startColumn(columnIdx: number) {
  const currentState = columnStates.value[columnIdx];
  if (!currentState || currentState.status !== 'ready' || isAnyColumnActive.value) return;
  stopColumnTimer();
  columnStates.value[columnIdx] = {
    status: 'active',
    remaining: currentState.remaining || getColumnDuration(columnIdx),
  };
  beginColumnCountdown(columnIdx);
}

function stopColumnTimer() {
  if (columnTimerHandle.value) {
    clearInterval(columnTimerHandle.value);
    columnTimerHandle.value = null;
  }
}

function beginColumnCountdown(columnIdx: number, options: { unlockNext?: boolean } = {}) {
  const { unlockNext = true } = options;
  stopColumnTimer();
  columnTimerHandle.value = window.setInterval(() => {
    const activeState = columnStates.value[columnIdx];
    if (!activeState || activeState.status !== 'active') {
      stopColumnTimer();
      return;
    }
    activeState.remaining = Math.max(activeState.remaining - 1, 0);
    if (activeState.remaining === 0) {
      finishColumn(columnIdx, unlockNext);
    }
  }, 1000);
}

function finishColumn(columnIdx: number, unlockNext = true) {
  const currentState = columnStates.value[columnIdx];
  if (!currentState) return;
  columnStates.value[columnIdx] = { status: 'finished', remaining: 0 };
  stopColumnTimer();
  if (!unlockNext) {
    if (columnIdx === 0 && columnStates.value[1]?.status === 'finished') {
      columnStates.value[1] = { ...columnStates.value[1], remaining: 0 };
    }
    return;
  }
  const nextState = columnStates.value[columnIdx + 1];
  if (nextState && nextState.status === 'locked') {
    columnStates.value[columnIdx + 1] = {
      status: 'ready',
      remaining: nextState.remaining || getColumnDuration(columnIdx + 1),
    };
  }
}

function isColumnInteractive(columnKey: keyof LpsPage1ResponseRow) {
  const state = columnStates.value[COLUMN_INDEX_BY_KEY[columnKey]];
  return state?.status === 'active';
}

function formatColumnRemaining(seconds: number) {
  return formatTime(seconds);
}

function getColumnDuration(columnIdx: number) {
  return COLUMN_DURATION_SECONDS[columnIdx] ?? COLUMN_DURATION_SECONDS[0];
}

function createInitialColumnStates(): LpsColumnState[] {
  return COLUMN_DURATION_SECONDS.map((duration, idx) => ({
    status: idx === 0 ? 'ready' : 'locked',
    remaining: duration,
  }));
}

function isColumnFullyAnswered(columnKey: 'col1' | 'col2') {
  return lpsRows.every((row, idx) => {
    const word = columnKey === 'col1' ? row.column1 : row.column2;
    if (!word.length) return true;
    const picks = page1Responses.value[idx]?.[columnKey];
    return picks?.some(Boolean) ?? false;
  });
}

function maybeReactivateColumn1FromColumn2() {
  const column2State = columnStates.value[1];
  if (!column2State || column2State.status !== 'active') return;
  if (!isColumnFullyAnswered('col2')) return;
  if (column2State.remaining <= 0) return;

  const column1State = columnStates.value[0];
  if (column1State?.status === 'active') return;

  stopColumnTimer();
  columnStates.value[1] = { status: 'finished', remaining: column2State.remaining };
  columnStates.value[0] = { status: 'active', remaining: column2State.remaining };
  beginColumnCountdown(0, { unlockNext: false });
}

function scoreRow(rowIdx: number, solutions: LpsPage1Solution, responses: LpsPage1ResponseRow) {
  const cols: Array<keyof LpsPage1Solution> = ['col1', 'col2', 'col3', 'col4', 'col5'];
  return cols.reduce((sum, colKey) => {
    const correctIndices = solutions[colKey] ?? [];
    const picks = responses[colKey as keyof LpsPage1ResponseRow] as boolean[] | undefined;
    if (!correctIndices.length || !picks?.length) return sum;
    const correctPicks = correctIndices.filter((idx) => picks[idx]);
    return sum + correctPicks.length;
  }, 0);
}

const page1Score = computed(() =>
  page1Responses.value.reduce((total, response, idx) => {
    const solutions = lpsSolutions[idx] ?? {};
    return total + scoreRow(idx, solutions, response);
  }, 0),
);

const page1MaxScore = computed(() =>
  lpsSolutions.reduce(
    (total, solution) =>
      total +
      (solution.col1?.length ?? 0) +
      (solution.col2?.length ?? 0) +
      (solution.col3?.length ?? 0) +
      (solution.col4?.length ?? 0) +
      (solution.col5?.length ?? 0),
    0,
  ),
);
</script>

<template>

  <Head :title="testLabel" />

  <!-- Whole page scrolls -->
  <div class="min-h-screen overflow-x-auto bg-muted/15 p-4">
    <div class="mx-auto w-[1120px] max-w-none">
      <!-- Top bar -->
      <div class="mb-4 flex items-end justify-between gap-4">
        <div class="space-y-1">
          <h1 class="text-xl font-bold tracking-tight">{{ testLabel }}</h1>
          <div class="text-xs text-muted-foreground" v-if="showTest">Seite {{ pageIndex + 1 }} / 3</div>
        </div>

        <div class="flex flex-col items-end gap-2">
          <div class="w-[520px]">
            <TimeRemainingAlerts :time-remaining-seconds="props.timeRemainingSeconds" />
          </div>
          <div v-if="showTest" class="flex items-center gap-3 text-xs text-muted-foreground">
            <span class="font-medium text-foreground">Zeit: {{ formatTime(totalElapsed) }}</span>
            <span v-if="isForcedFinish" class="font-semibold text-destructive">
              Test wird beendet in {{ forcedFinishCountdown }}s
            </span>
          </div>
        </div>
      </div>

      <!-- Start screen -->
      <div v-if="!showTest" class="rounded-2xl border bg-background p-8 shadow-sm">
        <div class="mx-auto max-w-3xl space-y-4 text-center">
          <p class="text-base text-muted-foreground">
            Der {{ testLabel }}-Test umfasst drei Seiten. Beginnen Sie mit der ersten Seite. Die Spalten 1 und 2 sind
            zusammengefasst, die Spalten 3, 4 und 5 stehen getrennt nebeneinander. Arbeiten Sie jede Zeile von oben
            nach unten durch.
          </p>
          <Button class="px-8" @click="startTest">Test starten</Button>
        </div>
      </div>

      <!-- Test -->
      <div v-else class="space-y-4">
        <!-- Controls -->
        <div class="flex flex-wrap items-center justify-between gap-2">
          <div class="flex items-center gap-2">
            <Button variant="outline" size="sm" :disabled="pageIndex === 0 || isAnyColumnActive" @click="prevPage">
              Zurück
            </Button>
            <Button variant="secondary" size="sm" :disabled="pageIndex >= 2 || isAnyColumnActive" @click="nextPage">
              Weiter
            </Button>
          </div>

          <div class="flex items-center gap-2">
            <Button variant="destructive" size="sm" @click="finishTest">Test beenden</Button>
          </div>
        </div>

        <!-- Page 1 -->
        <div v-if="pageIndex === 0" class="space-y-3">
          <!-- Column timers / start buttons -->
          <div class="rounded-xl border bg-background px-4 py-3 shadow-sm">
            <div class="flex items-center justify-between gap-4">
              <div class="text-xs text-muted-foreground">
                Spalte starten (Dauer: Sp 1 {{ formatTime(getColumnDuration(0)) }}, Sp 2
                {{ formatTime(getColumnDuration(1)) }}, Sp 3–5 {{ formatTime(getColumnDuration(2)) }}).
                Nur eine Spalte gleichzeitig.
              </div>

              <div class="flex items-center gap-3">
                <div v-for="(state, idx) in columnStates" :key="`column-state-${idx}`" class="flex items-center gap-2">
                  <div class="rounded-lg border px-3 py-2 text-xs" :class="state.status === 'active'
                    ? 'border-destructive/50 bg-destructive/5 text-destructive'
                    : state.status === 'ready'
                      ? 'border-foreground/20 bg-background text-foreground'
                      : state.status === 'finished'
                        ? 'border-foreground/10 bg-muted/30 text-muted-foreground'
                        : 'border-foreground/10 bg-muted/20 text-muted-foreground'">
                    <div class="flex items-center gap-2">
                      <span class="font-semibold">Sp {{ idx + 1 }}</span>
                      <span v-if="state.status === 'active'" class="tabular-nums font-semibold">
                        {{ formatColumnRemaining(state.remaining) }}
                      </span>
                      <span v-else-if="state.status === 'ready'">bereit</span>
                      <span v-else-if="state.status === 'finished'">fertig</span>
                      <span v-else>gesperrt</span>
                    </div>
                  </div>

                  <Button v-if="state.status === 'ready'" size="sm" :disabled="isAnyColumnActive"
                    @click="startColumn(idx)">
                    Start
                  </Button>
                </div>
              </div>
            </div>
          </div>

          <!-- Minimal “letter sheet”: no boxes, no row lines -->
          <div class="rounded-2xl border bg-background p-4 shadow-sm">
            <!-- header (optional, minimal) -->
            <div class="mb-3 grid grid-cols-5 text-center text-[13px] font-extrabold tracking-wide text-foreground">
              <div class="col-span-2">1 + 2</div>
              <div>3</div>
              <div>4</div>
              <div>5</div>
            </div>

            <!-- content -->
            <div class="inline-grid grid-cols-5 gap-x-2">
              <!-- Columns 1/2 area -->
              <div class="col-span-1 border-solid border-4">
                <div v-for="(row, idx) in lpsRows" :key="`${row.id}-c1`" class="py-[3px]">
                  <div class="lps-letters">
                    <button v-for="(char, charIdx) in row.column1.split('')" :key="`${row.id}-1-${charIdx}`"
                      type="button" class="lps-letter"
                      :class="page1Responses[idx].col1[charIdx] ? 'lps-letter--selected' : ''"
                      :disabled="!isColumnInteractive('col1')" :aria-pressed="page1Responses[idx].col1[charIdx]"
                      @click="toggleSelection(idx, 'col1', charIdx)">
                      {{ char }}
                    </button>
                  </div>
                </div>
              </div>

              <div class="col-span-1 lps-sep">
                <div v-for="(row, idx) in lpsRows" :key="`${row.id}-c2`" class="py-[3px]">
                  <div class="lps-letters">
                    <button v-for="(char, charIdx) in row.column2.split('')" :key="`${row.id}-2-${charIdx}`"
                      type="button" class="lps-letter"
                      :class="page1Responses[idx].col2[charIdx] ? 'lps-letter--selected' : ''"
                      :disabled="!isColumnInteractive('col2')" :aria-pressed="page1Responses[idx].col2[charIdx]"
                      @click="toggleSelection(idx, 'col2', charIdx)">
                      {{ char }}
                    </button>
                  </div>
                </div>
              </div>

              <!-- 3/4/5 empty placeholders (keep structure like original test page) -->
              <div class="col-span-1 lps-sep">
                <div v-for="(row, idx) in lpsRows" :key="`${row.id}-c3`" class="py-[3px]">
                  <div v-if="row.column3?.length">
                    <div
                      v-if="row.column3SvgMeta && row.column3.every((option) => option.pathData)"
                      class="flex justify-center"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        version="1.1"
                        class="select-none"
                        :viewBox="row.column3SvgMeta.viewBox"
                        :width="row.column3SvgMeta.width"
                        :height="row.column3SvgMeta.height"
                      >
                        <g
                          v-for="(option, optionIdx) in row.column3"
                          :id="option.id"
                          :key="option.id"
                          role="button"
                          class="lps-figure-shape-group"
                          :transform="option.transform"
                          :tabindex="isColumnInteractive('col3') ? 0 : -1"
                          :aria-pressed="page1Responses[idx].col3[optionIdx]"
                          :class="!isColumnInteractive('col3') ? 'lps-figure-shape--disabled' : ''"
                          @click="isColumnInteractive('col3') && toggleSelection(idx, 'col3', optionIdx)"
                          @keydown.enter.prevent="toggleSelection(idx, 'col3', optionIdx)"
                          @keydown.space.prevent="toggleSelection(idx, 'col3', optionIdx)"
                        >
                          <path
                            fill="#090d0e"
                            class="lps-figure-shape"
                            :class="[
                              page1Responses[idx].col3[optionIdx] ? 'lps-figure-shape--selected' : '',
                            ]"
                            :d="option.pathData"
                          />
                          <clipPath :id="`${option.id}-clip`">
                            <path :d="option.pathData" :transform="option.transform" />
                          </clipPath>
                          <line
                            v-if="page1Responses[idx].col3[optionIdx]"
                            class="lps-figure-slash"
                            :clip-path="`url(#${option.id}-clip)`"
                            x1="0"
                            :y1="row.column3SvgMeta.height - 6"
                            :x2="row.column3SvgMeta.width"
                            y2="6"
                          />
                        </g>
                      </svg>
                    </div>
                    <div v-else class="grid grid-cols-8 gap-1">
                      <button
                        v-for="(option, optionIdx) in row.column3"
                        :id="option.id"
                        :key="option.id"
                        type="button"
                        class="lps-figure"
                        :class="page1Responses[idx].col3[optionIdx] ? 'lps-figure--selected' : ''"
                        :disabled="!isColumnInteractive('col3')"
                        :aria-pressed="page1Responses[idx].col3[optionIdx]"
                        @click="toggleSelection(idx, 'col3', optionIdx)"
                      >
                        <img :src="option.src" class="mx-auto h-9 w-9" alt="" />
                      </button>
                    </div>
                  </div>
                  <div v-else class="text-center text-xs text-muted-foreground/60">—</div>
                </div>
              </div>
              <!-- Columns 4 area -->
              <div class="col-span-1 lps-sep">
                <div v-for="(row, idx) in lpsRows" :key="`${row.id}-c2`" class="py-[3px]">
                  <div class="lps-letters">
                    <button v-for="(char, charIdx) in row.column4.split('')" :key="`${row.id}-4-${charIdx}`"
                      type="button" class="lps-letter"
                      :class="page1Responses[idx].col4[charIdx] ? 'lps-letter--selected' : ''"
                      :disabled="!isColumnInteractive('col4')" :aria-pressed="page1Responses[idx].col4[charIdx]"
                      @click="toggleSelection(idx, 'col4', charIdx)">
                      {{ char }}
                    </button>
                  </div>
                </div>
              </div>
              <div class="col-span-1 lps-sep">
                <div v-for="(row, idx) in lpsRows" :key="`${row.id}-c2`" class="py-[3px]">
                  <div class="lps-letters">
                    <button v-for="(char, charIdx) in row.column5.split('')" :key="`${row.id}-4-${charIdx}`"
                      type="button" class="lps-letter"
                      :class="page1Responses[idx].col5[charIdx] ? 'lps-letter--selected' : ''"
                      :disabled="!isColumnInteractive('col5')" :aria-pressed="page1Responses[idx].col5[charIdx]"
                      @click="toggleSelection(idx, 'col5', charIdx)">
                      {{ char }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div
            class="flex items-center justify-between rounded-xl border bg-background px-4 py-3 text-xs text-muted-foreground shadow-sm">
            <div>
              Punkte gesamt:
              <span class="font-semibold text-foreground">
                {{ page1MaxScore ? `${page1Score} / ${page1MaxScore}` : '–' }}
              </span>
            </div>
            <div class="text-[11px]">Spalte 3 enthält jetzt die Bildauswahl für LPS-B; weitere Reihen folgen.</div>
          </div>
        </div>

        <!-- Other pages placeholder -->
        <div v-else
          class="flex min-h-[200px] items-center justify-center rounded-2xl border border-dashed bg-background p-8 text-muted-foreground">
          Weitere Seiten dieses Tests werden in den nächsten Schritten ergänzt.
        </div>
      </div>
    </div>
  </div>

  <Dialog v-model:open="endConfirmOpen">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Test wirklich beenden?</DialogTitle>
        <DialogDescription>Die Antworten für alle bisher bearbeiteten Seiten werden gespeichert.</DialogDescription>
      </DialogHeader>
      <DialogFooter>
        <Button variant="outline" @click="cancelEnd">Abbrechen</Button>
        <Button variant="destructive" @click="confirmEnd">Beenden</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<style scoped>
/* Letters packed close, bold, no boxes/lines */
.lps-letters {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  /* very close */
  align-items: baseline;
}

.lps-letter {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 16px;
  height: 20px;
  padding: 0 1px;
  border: 0;
  border-radius: 0;
  background: transparent;
  font-weight: 900;
  font-size: 16px;
  line-height: 1;
  letter-spacing: -0.02em;
  user-select: none;
}

.lps-letter:focus-visible {
  outline: 2px solid rgb(59 130 246 / 0.6);
  outline-offset: 1px;
  border-radius: 2px;
}

.lps-letter:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

/* Selected mark: red pencil-like slash */
.lps-letter--selected {
  position: relative;
}

.lps-letter--selected::after {
  content: '';
  position: absolute;
  left: -2px;
  right: -2px;
  top: 50%;
  border-top: 3px solid rgb(220 38 38);
  transform: rotate(-35deg);
  transform-origin: center;
}

.lps-figure-shape-group {
  cursor: pointer;
  outline: none;
}

.lps-figure-shape {
  transition: opacity 120ms ease, transform 120ms ease;
}

.lps-figure-shape-group:hover .lps-figure-shape {
  opacity: 0.85;
}

.lps-figure-shape-group:focus-visible .lps-figure-shape {
  opacity: 0.7;
}

.lps-figure-shape--selected {
  opacity: 0.95;
  fill: rgb(220 38 38);
  stroke: rgb(220 38 38);
}

.lps-figure-shape--disabled {
  opacity: 0.45;
  cursor: not-allowed;
  pointer-events: none;
}

.lps-figure-slash {
  stroke: rgb(220 38 38);
  stroke-width: 4;
  stroke-linecap: round;
  pointer-events: none;
}

.lps-figure {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 46px;
  width: 46px;
  padding: 4px;
  border: 1px solid hsl(var(--border));
  border-radius: 10px;
  background: hsl(var(--background));
  transition: border-color 150ms ease, box-shadow 150ms ease;
}

.lps-figure img {
  pointer-events: none;
}

.lps-figure:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.lps-figure--selected {
  border-color: hsl(var(--primary));
  box-shadow: 0 0 0 3px hsla(var(--primary), 0.25);
}

/* Vertical separator between column 1 and 2 */
/* .lps-sep {
  border-left: 2px solid rgb(17 17 17 / 0.9);
  padding-left: 5px;
} */
</style>
