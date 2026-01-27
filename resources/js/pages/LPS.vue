<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import TimeRemainingAlerts from '@/components/TimeRemainingAlerts.vue';
import { useTeacherForceFinish } from '@/composables/useTeacherForceFinish';
import { getLpsDataset, type LpsColumn3Option, type LpsPage1Solution } from '@/pages/Questions/LPSPage1';
import { getLpsPage5Dataset, type LpsPage5Solution } from '@/pages/Questions/LPSPage5';
import { getLpsPage6Dataset, type LpsPage6Option, type LpsPage6Solution } from '@/pages/Questions/LPSPage6';
import { getLpsPage7Dataset, type LpsPage7Prompt, type LpsPage7Solution } from '@/pages/Questions/LPSPage7';
import { getLpsPage8Dataset, type LpsPage8Prompt, type LpsPage8Solution } from '@/pages/Questions/LPSPage8';
import { getLpsPage9Dataset, type LpsPage9Prompt, type LpsPage9Solution } from '@/pages/Questions/LPSPage9';
import { getLpsPage10Dataset } from '@/pages/Questions/LPSPage10';
import { getLpsPage11Dataset, type LpsPage11Solution } from '@/pages/Questions/LPSPage11';
import { LPS_PAGE1_COLUMN3_B_EXMPLE1_SHAPES, LPS_PAGE1_COLUMN3_B_EXMPLE2_SHAPES, sortColumn3Shapes } from '@/pages/Questions/lpsPage1SvgShapes';
import { LPS_PAGE5_COLUMN7_B_EXAMPLE1_SHAPES, LPS_PAGE5_COLUMN7_B_EXAMPLE2_SHAPES } from '@/pages/Questions/lpsPage5SvgShapes';
import { LPS_PAGE6_COLUMN8_B_EXAMPLE } from '@/pages/Questions/lpsPage6SvgShapes';
import { LPS_PAGE8_OPTION_SVGS } from '@/pages/Questions/lpsPage8SvgShapes';

type LpsPage1ResponseRow = { col1: boolean[]; col2: boolean[]; col3: boolean[]; col4: boolean[]; col5: boolean[] };
type LpsPage5ResponseRow = { col7: boolean[] };
type LpsPage6ResponseRow = { col8: boolean[] };
type LpsPage7ResponseRow = { prompts: boolean[][] };
type LpsPage8ResponseRow = { prompts: boolean[][] };
type LpsPage9ResponseRow = { prompts: boolean[][] };
type LpsPage10ResponseRow = { paths: boolean[] };
type LpsPage11ResponseRow = { col13: boolean[]; col14: boolean[] };
type LpsPage6OptionGroup = { label: string; options: Array<{ option: LpsPage6Option; index: number }> };
type LpsColumnExample = { word: string; selectedIndex: number };
type LpsColumn3Example = {
  options: LpsColumn3Option[];
  selectedIndex: number;
  svgMeta: { viewBox: string; width: number; height: number };
};
type LpsPage6ExampleRow = {
  svg: string;
  svgMeta?: { width: number; height: number; viewBox?: string };
  options: LpsPage6Option[];
  selected: boolean[];
  optionGroups: LpsPage6OptionGroup[];
};
type LpsSequenceExample = { tokens: string[]; selectedIndex?: number };

type ColumnStatus = 'locked' | 'ready' | 'active' | 'finished';

type LpsColumnState = { status: ColumnStatus; remaining: number };


const BASE_COLUMN_DURATION_SECONDS = [120, 180, 300, 480, 180, 120, 240, 180, 180, 60, 120];
// const BASE_COLUMN_DURATION_SECONDS = [1, 1, 1, 1, 1, 31, 1, 1, 1, 1, 1];
const BASE_COLUMN_LABELS = [1, 2, 3, 4, 5, 7, 8, 9, 10, 11, 12];
const BASE_PAGE_SECTIONS = [
  { title: 'Spalten 1 + 2', columnIndices: [0, 1] },
  { title: 'Spalte 3', columnIndices: [2] },
  { title: 'Spalte 4', columnIndices: [3] },
  { title: 'Spalte 5', columnIndices: [4] },
  { title: 'Spalte 7', columnIndices: [5] },
  { title: 'Spalte 8', columnIndices: [6] },
  { title: 'Spalte 9', columnIndices: [7] },
  { title: 'Spalte 10', columnIndices: [8] },
  { title: 'Spalte 11', columnIndices: [9] },
  { title: 'Spalte 12', columnIndices: [10] },
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
    page9?: LpsPage9ResponseRow[];
    page9_score?: number;
    page9_max_score?: number;
    page10?: LpsPage10ResponseRow[];
    page10_score?: number;
    page10_max_score?: number;
    page11?: LpsPage11ResponseRow[];
    page11_positive_score?: number;
    page11_negative_score?: number;
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
const { rows: lpsPage9Rows, solutions: lpsPage9Solutions } = getLpsPage9Dataset(props.testName);
const { rows: lpsPage10Rows, solutions: lpsPage10Solutions } = getLpsPage10Dataset(props.testName);
const { rows: lpsPage11Rows, solutions: lpsPage11Solutions } = getLpsPage11Dataset(props.testName);

const PAGE7_GRID_GAP_PX = 12;
// Update this width if the shape panels get larger/smaller so the arrows anchor to the panel edges.
const PAGE7_DEFAULT_SHAPE_WIDTH = 170;
// Increase/decrease this inset to shorten/lengthen the page 7 arrows without touching the CSS.
const PAGE7_ARROW_INSET_PX = 18;
const PAGE9_GRID_GAP_PX = 12;
const PAGE9_DEFAULT_SHAPE_WIDTH = 170;
const PAGE9_ARROW_INSET_PX = 18;
const PAGE8_ARROW_ROWS: Record<number, number[]> = {
  0: [1],
  1: [0],
};
const PAGE8_ARROW_TOP = 'calc(100% - 8px)';
const PAGE8_GRID_GAP_PX = 12;
const PAGE8_ROW_PADDING_PX = 16;
const page7Arrows: Record<number, Array<{ from: number; to: number }>> = {
  0: [{ from: 2, to: 3 }],
  1: [{ from: 1, to: 2 }],
};
const page9Arrows = page7Arrows;
const column1Examples: LpsColumnExample[] = [
  { word: 'Kraide', selectedIndex: 2 },
  { word: 'Tellor', selectedIndex: 4 },
];
const column2Examples: LpsColumnExample[] = [
  { word: 'Ffeife', selectedIndex: 0 },
  { word: 'Schole', selectedIndex: 3 },
];
const column4Examples: LpsColumnExample[] = [
  { word: '2 2 2 2 2 3 2 2 2', selectedIndex: 5 },
  { word: 'a b a b a b a a a', selectedIndex: 7 },
];
const column5Examples: LpsColumnExample[] = [
  { word: 'GZWER', selectedIndex: 1 },
  { word: 'CKERA', selectedIndex: 4 },
];
const column3ExampleMeta = { viewBox: '0 0 670 70', width: 670, height: 70 } as const;
const column3Examples: LpsColumn3Example[] = [
  {
    options: sortColumn3Shapes(LPS_PAGE1_COLUMN3_B_EXMPLE1_SHAPES).map((pathData, idx) => ({
      id: `lps-b-example1-shape${idx + 1}`,
      pathData,
    })),
    selectedIndex: 4,
    svgMeta: column3ExampleMeta,
  },
  {
    options: sortColumn3Shapes(LPS_PAGE1_COLUMN3_B_EXMPLE2_SHAPES).map((pathData, idx) => ({
      id: `lps-b-example2-shape${idx + 1}`,
      pathData,
    })),
    selectedIndex: 7,
    svgMeta: column3ExampleMeta,
  },
];
const column7ExampleMeta = { viewBox: '0 0 320 70', width: 320, height: 70 } as const;
const column7Examples: LpsColumn3Example[] = [
  {
    options: LPS_PAGE5_COLUMN7_B_EXAMPLE1_SHAPES.map((pathData, idx) => ({
      id: `lps-p5c7-example1-shape${idx + 1}`,
      pathData,
    })),
    selectedIndex: 3,
    svgMeta: column7ExampleMeta,
  },
  {
    options: LPS_PAGE5_COLUMN7_B_EXAMPLE2_SHAPES.map((pathData, idx) => ({
      id: `lps-p5c7-example2-shape${idx + 1}`,
      pathData,
    })),
    selectedIndex: 1,
    svgMeta: column7ExampleMeta,
  },
];
const column13Examples: LpsSequenceExample[] = [
  { tokens: 'A00B00'.split('') },
  { tokens: '00L0M0'.split(''), selectedIndex: 5 },
  { tokens: '0S000R'.split('') },
  { tokens: '00F00T'.split(''), selectedIndex: 4 },
];
const column14Examples: LpsSequenceExample[] = [
  { tokens: 'A03B00'.split(''), selectedIndex: 2 },
  { tokens: '00L000'.split(''), selectedIndex: 4 },
  { tokens: '0S000R'.split('') },
  { tokens: '00F00T'.split('') },
];
function buildPage6ExampleSelection(example: (typeof LPS_PAGE6_COLUMN8_B_EXAMPLE)[number]) {
  const selection = buildSelection(example.options.length ?? 0);
  example.correctIndices?.forEach((idx) => {
    if (selection[idx] !== undefined) {
      selection[idx] = true;
    }
  });
  return selection;
}

const page6ExampleRows: LpsPage6ExampleRow[] = LPS_PAGE6_COLUMN8_B_EXAMPLE.map((example) => ({
  svg: example.svg,
  svgMeta: example.svgMeta,
  options: example.options,
  selected: buildPage6ExampleSelection(example),
  optionGroups: buildPage6OptionGroups(example.options),
}));
const isPage7ExamplePrompt = (rowIdx: number, promptIdx: number) =>
  props.testName === 'LPS-B' && rowIdx === 0 && promptIdx < 2;
const isPage8ExamplePrompt = (rowIdx: number, promptIdx: number) =>
  props.testName === 'LPS-B' && promptIdx === 0 && rowIdx < 2;
const isPage9ExamplePrompt = (rowIdx: number, promptIdx: number) =>
  props.testName === 'LPS-B' && rowIdx === 0 && promptIdx < 2;
const isPage10ExampleRow = (rowIdx: number) => props.testName === 'LPS-B' && rowIdx < 2;

const showTest = ref(false);
const pageIndex = ref(0);
const isLpsB = computed(() => props.testName === 'LPS-B');
const pageSections = computed(() => {
  if (!isLpsB.value) return BASE_PAGE_SECTIONS;
  return [
    ...BASE_PAGE_SECTIONS.slice(0, 4),
    { title: 'Spalte 6', columnIndices: [] },
    ...BASE_PAGE_SECTIONS.slice(4),
    { title: 'Spalten 13 + 14', columnIndices: [11, 12] },
  ];
});
const pageCount = computed(() => pageSections.value.length);
const currentSection = computed(() => pageSections.value[pageIndex.value] ?? pageSections.value[0]);
const visibleColumnIndices = computed(() => currentSection.value.columnIndices);
const visibleColumnStates = computed(() =>
  visibleColumnIndices.value.map((idx) => ({ idx, state: columnStates.value[idx] })),
);
const pageIndexByColumn = computed(() => {
  const offset = isLpsB.value ? 1 : 0;
  return {
    col6: isLpsB.value ? 4 : -1,
    col7: 4 + offset,
    col8: 5 + offset,
    col9: 6 + offset,
    col10: 7 + offset,
    col11: 8 + offset,
    col12: 9 + offset,
    col14: 10 + offset,
  } as const;
});
const alphabetLetters = 'abcdefghijklmnopqrstuvwxyz'.split('');
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
function getPage9ShapeWidth(rowIdx: number) {
  return lpsPage9Rows[rowIdx]?.prompts?.[0]?.svgMeta?.width ?? PAGE9_DEFAULT_SHAPE_WIDTH;
}

function getPage7ArrowStyle(rowIdx: number, fromCol: number, toCol: number) {
  const columnWidth = `calc((100% - (${PAGE7_GRID_GAP_PX * 2}px)) / 3)`;
  const shapeWidth = getPage7ShapeWidth(rowIdx);
  const startLeft = `calc((${columnWidth} * ${fromCol - 0.5}) + ${PAGE7_GRID_GAP_PX * (fromCol - 1)}px + ${shapeWidth / 2 + PAGE7_ARROW_INSET_PX
    }px)`;
  const width = `calc((${columnWidth} * ${toCol - fromCol}) + ${PAGE7_GRID_GAP_PX * (toCol - fromCol)}px - ${shapeWidth + PAGE7_ARROW_INSET_PX * 2
    }px)`;

  return { left: startLeft, width } as const;
}
function getPage9ArrowStyle(rowIdx: number, fromCol: number, toCol: number) {
  const columnWidth = `calc((100% - (${PAGE9_GRID_GAP_PX * 2}px)) / 3)`;
  const shapeWidth = getPage9ShapeWidth(rowIdx);
  const startLeft = `calc((${columnWidth} * ${fromCol - 0.5}) + ${PAGE9_GRID_GAP_PX * (fromCol - 1)}px + ${shapeWidth / 2 + PAGE9_ARROW_INSET_PX
    }px)`;
  const width = `calc((${columnWidth} * ${toCol - fromCol}) + ${PAGE9_GRID_GAP_PX * (toCol - fromCol)}px - ${shapeWidth + PAGE9_ARROW_INSET_PX * 2
    }px)`;

  return { left: startLeft, width } as const;
}
function getPage8ArrowStyle(columnIdx: number) {
  const columnWidth = `calc((100% - ${PAGE8_GRID_GAP_PX}px) / 2)`;
  const left = columnIdx === 0
    ? `calc(${PAGE8_ROW_PADDING_PX}px + (${columnWidth} / 2))`
    : `calc(${PAGE8_ROW_PADDING_PX}px + ${columnWidth} + ${PAGE8_GRID_GAP_PX}px + (${columnWidth} / 2))`;
  return { left, top: PAGE8_ARROW_TOP } as const;
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

const columnDurations = computed(() =>
  props.testName === 'LPS-B' ? [...BASE_COLUMN_DURATION_SECONDS, 180, 180] : BASE_COLUMN_DURATION_SECONDS,
);
const columnLabels = computed(() =>
  props.testName === 'LPS-B' ? [...BASE_COLUMN_LABELS, 14, 13] : BASE_COLUMN_LABELS,
);

const columnStates = ref<LpsColumnState[]>(
  props.pausedTestResult?.columnStates?.length === columnDurations.value.length
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

function buildPage9ExamplePromptSelection(rowIdx: number, promptIdx: number, prompt: LpsPage9Prompt) {
  const selection = buildSelection(prompt.options.length ?? 0);
  const correctIdx = lpsPage9Solutions[rowIdx]?.correctOptionIndices?.[promptIdx];

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

const page9Responses = ref<LpsPage9ResponseRow[]>(
  lpsPage9Rows.map((row, idx) => {
    const pausedRow = props.pausedTestResult?.page9?.[idx];
    return {
      prompts: row.prompts.map((prompt, promptIdx) => {
        if (isPage9ExamplePrompt(idx, promptIdx)) {
          return buildPage9ExamplePromptSelection(idx, promptIdx, prompt);
        }

        return buildSelection(prompt.options.length ?? 0, pausedRow?.prompts?.[promptIdx]);
      }),
    };
  }),
);

function buildPage10ExampleSelection(rowIdx: number) {
  const selection = buildSelection(lpsPage10Rows[rowIdx]?.options?.length ?? 0);
  const correctIdx = lpsPage10Solutions[rowIdx]?.correctIndex;

  if (typeof correctIdx === 'number' && selection[correctIdx] !== undefined) {
    selection[correctIdx] = true;
  }

  return selection;
}

const page10Responses = ref<LpsPage10ResponseRow[]>(
  lpsPage10Rows.map((row, idx) => {
    const pausedRow = props.pausedTestResult?.page10?.[idx];
    const baseSelection = isPage10ExampleRow(idx)
      ? buildPage10ExampleSelection(idx)
      : buildSelection(row.options.length ?? 0, pausedRow?.paths);

    return { paths: baseSelection };
  }),
);

function getSequenceTokens(value: string) {
  if (!value) return [];
  return value.trim().split(/\s+/);
}

const page11Responses = ref<LpsPage11ResponseRow[]>(
  lpsPage11Rows.map((row, idx) => {
    const pausedRow = props.pausedTestResult?.page11?.[idx];
    const col13Tokens = getSequenceTokens(row.column13);
    const col14Tokens = getSequenceTokens(row.column14);
    return {
      col13: buildSelection(col13Tokens.length, pausedRow?.col13),
      col14: buildSelection(col14Tokens.length, pausedRow?.col14),
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
  pageIndex.value = Math.min(pageIndex.value, pageCount.value - 1);
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
  [
    page1Responses,
    page5Responses,
    page6Responses,
    page7Responses,
    page8Responses,
    page9Responses,
  page10Responses,
  page11Responses,
  pageIndex,
  totalElapsed,
  columnStates,
],
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
      page9: page9Responses.value,
      page9_score: page9Score.value,
      page9_max_score: page9MaxScore.value,
      page10: page10Responses.value,
      page10_score: page10Score.value,
      page10_max_score: page10MaxScore.value,
      page11: page11Responses.value,
      page11_positive_score: page11PositiveScore.value,
      page11_negative_score: page11NegativeScore.value,
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
  pageIndex.value = Math.min(pageIndex.value + 1, pageCount.value - 1);
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
    page9: page9Responses.value,
    page10: page10Responses.value,
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
    page9_score: page9Score.value,
    page9_max_score: page9MaxScore.value,
    page10_score: page10Score.value,
    page10_max_score: page10MaxScore.value,
    page11: page11Responses.value,
    page11_positive_score: page11PositiveScore.value,
    page11_negative_score: page11NegativeScore.value,
  });
}

function formatTime(seconds: number) {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins}:${secs.toString().padStart(2, '0')}`;
}

type ColumnKey = keyof LpsPage1ResponseRow | 'col7' | 'col8' | 'col9' | 'col10' | 'col11' | 'col12' | 'col13' | 'col14';

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
  col11: 9,
  col12: 10,
  col14: 11,
  col13: 12,
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

function togglePage9Selection(rowIdx: number, promptIdx: number, optionIdx: number) {
  if (!isColumnInteractive('col11') || isPage9ExamplePrompt(rowIdx, promptIdx)) return;
  const row = page9Responses.value[rowIdx];
  if (!row?.prompts?.[promptIdx]?.length) return;
  const currentlySelected = row.prompts[promptIdx][optionIdx];
  row.prompts[promptIdx] = row.prompts[promptIdx].map((_, idx) => (idx === optionIdx ? !currentlySelected : false));
}

function togglePage10Selection(rowIdx: number, optionIdx: number) {
  if (!isColumnInteractive('col12') || isPage10ExampleRow(rowIdx)) return;
  const row = page10Responses.value[rowIdx];
  if (!row?.paths?.length) return;
  const currentlySelected = row.paths[optionIdx];
  row.paths = row.paths.map((_, idx) => (idx === optionIdx ? !currentlySelected : false));
}

function togglePage11Selection(rowIdx: number, columnKey: 'col13' | 'col14', tokenIdx: number) {
  if (!isColumnInteractive(columnKey)) return;
  const row = page11Responses.value[rowIdx];
  if (!row?.[columnKey]?.length) return;
  const currentlySelected = row[columnKey][tokenIdx];
  row[columnKey] = row[columnKey].map((selected, idx) => (idx === tokenIdx ? !currentlySelected : selected));
}

function clearPage11Selection(rowIdx: number, columnKey: 'col13' | 'col14', tokenIdx: number) {
  if (!isColumnInteractive(columnKey)) return;
  const row = page11Responses.value[rowIdx];
  if (!row?.[columnKey]?.length) return;
  row[columnKey] = row[columnKey].map((selected, idx) => (idx === tokenIdx ? false : selected));
}

function handlePage11Click(
  event: MouseEvent,
  rowIdx: number,
  columnKey: 'col13' | 'col14',
  tokenIdx: number,
) {
  if (event.detail > 1) return;
  togglePage11Selection(rowIdx, columnKey, tokenIdx);
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
  return columnLabels.value[idx] ?? idx + 1;
}

function formatSectionDurations(indices: number[]) {
  return indices
    .map((idx) => `Sp ${getColumnLabel(idx)} ${formatTime(getColumnDuration(idx))}`)
    .join(', ');
}

function getColumnDuration(columnIdx: number) {
  return columnDurations.value[columnIdx] ?? columnDurations.value[0];
}

function createInitialColumnStates(): LpsColumnState[] {
  return columnDurations.value.map((duration, idx) => ({
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

function scorePage1Column(columnKey: keyof LpsPage1Solution, responseKey: keyof LpsPage1ResponseRow) {
  return page1Responses.value.reduce((total, response, idx) => {
    const correctIndices = lpsSolutions[idx]?.[columnKey] ?? [];
    const picks = response[responseKey];
    if (!correctIndices.length || !picks?.length) return total;
    const correctPicks = correctIndices.filter((index) => picks[index]);
    return total + correctPicks.length;
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

const page1ColumnScores = computed(() => ({
  col1: scorePage1Column('col1', 'col1'),
  col2: scorePage1Column('col2', 'col2'),
  col3: scorePage1Column('col3', 'col3'),
  col4: scorePage1Column('col4', 'col4'),
  col5: scorePage1Column('col5', 'col5'),
}));

const page1ColumnMaxScores = computed(() => ({
  col1: lpsSolutions.reduce((total, solution) => total + (solution.col1?.length ?? 0), 0),
  col2: lpsSolutions.reduce((total, solution) => total + (solution.col2?.length ?? 0), 0),
  col3: lpsSolutions.reduce((total, solution) => total + (solution.col3?.length ?? 0), 0),
  col4: lpsSolutions.reduce((total, solution) => total + (solution.col4?.length ?? 0), 0),
  col5: lpsSolutions.reduce((total, solution) => total + (solution.col5?.length ?? 0), 0),
}));

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

function scorePage9Row(rowIdx: number, solutions: LpsPage9Solution, responses: LpsPage9ResponseRow) {
  return solutions.correctOptionIndices.reduce((sum, correctIdx, promptIdx) => {
    if (isPage9ExamplePrompt(rowIdx, promptIdx)) return sum;
    if (correctIdx === null || correctIdx === undefined) return sum;
    const picks = responses.prompts?.[promptIdx];
    if (!picks?.length) return sum;
    return picks[correctIdx] ? sum + 1 : sum;
  }, 0);
}

function scorePage10Row(rowIdx: number, responses: LpsPage10ResponseRow) {
  if (isPage10ExampleRow(rowIdx)) return 0;
  const correctIdx = lpsPage10Solutions[rowIdx]?.correctIndex;
  const picks = responses.paths;
  if (correctIdx === null || correctIdx === undefined || !picks?.length) return 0;
  return picks[correctIdx] ? 1 : 0;
}

function scorePage11ColumnRow(
  rowIdx: number,
  columnKey: keyof LpsPage11Solution,
  solutions: LpsPage11Solution,
  responses: LpsPage11ResponseRow,
) {
  const correctIndices = solutions[columnKey] ?? [];
  const picks = responses[columnKey as keyof LpsPage11ResponseRow] as boolean[] | undefined;
  if (!picks?.length) return { positive: 0, negative: 0 };
  const selectedIndices = picks.flatMap((selected, idx) => (selected ? [idx] : []));
  if (!selectedIndices.length) return { positive: 0, negative: 0 };
  const row = lpsPage11Rows[rowIdx];
  const columnValue = columnKey === 'col13' ? row?.column13 : row?.column14;
  const tokens = getSequenceTokens(columnValue ?? '');
  const correctTokens = correctIndices.map((idx) => tokens[idx]).filter((token) => token !== undefined);
  const remainingCorrect = [...correctTokens];
  let positive = 0;
  let negative = 0;

  selectedIndices.forEach((idx) => {
    const token = tokens[idx];
    const matchIndex = remainingCorrect.indexOf(token);
    if (matchIndex !== -1) {
      positive += 1;
      remainingCorrect.splice(matchIndex, 1);
      return;
    }
    negative += 1;
  });

  return { positive, negative: columnKey === 'col13' ? negative : 0 };
}

const page9Score = computed(() =>
  page9Responses.value.reduce((total, response, idx) => {
    const solutions = lpsPage9Solutions[idx];
    if (!solutions) return total;
    return total + scorePage9Row(idx, solutions, response);
  }, 0),
);

const page10Score = computed(() =>
  page10Responses.value.reduce((total, response, idx) => total + scorePage10Row(idx, response), 0),
);

const page11ColumnScores = computed(() => {
  const scoreColumn = (columnKey: keyof LpsPage11Solution) =>
    page11Responses.value.reduce(
      (totals, response, idx) => {
        const solutions = lpsPage11Solutions[idx];
        if (!solutions) return totals;
        const rowScore = scorePage11ColumnRow(idx, columnKey, solutions, response);
        return {
          positive: totals.positive + rowScore.positive,
          negative: totals.negative + rowScore.negative,
        };
      },
      { positive: 0, negative: 0 },
    );

  return {
    col13: scoreColumn('col13'),
    col14: scoreColumn('col14'),
  };
});

const page11ColumnMaxScores = computed(() => ({
  col13: lpsPage11Solutions.reduce((total, solution) => total + (solution.col13?.length ?? 0), 0),
  col14: lpsPage11Solutions.reduce((total, solution) => total + (solution.col14?.length ?? 0), 0),
}));

const page11PositiveScore = computed(() => page11ColumnScores.value.col13.positive);
const page11NegativeScore = computed(() => page11ColumnScores.value.col13.negative);

const page9MaxScore = computed(() =>
  lpsPage9Solutions.reduce((total, solution, rowIdx) => {
    const rowTotal = solution.correctOptionIndices.reduce((rowSum, idx, promptIdx) => {
      if (isPage9ExamplePrompt(rowIdx, promptIdx)) return rowSum;
      return idx !== null ? rowSum + 1 : rowSum;
    }, 0);
    return total + rowTotal;
  }, 0),
);

const page10MaxScore = computed(
  () =>
    lpsPage10Solutions.reduce(
      (total, solution, idx) =>
        total + (solution.correctIndex !== null && !isPage10ExampleRow(idx) ? 1 : 0),
      0,
    ),
);

const totalScore = computed(
  () =>
    page1Score.value +
    page5Score.value +
    page6Score.value +
    page7Score.value +
    page8Score.value +
    page9Score.value +
    page10Score.value,
);
const totalMaxScore = computed(
  () =>
    page1MaxScore.value +
    page5MaxScore.value +
    page6MaxScore.value +
    page7MaxScore.value +
    page8MaxScore.value +
    page9MaxScore.value +
    page10MaxScore.value,
);

type ColumnScoreSummary = { score: number | null; max: number | null; negative?: number | null };

const columnScoreByIndex = computed<Record<number, ColumnScoreSummary>>(() => ({
  0: { score: page1ColumnScores.value.col1, max: page1ColumnMaxScores.value.col1 },
  1: { score: page1ColumnScores.value.col2, max: page1ColumnMaxScores.value.col2 },
  2: { score: page1ColumnScores.value.col3, max: page1ColumnMaxScores.value.col3 },
  3: { score: page1ColumnScores.value.col4, max: page1ColumnMaxScores.value.col4 },
  4: { score: page1ColumnScores.value.col5, max: page1ColumnMaxScores.value.col5 },
  5: { score: page5Score.value, max: page5MaxScore.value },
  6: { score: page6Score.value, max: page6MaxScore.value },
  7: { score: page7Score.value, max: page7MaxScore.value },
  8: { score: page8Score.value, max: page8MaxScore.value },
  9: { score: page9Score.value, max: page9MaxScore.value },
  10: { score: page10Score.value, max: page10MaxScore.value },
  11: {
    score: page11ColumnScores.value.col14.positive,
    max: page11ColumnMaxScores.value.col14,
    negative: page11ColumnScores.value.col14.negative,
  },
  12: {
    score: page11ColumnScores.value.col13.positive,
    max: page11ColumnMaxScores.value.col13,
    negative: page11ColumnScores.value.col13.negative,
  },
}));

function formatColumnScore(columnIdx: number) {
  const scoreEntry = columnScoreByIndex.value[columnIdx];
  if (!scoreEntry || scoreEntry.score === null || scoreEntry.max === null) return '–';
  const scoreText = `${scoreEntry.score} / ${scoreEntry.max}`;
  if (scoreEntry.negative) {
    return `${scoreText} • -${scoreEntry.negative}`;
  }
  return scoreText;
}
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
              Der {{ testLabel }}-Test umfasst sieben Schritte. Beginnen Sie mit den Spalten 1 und 2, anschließend
              folgen
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
              <Button variant="secondary" size="sm" :disabled="pageIndex >= pageCount - 1 || isAnyVisibleColumnActive"
                @click="nextPage">
                Weiter
              </Button>
            </div>

            <div class="flex items-center gap-2">
              <Button variant="destructive" size="sm" @click="finishTest">Test beenden</Button>
            </div>
          </div>

          <!-- Column timers / start buttons -->
          <div v-if="visibleColumnIndices.length" class="rounded-xl border bg-background px-4 py-3 shadow-sm">
            <div class="flex items-center justify-between gap-4">
              <div class="text-xs text-muted-foreground">
                Spalte starten (Dauer: {{ sectionDurationText }}). Nur eine Spalte gleichzeitig.
              </div>

              <div class="flex items-center gap-3">
                <div v-for="entry in visibleColumnStates" :key="`column-state-${entry.idx}`"
                  class="flex items-center gap-2">
                  <div class="rounded-lg border px-3 py-2 text-xs" :class="entry.state?.status === 'active'
                    ? 'border-destructive/50 bg-destructive/5 text-destructive'
                    : entry.state?.status === 'ready'
                      ? 'border-foreground/20 bg-background text-foreground'
                      : entry.state?.status === 'finished'
                        ? 'border-foreground/10 bg-muted/30 text-muted-foreground'
                        : 'border-foreground/10 bg-muted/20 text-muted-foreground'">
                    <div class="flex items-center gap-2">
                      <span class="font-semibold">Sp {{ getColumnLabel(entry.idx) }}</span>
                      <span v-if="entry.state?.status === 'active'" class="tabular-nums font-semibold">
                        {{ formatColumnRemaining(entry.state.remaining) }}
                      </span>
                      <span v-else-if="entry.state?.status === 'ready'">bereit</span>
                      <span v-else-if="entry.state?.status === 'finished'">fertig</span>
                      <span v-else>gesperrt</span>
                    </div>
                    <div v-if="entry.state?.status === 'finished'" class="mt-1 text-[11px] text-muted-foreground">
                      Punkte:
                      <span class="font-semibold text-foreground">
                        {{ formatColumnScore(entry.idx) }}
                      </span>
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
                  <div class="mb-3">
                    <div class="mb-1 text-center text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Beispiel</div>
                    <div v-for="example in column1Examples" :key="`example-col1-${example.word}`" class="py-[1px]">
                      <div class="lps-letters">
                        <button v-for="(char, charIdx) in example.word.split('')" :key="`example-col1-${example.word}-${charIdx}`"
                          type="button" class="lps-letter"
                          :class="charIdx === example.selectedIndex ? 'lps-letter--selected' : ''" disabled
                          :aria-pressed="charIdx === example.selectedIndex">
                          {{ char }}
                        </button>
                      </div>
                    </div>
                  </div>
                  <div class="my-3 border-t border-muted-foreground/20"></div>

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
                  <div class="mb-3">
                    <div class="mb-1 text-center text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Beispiel</div>
                    <div v-for="example in column2Examples" :key="`example-col2-${example.word}`" class="py-[1px]">
                      <div class="lps-letters">
                        <button v-for="(char, charIdx) in example.word.split('')" :key="`example-col2-${example.word}-${charIdx}`"
                          type="button" class="lps-letter"
                          :class="charIdx === example.selectedIndex ? 'lps-letter--selected' : ''" disabled
                          :aria-pressed="charIdx === example.selectedIndex">
                          {{ char }}
                        </button>
                      </div>
                    </div>
                  </div>
                  <div class="my-3 border-t border-muted-foreground/20"></div>

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
                  <div v-if="column3Examples.length" class="mb-4 space-y-3">
                    <div class="text-center text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Beispiel</div>
                    <div v-for="(example, exampleIdx) in column3Examples" :key="`example-col3-${exampleIdx}`"
                      class="py-[10px]">
                      <div class="flex items-center justify-center leading-none">
                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="lps-column3-svg select-none"
                          :viewBox="example.svgMeta.viewBox" :width="example.svgMeta.width"
                          :height="example.svgMeta.height">
                          <g v-for="(option, optionIdx) in example.options" :id="option.id" :key="option.id"
                            role="button" class="lps-figure-shape-group lps-figure-shape--disabled"
                            :aria-pressed="optionIdx === example.selectedIndex" :aria-disabled="true">
                            <path class="lps-figure-hit" :d="option.pathData" />
                            <path fill="#090d0e" class="lps-figure-shape" :class="[
                              optionIdx === example.selectedIndex ? 'lps-figure-shape--selected' : '',
                            ]" :d="option.pathData" />
                            <clipPath :id="`${option.id}-clip`">
                              <path :d="option.pathData" :transform="option.transform" />
                            </clipPath>
                            <line v-if="optionIdx === example.selectedIndex" class="lps-figure-slash"
                              :clip-path="`url(#${option.id}-clip)`" x1="0" :y1="example.svgMeta.height - 6"
                              :x2="example.svgMeta.width" y2="6" />
                          </g>
                        </svg>
                      </div>
                    </div>
                  </div>
                  <div v-if="column3Examples.length" class="my-3 border-t border-muted-foreground/20"></div>
                  <div v-for="(row, idx) in lpsRows" :key="`${row.id}-c3`" class="py-[10px]">
                    <div v-if="row.column3?.length">
                      <div v-if="row.column3SvgMeta && row.column3.every((option) => option.pathData)"
                        class="flex items-center justify-center leading-none">
                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="lps-column3-svg select-none"
                          :viewBox="row.column3SvgMeta.viewBox" :width="row.column3SvgMeta.width"
                          :height="row.column3SvgMeta.height">
                          <g v-for="(option, optionIdx) in row.column3" :id="option.id" :key="option.id" role="button"
                            class="lps-figure-shape-group" :transform="option.transform"
                            :tabindex="isColumnInteractive('col3') ? 0 : -1"
                            :aria-pressed="page1Responses[idx].col3[optionIdx]"
                            :class="!isColumnInteractive('col3') ? 'lps-figure-shape--disabled' : ''"
                            @click="isColumnInteractive('col3') && toggleSelection(idx, 'col3', optionIdx)"
                            @keydown.enter.prevent="toggleSelection(idx, 'col3', optionIdx)"
                            @keydown.space.prevent="toggleSelection(idx, 'col3', optionIdx)">
                            <path class="lps-figure-hit" :d="option.pathData" />
                            <path fill="#090d0e" class="lps-figure-shape" :class="[
                              page1Responses[idx].col3[optionIdx] ? 'lps-figure-shape--selected' : '',
                            ]" :d="option.pathData" />
                            <clipPath :id="`${option.id}-clip`">
                              <path :d="option.pathData" :transform="option.transform" />
                            </clipPath>
                            <line v-if="page1Responses[idx].col3[optionIdx]" class="lps-figure-slash"
                              :clip-path="`url(#${option.id}-clip)`" x1="0" :y1="row.column3SvgMeta.height - 6"
                              :x2="row.column3SvgMeta.width" y2="6" />
                          </g>
                        </svg>
                      </div>
                      <div v-else class="grid grid-cols-8 gap-1">
                        <button v-for="(option, optionIdx) in row.column3" :id="option.id" :key="option.id"
                          type="button" class="lps-figure"
                          :class="page1Responses[idx].col3[optionIdx] ? 'lps-figure--selected' : ''"
                          :disabled="!isColumnInteractive('col3')" :aria-pressed="page1Responses[idx].col3[optionIdx]"
                          @click="toggleSelection(idx, 'col3', optionIdx)">
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
            <div class="lps-alphabet-float" aria-hidden="true">
              <div class="lps-alphabet-box">
                <div class="lps-alphabet-row">
                  <span v-for="(char, charIdx) in alphabetLetters.slice(0, 13)" :key="`alpha-top-${charIdx}`">
                    {{ char }}
                  </span>
                </div>
                <div class="lps-alphabet-row">
                  <span v-for="(char, charIdx) in alphabetLetters.slice(13)" :key="`alpha-bottom-${charIdx}`">
                    {{ char }}
                  </span>
                </div>
              </div>
            </div>
            <div class="rounded-2xl border bg-background p-4 shadow-sm">
              <div class="mb-3 text-center text-[13px] font-extrabold tracking-wide text-foreground">Spalte 4</div>

              <div>
                <div class="mb-3">
                  <div class="mb-1 text-center text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Beispiel</div>
                  <div v-for="example in column4Examples" :key="`example-col4-${example.word}`" class="py-[1px]">
                    <div class="lps-letters">
                      <button v-for="(char, charIdx) in example.word.split(' ')" :key="`example-col4-${example.word}-${charIdx}`"
                        type="button" class="lps-letter"
                        :class="charIdx === example.selectedIndex ? 'lps-letter--selected' : ''" disabled
                        :aria-pressed="charIdx === example.selectedIndex">
                        {{ char }}
                      </button>
                    </div>
                  </div>
                </div>
                <div class="my-3 border-t border-muted-foreground/20"></div>

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
                <div class="mb-3">
                  <div class="mb-1 text-center text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Beispiel</div>
                  <div v-for="example in column5Examples" :key="`example-col5-${example.word}`" class="py-[1px]">
                    <div class="lps-letters">
                      <button v-for="(char, charIdx) in example.word.split('')" :key="`example-col5-${example.word}-${charIdx}`"
                        type="button" class="lps-letter"
                        :class="charIdx === example.selectedIndex ? 'lps-letter--selected' : ''" disabled
                        :aria-pressed="charIdx === example.selectedIndex">
                        {{ char }}
                      </button>
                    </div>
                  </div>
                </div>
                <div class="my-3 border-t border-muted-foreground/20"></div>

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

          <!-- Page 5: Spalte 6 -->
          <div v-else-if="pageIndex === pageIndexByColumn.col6" class="space-y-3">
            <div class="rounded-2xl border bg-background p-6 shadow-sm">
              <div class="mb-3 text-center text-[13px] font-extrabold tracking-wide text-foreground">Spalte 6</div>
              <div class="mb-4 space-y-2 text-center">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Beispiel</div>
                <div class="flex flex-col items-center gap-1 text-base text-foreground">
                  <div>
                    L.l <span class="lps-handwriting">lernen</span>
                  </div>
                  <div>
                    P.p <span class="lps-handwriting">Pech</span>
                  </div>
                </div>
              </div>
              <p class="text-center text-sm text-muted-foreground">
                Bitte beachten Sie die Anweisungen des Prüfers.
              </p>
            </div>
          </div>

          <!-- Page 5: Spalte 7 -->
          <div v-else-if="pageIndex === pageIndexByColumn.col7" class="space-y-3">
            <div class="rounded-2xl border bg-background p-4 shadow-sm">
              <div class="mb-4 text-center text-[13px] font-extrabold tracking-wide text-foreground">Spalte 7</div>

              <div class="flex justify-center">
                <div class="w-full max-w-4xl">
                  <div v-if="column7Examples.length" class="mb-4 space-y-3">
                    <div class="text-center text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Beispiel</div>
                    <div v-for="(example, exampleIdx) in column7Examples" :key="`example-col7-${exampleIdx}`"
                      class="py-[10px]">
                      <div class="flex items-center justify-center leading-none">
                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="lps-column3-svg select-none"
                          :viewBox="example.svgMeta.viewBox" :width="example.svgMeta.width"
                          :height="example.svgMeta.height">
                          <g v-for="(option, optionIdx) in example.options" :id="option.id" :key="option.id"
                            role="button" class="lps-figure-shape-group lps-figure-shape--disabled"
                            :aria-pressed="optionIdx === example.selectedIndex" :aria-disabled="true">
                            <path class="lps-figure-hit" :d="option.pathData" />
                            <path fill="#090d0e" class="lps-figure-shape" :class="[
                              optionIdx === example.selectedIndex ? 'lps-figure-shape--selected' : '',
                            ]" :d="option.pathData" />
                            <clipPath :id="`${option.id}-clip`">
                              <path :d="option.pathData" :transform="option.transform" />
                            </clipPath>
                            <line v-if="optionIdx === example.selectedIndex" class="lps-figure-slash"
                              :clip-path="`url(#${option.id}-clip)`" x1="0" :y1="example.svgMeta.height - 6"
                              :x2="example.svgMeta.width" y2="6" />
                          </g>
                        </svg>
                      </div>
                    </div>
                  </div>
                  <div v-if="column7Examples.length" class="my-3 border-t border-muted-foreground/20"></div>
                  <div v-for="(row, idx) in lpsPage5Rows" :key="`${row.id}-c7`" class="py-[10px]">
                    <div v-if="row.column7?.length">
                      <div v-if="row.column7SvgMeta && row.column7.every((option) => option.pathData)"
                        class="flex items-center justify-center leading-none">
                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="lps-column3-svg select-none"
                          :viewBox="row.column7SvgMeta.viewBox" :width="row.column7SvgMeta.width"
                          :height="row.column7SvgMeta.height">
                          <g v-for="(option, optionIdx) in row.column7" :id="option.id" :key="option.id" role="button"
                            class="lps-figure-shape-group" :transform="option.transform"
                            :tabindex="isColumnInteractive('col7') ? 0 : -1"
                            :aria-pressed="page5Responses[idx].col7[optionIdx]"
                            :class="!isColumnInteractive('col7') ? 'lps-figure-shape--disabled' : ''"
                            @click="isColumnInteractive('col7') && togglePage5Selection(idx, optionIdx)"
                            @keydown.enter.prevent="togglePage5Selection(idx, optionIdx)"
                            @keydown.space.prevent="togglePage5Selection(idx, optionIdx)">
                            <path class="lps-figure-hit" :d="option.pathData" />
                            <path fill="#090d0e" class="lps-figure-shape" :class="[
                              page5Responses[idx].col7[optionIdx] ? 'lps-figure-shape--selected' : '',
                            ]" :d="option.pathData" />
                            <clipPath :id="`${option.id}-clip`">
                              <path :d="option.pathData" :transform="option.transform" />
                            </clipPath>
                            <line v-if="page5Responses[idx].col7[optionIdx]" class="lps-figure-slash"
                              :clip-path="`url(#${option.id}-clip)`" x1="0" :y1="row.column7SvgMeta.height - 6"
                              :x2="row.column7SvgMeta.width" y2="6" />
                          </g>
                        </svg>
                      </div>
                      <div v-else class="grid grid-cols-8 gap-1">
                        <button v-for="(option, optionIdx) in row.column7" :id="option.id" :key="option.id"
                          type="button" class="lps-figure"
                          :class="page5Responses[idx].col7[optionIdx] ? 'lps-figure--selected' : ''"
                          :disabled="!isColumnInteractive('col7')" :aria-pressed="page5Responses[idx].col7[optionIdx]"
                          @click="togglePage5Selection(idx, optionIdx)">
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
          <div v-else-if="pageIndex === pageIndexByColumn.col8" class="space-y-3">
            <div class="rounded-2xl border bg-background p-4 shadow-sm">
              <div class="mb-4 text-center text-[13px] font-extrabold tracking-wide text-foreground">Spalte 8</div>

              <div class="space-y-6">
                <div v-if="page6ExampleRows.length" class="space-y-3">
                  <div class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Beispiel</div>
                  <div v-for="(row, idx) in page6ExampleRows" :key="`example-c8-${idx}`"
                    class="rounded-xl border bg-muted/40 p-4">
                    <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-[1.25fr_1fr]">
                      <div class="flex justify-center">
                        <div v-if="row.svg" class="flex items-center justify-center rounded-lg bg-white p-3 shadow-sm"
                          v-html="row.svg"
                          :style="{ width: `${row.svgMeta?.width ?? 420}px`, height: `${row.svgMeta?.height ?? 220}px` }">
                        </div>
                        <div v-else class="text-center text-xs text-muted-foreground/60">—</div>
                      </div>

                      <div class="space-y-2">
                        <div class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Antwort auswählen
                        </div>
                        <div v-if="row.optionGroups.length" class="space-y-2">
                          <div v-for="group in row.optionGroups" :key="`example-c8-group-${group.label}`"
                            class="flex items-center gap-3">
                            <div class="lps-number-label">
                              {{ group.label }}
                            </div>
                            <div class="flex flex-wrap gap-2">
                              <button v-for="groupOption in group.options"
                                :key="`example-c8-${groupOption.index}`" type="button" class="lps-letter"
                                :class="row.selected[groupOption.index] ? 'lps-letter--selected' : ''" disabled
                                :aria-pressed="row.selected[groupOption.index]">
                                {{ groupOption.option.label }}
                              </button>
                            </div>
                          </div>
                        </div>
                        <div v-else class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                          <button v-for="(option, optionIdx) in row.options" :key="`example-c8-${optionIdx}`"
                            type="button" class="lps-letter"
                            :class="row.selected[optionIdx] ? 'lps-letter--selected' : ''" disabled
                            :aria-pressed="row.selected[optionIdx]">
                            {{ option.label }}
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-for="(row, idx) in lpsPage6Rows" :key="`${row.id}-c8`" class="rounded-xl border bg-muted/40 p-4">
                  <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-[1.25fr_1fr]">
                    <div class="flex justify-center">
                      <div v-if="row.column8Svg"
                        class="flex items-center justify-center rounded-lg bg-white p-3 shadow-sm"
                        v-html="row.column8Svg"
                        :style="{ width: `${row.column8SvgMeta?.width ?? 420}px`, height: `${row.column8SvgMeta?.height ?? 220}px` }">
                      </div>
                      <div v-else class="text-center text-xs text-muted-foreground/60">—</div>
                    </div>

                    <div class="space-y-2">
                      <div class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Antwort auswählen
                      </div>
                      <div v-if="page6OptionGroups[idx].length" class="space-y-2">
                        <div v-for="group in page6OptionGroups[idx]" :key="`${row.id}-c8-group-${group.label}`"
                          class="flex items-center gap-3">
                          <div class="lps-number-label">
                            {{ group.label }}
                          </div>
                          <div class="flex flex-wrap gap-2">
                            <button v-for="groupOption in group.options" :key="`${row.id}-c8-${groupOption.index}`"
                              type="button" class="lps-letter"
                              :class="page6Responses[idx].col8[groupOption.index] ? 'lps-letter--selected' : ''"
                              :disabled="!isColumnInteractive('col8') || !row.column8Svg"
                              :aria-pressed="page6Responses[idx].col8[groupOption.index]"
                              @click="togglePage6Selection(idx, groupOption.index)">
                              {{ groupOption.option.label }}
                            </button>
                          </div>
                        </div>
                      </div>
                      <div v-else class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                        <button v-for="(option, optionIdx) in row.column8Options" :key="`${row.id}-c8-${optionIdx}`"
                          type="button" class="lps-letter"
                          :class="page6Responses[idx].col8[optionIdx] ? 'lps-letter--selected' : ''"
                          :disabled="!isColumnInteractive('col8') || !row.column8Svg"
                          :aria-pressed="page6Responses[idx].col8[optionIdx]"
                          @click="togglePage6Selection(idx, optionIdx)">
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
          <div v-else-if="pageIndex === pageIndexByColumn.col9" class="space-y-3">
            <div class="rounded-2xl border bg-background p-4 shadow-sm">
              <div class="mb-4 text-center text-[13px] font-extrabold tracking-wide text-foreground">Spalte 9</div>

              <div class="space-y-4">
                <div v-for="(row, rowIdx) in lpsPage7Rows" :key="`${row.id}-c9`" class="relative space-y-3">
                  <div v-if="page7Arrows[rowIdx]?.length" class="pointer-events-none absolute inset-x-0 top-0 w-full"
                    aria-hidden="true">
                    <svg v-for="(arrow, arrowIdx) in page7Arrows[rowIdx]" :key="`${row.id}-arrow-${arrowIdx}`"
                      class="page7-arrow" :style="getPage7ArrowStyle(rowIdx, arrow.from, arrow.to)"
                      viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                      <path
                        d="M492.888 159.339 359.286 25.738c-12.347-12.346-28.691-19.112-46.115-19.112q-.463 0-.926.006c-17.763.245-34.282 7.495-46.513 20.417-11.8 12.466-18.025 28.906-17.531 46.295.489 17.172 7.455 33.325 19.615 45.486l22.083 22.083c-71.908 12.999-137.081 47.191-189.527 99.638-51.575 51.575-85.891 116.367-99.236 187.373-3.609 19.202 1.471 38.833 13.935 53.859 12.437 14.992 30.743 23.591 50.224 23.591 31.36 0 58.309-22.214 64.078-52.821 15.542-82.461 75.257-149.309 153.301-175.281l-14.886 14.886c-25.298 25.299-26.126 66.559-1.844 91.976 12.443 13.026 29.207 20.199 47.203 20.199 17.429 0 33.815-6.787 46.14-19.111L492.888 251.62C505.212 239.295 512 222.909 512 205.479s-6.788-33.815-19.112-46.14m-14.425 77.858L344.862 370.798c-8.472 8.472-19.736 13.137-31.716 13.137-12.37 0-23.895-4.933-32.453-13.891-16.687-17.466-16.006-45.934 1.519-63.459l40.756-40.756a10.2 10.2 0 0 0-8.876-17.276C210.973 265.6 128.684 346.064 109.326 448.776c-3.953 20.974-22.471 36.198-44.032 36.198a44.73 44.73 0 0 1-34.524-16.216c-8.577-10.339-12.071-23.849-9.586-37.067 12.583-66.95 44.953-128.057 93.613-176.713 54.075-54.076 122.482-87.581 197.828-96.892a10.2 10.2 0 0 0 5.961-17.335l-36.344-36.344c-17.462-17.461-18.222-45.874-1.695-63.334 8.412-8.887 19.769-13.874 31.979-14.041q.318-.005.636-.005c11.978 0 23.214 4.651 31.702 13.137l133.601 133.601c8.472 8.472 13.137 19.736 13.137 31.716-.001 11.98-4.666 23.243-13.139 31.716" />
                      <path
                        d="M472.17 209.312c-3.984-3.981-10.441-3.981-14.424 0l-74.149 74.148c-3.983 3.984-3.983 10.442 0 14.425a10.17 10.17 0 0 0 7.212 2.987c2.61 0 5.221-.997 7.212-2.987l74.149-74.148c3.983-3.984 3.983-10.442 0-14.425M372.907 308.399c-3.984-3.982-10.442-3.982-14.425 0l-5.665 5.665c-3.983 3.984-3.983 10.442 0 14.425a10.17 10.17 0 0 0 7.212 2.987c2.61 0 5.221-.996 7.213-2.987l5.665-5.665c3.983-3.984 3.983-10.443 0-14.425" />
                    </svg>
                  </div>
                  <div class="grid gap-3 md:grid-cols-3">
                    <div v-for="(prompt, promptIdx) in row.prompts" :key="prompt.id"
                      class="space-y-3 rounded-lg bg-background p-3 shadow-sm">
                      <div class="flex justify-center">
                        <div class="lps-shape-panel" v-html="prompt.svg" :style="getShapePanelStyle(prompt.svgMeta)">
                        </div>
                      </div>

                      <div class="flex flex-wrap justify-center gap-2">
                        <button v-for="(option, optionIdx) in prompt.options" :key="`${prompt.id}-${option}`"
                          type="button" class="lps-number-chip" :class="page7Responses[rowIdx]?.prompts?.[promptIdx]?.[optionIdx]
                            ? 'lps-number-chip--selected'
                            : ''" :disabled="!isColumnInteractive('col9') || isPage7ExamplePrompt(rowIdx, promptIdx)"
                          :aria-pressed="page7Responses[rowIdx]?.prompts?.[promptIdx]?.[optionIdx]"
                          @click="togglePage7Selection(rowIdx, promptIdx, optionIdx)">
                          {{ option }}
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-else-if="pageIndex === pageIndexByColumn.col11" class="space-y-3">
            <div class="rounded-2xl border bg-background p-4 shadow-sm">
              <div class="mb-1 text-center text-[13px] font-extrabold tracking-wide text-foreground">Spalte 11</div>
              <p class="text-center text-xs text-muted-foreground">Finde den richtigen Buchstaben zu jeder Form.</p>

              <div class="space-y-4">
                <div v-for="(row, rowIdx) in lpsPage9Rows" :key="`${row.id}-c11`"
                  class="relative rounded-xl border bg-muted/30 p-4 shadow-sm">
                  <div v-if="page9Arrows[rowIdx]?.length" class="pointer-events-none absolute inset-x-0 top-0 w-full"
                    aria-hidden="true">
                    <svg v-for="(arrow, arrowIdx) in page9Arrows[rowIdx]" :key="`${row.id}-arrow-${arrowIdx}`"
                      class="page7-arrow" :style="getPage9ArrowStyle(rowIdx, arrow.from, arrow.to)"
                      viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                      <path
                        d="M492.888 159.339 359.286 25.738c-12.347-12.346-28.691-19.112-46.115-19.112q-.463 0-.926.006c-17.763.245-34.282 7.495-46.513 20.417-11.8 12.466-18.025 28.906-17.531 46.295.489 17.172 7.455 33.325 19.615 45.486l22.083 22.083c-71.908 12.999-137.081 47.191-189.527 99.638-51.575 51.575-85.891 116.367-99.236 187.373-3.609 19.202 1.471 38.833 13.935 53.859 12.437 14.992 30.743 23.591 50.224 23.591 31.36 0 58.309-22.214 64.078-52.821 15.542-82.461 75.257-149.309 153.301-175.281l-14.886 14.886c-25.298 25.299-26.126 66.559-1.844 91.976 12.443 13.026 29.207 20.199 47.203 20.199 17.429 0 33.815-6.787 46.14-19.111L492.888 251.62C505.212 239.295 512 222.909 512 205.479s-6.788-33.815-19.112-46.14m-14.425 77.858L344.862 370.798c-8.472 8.472-19.736 13.137-31.716 13.137-12.37 0-23.895-4.933-32.453-13.891-16.687-17.466-16.006-45.934 1.519-63.459l40.756-40.756a10.2 10.2 0 0 0-8.876-17.276C210.973 265.6 128.684 346.064 109.326 448.776c-3.953 20.974-22.471 36.198-44.032 36.198a44.73 44.73 0 0 1-34.524-16.216c-8.577-10.339-12.071-23.849-9.586-37.067 12.583-66.95 44.953-128.057 93.613-176.713 54.075-54.076 122.482-87.581 197.828-96.892a10.2 10.2 0 0 0 5.961-17.335l-36.344-36.344c-17.462-17.461-18.222-45.874-1.695-63.334 8.412-8.887 19.769-13.874 31.979-14.041q.318-.005.636-.005c11.978 0 23.214 4.651 31.702 13.137l133.601 133.601c8.472 8.472 13.137 19.736 13.137 31.716-.001 11.98-4.666 23.243-13.139 31.716" />
                      <path
                        d="M472.17 209.312c-3.984-3.981-10.441-3.981-14.424 0l-74.149 74.148c-3.983 3.984-3.983 10.442 0 14.425a10.17 10.17 0 0 0 7.212 2.987c2.61 0 5.221-.997 7.212-2.987l74.149-74.148c3.983-3.984 3.983-10.442 0-14.425M372.907 308.399c-3.984-3.982-10.442-3.982-14.425 0l-5.665 5.665c-3.983 3.984-3.983 10.442 0 14.425a10.17 10.17 0 0 0 7.212 2.987c2.61 0 5.221-.996 7.213-2.987l5.665-5.665c3.983-3.984 3.983-10.443 0-14.425" />
                    </svg>
                  </div>
                  <div class="grid gap-3 md:grid-cols-3">
                    <div v-for="(prompt, promptIdx) in row.prompts" :key="prompt.id"
                      class="space-y-3 rounded-lg bg-background p-3 shadow-sm">
                      <div class="flex justify-center">
                        <div class="lps-shape-panel page9-shape-panel" v-html="prompt.svg"
                          :style="getShapePanelStyle(prompt.svgMeta)"></div>
                      </div>

                      <div class="flex flex-wrap justify-center gap-2">
                        <button v-for="(option, optionIdx) in prompt.options" :key="`${prompt.id}-${option}`"
                          type="button" class="page9-letter"
                          :class="page9Responses[rowIdx]?.prompts?.[promptIdx]?.[optionIdx] ? 'page9-letter--selected' : ''"
                          :disabled="!isColumnInteractive('col11') || isPage9ExamplePrompt(rowIdx, promptIdx)"
                          :aria-pressed="page9Responses[rowIdx]?.prompts?.[promptIdx]?.[optionIdx]"
                          @click="togglePage9Selection(rowIdx, promptIdx, optionIdx)">
                          {{ option }}
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-else-if="pageIndex === pageIndexByColumn.col12" class="space-y-3">
            <div class="rounded-2xl border bg-background p-4 shadow-sm">
              <div class="mb-1 text-center text-[13px] font-extrabold tracking-wide text-foreground">Spalte 12</div>
              <!-- <p class="text-center text-xs text-muted-foreground">Wähle den passenden Pfad in jeder Vorlage aus.</p> -->

              <div class="flex justify-center">
                <div class="w-full max-w-4xl">
                  <div v-for="(row, rowIdx) in lpsPage10Rows" :key="`${row.id}-c12`" class="py-[10px]">
                    <div v-if="row.options?.length" class="page10-options-row">
                      <button v-for="(option, optionIdx) in row.options" :key="option.id" type="button"
                        class="page10-option"
                        :class="page10Responses[rowIdx].paths[optionIdx] ? 'page10-option--selected' : ''"
                        :disabled="!isColumnInteractive('col12') || isPage10ExampleRow(rowIdx)"
                        :aria-pressed="page10Responses[rowIdx].paths[optionIdx]"
                        @click="togglePage10Selection(rowIdx, optionIdx)"
                        @keydown.enter.prevent="togglePage10Selection(rowIdx, optionIdx)"
                        @keydown.space.prevent="togglePage10Selection(rowIdx, optionIdx)">
                        <span class="page10-option-figure" v-html="option.svg"
                          :style="getShapePanelStyle(option.svgMeta)"></span>
                        <span class="sr-only">Option {{ optionIdx + 1 }}</span>
                      </button>
                    </div>
                    <div v-else class="text-center text-xs text-muted-foreground/60">—</div>
                    <!-- <div v-if="isPage10ExampleRow(rowIdx)" class="pt-1 text-center text-[11px] uppercase tracking-wide text-muted-foreground">Beispiel</div> -->
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-else-if="pageIndex === pageIndexByColumn.col14" class="space-y-3">
            <div class="rounded-2xl border bg-background p-4 shadow-sm">
              <div class="mb-3 text-center text-[13px] font-extrabold tracking-wide text-foreground">Spalten 13 + 14</div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <div class="mb-3">
                    <div class="mb-1 text-center text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Beispiel</div>
                    <div v-for="(example, exampleIdx) in column13Examples" :key="`example-col13-${exampleIdx}`"
                      class="py-[1px]">
                      <div class="lps-sequence-row">
                        <button v-for="(token, tokenIdx) in example.tokens"
                          :key="`example-col13-${exampleIdx}-${tokenIdx}`" type="button" class="lps-letter"
                          :class="example.selectedIndex === tokenIdx ? 'lps-letter--selected' : ''" disabled
                          :aria-pressed="example.selectedIndex === tokenIdx">
                          {{ token }}
                        </button>
                      </div>
                    </div>
                  </div>
                  <div class="my-3 border-t border-muted-foreground/20"></div>

                  <div v-for="(row, idx) in lpsPage11Rows" :key="`${row.id}-c13`" class="py-[1px]">
                    <div class="lps-sequence-row">
                      <button v-for="(token, tokenIdx) in getSequenceTokens(row.column13)"
                        :key="`${row.id}-13-${tokenIdx}`" type="button" class="lps-letter"
                        :class="page11Responses[idx].col13[tokenIdx] ? 'lps-letter--selected' : ''"
                        :disabled="!isColumnInteractive('col13')" :aria-pressed="page11Responses[idx].col13[tokenIdx]"
                        @click="handlePage11Click($event, idx, 'col13', tokenIdx)"
                        @dblclick.prevent="clearPage11Selection(idx, 'col13', tokenIdx)">
                        {{ token }}
                      </button>
                    </div>
                  </div>
                </div>

                <div class="lps-sep">
                  <div class="mb-3">
                    <div class="mb-1 text-center text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Beispiel</div>
                    <div v-for="(example, exampleIdx) in column14Examples" :key="`example-col14-${exampleIdx}`"
                      class="py-[1px]">
                      <div class="lps-sequence-row">
                        <button v-for="(token, tokenIdx) in example.tokens"
                          :key="`example-col14-${exampleIdx}-${tokenIdx}`" type="button" class="lps-letter"
                          :class="example.selectedIndex === tokenIdx ? 'lps-letter--selected' : ''" disabled
                          :aria-pressed="example.selectedIndex === tokenIdx">
                          {{ token }}
                        </button>
                      </div>
                    </div>
                  </div>
                  <div class="my-3 border-t border-muted-foreground/20"></div>
                  <div v-for="(row, idx) in lpsPage11Rows" :key="`${row.id}-c14`" class="py-[1px]">
                    <div class="lps-sequence-row">
                      <button v-for="(token, tokenIdx) in getSequenceTokens(row.column14)"
                        :key="`${row.id}-14-${tokenIdx}`" type="button" class="lps-letter"
                        :class="page11Responses[idx].col14[tokenIdx] ? 'lps-letter--selected' : ''"
                        :disabled="!isColumnInteractive('col14')" :aria-pressed="page11Responses[idx].col14[tokenIdx]"
                        @click="handlePage11Click($event, idx, 'col14', tokenIdx)"
                        @dblclick.prevent="clearPage11Selection(idx, 'col14', tokenIdx)">
                        {{ token }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div
              class="flex flex-wrap items-center justify-between gap-3 rounded-xl border bg-background px-4 py-3 text-xs text-muted-foreground shadow-sm">
              <div>
                Pluspunkte:
                <span class="font-semibold text-foreground">+{{ page11PositiveScore }}</span>
              </div>
              <div>
                Minuspunkte:
                <span class="font-semibold text-foreground">-{{ page11NegativeScore }}</span>
              </div>
            </div>
          </div>

          <div v-else-if="pageIndex === pageIndexByColumn.col10" class="space-y-3">
            <div class="rounded-2xl border bg-background p-4 shadow-sm">
              <div class="mb-1 text-center text-[13px] font-extrabold tracking-wide text-foreground">Spalte 10</div>
              <!-- <p class="text-center text-xs text-muted-foreground">Finde die passende Form zu jeder Vorlage.</p> -->

              <div class="mb-4 flex flex-wrap justify-center gap-3 rounded-xl border bg-background p-3 shadow-sm">
                <div v-for="option in LPS_PAGE8_OPTION_SVGS" :key="`page8-options-${option.id}`"
                  class="page8-option-preview" v-html="option.svg"></div>
              </div>

              <div class="space-y-4">
                <div v-for="(row, rowIdx) in lpsPage8Rows" :key="`${row.id}-c10`"
                  class="relative rounded-xl border bg-muted/30 p-4 shadow-sm">
                  <div v-if="PAGE8_ARROW_ROWS[rowIdx]?.length" class="pointer-events-none absolute inset-x-0 top-0 h-full"
                    aria-hidden="true">
                    <svg v-for="columnIdx in PAGE8_ARROW_ROWS[rowIdx]" :key="`page8-arrow-${row.id}-${columnIdx}`"
                      class="page8-arrow" :style="getPage8ArrowStyle(columnIdx)" viewBox="0 0 512 512"
                      xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                      <path
                        d="M484.964 265.749c-12.465-11.8-28.938-18.052-46.298-17.532-17.172.488-33.327 7.454-45.489 19.616l-22.083 22.084c-13.001-71.913-47.195-137.09-99.645-189.54-51.578-51.579-116.375-85.896-187.383-99.24-19.2-3.61-38.834 1.471-53.862 13.936C15.211 27.511 6.612 45.817 6.612 65.299c0 31.362 22.215 58.312 52.823 64.082 82.466 15.544 149.317 75.261 175.291 153.31l-14.887-14.887c-25.301-25.299-66.563-26.13-91.98-1.844-13.027 12.444-20.201 29.208-20.201 47.205 0 17.431 6.788 33.817 19.113 46.143l133.609 133.61C273.102 505.64 289.812 512 306.523 512c16.71 0 33.42-6.361 46.144-19.081L486.276 359.31c12.565-12.566 19.35-29.274 19.107-47.045-.246-17.764-7.497-34.284-20.419-46.516m-13.115 79.134L338.24 478.492c-17.489 17.489-45.946 17.489-63.435 0L141.196 344.883c-8.473-8.472-13.139-19.737-13.139-31.718 0-12.372 4.934-23.896 13.892-32.455 8.453-8.076 19.483-12.085 30.602-12.085 11.857 0 23.816 4.559 32.859 13.605l40.758 40.759a10.2 10.2 0 0 0 17.277-8.876C246.397 210.988 165.929 128.694 63.21 109.334c-20.975-3.954-36.2-22.473-36.2-44.034a44.73 44.73 0 0 1 16.217-34.527c10.34-8.577 23.853-12.073 37.07-9.586 66.953 12.582 128.065 44.953 176.725 93.616 54.079 54.078 87.586 122.489 96.899 197.839a10.2 10.2 0 0 0 17.335 5.961l36.345-36.345c17.461-17.463 45.875-18.222 63.338-1.694 8.887 8.414 13.875 19.771 14.042 31.981.17 12.216-4.495 23.701-13.132 32.338" />
                      <path
                        d="m302.691 457.773-74.153-74.154c-3.984-3.98-10.441-3.982-14.426 0-3.983 3.984-3.983 10.443 0 14.426l74.153 74.154a10.17 10.17 0 0 0 7.212 2.988c2.61 0 5.221-.995 7.213-2.988 3.984-3.984 3.984-10.443.001-14.426M203.598 358.503l-5.665-5.665c-3.985-3.982-10.442-3.982-14.426 0-3.983 3.984-3.983 10.443 0 14.426l5.665 5.665c1.992 1.991 4.601 2.988 7.212 2.988s5.22-.997 7.213-2.988c3.984-3.984 3.984-10.443.001-14.426" />
                    </svg>
                  </div>
                  <div class="grid gap-3 md:grid-cols-2">
                    <div v-for="(prompt, promptIdx) in row.prompts" :key="prompt.id"
                      class="space-y-3 rounded-lg bg-background p-3 shadow-sm">
                      <div class="flex justify-center">
                        <div class="lps-shape-panel page8-shape-panel" v-html="prompt.svg"
                          :style="getShapePanelStyle(prompt.svgMeta)"></div>
                      </div>

                      <div class="flex flex-wrap justify-center gap-2">
                        <button v-for="(option, optionIdx) in prompt.options" :key="option.id" type="button"
                          class="page8-option" :class="page8Responses[rowIdx]?.prompts?.[promptIdx]?.[optionIdx]
                            ? 'page8-option--selected'
                            : ''" :disabled="!isColumnInteractive('col10') || isPage8ExamplePrompt(rowIdx, promptIdx)"
                          :aria-pressed="page8Responses[rowIdx]?.prompts?.[promptIdx]?.[optionIdx]"
                          @click="togglePage8Selection(rowIdx, promptIdx, optionIdx)">
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

.lps-sequence-row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  width: 260px;
  margin: 0 auto;
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
  top: 54px;
  height: 64px;
  width: 64px;
  color: #4b5563;
  z-index: 1;
}

.page7-arrow path {
  fill: currentColor;
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

.page8-option-preview {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 78px;
  height: 78px;
  border-radius: 12px;
  border: 1px solid rgb(226 232 240);
  background: white;
  box-shadow: 0 4px 14px rgb(15 23 42 / 0.08);
}

.page8-option-preview svg {
  width: 52px;
  height: 52px;
}

.page8-arrow {
  position: absolute;
  height: 64px;
  width: 64px;
  transform: translate(-50%, -50%);
  color: #4b5563;
  z-index: 1;
}

.page8-arrow path {
  fill: currentColor;
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
  align-items: center;
  justify-content: center;
  width: 72px;
  height: 72px;
}

.page8-option svg {
  width: 64px;
  height: 64px;
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

.page9-shape-panel {
  min-width: 150px;
  min-height: 130px;
  border: 4px solid black;
}

.page9-letter {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 54px;
  height: 48px;
  border-radius: 10px;
  border: 1px solid rgb(226 232 240);
  background: white;
  font-weight: 800;
  font-size: 18px;
  color: rgb(15 23 42);
  transition: transform 120ms ease, box-shadow 120ms ease, border-color 120ms ease, background-color 120ms ease;
}

.page9-letter:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgb(15 23 42 / 0.12);
  border-color: rgb(203 213 225);
}

.page9-letter:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.page9-letter--selected {
  border-color: rgb(239 68 68);
  box-shadow: 0 0 0 3px rgb(254 226 226);
  background: rgb(254 242 242);
}

.page10-options-row {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: flex-end;
  gap: 4px;
}

.page10-option {
  display: inline-flex;
  align-items: flex-end;
  justify-content: center;
  padding: 2px;
  background: transparent;
  border: none;
  box-shadow: none;
  border-radius: 8px;
  cursor: pointer;
  transition: transform 120ms ease, box-shadow 120ms ease, background-color 120ms ease;
}

.page10-option-figure {
  display: inline-flex;
  align-items: flex-end;
  justify-content: center;
  padding: 2px;
  width: var(--shape-width, auto);
  height: var(--shape-height, auto);
}

.page10-option svg {
  display: block;
  width: 100%;
  height: 100%;
}

.page10-option:hover:not(:disabled) {
  transform: translateY(-1px);
  background: rgba(0, 0, 0, 0.04);
}

.page10-option:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.page10-option--selected {
  background: rgba(239, 68, 68, 0.08);
  box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.65);
}

.lps-alphabet-float {
  position: fixed;
  left: 24px;
  top: 50%;
  transform: translateY(-50%);
  z-index: 40;
  pointer-events: none;
}

.lps-alphabet-box {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 10px 14px;
  border: 2px solid rgb(15 23 42);
  background: rgb(248 250 252);
  box-shadow: 0 6px 16px rgb(15 23 42 / 0.12);
  font-weight: 800;
  font-size: 16px;
  letter-spacing: 0.04em;
  text-transform: lowercase;
}

.lps-alphabet-row {
  display: flex;
  gap: 8px;
  justify-content: center;
}

.lps-handwriting {
  font-family: "Segoe Print", "Bradley Hand", "Comic Sans MS", "Apple Chancery", cursive;
  font-size: 22px;
  letter-spacing: 0.02em;
  font-weight: 500;
}

/* Vertical separator between column 1 and 2 */
/* .lps-sep {
  border-left: 2px solid rgb(17 17 17 / 0.9);
  padding-left: 5px;
} */
</style>
