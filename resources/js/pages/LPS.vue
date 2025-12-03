<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import TimeRemainingAlerts from '@/components/TimeRemainingAlerts.vue';
import { useTeacherForceFinish } from '@/composables/useTeacherForceFinish';
import { LPS_PAGE1_ROWS, LPS_PAGE1_SOLUTIONS, type LpsPage1Solution } from '@/pages/Questions/LPSPage1';

type LpsPage1ResponseRow = { col1: boolean[]; col2: boolean[]; col3: boolean[]; col4: boolean[]; col5: boolean[] };

type ColumnStatus = 'locked' | 'ready' | 'active' | 'finished';

type LpsColumnState = { status: ColumnStatus; remaining: number };

const COLUMN_DURATION_SECONDS = 60;

const props = defineProps<{
  pausedTestResult?: {
    pageIndex?: number;
    total_time_seconds?: number;
    page1?: LpsPage1ResponseRow[];
    columnStates?: LpsColumnState[];
  };
  timeRemainingSeconds?: number | null;
}>();

const emit = defineEmits(['complete', 'update:answers']);

const showTest = ref(false);
const pageIndex = ref(0);
const elapsedSecondsBeforeResume = ref(props.pausedTestResult?.total_time_seconds ?? 0);
const runningElapsedSeconds = ref(0);
const timerHandle = ref<number | null>(null);

function buildSelection(word: string, saved?: boolean[]) {
  const base = word.split('').map(() => false);
  if (!saved) return base;
  if (saved.length === base.length) return saved;
  return base.map((_, idx) => saved[idx] ?? false);
}

const page1Responses = ref<LpsPage1ResponseRow[]>(
  LPS_PAGE1_ROWS.map((row, idx) => {
    const pausedRow = props.pausedTestResult?.page1?.[idx];
    return {
      col1: buildSelection(row.column1, pausedRow?.col1),
      col2: buildSelection(row.column2, pausedRow?.col2),
      col3: pausedRow?.col3 ?? [],
      col4: pausedRow?.col4 ?? [],
      col5: pausedRow?.col5 ?? [],
    };
  }),
);

const columnStates = ref<LpsColumnState[]>(
  props.pausedTestResult?.columnStates?.length === 5
    ? props.pausedTestResult.columnStates
    : [
        { status: 'ready', remaining: COLUMN_DURATION_SECONDS },
        { status: 'locked', remaining: COLUMN_DURATION_SECONDS },
        { status: 'locked', remaining: COLUMN_DURATION_SECONDS },
        { status: 'locked', remaining: COLUMN_DURATION_SECONDS },
        { status: 'locked', remaining: COLUMN_DURATION_SECONDS },
      ],
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

function toggleSelection(rowIdx: number, column: 'col1' | 'col2', charIdx: number) {
  const columnNumber = column === 'col1' ? 1 : 2;
  if (!isColumnInteractive(columnNumber)) return;
  const row = page1Responses.value[rowIdx];
  if (!row || !row[column]?.length) return;
  row[column][charIdx] = !row[column][charIdx];
}

function resetColumns() {
  stopColumnTimer();
  columnStates.value = [
    { status: 'ready', remaining: COLUMN_DURATION_SECONDS },
    { status: 'locked', remaining: COLUMN_DURATION_SECONDS },
    { status: 'locked', remaining: COLUMN_DURATION_SECONDS },
    { status: 'locked', remaining: COLUMN_DURATION_SECONDS },
    { status: 'locked', remaining: COLUMN_DURATION_SECONDS },
  ];
}

function startColumn(columnIdx: number) {
  const currentState = columnStates.value[columnIdx];
  if (!currentState || currentState.status !== 'ready' || isAnyColumnActive.value) return;
  stopColumnTimer();
  columnStates.value[columnIdx] = { status: 'active', remaining: COLUMN_DURATION_SECONDS };
  beginColumnCountdown(columnIdx);
}

function stopColumnTimer() {
  if (columnTimerHandle.value) {
    clearInterval(columnTimerHandle.value);
    columnTimerHandle.value = null;
  }
}

function beginColumnCountdown(columnIdx: number) {
  stopColumnTimer();
  columnTimerHandle.value = window.setInterval(() => {
    const activeState = columnStates.value[columnIdx];
    if (!activeState || activeState.status !== 'active') {
      stopColumnTimer();
      return;
    }
    activeState.remaining = Math.max(activeState.remaining - 1, 0);
    if (activeState.remaining === 0) {
      finishColumn(columnIdx);
    }
  }, 1000);
}

function finishColumn(columnIdx: number) {
  const currentState = columnStates.value[columnIdx];
  if (!currentState) return;
  columnStates.value[columnIdx] = { status: 'finished', remaining: 0 };
  stopColumnTimer();
  const nextState = columnStates.value[columnIdx + 1];
  if (nextState && nextState.status === 'locked') {
    columnStates.value[columnIdx + 1] = { status: 'ready', remaining: COLUMN_DURATION_SECONDS };
  }
}

function isColumnInteractive(columnNumber: number) {
  const state = columnStates.value[columnNumber - 1];
  return state?.status === 'active';
}

function formatColumnRemaining(seconds: number) {
  return formatTime(seconds);
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
    const solutions = LPS_PAGE1_SOLUTIONS[idx] ?? {};
    return total + scoreRow(idx, solutions, response);
  }, 0),
);

const page1MaxScore = computed(() =>
  LPS_PAGE1_SOLUTIONS.reduce(
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
  <Head title="LPS" />

  <div class="p-4">
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-2xl font-bold">LPS</h1>
      <div class="text-sm text-muted-foreground" v-if="showTest">
        Seite {{ pageIndex + 1 }} / 3
      </div>
    </div>

    <div class="mb-4">
      <TimeRemainingAlerts :time-remaining-seconds="props.timeRemainingSeconds" />
    </div>

    <div class="rounded-xl border bg-background p-6 shadow-sm">
      <div v-if="!showTest" class="flex flex-col items-center gap-4 text-center">
        <p class="max-w-2xl text-lg text-muted-foreground">
          Der LPS-Test umfasst drei Seiten. Beginnen Sie mit der ersten Seite. Die Spalten 1 und 2 sind
          zusammengefasst, die Spalten 3, 4 und 5 stehen getrennt nebeneinander. Arbeiten Sie jede Zeile von oben
          nach unten durch.
        </p>
        <Button class="mt-2" @click="startTest">Test starten</Button>
      </div>

      <div v-else>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
          <div class="flex items-center gap-4 text-sm text-muted-foreground">
            <span>Benötigte Zeit: {{ formatTime(totalElapsed) }}</span>
            <span v-if="isForcedFinish" class="font-semibold text-destructive">
              Test wird beendet in {{ forcedFinishCountdown }}s
            </span>
          </div>
          <div class="flex items-center gap-2">
            <Button variant="outline" :disabled="pageIndex === 0 || isAnyColumnActive" @click="prevPage">
              Zurück
            </Button>
            <Button variant="secondary" :disabled="pageIndex >= 2 || isAnyColumnActive" @click="nextPage">
              Weiter
            </Button>
            <Button variant="destructive" @click="finishTest">Test beenden</Button>
          </div>
        </div>

        <div v-if="pageIndex === 0" class="overflow-x-auto rounded-lg border">
          <div class="grid grid-cols-1 gap-3 border-b bg-muted/30 p-4 text-sm md:grid-cols-2">
            <div class="space-y-1">
              <div class="font-semibold">Spalten-Steuerung</div>
              <p class="text-muted-foreground">
                Nur eine Spalte kann gleichzeitig bearbeitet werden. Jede gestartete Spalte schließt sich nach 1:00
                Minute und gibt die nächste Spalte frei.
              </p>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div
                v-for="(state, idx) in columnStates.slice(0, 2)"
                :key="`column-state-${idx}`"
                class="rounded-lg border bg-background p-3 shadow-sm"
              >
                <div class="flex items-center justify-between text-sm font-semibold">
                  <span>Spalte {{ idx + 1 }}</span>
                  <span
                    v-if="state.status === 'active'"
                    class="text-destructive"
                    aria-live="polite"
                    aria-atomic="true"
                  >
                    {{ formatColumnRemaining(state.remaining) }}
                  </span>
                  <span v-else-if="state.status === 'finished'" class="text-muted-foreground">Abgeschlossen</span>
                  <span v-else-if="state.status === 'ready'" class="text-muted-foreground">Bereit</span>
                  <span v-else class="text-muted-foreground">Gesperrt</span>
                </div>
                <div class="mt-2 flex flex-wrap gap-2">
                  <Button
                    v-if="state.status === 'ready'"
                    size="sm"
                    :disabled="isAnyColumnActive"
                    @click="startColumn(idx)"
                  >
                    Spalte starten
                  </Button>
                  <div v-else-if="state.status === 'active'" class="text-sm text-muted-foreground">
                    Läuft …
                  </div>
                  <div v-else-if="state.status === 'finished'" class="text-sm text-muted-foreground">Beendet</div>
                  <div v-else class="text-sm text-muted-foreground">Noch gesperrt</div>
                </div>
              </div>
            </div>
          </div>

          <table class="min-w-[960px] w-full border-collapse text-sm">
            <thead>
              <tr class="bg-muted/40 text-left">
                <th colspan="2" class="px-4 py-3 text-center text-base font-semibold">1 + 2</th>
                <th class="px-4 py-3 text-center text-base font-semibold">3</th>
                <th class="px-4 py-3 text-center text-base font-semibold">4</th>
                <th class="px-4 py-3 text-center text-base font-semibold">5</th>
              </tr>
              <tr class="bg-muted/20 text-left">
                <th class="px-4 py-2 text-center font-semibold">1</th>
                <th class="px-4 py-2 text-center font-semibold">2</th>
                <th class="px-4 py-2 text-center font-semibold">3</th>
                <th class="px-4 py-2 text-center font-semibold">4</th>
                <th class="px-4 py-2 text-center font-semibold">5</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, idx) in LPS_PAGE1_ROWS" :key="row.id" class="border-t">
                <td class="px-3 py-3 align-top">
                  <div class="flex flex-wrap gap-2" role="group" :aria-label="`Zeile ${idx + 1} Spalte 1`">
                    <button
                      v-for="(char, charIdx) in row.column1.split('')"
                      :key="`${row.id}-1-${charIdx}`"
                      type="button"
                      class="relative flex h-9 w-9 items-center justify-center rounded-full border bg-white font-semibold transition hover:border-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50"
                      :class="page1Responses[idx].col1[charIdx] ? 'border-destructive bg-destructive/10' : ''"
                      :disabled="!isColumnInteractive(1)"
                      :aria-pressed="page1Responses[idx].col1[charIdx]"
                      @click="toggleSelection(idx, 'col1', charIdx)"
                    >
                      <span class="text-base leading-none">{{ char }}</span>
                      <span v-if="page1Responses[idx].col1[charIdx]" class="absolute text-xl font-bold leading-none text-destructive">
                        /
                      </span>
                    </button>
                  </div>
                </td>
                <td class="px-3 py-3 align-top">
                  <div class="flex flex-wrap gap-2" role="group" :aria-label="`Zeile ${idx + 1} Spalte 2`">
                    <button
                      v-for="(char, charIdx) in row.column2.split('')"
                      :key="`${row.id}-2-${charIdx}`"
                      type="button"
                      class="relative flex h-9 w-9 items-center justify-center rounded-full border bg-white font-semibold transition hover:border-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50"
                      :class="page1Responses[idx].col2[charIdx] ? 'border-destructive bg-destructive/10' : ''"
                      :disabled="!isColumnInteractive(2)"
                      :aria-pressed="page1Responses[idx].col2[charIdx]"
                      @click="toggleSelection(idx, 'col2', charIdx)"
                    >
                      <span class="text-base leading-none">{{ char }}</span>
                      <span v-if="page1Responses[idx].col2[charIdx]" class="absolute text-xl font-bold leading-none text-destructive">
                        /
                      </span>
                    </button>
                  </div>
                </td>
                <td class="px-3 py-3 align-top text-center text-muted-foreground">–</td>
                <td class="px-3 py-3 align-top text-center text-muted-foreground">–</td>
                <td class="px-3 py-3 align-top text-center text-muted-foreground">–</td>
              </tr>
            </tbody>
          </table>
          <div class="flex items-center justify-between border-t bg-muted/20 px-4 py-3 text-sm text-muted-foreground">
            <div>
              Punkte gesamt:
              <span class="font-semibold text-foreground">
                {{ page1MaxScore ? `${page1Score} / ${page1MaxScore}` : '–' }}
              </span>
            </div>
            <div class="text-xs">Spalten 3–5 sind derzeit leer und werden später ergänzt.</div>
          </div>
        </div>

        <div v-else class="flex min-h-[200px] items-center justify-center rounded-lg border border-dashed p-8 text-muted-foreground">
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
