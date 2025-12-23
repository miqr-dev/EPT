<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import TimeRemainingAlerts from '@/components/TimeRemainingAlerts.vue';
import { useTeacherForceFinish } from '@/composables/useTeacherForceFinish';
import { getLpsDataset, type LpsPage1Solution } from '@/pages/Questions/LPSPage1';
import { getLpsPage5Dataset, type LpsPage5Solution } from '@/pages/Questions/LPSPage5';
import { getLpsPage6Dataset, type LpsPage6Option, type LpsPage6Solution } from '@/pages/Questions/LPSPage6';
import { getLpsPage7Dataset, type LpsPage7Prompt, type LpsPage7Solution } from '@/pages/Questions/LPSPage7';
import { getLpsPage8Dataset, type LpsPage8Prompt, type LpsPage8Solution } from '@/pages/Questions/LPSPage8';

type LpsPage1ResponseRow = { col1: boolean[]; col2: boolean[]; col3: boolean[]; col4: boolean[]; col5: boolean[] };
type LpsPage5ResponseRow = { col7: boolean[] };
type LpsPage6ResponseRow = { col8: boolean[] };
type LpsPage7ResponseRow = { prompts: boolean[][] };
type LpsPage8ResponseRow = { prompts: boolean[][] };
type LpsPage6OptionGroup = { label: string; options: Array<{ option: LpsPage6Option; index: number }> };

type ColumnStatus = 'locked' | 'ready' | 'active' | 'finished';

type LpsColumnState = { status: ColumnStatus; remaining: number };

const COLUMN_DURATION_SECONDS = [3, 3, 3, 3, 3, 3, 120, 120, 180];
const COLUMN_LABELS = [1, 2, 3, 4, 5, 7, 8, 9, 10];
const PAGE_SECTIONS = [
  { title: 'Spalten 1 + 2', columnIndices: [0, 1] },
  { title: 'Spalte 3', columnIndices: [2] },
  { title: 'Spalte 4', columnIndices: [3] },
  { title: 'Spalte 5', columnIndices: [4] },
  { title: 'Spalte 7', columnIndices: [5] },
  { title: 'Spalte 8', columnIndices: [6] },
  { title: 'Spalte 9', columnIndices: [7] },
  { title: 'Spalte 10', columnIndices: [8] },
];

const props = defineProps<{
  pausedTestResult?: {
    pageIndex?: number;
    total_time_seconds?: number;
    page1?: LpsPage1ResponseRow[];
    page5?: LpsPage5ResponseRow[];
    page6?: LpsPage6ResponseRow[];
    columnStates?: LpsColumnState[];
    page5_score?: number;
    page5_max_score?: number;
    page6_score?: number;
    page6_max_score?: number;
    page7?: LpsPage7ResponseRow[];
    page7_score?: number;
    page7_max_score?: number;
    page8?: LpsPage8ResponseRow[];
    page8_score?: number;
    page8_max_score?: number;
  };
  timeRemainingSeconds?: number | null;
  testName?: string;
}>();

const emit = defineEmits(['complete', 'update:answers']);

const { rows: lpsRows, solutions: lpsSolutions } = getLpsDataset(props.testName);
const { rows: lpsPage5Rows, solutions: lpsPage5Solutions } = getLpsPage5Dataset(props.testName);
const { rows: lpsPage6Rows, solutions: lpsPage6Solutions } = getLpsPage6Dataset(props.testName);
const { rows: lpsPage7Rows, solutions: lpsPage7Solutions } = getLpsPage7Dataset(props.testName);
const { rows: lpsPage8Rows, solutions: lpsPage8Solutions } = getLpsPage8Dataset(props.testName);

const PAGE7_GRID_GAP_PX = 12;
// Update this width if the shape panels get larger/smaller so the arrows anchor to the panel edges.
const PAGE7_DEFAULT_SHAPE_WIDTH = 170;
// Increase/decrease this inset to shorten/lengthen the page 7 arrows without touching the CSS.
const PAGE7_ARROW_INSET_PX = 18;
const page7Arrows: Record<number, Array<{ from: number; to: number }>> = {
  0: [{ from: 2, to: 3 }],
  1: [{ from: 1, to: 2 }],
};
const isPage7ExamplePrompt = (rowIdx: number, promptIdx: number) =>
  props.testName === 'LPS-B' && rowIdx === 0 && promptIdx < 2;
const isPage8ExamplePrompt = (rowIdx: number, promptIdx: number) =>
  props.testName === 'LPS-B' && rowIdx === 0 && promptIdx < 2;

const showTest = ref(false);
const pageIndex = ref(0);
const pageCount = PAGE_SECTIONS.length;
const currentSection = computed(() => PAGE_SECTIONS[pageIndex.value] ?? PAGE_SECTIONS[0]);
const visibleColumnIndices = computed(() => currentSection.value.columnIndices);
const visibleColumnStates = computed(() =>
  visibleColumnIndices.value.map((idx) => ({ idx, state: columnStates.value[idx] })),
);
const isAnyVisibleColumnActive = computed(() =>
  visibleColumnIndices.value.some((idx) => columnStates.value[idx]?.status === 'active'),
);
function getShapePanelStyle(svgMeta?: { width: number; height: number }) {
  if (!svgMeta) return {};

  return {
    '--shape-width': `${svgMeta.width}px`,
    '--shape-height': `${svgMeta.height}px`,
  } as const;
}
function getPage7ShapeWidth(rowIdx: number) {
  return lpsPage7Rows[rowIdx]?.prompts?.[0]?.svgMeta?.width ?? PAGE7_DEFAULT_SHAPE_WIDTH;
}

function getPage7ArrowStyle(rowIdx: number, fromCol: number, toCol: number) {
  const columnWidth = `calc((100% - (${PAGE7_GRID_GAP_PX * 2}px)) / 3)`;
  const shapeWidth = getPage7ShapeWidth(rowIdx);
  const startLeft = `calc((${columnWidth} * ${fromCol - 0.5}) + ${PAGE7_GRID_GAP_PX * (fromCol - 1)}px + ${
    shapeWidth / 2 + PAGE7_ARROW_INSET_PX
  }px)`;
  const width = `calc((${columnWidth} * ${toCol - fromCol}) + ${PAGE7_GRID_GAP_PX * (toCol - fromCol)}px - ${
    shapeWidth + PAGE7_ARROW_INSET_PX * 2
  }px)`;

  return { left: startLeft, width } as const;
}
const elapsedSecondsBeforeResume = ref(props.pausedTestResult?.total_time_seconds ?? 0);
const runningElapsedSeconds = ref(0);
const timerHandle = ref<number | null>(null);

function buildSelection(length: number, saved?: boolean[]) {
  const base = Array.from({ length }, () => false);
  if (!saved) return base;
  if (saved.length === base.length) return saved;
  return base.map((_, idx) => saved[idx] ?? false);
}

function buildExamplePromptSelection(rowIdx: number, promptIdx: number, prompt: LpsPage7Prompt) {
  const selection = buildSelection(prompt.options.length ?? 0);
  const correctIdx = lpsPage7Solutions[rowIdx]?.correctOptionIndices?.[promptIdx];

  if (typeof correctIdx === 'number' && selection[correctIdx] !== undefined) {
    selection[correctIdx] = true;
  }

  return selection;
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
  props.pausedTestResult?.columnStates?.length === COLUMN_DURATION_SECONDS.length
    ? props.pausedTestResult.columnStates
    : createInitialColumnStates(),
);

const page5Responses = ref<LpsPage5ResponseRow[]>(
  lpsPage5Rows.map((row, idx) => {
    const pausedRow = props.pausedTestResult?.page5?.[idx];
    return { col7: buildSelection(row.column7?.length ?? 0, pausedRow?.col7) };
  }),
);

const page6Responses = ref<LpsPage6ResponseRow[]>(
  lpsPage6Rows.map((row, idx) => {
    const pausedRow = props.pausedTestResult?.page6?.[idx];
    return { col8: buildSelection(row.column8Options.length ?? 0, pausedRow?.col8) };
  }),
);

const page7Responses = ref<LpsPage7ResponseRow[]>(
  lpsPage7Rows.map((row, idx) => {
    const pausedRow = props.pausedTestResult?.page7?.[idx];
    return {
      prompts: row.prompts.map((prompt, promptIdx) => {
        if (isPage7ExamplePrompt(idx, promptIdx)) {
          return buildExamplePromptSelection(idx, promptIdx, prompt);
        }

        return buildSelection(prompt.options.length ?? 0, pausedRow?.prompts?.[promptIdx]);
      }),
    };
  }),
);

function buildPage8ExamplePromptSelection(rowIdx: number, promptIdx: number, prompt: LpsPage8Prompt) {
  const selection = buildSelection(prompt.options.length ?? 0);
  const correctIdx = lpsPage8Solutions[rowIdx]?.correctOptionIndices?.[promptIdx];

  if (typeof correctIdx === 'number' && selection[correctIdx] !== undefined) {
    selection[correctIdx] = true;
  }

  return selection;
}

const page8Responses = ref<LpsPage8ResponseRow[]>(
  lpsPage8Rows.map((row, idx) => {
    const pausedRow = props.pausedTestResult?.page8?.[idx];
    return {
      prompts: row.prompts.map((prompt, promptIdx) => {
        if (isPage8ExamplePrompt(idx, promptIdx)) {
          return buildPage8ExamplePromptSelection(idx, promptIdx, prompt);
        }

        return buildSelection(prompt.options.length ?? 0, pausedRow?.prompts?.[promptIdx]);
      }),
    };
  }),
);

const page6OptionGroups = computed<LpsPage6OptionGroup[][]>(() =>
  lpsPage6Rows.map((row) => buildPage6OptionGroups(row.column8Options ?? [])),
);

const sectionDurationText = computed(() => formatSectionDurations(visibleColumnIndices.value));

const activeColumnIndex = computed(() => columnStates.value.findIndex((c) => c.status === 'active'));
const isAnyColumnActive = computed(() => activeColumnIndex.value !== -1);
const columnTimerHandle = ref<number | null>(null);

if (props.pausedTestResult) {
  if (typeof props.pausedTestResult.pageIndex === 'number') {
    pageIndex.value = props.pausedTestResult.pageIndex;
  }
  pageIndex.value = Math.min(pageIndex.value, pageCount - 1);
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
  [page1Responses, page5Responses, page6Responses, page7Responses, page8Responses, pageIndex, totalElapsed, columnStates],
  () => {
    emit('update:answers', {
      pageIndex: pageIndex.value,
      total_time_seconds: totalElapsed.value,
      page1: page1Responses.value,
      columnStates: columnStates.value,
      page1_score: page1Score.value,
      page1_max_score: page1MaxScore.value,
      page5: page5Responses.value,
      page5_score: page5Score.value,
      page5_max_score: page5MaxScore.value,
      page6: page6Responses.value,
      page6_score: page6Score.value,
      page6_max_score: page6MaxScore.value,
      page7: page7Responses.value,
      page7_score: page7Score.value,
      page7_max_score: page7MaxScore.value,
      page8: page8Responses.value,
      page8_score: page8Score.value,
      page8_max_score: page8MaxScore.value,
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
  pageIndex.value = Math.min(pageIndex.value + 1, pageCount - 1);
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
    page5: page5Responses.value,
    page6: page6Responses.value,
    page7: page7Responses.value,
    page8: page8Responses.value,
    columnStates: columnStates.value,
    page1_score: page1Score.value,
    page1_max_score: page1MaxScore.value,
    page5_score: page5Score.value,
    page5_max_score: page5MaxScore.value,
    page6_score: page6Score.value,
    page6_max_score: page6MaxScore.value,
    page7_score: page7Score.value,
    page7_max_score: page7MaxScore.value,
    page8_score: page8Score.value,
    page8_max_score: page8MaxScore.value,
  });
}

function formatTime(seconds: number) {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins}:${secs.toString().padStart(2, '0')}`;
}

type ColumnKey = keyof LpsPage1ResponseRow | 'col7' | 'col8' | 'col9' | 'col10';

const COLUMN_INDEX_BY_KEY: Record<ColumnKey, number> = {
  col1: 0,
  col2: 1,
  col3: 2,
  col4: 3,
  col5: 4,
  col7: 5,
  col8: 6,
  col9: 7,
  col10: 8,
};

function buildPage6OptionGroups(options: LpsPage6Option[]): LpsPage6OptionGroup[] {
  if (!options.some((option) => option.group)) return [];

  const groupMap = new Map<string, LpsPage6OptionGroup>();

  options.forEach((option, index) => {
    const key = option.group ?? `group-${index}`;

    if (!groupMap.has(key)) {
      groupMap.set(key, { label: option.group ?? '', options: [] });
    }

    groupMap.get(key)?.options.push({ option, index });
  });

  return Array.from(groupMap.values());
}

function toggleSelection(rowIdx: number, column: keyof LpsPage1ResponseRow, charIdx: number) {
  if (!isColumnInteractive(column)) return;
  const row = page1Responses.value[rowIdx];
  if (!row || !row[column]?.length) return;
  const currentlySelected = row[column][charIdx];
  row[column] = row[column].map((_, idx) => (idx === charIdx ? !currentlySelected : false));
}

function togglePage5Selection(rowIdx: number, charIdx: number) {
  if (!isColumnInteractive('col7')) return;
  const row = page5Responses.value[rowIdx];
  if (!row?.col7?.length) return;
  const currentlySelected = row.col7[charIdx];
  row.col7 = row.col7.map((_, idx) => (idx === charIdx ? !currentlySelected : false));
}

function togglePage6Selection(rowIdx: number, charIdx: number) {
  if (!isColumnInteractive('col8')) return;
  const row = page6Responses.value[rowIdx];
  const options = lpsPage6Rows[rowIdx]?.column8Options ?? [];
  if (!row?.col8?.length) return;
  const targetGroup = options[charIdx]?.group;
  const currentlySelected = row.col8[charIdx];

  row.col8 = row.col8.map((selected, idx) => {
    if (targetGroup) {
      const isSameGroup = options[idx]?.group === targetGroup;
      if (idx === charIdx) return !currentlySelected;
      return isSameGroup ? false : selected;
    }

    return idx === charIdx ? !currentlySelected : false;
  });
}

function togglePage7Selection(rowIdx: number, promptIdx: number, optionIdx: number) {
  if (!isColumnInteractive('col9') || isPage7ExamplePrompt(rowIdx, promptIdx)) return;
  const row = page7Responses.value[rowIdx];
  if (!row?.prompts?.[promptIdx]?.length) return;
  const currentlySelected = row.prompts[promptIdx][optionIdx];
  row.prompts[promptIdx] = row.prompts[promptIdx].map((_, idx) => (idx === optionIdx ? !currentlySelected : false));
}

function togglePage8Selection(rowIdx: number, promptIdx: number, optionIdx: number) {
  if (!isColumnInteractive('col10') || isPage8ExamplePrompt(rowIdx, promptIdx)) return;
  const row = page8Responses.value[rowIdx];
  if (!row?.prompts?.[promptIdx]?.length) return;
  const currentlySelected = row.prompts[promptIdx][optionIdx];
  row.prompts[promptIdx] = row.prompts[promptIdx].map((_, idx) => (idx === optionIdx ? !currentlySelected : false));
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

function isColumnInteractive(columnKey: ColumnKey) {
  const state = columnStates.value[COLUMN_INDEX_BY_KEY[columnKey]];
  return state?.status === 'active';
}

function formatColumnRemaining(seconds: number) {
  return formatTime(seconds);
}

function getColumnLabel(idx: number) {
  return COLUMN_LABELS[idx] ?? idx + 1;
}

function formatSectionDurations(indices: number[]) {
  return indices
    .map((idx) => `Sp ${getColumnLabel(idx)} ${formatTime(getColumnDuration(idx))}`)
    .join(', ');
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

function scorePage5Row(rowIdx: number, solutions: LpsPage5Solution, responses: LpsPage5ResponseRow) {
  const cols: Array<keyof LpsPage5Solution> = ['col7'];
  return cols.reduce((sum, colKey) => {
    const correctIndices = solutions[colKey] ?? [];
    const picks = responses[colKey as keyof LpsPage5ResponseRow] as boolean[] | undefined;
    if (!correctIndices.length || !picks?.length) return sum;
    const correctPicks = correctIndices.filter((idx) => picks[idx]);
    return sum + correctPicks.length;
  }, 0);
}

const page5Score = computed(() =>
  page5Responses.value.reduce((total, response, idx) => {
    const solutions = lpsPage5Solutions[idx] ?? {};
    return total + scorePage5Row(idx, solutions, response);
  }, 0),
);

const page5MaxScore = computed(() =>
  lpsPage5Solutions.reduce((total, solution) => total + (solution.col7?.length ?? 0), 0),
);

function scorePage6Row(rowIdx: number, solutions: LpsPage6Solution, responses: LpsPage6ResponseRow) {
  const cols: Array<keyof LpsPage6Solution> = ['col8'];
  return cols.reduce((sum, colKey) => {
    const correctIndices = solutions[colKey] ?? [];
    const picks = responses[colKey as keyof LpsPage6ResponseRow] as boolean[] | undefined;
    if (!correctIndices.length || !picks?.length) return sum;
    const correctPicks = correctIndices.filter((idx) => picks[idx]);
    return sum + correctPicks.length;
  }, 0);
}

const page6Score = computed(() =>
  page6Responses.value.reduce((total, response, idx) => {
    const solutions = lpsPage6Solutions[idx] ?? {};
    return total + scorePage6Row(idx, solutions, response);
  }, 0),
);

const page6MaxScore = computed(() =>
  lpsPage6Solutions.reduce((total, solution) => total + (solution.col8?.length ?? 0), 0),
);

function scorePage7Row(rowIdx: number, solutions: LpsPage7Solution, responses: LpsPage7ResponseRow) {
  return solutions.correctOptionIndices.reduce((sum, correctIdx, promptIdx) => {
    if (isPage7ExamplePrompt(rowIdx, promptIdx)) return sum;
    if (correctIdx === null || correctIdx === undefined) return sum;
    const picks = responses.prompts?.[promptIdx];
    if (!picks?.length) return sum;
    return picks[correctIdx] ? sum + 1 : sum;
  }, 0);
}

const page7Score = computed(() =>
  page7Responses.value.reduce((total, response, idx) => {
    const solutions = lpsPage7Solutions[idx];
    if (!solutions) return total;
    return total + scorePage7Row(idx, solutions, response);
  }, 0),
);

const page7MaxScore = computed(() =>
  lpsPage7Solutions.reduce((total, solution, rowIdx) => {
    const rowTotal = solution.correctOptionIndices.reduce((rowSum, idx, promptIdx) => {
      if (isPage7ExamplePrompt(rowIdx, promptIdx)) return rowSum;
      return idx !== null ? rowSum + 1 : rowSum;
    }, 0);

    return total + rowTotal;
  }, 0),
);

function scorePage8Row(rowIdx: number, solutions: LpsPage8Solution, responses: LpsPage8ResponseRow) {
  return solutions.correctOptionIndices.reduce((sum, correctIdx, promptIdx) => {
    if (isPage8ExamplePrompt(rowIdx, promptIdx)) return sum;
    if (correctIdx === null || correctIdx === undefined) return sum;
    const picks = responses.prompts?.[promptIdx];
    if (!picks?.length) return sum;
    return picks[correctIdx] ? sum + 1 : sum;
  }, 0);
}

const page8Score = computed(() =>
  page8Responses.value.reduce((total, response, idx) => {
    const solutions = lpsPage8Solutions[idx];
    if (!solutions) return total;
    return total + scorePage8Row(idx, solutions, response);
  }, 0),
);

const page8MaxScore = computed(() =>
  lpsPage8Solutions.reduce((total, solution, rowIdx) => {
    const rowTotal = solution.correctOptionIndices.reduce((rowSum, idx, promptIdx) => {
      if (isPage8ExamplePrompt(rowIdx, promptIdx)) return rowSum;
      return idx !== null ? rowSum + 1 : rowSum;
    }, 0);

    return total + rowTotal;
  }, 0),
);

const totalScore = computed(
  () =>
    page1Score.value + page5Score.value + page6Score.value + page7Score.value + page8Score.value,
);
const totalMaxScore = computed(
  () =>
    page1MaxScore.value + page5MaxScore.value + page6MaxScore.value + page7MaxScore.value + page8MaxScore.value,
);
</script>

<template>

  <Head :title="testLabel" />

  <!-- Whole page scrolls -->
  <div class="min-h-screen overflow-x-auto bg-muted/15 p-4">
   <div class="flex justify-center">
     <div class="mx-auto w-[1120px] max-w-none">
      <!-- Top bar -->
      <div class="mb-4 flex items-end justify-between gap-4">
        <div class="space-y-1">
          <h1 class="text-xl font-bold tracking-tight">{{ testLabel }}</h1>
          <div class="text-xs text-muted-foreground" v-if="showTest">Seite {{ pageIndex + 1 }} / {{ pageCount }}</div>
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
              Der {{ testLabel }}-Test umfasst sieben Schritte. Beginnen Sie mit den Spalten 1 und 2, anschließend folgen
              die Spalten 3, 4, 5, 7, 8 und 9 jeweils auf einer eigenen Seite. Arbeiten Sie jede Zeile von oben nach
              unten durch.
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
            <Button variant="secondary" size="sm"
              :disabled="pageIndex >= pageCount - 1 || isAnyVisibleColumnActive" @click="nextPage">
              Weiter
            </Button>
          </div>

          <div class="flex items-center gap-2">
            <Button variant="destructive" size="sm" @click="finishTest">Test beenden</Button>
          </div>
        </div>

        <!-- Column timers / start buttons -->
        <div class="rounded-xl border bg-background px-4 py-3 shadow-sm">
          <div class="flex items-center justify-between gap-4">
            <div class="text-xs text-muted-foreground">
              Spalte starten (Dauer: {{ sectionDurationText }}). Nur eine Spalte gleichzeitig.
            </div>

            <div class="flex items-center gap-3">
              <div v-for="entry in visibleColumnStates" :key="`column-state-${entry.idx}`" class="flex items-center gap-2">
                <div
                  class="rounded-lg border px-3 py-2 text-xs"
                  :class="entry.state?.status === 'active'
                    ? 'border-destructive/50 bg-destructive/5 text-destructive'
                    : entry.state?.status === 'ready'
                      ? 'border-foreground/20 bg-background text-foreground'
                      : entry.state?.status === 'finished'
                        ? 'border-foreground/10 bg-muted/30 text-muted-foreground'
                        : 'border-foreground/10 bg-muted/20 text-muted-foreground'"
                >
                  <div class="flex items-center gap-2">
                    <span class="font-semibold">Sp {{ getColumnLabel(entry.idx) }}</span>
                    <span v-if="entry.state?.status === 'active'" class="tabular-nums font-semibold">
                      {{ formatColumnRemaining(entry.state.remaining) }}
                    </span>
                    <span v-else-if="entry.state?.status === 'ready'">bereit</span>
                    <span v-else-if="entry.state?.status === 'finished'">fertig</span>
                    <span v-else>gesperrt</span>
                  </div>
                </div>

                <Button v-if="entry.state?.status === 'ready'" size="sm" :disabled="isAnyColumnActive"
                  @click="startColumn(entry.idx)">
                  Start
                </Button>
              </div>
            </div>
          </div>
        </div>

        <!-- Page 1: Spalten 1 + 2 -->
        <div v-if="pageIndex === 0" class="space-y-3">
          <div class="rounded-2xl border bg-background p-4 shadow-sm">
            <div class="mb-3 text-center text-[13px] font-extrabold tracking-wide text-foreground">Spalten 1 + 2</div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <div v-for="(row, idx) in lpsRows" :key="`${row.id}-c1`" class="py-[1px]">
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

              <div class="lps-sep">
                <div v-for="(row, idx) in lpsRows" :key="`${row.id}-c2`" class="py-[1px]">
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
            </div>
          </div>
        </div>

        <!-- Page 2: Spalte 3 -->
        <div v-else-if="pageIndex === 1" class="space-y-3">
          <div class="rounded-2xl border bg-background p-4 shadow-sm">
            <div class="mb-4 text-center text-[13px] font-extrabold tracking-wide text-foreground">Spalte 3</div>

            <div class="flex justify-center">
              <div class="w-full max-w-4xl">
                <div v-for="(row, idx) in lpsRows" :key="`${row.id}-c3`" class="py-[10px]">
                  <div v-if="row.column3?.length">
                    <div
                      v-if="row.column3SvgMeta && row.column3.every((option) => option.pathData)"
                      class="flex items-center justify-center leading-none"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        version="1.1"
                        class="lps-column3-svg select-none"
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
                            class="lps-figure-hit"
                            :d="option.pathData"
                          />
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
            </div>
          </div>
        </div>

        <!-- Page 3: Spalte 4 -->
        <div v-else-if="pageIndex === 2" class="space-y-3">
          <div class="rounded-2xl border bg-background p-4 shadow-sm">
            <div class="mb-3 text-center text-[13px] font-extrabold tracking-wide text-foreground">Spalte 4</div>

            <div>
              <div v-for="(row, idx) in lpsRows" :key="`${row.id}-c4`" class="py-[1px]">
                <div class="lps-letters">
                  <button v-for="(char, charIdx) in row.column4.split(' ')" :key="`${row.id}-4-${charIdx}`"
                    type="button" class="lps-letter"
                    :class="page1Responses[idx].col4[charIdx] ? 'lps-letter--selected' : ''"
                    :disabled="!isColumnInteractive('col4')" :aria-pressed="page1Responses[idx].col4[charIdx]"
                    @click="toggleSelection(idx, 'col4', charIdx)">
                    {{ char }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Page 4: Spalte 5 -->
        <div v-else-if="pageIndex === 3" class="space-y-3">
          <div class="rounded-2xl border bg-background p-4 shadow-sm">
            <div class="mb-3 text-center text-[13px] font-extrabold tracking-wide text-foreground">Spalte 5</div>

            <div>
              <div v-for="(row, idx) in lpsRows" :key="`${row.id}-c5`" class="py-[1px]">
                <div class="lps-letters">
                  <button v-for="(char, charIdx) in row.column5.split('')" :key="`${row.id}-5-${charIdx}`"
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

        <!-- Page 5: Spalte 7 -->
        <div v-else-if="pageIndex === 4" class="space-y-3">
          <div class="rounded-2xl border bg-background p-4 shadow-sm">
            <div class="mb-4 text-center text-[13px] font-extrabold tracking-wide text-foreground">Spalte 7</div>

            <div class="flex justify-center">
              <div class="w-full max-w-4xl">
                <div v-for="(row, idx) in lpsPage5Rows" :key="`${row.id}-c7`" class="py-[10px]">
                  <div v-if="row.column7?.length">
                    <div
                      v-if="row.column7SvgMeta && row.column7.every((option) => option.pathData)"
                      class="flex items-center justify-center leading-none"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        version="1.1"
                        class="lps-column3-svg select-none"
                        :viewBox="row.column7SvgMeta.viewBox"
                        :width="row.column7SvgMeta.width"
                        :height="row.column7SvgMeta.height"
                      >
                        <g
                          v-for="(option, optionIdx) in row.column7"
                          :id="option.id"
                          :key="option.id"
                          role="button"
                          class="lps-figure-shape-group"
                          :transform="option.transform"
                          :tabindex="isColumnInteractive('col7') ? 0 : -1"
                          :aria-pressed="page5Responses[idx].col7[optionIdx]"
                          :class="!isColumnInteractive('col7') ? 'lps-figure-shape--disabled' : ''"
                          @click="isColumnInteractive('col7') && togglePage5Selection(idx, optionIdx)"
                          @keydown.enter.prevent="togglePage5Selection(idx, optionIdx)"
                          @keydown.space.prevent="togglePage5Selection(idx, optionIdx)"
                        >
                          <path
                            class="lps-figure-hit"
                            :d="option.pathData"
                          />
                          <path
                            fill="#090d0e"
                            class="lps-figure-shape"
                            :class="[
                              page5Responses[idx].col7[optionIdx] ? 'lps-figure-shape--selected' : '',
                            ]"
                            :d="option.pathData"
                          />
                          <clipPath :id="`${option.id}-clip`">
                            <path :d="option.pathData" :transform="option.transform" />
                          </clipPath>
                          <line
                            v-if="page5Responses[idx].col7[optionIdx]"
                            class="lps-figure-slash"
                            :clip-path="`url(#${option.id}-clip)`"
                            x1="0"
                            :y1="row.column7SvgMeta.height - 6"
                            :x2="row.column7SvgMeta.width"
                            y2="6"
                          />
                        </g>
                      </svg>
                    </div>
                    <div v-else class="grid grid-cols-8 gap-1">
                      <button
                        v-for="(option, optionIdx) in row.column7"
                        :id="option.id"
                        :key="option.id"
                        type="button"
                        class="lps-figure"
                        :class="page5Responses[idx].col7[optionIdx] ? 'lps-figure--selected' : ''"
                        :disabled="!isColumnInteractive('col7')"
                        :aria-pressed="page5Responses[idx].col7[optionIdx]"
                        @click="togglePage5Selection(idx, optionIdx)"
                      >
                        <img :src="option.src" class="mx-auto h-9 w-9" alt="" />
                      </button>
                    </div>
                  </div>
                  <div v-else class="text-center text-xs text-muted-foreground/60">—</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Page 6: Spalte 8 -->
        <div v-else-if="pageIndex === 5" class="space-y-3">
          <div class="rounded-2xl border bg-background p-4 shadow-sm">
            <div class="mb-4 text-center text-[13px] font-extrabold tracking-wide text-foreground">Spalte 8</div>

            <div class="space-y-6">
              <div v-for="(row, idx) in lpsPage6Rows" :key="`${row.id}-c8`" class="rounded-xl border bg-muted/40 p-4">
                <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-[1.25fr_1fr]">
                  <div class="flex justify-center">
                    <div
                      v-if="row.column8Svg"
                      class="flex items-center justify-center rounded-lg bg-white p-3 shadow-sm"
                      v-html="row.column8Svg"
                      :style="{ width: `${row.column8SvgMeta?.width ?? 420}px`, height: `${row.column8SvgMeta?.height ?? 220}px` }"
                    ></div>
                    <div v-else class="text-center text-xs text-muted-foreground/60">—</div>
                  </div>

                  <div class="space-y-2">
                    <div class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Antwort auswählen</div>
                    <div v-if="page6OptionGroups[idx].length" class="space-y-2">
                      <div
                        v-for="group in page6OptionGroups[idx]"
                        :key="`${row.id}-c8-group-${group.label}`"
                        class="flex items-center gap-3"
                      >
                        <div class="lps-number-label">
                          {{ group.label }}
                        </div>
                        <div class="flex flex-wrap gap-2">
                          <button
                            v-for="groupOption in group.options"
                            :key="`${row.id}-c8-${groupOption.index}`"
                            type="button"
                            class="lps-letter"
                            :class="page6Responses[idx].col8[groupOption.index] ? 'lps-letter--selected' : ''"
                            :disabled="!isColumnInteractive('col8') || !row.column8Svg"
                            :aria-pressed="page6Responses[idx].col8[groupOption.index]"
                            @click="togglePage6Selection(idx, groupOption.index)"
                          >
                            {{ groupOption.option.label }}
                          </button>
                        </div>
                      </div>
                    </div>
                    <div v-else class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                      <button
                        v-for="(option, optionIdx) in row.column8Options"
                        :key="`${row.id}-c8-${optionIdx}`"
                        type="button"
                        class="lps-letter"
                        :class="page6Responses[idx].col8[optionIdx] ? 'lps-letter--selected' : ''"
                        :disabled="!isColumnInteractive('col8') || !row.column8Svg"
                        :aria-pressed="page6Responses[idx].col8[optionIdx]"
                        @click="togglePage6Selection(idx, optionIdx)"
                      >
                        {{ option.label }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Page 7: Spalte 9 -->
        <div v-else-if="pageIndex === 6" class="space-y-3">
          <div class="rounded-2xl border bg-background p-4 shadow-sm">
            <div class="mb-4 text-center text-[13px] font-extrabold tracking-wide text-foreground">Spalte 9</div>

            <div class="space-y-4">
              <div
                v-for="(row, rowIdx) in lpsPage7Rows"
                :key="`${row.id}-c9`"
                class="relative space-y-3"
              >
                <div
                  v-if="page7Arrows[rowIdx]?.length"
                  class="pointer-events-none absolute inset-x-0 top-0 w-full"
                  aria-hidden="true"
                >
                  <div
                    v-for="(arrow, arrowIdx) in page7Arrows[rowIdx]"
                    :key="`${row.id}-arrow-${arrowIdx}`"
                    class="page7-arrow"
                    :style="getPage7ArrowStyle(rowIdx, arrow.from, arrow.to)"
                  ></div>
                </div>
                <div class="grid gap-3 md:grid-cols-3">
                  <div
                    v-for="(prompt, promptIdx) in row.prompts"
                    :key="prompt.id"
                    class="space-y-3 rounded-lg bg-background p-3 shadow-sm"
                  >
                    <div class="flex justify-center">
                      <div
                        class="lps-shape-panel"
                        v-html="prompt.svg"
                        :style="getShapePanelStyle(prompt.svgMeta)"
                      ></div>
                    </div>

                    <div class="flex flex-wrap justify-center gap-2">
                      <button
                        v-for="(option, optionIdx) in prompt.options"
                        :key="`${prompt.id}-${option}`"
                        type="button"
                        class="lps-number-chip"
                        :class="page7Responses[rowIdx]?.prompts?.[promptIdx]?.[optionIdx]
                          ? 'lps-number-chip--selected'
                          : ''"
                        :disabled="!isColumnInteractive('col9') || isPage7ExamplePrompt(rowIdx, promptIdx)"
                        :aria-pressed="page7Responses[rowIdx]?.prompts?.[promptIdx]?.[optionIdx]"
                        @click="togglePage7Selection(rowIdx, promptIdx, optionIdx)"
                      >
                        {{ option }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

          <div v-else-if="pageIndex === 7" class="space-y-3">
            <div class="rounded-2xl border bg-background p-4 shadow-sm">
              <div class="mb-1 text-center text-[13px] font-extrabold tracking-wide text-foreground">Spalte 10</div>
              <p class="text-center text-xs text-muted-foreground">Finde die passende Form zu jeder Vorlage.</p>

            <div class="space-y-4">
              <div
                v-for="(row, rowIdx) in lpsPage8Rows"
                :key="`${row.id}-c10`"
                class="rounded-xl border bg-muted/30 p-4 shadow-sm"
              >
                <div class="grid gap-3 md:grid-cols-2">
                  <div
                    v-for="(prompt, promptIdx) in row.prompts"
                    :key="prompt.id"
                    class="space-y-3 rounded-lg bg-background p-3 shadow-sm"
                  >
                    <div class="flex justify-center">
                      <div
                        class="lps-shape-panel page8-shape-panel"
                        v-html="prompt.svg"
                        :style="getShapePanelStyle(prompt.svgMeta)"
                      ></div>
                    </div>

                    <div class="flex flex-wrap justify-center gap-2">
                      <button
                        v-for="(option, optionIdx) in prompt.options"
                        :key="option.id"
                        type="button"
                        class="page8-option"
                        :class="page8Responses[rowIdx]?.prompts?.[promptIdx]?.[optionIdx]
                          ? 'page8-option--selected'
                          : ''"
                        :disabled="!isColumnInteractive('col10') || isPage8ExamplePrompt(rowIdx, promptIdx)"
                        :aria-pressed="page8Responses[rowIdx]?.prompts?.[promptIdx]?.[optionIdx]"
                        @click="togglePage8Selection(rowIdx, promptIdx, optionIdx)"
                      >
                        <span v-html="option.svg"></span>
                      </button>
                    </div>
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
                {{ totalMaxScore ? `${totalScore} / ${totalMaxScore}` : '–' }}
              </span>
            </div>
          </div>
        </div>
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
  gap: 22px;
  /* very close */
  align-items: baseline;
  justify-content: center;
}

.lps-column3-svg {
  display: block;
  line-height: 0;
  height: 32px;
  width: auto;
}

.lps-letter {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 12px;
  height: 35px;
  padding: 0 1px;
  border: 0;
  border-radius: 0;
  background: transparent;
  font-weight: 900;
  font-size: 24px;
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
  /* Allow clicks anywhere inside the shape's bounding box. */
  pointer-events: bounding-box;
}

.lps-figure-hit {
  fill: transparent;
  stroke: transparent;
  stroke-width: 16px;
  stroke-linejoin: round;
  /* Expand the clickable surface beyond the visible shape. */
  pointer-events: stroke;
}

.lps-figure-shape {
  transition: opacity 120ms ease, transform 120ms ease;
  /* Ensure the entire painted area (not just the outline) captures pointer events. */
  pointer-events: visibleFill;
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

.lps-number-label {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 1.25rem;
  height: 35px;
  padding: 0 2px;
  font-weight: 900;
  font-size: 24px;
  line-height: 1;
  letter-spacing: -0.02em;
  color: inherit;
}

.lps-shape-panel {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: var(--shape-width, 170px);
  height: var(--shape-height, 140px);
  min-width: 170px;
  min-height: 140px;
  background: white;
  border-radius: 10px;
  box-shadow: 0 4px 18px rgb(15 23 42 / 0.08);
  overflow: hidden;
}

.lps-shape-panel svg {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.page7-arrow {
  position: absolute;
  top: 64px;
  height: 18px;
  border-radius: 9999px;
  background: #4b5563;
  z-index: 1;
}

.page7-arrow::after {
  content: '';
  position: absolute;
  top: 50%;
  right: -26px;
  transform: translateY(-50%);
  border-left: 34px solid #4b5563;
  border-top: 18px solid transparent;
  border-bottom: 18px solid transparent;
}

.lps-number-chip {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 44px;
  border-radius: 10px;
  border: 1px solid rgb(226 232 240);
  background: white;
  font-weight: 800;
  font-size: 18px;
  line-height: 1;
  letter-spacing: -0.01em;
  color: rgb(15 23 42);
  transition: transform 120ms ease, box-shadow 120ms ease, border-color 120ms ease, background-color 120ms ease;
}

.lps-number-chip:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgb(15 23 42 / 0.12);
  border-color: rgb(203 213 225);
}

.lps-number-chip:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.lps-number-chip--selected {
  border-color: rgb(239 68 68);
  background: rgb(254 242 242);
}

.page8-shape-panel {
  min-width: 180px;
  min-height: 150px;
}

.page8-option {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 90px;
  height: 90px;
  border-radius: 14px;
  border: 1px solid rgb(226 232 240);
  background: white;
  box-shadow: 0 4px 14px rgb(15 23 42 / 0.08);
  transition: transform 120ms ease, box-shadow 120ms ease, border-color 120ms ease, background-color 120ms ease;
}

.page8-option span {
  display: inline-flex;
  width: 72px;
  height: 72px;
}

.page8-option svg {
  width: 100%;
  height: 100%;
}

.page8-option:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 8px 18px rgb(15 23 42 / 0.12);
  border-color: rgb(203 213 225);
}

.page8-option:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.page8-option--selected {
  border-color: rgb(239 68 68);
  box-shadow: 0 0 0 3px rgb(254 226 226);
  background: rgb(254 242 242);
}

/* Vertical separator between column 1 and 2 */
/* .lps-sep {
  border-left: 2px solid rgb(17 17 17 / 0.9);
  padding-left: 5px;
} */
</style>
