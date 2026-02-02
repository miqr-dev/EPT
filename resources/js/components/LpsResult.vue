<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import axios from 'axios';
import { Button } from '@/components/ui/button';
import { getLpsDataset, type LpsPage1Solution } from '@/pages/Questions/LPSPage1';
import { getLpsPage5Dataset } from '@/pages/Questions/LPSPage5';
import { getLpsPage6Dataset } from '@/pages/Questions/LPSPage6';
import { getLpsPage7Dataset } from '@/pages/Questions/LPSPage7';
import { getLpsPage8Dataset } from '@/pages/Questions/LPSPage8';
import { getLpsPage9Dataset } from '@/pages/Questions/LPSPage9';
import { getLpsPage10Dataset } from '@/pages/Questions/LPSPage10';
import { getLpsPage11Dataset, type LpsPage11Solution } from '@/pages/Questions/LPSPage11';
import {
  LPS_B_AGE_GROUP_LABELS,
  LPS_B_IQ_BY_T_RANGES,
  LPS_B_SCORE_TABLES,
  type LpsBAgeGroupKey,
  type LpsBScoreKey,
  type LpsBScoreRangeRow,
  type LpsBScoreRow,
  type LpsBTotalScoreRow,
} from '@/pages/Scores/LPS/lpsbScoreTables';

type LpsPage1ResponseRow = { col1: boolean[]; col2: boolean[]; col3: boolean[]; col4: boolean[]; col5: boolean[] };
type LpsPage5ResponseRow = { col7: boolean[] };
type LpsPage6ResponseRow = { col8: boolean[] };
type LpsPage7ResponseRow = { prompts: boolean[][] };
type LpsPage8ResponseRow = { prompts: boolean[][] };
type LpsPage9ResponseRow = { prompts: boolean[][] };
type LpsPage10ResponseRow = { paths: boolean[] };
type LpsPage11ResponseRow = { col13: boolean[]; col14: boolean[] };

const props = defineProps<{
  results: Record<string, any> | null;
  testName?: string;
  participantProfile?: { age: number; sex?: string } | null;
  testResultId?: number | null;
  manualScores?: Record<string, number | string | null>;
}>();

const emit = defineEmits<{
  'manual-score-updated': [{ key: string; value: number | null }];
}>();

const testLabel = computed(() => props.testName ?? 'LPS');
const isLpsB = computed(() => props.testName === 'LPS-B');
const lpsbTValues = [30, 35, 40, 45, 50, 55, 60, 65, 70];
const lpsbTickStep = 5;
const lpsbMinorTicks = 4;
const lpsbLabelWidth = 44;
const lpsbRawWidth = 36;
const lpsbTValueWidth = 26;
const lpsbHatchWidth = 18;
const lpsbScoreWidth = 26;
const lpsbRowHeight = 28;
const lpsbHeaderHeight = 0;
const lpsbMarkerSize = 8;
const totalTime = computed(() => props.results?.total_time_seconds ?? null);
const participantProfile = computed(() => props.participantProfile ?? null);
const page1 = computed<LpsPage1ResponseRow[]>(() => props.results?.page1 ?? []);
const page5 = computed<LpsPage5ResponseRow[]>(() => props.results?.page5 ?? []);
const page6 = computed<LpsPage6ResponseRow[]>(() => props.results?.page6 ?? []);
const page7 = computed<LpsPage7ResponseRow[]>(() => props.results?.page7 ?? []);
const page8 = computed<LpsPage8ResponseRow[]>(() => props.results?.page8 ?? []);
const page9 = computed<LpsPage9ResponseRow[]>(() => props.results?.page9 ?? []);
const page10 = computed<LpsPage10ResponseRow[]>(() => props.results?.page10 ?? []);
const page11 = computed<LpsPage11ResponseRow[]>(() => props.results?.page11 ?? []);
const col14WrongOverride = computed<number | null>(() => {
  const raw = props.results?.column14_wrong ?? props.results?.col14_wrong ?? null;
  const parsed = raw === '' || raw === null || raw === undefined ? null : Number(raw);
  return Number.isNaN(parsed) ? null : parsed;
});
const column6Manual = ref<number | null>(null);
const isSavingManual = ref(false);
const manualSaveMessage = ref<string | null>(null);
const manualSaveError = ref<string | null>(null);
const canSaveManual = computed(() => !!props.testResultId);

function parseManualValue(raw: unknown) {
  const parsed = raw === '' || raw === null || raw === undefined ? null : Number(raw);
  return Number.isNaN(parsed) ? null : parsed;
}

watch(
  () => props.manualScores?.column6_score ?? props.results?.column6_score ?? null,
  (val) => {
    column6Manual.value = parseManualValue(val);
  },
  { immediate: true },
);

watch(column6Manual, () => {
  manualSaveMessage.value = null;
  manualSaveError.value = null;
});

async function saveManualColumn6() {
  if (!props.testResultId) return;
  isSavingManual.value = true;
  manualSaveMessage.value = null;
  manualSaveError.value = null;
  try {
    await axios.put(route('test-results.manual-scores.update', { testResult: props.testResultId }), {
      key: 'column6_score',
      value: column6Manual.value,
    });
    manualSaveMessage.value = 'Gespeichert';
    emit('manual-score-updated', { key: 'column6_score', value: column6Manual.value });
  } catch (error) {
    manualSaveError.value = 'Speichern fehlgeschlagen';
    console.error(error);
  } finally {
    isSavingManual.value = false;
  }
}

const { rows: lpsRows, solutions: lpsSolutions } = getLpsDataset(props.testName);
const { solutions: page5Solutions } = getLpsPage5Dataset(props.testName);
const { solutions: page6Solutions } = getLpsPage6Dataset(props.testName);
const { solutions: page7Solutions } = getLpsPage7Dataset(props.testName);
const { solutions: page8Solutions } = getLpsPage8Dataset(props.testName);
const { solutions: page9Solutions } = getLpsPage9Dataset(props.testName);
const { solutions: page10Solutions } = getLpsPage10Dataset(props.testName);
const { rows: page11Rows, solutions: page11Solutions } = getLpsPage11Dataset(props.testName);

const displayRows = computed(() =>
  lpsRows.map((row, idx) => ({
    row,
    response:
      page1.value[idx] ?? {
        col1: [],
        col2: [],
        col3: [],
        col4: [],
        col5: [],
      },
  })),
);

const isPage7ExamplePrompt = (rowIdx: number, promptIdx: number) =>
  props.testName === 'LPS-B' && rowIdx === 0 && promptIdx < 2;
const isPage8ExamplePrompt = (rowIdx: number, promptIdx: number) =>
  props.testName === 'LPS-B' && promptIdx === 0 && rowIdx < 2;
const isPage9ExamplePrompt = (rowIdx: number, promptIdx: number) =>
  props.testName === 'LPS-B' && rowIdx === 0 && promptIdx < 2;
const isPage10ExampleRow = (rowIdx: number) => props.testName === 'LPS-B' && rowIdx < 2;

function formatTime(seconds?: number | null) {
  if (seconds == null) return '–';
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins}:${secs.toString().padStart(2, '0')}`;
}

function scorePage1Column(columnKey: keyof LpsPage1Solution, responseKey: keyof LpsPage1ResponseRow) {
  return page1.value.reduce((total, response, idx) => {
    const correctIndices = lpsSolutions[idx]?.[columnKey] ?? [];
    const picks = response[responseKey];
    if (!correctIndices.length || !picks?.length) return total;
    const correctPicks = correctIndices.filter((index) => picks[index]);
    return total + correctPicks.length;
  }, 0);
}

function scorePage5Column() {
  return page5.value.reduce((total, response, idx) => {
    const correctIndices = page5Solutions[idx]?.col7 ?? [];
    if (!correctIndices.length || !response?.col7?.length) return total;
    const correctPicks = correctIndices.filter((index) => response.col7[index]);
    return total + correctPicks.length;
  }, 0);
}

function scorePage6Column() {
  return page6.value.reduce((total, response, idx) => {
    const correctIndices = page6Solutions[idx]?.col8 ?? [];
    if (!correctIndices.length || !response?.col8?.length) return total;
    const correctPicks = correctIndices.filter((index) => response.col8[index]);
    return total + correctPicks.length;
  }, 0);
}

function scorePromptRows(
  responses: Array<{ prompts: boolean[][] }>,
  solutions: Array<{ correctOptionIndices: (number | null)[] }>,
  isExample: (rowIdx: number, promptIdx: number) => boolean,
) {
  return responses.reduce((total, response, rowIdx) => {
    const solution = solutions[rowIdx];
    if (!solution) return total;
    const rowScore = solution.correctOptionIndices.reduce((sum, correctIdx, promptIdx) => {
      if (isExample(rowIdx, promptIdx)) return sum;
      if (correctIdx === null || correctIdx === undefined) return sum;
      const picks = response.prompts?.[promptIdx];
      if (!picks?.length) return sum;
      return picks[correctIdx] ? sum + 1 : sum;
    }, 0);
    return total + rowScore;
  }, 0);
}

function scorePage10Column() {
  return page10.value.reduce((total, response, rowIdx) => {
    if (isPage10ExampleRow(rowIdx)) return total;
    const correctIdx = page10Solutions[rowIdx]?.correctIndex;
    if (correctIdx === null || correctIdx === undefined) return total;
    if (!response?.paths?.length) return total;
    return total + (response.paths[correctIdx] ? 1 : 0);
  }, 0);
}

function getSequenceTokens(value: string) {
  if (!value) return [];
  return value.trim().split(/\s+/);
}

function scorePage11Column(columnKey: keyof LpsPage11Solution) {
  return page11.value.reduce(
    (totals, response, idx) => {
      const solutions = page11Solutions[idx];
      if (!solutions) return totals;
      const correctIndices = solutions[columnKey] ?? [];
      const picks = response[columnKey as keyof LpsPage11ResponseRow] as boolean[] | undefined;
      if (!picks?.length) return totals;
      const selectedIndices = picks.flatMap((selected, pickIdx) => (selected ? [pickIdx] : []));
      if (!selectedIndices.length) return totals;
      const row = page11Rows[idx];
      const columnValue = columnKey === 'col13' ? row?.column13 : row?.column14;
      const tokens = getSequenceTokens(columnValue ?? '');
      const correctTokens = correctIndices.map((index) => tokens[index]).filter((token) => token !== undefined);
      const remainingCorrect = [...correctTokens];
      let positive = 0;
      let negative = 0;

      selectedIndices.forEach((pickIdx) => {
        const token = tokens[pickIdx];
        const matchIndex = remainingCorrect.indexOf(token);
        if (matchIndex !== -1) {
          positive += 1;
          remainingCorrect.splice(matchIndex, 1);
          return;
        }
        negative += 1;
      });

      return {
        positive: totals.positive + positive,
        negative: totals.negative + negative,
      };
    },
    { positive: 0, negative: 0 },
  );
}

const columnScores = computed(() => {
  const col1 = scorePage1Column('col1', 'col1');
  const col2 = scorePage1Column('col2', 'col2');
  const col3 = scorePage1Column('col3', 'col3');
  const col4 = scorePage1Column('col4', 'col4');
  const col5 = scorePage1Column('col5', 'col5');
  const col7 = scorePage5Column();
  const col8 = scorePage6Column();
  const col9 = scorePromptRows(page7.value, page7Solutions, isPage7ExamplePrompt);
  const col10 = scorePromptRows(page8.value, page8Solutions, isPage8ExamplePrompt);
  const col11 = scorePromptRows(page9.value, page9Solutions, isPage9ExamplePrompt);
  const col12 = scorePage10Column();
  const col13Stats = scorePage11Column('col13');
  const col14Stats = scorePage11Column('col14');
  const col13 = col13Stats.positive;
  const col14 = col14Stats.positive;
  const col14Wrong = col14WrongOverride.value ?? col14Stats.negative;

  return {
    col1,
    col2,
    col3,
    col4,
    col5,
    col6: column6Manual.value ?? 0,
    col7,
    col8,
    col9,
    col10,
    col11,
    col12,
    col13,
    col14,
    col14Wrong,
  };
});

const totalScores = computed(() => {
  const a = columnScores.value.col1 + columnScores.value.col2;
  const b = columnScores.value.col3;
  const c = columnScores.value.col4;
  const d = columnScores.value.col3 + columnScores.value.col4;
  const e = columnScores.value.col5;
  const f = columnScores.value.col6;
  const g = columnScores.value.col5 + columnScores.value.col6;
  const h = columnScores.value.col7;
  const i = columnScores.value.col8;
  const j = columnScores.value.col9;
  const k = columnScores.value.col10;
  const l = columnScores.value.col7 + columnScores.value.col8 + columnScores.value.col9 + columnScores.value.col10;
  const m = columnScores.value.col11;
  const n = columnScores.value.col12;
  const o = columnScores.value.col11 + columnScores.value.col12;
  const p = columnScores.value.col13;
  const q = columnScores.value.col14;
  const r = columnScores.value.col13 + columnScores.value.col14;
  const s = columnScores.value.col14Wrong;
  const total = a + d + g + l + o + r;

  return { a, b, c, d, e, f, g, h, i, j, k, l, m, n, o, p, q, r, s, total };
});

function getAgeGroupKey(age?: number | null): LpsBAgeGroupKey | null {
  if (age === null || age === undefined || Number.isNaN(age)) return null;
  if (age <= 18) return 'under_18';
  if (age <= 20) return '19_20';
  if (age <= 29) return '21_29';
  if (age <= 39) return '30_39';
  if (age <= 49) return '40_49';
  return '50_plus';
}

const ageGroupKey = computed(() => getAgeGroupKey(participantProfile.value?.age));
const ageGroupLabel = computed(() => (ageGroupKey.value ? LPS_B_AGE_GROUP_LABELS[ageGroupKey.value] : '–'));

function matchesScoreEntry(entry: LpsBScoreRow | LpsBScoreRangeRow, raw: number) {
  if ('raw' in entry) return entry.raw === raw;
  return raw >= entry.min && raw <= entry.max;
}

function lookupColumnScore(key: LpsBScoreKey, raw: number) {
  if (!ageGroupKey.value) return null;
  const table = LPS_B_SCORE_TABLES[ageGroupKey.value]?.columns?.[key] ?? [];
  const directMatch = table.find((entry) => matchesScoreEntry(entry, raw));
  if (directMatch) return directMatch;
  const sorted = [...table].sort((a, b) => {
    const aMin = 'raw' in a ? a.raw : a.min;
    const bMin = 'raw' in b ? b.raw : b.min;
    return aMin - bMin;
  });
  const lowerMatch = sorted.reduce<LpsBScoreRow | LpsBScoreRangeRow | null>((closest, entry) => {
    const entryMax = 'raw' in entry ? entry.raw : entry.max;
    if (entryMax <= raw) return entry;
    return closest;
  }, null);
  if (lowerMatch) return lowerMatch;
  return sorted[0] ?? null;
}

function getScoreRowBounds(rows: Array<LpsBScoreRow | LpsBScoreRangeRow>) {
  let minRaw = Number.POSITIVE_INFINITY;
  let maxRaw = Number.NEGATIVE_INFINITY;
  let minT: number | null = null;
  let maxT: number | null = null;

  rows.forEach((entry) => {
    const entryMin = 'raw' in entry ? entry.raw : entry.min;
    const entryMax = 'raw' in entry ? entry.raw : entry.max;
    if (entryMin < minRaw) {
      minRaw = entryMin;
      minT = entry.t;
    }
    if (entryMax > maxRaw) {
      maxRaw = entryMax;
      maxT = entry.t;
    }
  });

  if (!Number.isFinite(minRaw) || !Number.isFinite(maxRaw)) {
    return null;
  }

  return { minRaw, maxRaw, minT, maxT };
}

function getChartTValue(key: LpsBScoreKey, raw: number) {
  const entry = lookupColumnScore(key, raw);
  return entry?.t ?? null;
}

function lookupTotalScore(raw: number) {
  if (!ageGroupKey.value) return null;
  const table = LPS_B_SCORE_TABLES[ageGroupKey.value]?.total ?? [];
  return (table as LpsBTotalScoreRow[]).find((entry) => matchesScoreEntry(entry, raw)) ?? null;
}

const totalScoreEntry = computed(() => lookupTotalScore(totalScores.value.total));
const iqFromT = computed(() => {
  const tValue = totalScoreEntry.value?.t;
  if (tValue === null || tValue === undefined) return null;
  return LPS_B_IQ_BY_T_RANGES.find((range) => tValue >= range.minT && tValue <= range.maxT)?.iq ?? null;
});
const glTickIndex = computed(() => {
  const tValue = totalScoreEntry.value?.t;
  if (tValue === null || tValue === undefined) return null;
  if (tValue < lpsbTValues[0] || tValue > lpsbTValues[lpsbTValues.length - 1]) return null;
  return tValue - lpsbTValues[0];
});
const glTickCellIndex = computed(() => (glTickIndex.value === null ? null : Math.floor(glTickIndex.value / lpsbTickStep)));
const glTickOffset = computed(() => (glTickIndex.value === null ? null : glTickIndex.value % lpsbTickStep));

type LpsbRowKey = LpsBScoreKey | 'total_raw';

const scoringRows = computed(() => [
  { key: 'test_1_2' as LpsbRowKey, name: '1+2', description: 'Gesamtpunkte', raw: totalScores.value.a, plot: true },
  { key: 'test_3' as LpsbRowKey, name: '3', description: 'Einzelpunkte', raw: totalScores.value.b, plot: true },
  { key: 'test_4' as LpsbRowKey, name: '4', description: 'Einzelpunkte', raw: totalScores.value.c, plot: true },
  { key: 'test_3_4' as LpsbRowKey, name: '3+4', description: 'Gesamtpunkte', raw: totalScores.value.d, plot: true },
  { key: 'test_5' as LpsbRowKey, name: '5', description: 'Einzelpunkte', raw: totalScores.value.e, plot: true },
  { key: 'test_6' as LpsbRowKey, name: '6', description: 'manuell', raw: totalScores.value.f, plot: true },
  { key: 'test_5_6' as LpsbRowKey, name: '5+6', description: 'Gesamtpunkte', raw: totalScores.value.g, plot: true },
  { key: 'test_7' as LpsbRowKey, name: '7', description: 'Einzelpunkte', raw: totalScores.value.h, plot: true },
  { key: 'test_8' as LpsbRowKey, name: '8', description: 'Einzelpunkte', raw: totalScores.value.i, plot: true },
  { key: 'test_9' as LpsbRowKey, name: '9', description: 'Einzelpunkte', raw: totalScores.value.j, plot: true },
  { key: 'test_10' as LpsbRowKey, name: '10', description: 'Einzelpunkte', raw: totalScores.value.k, plot: true },
  { key: 'test_7_10' as LpsbRowKey, name: '7-10', description: 'Gesamtpunkte', raw: totalScores.value.l, plot: true },
  { key: 'test_11' as LpsbRowKey, name: '11', description: 'Einzelpunkte', raw: totalScores.value.m, plot: true },
  { key: 'test_12' as LpsbRowKey, name: '12', description: 'Einzelpunkte', raw: totalScores.value.n, plot: true },
  { key: 'test_11_12' as LpsbRowKey, name: '11-12', description: 'Gesamtpunkte', raw: totalScores.value.o, plot: true },
  { key: 'test_13' as LpsbRowKey, name: '13', description: 'Einzelpunkte', raw: totalScores.value.p, plot: true },
  { key: 'test_14' as LpsbRowKey, name: '14', description: 'Einzelpunkte', raw: totalScores.value.q, plot: true },
  { key: 'test_13_14' as LpsbRowKey, name: '13-14', description: 'Gesamtpunkte', raw: totalScores.value.r, plot: true },
  { key: 'test_14_wrong' as LpsbRowKey, name: '-13', description: 'Fehler insgesamt', raw: totalScores.value.s, plot: true },
  { key: 'total_raw' as LpsbRowKey, name: 'GL', description: 'Gesamtrohwert', raw: totalScores.value.total, plot: false },
]);

function isScoreKey(key: LpsbRowKey): key is LpsBScoreKey {
  return key !== 'total_raw';
}

const scoringRowsWithScores = computed(() =>
  scoringRows.value.map((row) => {
    if (!isScoreKey(row.key)) {
      return { ...row, t: totalScoreEntry.value?.t ?? null, c: totalScoreEntry.value?.c ?? null };
    }
    const entry = lookupColumnScore(row.key, row.raw ?? 0);
    return {
      ...row,
      t: entry?.t ?? null,
      c: entry?.c ?? null,
    };
  }),
);

const lpsbGridWidth = computed(() => lpsbTValues.length * lpsbScoreWidth);
const lpsbGridHeight = computed(() => scoringRows.value.length * lpsbRowHeight);
const lpsbScoreOffsetX = lpsbLabelWidth + lpsbRawWidth + lpsbTValueWidth + lpsbHatchWidth;
const lpsbTopWidth = computed(() => lpsbScoreOffsetX + lpsbGridWidth.value);
const lpsbVertical40X = computed(() => lpsbScoreOffsetX + 2 * lpsbScoreWidth);
const lpsbVertical50X = computed(() => lpsbScoreOffsetX + 4 * lpsbScoreWidth);
const lpsbVertical60X = computed(() => lpsbScoreOffsetX + 6 * lpsbScoreWidth);
const lpsbMinT = lpsbTValues[0];
const lpsbMaxT = lpsbTValues[lpsbTValues.length - 1];
const lpsbStepT = lpsbTickStep;

const lpsbPoints = computed(() =>
  scoringRows.value.map((row, index) => {
    if (!row.plot || !isScoreKey(row.key)) return null;
    const tValue = getChartTValue(row.key as LpsBScoreKey, row.raw);
    if (tValue === null || tValue === undefined) return null;
    if (tValue < lpsbMinT || tValue > lpsbMaxT) return null;
    const tOffset = (tValue - lpsbMinT) / lpsbStepT;
    const x = lpsbScoreOffsetX + tOffset * lpsbScoreWidth;
    const y = index * lpsbRowHeight + lpsbRowHeight / 2;
    return { x, y };
  }),
);

const lpsbPolylinePoints = computed(() =>
  scoringRows.value
    .map((row, index) => {
      if (!row.plot || !isScoreKey(row.key)) return null;
      if (row.key === 'test_14_wrong') return null;
      const tValue = getChartTValue(row.key as LpsBScoreKey, row.raw);
      if (tValue === null || tValue === undefined) return null;
      if (tValue < lpsbMinT || tValue > lpsbMaxT) return null;
      const tOffset = (tValue - lpsbMinT) / lpsbStepT;
      const x = lpsbScoreOffsetX + tOffset * lpsbScoreWidth;
      const y = index * lpsbRowHeight + lpsbRowHeight / 2;
      return `${x},${y}`;
    })
    .filter((point): point is string => point !== null)
    .join(' '),
);

const lpsbPointsFiltered = computed(() => lpsbPoints.value.filter((point): point is { x: number; y: number } => !!point));
const lpsbPrimaryKeys = new Set<LpsBScoreKey>(['test_1_2', 'test_3_4', 'test_5_6', 'test_7_10', 'test_11_12', 'test_13_14']);
const lpsbSecondaryKeys = new Set<LpsBScoreKey>([
  'test_3',
  'test_4',
  'test_5',
  'test_6',
  'test_7',
  'test_8',
  'test_9',
  'test_10',
  'test_11',
  'test_12',
  'test_13',
  'test_14',
  'test_14_wrong',
]);
const lpsbPrimaryPoints = computed(() =>
  scoringRows.value
    .map((row, index) => {
      if (!row.plot || !isScoreKey(row.key)) return null;
      if (!lpsbPrimaryKeys.has(row.key)) return null;
      const tValue = getChartTValue(row.key as LpsBScoreKey, row.raw);
      if (tValue === null || tValue === undefined) return null;
      if (tValue < lpsbMinT || tValue > lpsbMaxT) return null;
      const tOffset = (tValue - lpsbMinT) / lpsbStepT;
      const x = lpsbScoreOffsetX + tOffset * lpsbScoreWidth;
      const y = index * lpsbRowHeight + lpsbRowHeight / 2;
      return { x, y };
    })
    .filter((point): point is { x: number; y: number } => !!point),
);
const lpsbPrimaryPolylinePoints = computed(() =>
  lpsbPrimaryPoints.value.map((point) => `${point.x},${point.y}`).join(' '),
);

const lpsbDividerKeys = new Set<LpsbRowKey>([
  'test_3',
  'test_3_4',
  'test_5',
  'test_5_6',
  'test_7',
  'test_7_10',
  'test_11',
  'test_11_12',
  'test_13',
  'test_13_14',
  'test_14_wrong',
  'total_raw',
]);
</script>

<template>
  <div class="space-y-6">
    <div v-if="!isLpsB" class="rounded-lg border bg-background">
      <div class="border-b px-4 py-3 text-base font-semibold">{{ testLabel }} – Seite 1</div>
      <div class="overflow-x-auto">
        <table class="min-w-[960px] w-full border-collapse text-sm">
          <thead>
            <tr class="bg-muted/40 text-left">
              <th colspan="2" class="px-4 py-2 text-center font-semibold">1 + 2</th>
              <th class="px-4 py-2 text-center font-semibold">3</th>
              <th class="px-4 py-2 text-center font-semibold">4</th>
              <th class="px-4 py-2 text-center font-semibold">5</th>
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
            <tr v-for="row in displayRows" :key="row.row.id" class="border-t align-top">
              <td class="px-3 py-3">
                <div class="flex flex-wrap gap-2">
                  <div
                    v-for="(char, charIdx) in row.row.column1.split('')"
                    :key="`${row.row.id}-1-${charIdx}`"
                    class="relative flex h-8 w-8 items-center justify-center rounded-full border bg-muted/20 font-semibold"
                  >
                    <span class="text-sm leading-none">{{ char }}</span>
                    <span v-if="row.response.col1?.[charIdx]" class="absolute text-lg font-bold leading-none text-destructive">
                      /
                    </span>
                  </div>
                </div>
              </td>
              <td class="px-3 py-3">
                <div class="flex flex-wrap gap-2">
                  <div
                    v-for="(char, charIdx) in row.row.column2.split('')"
                    :key="`${row.row.id}-2-${charIdx}`"
                    class="relative flex h-8 w-8 items-center justify-center rounded-full border bg-muted/20 font-semibold"
                  >
                    <span class="text-sm leading-none">{{ char }}</span>
                    <span v-if="row.response.col2?.[charIdx]" class="absolute text-lg font-bold leading-none text-destructive">
                      /
                    </span>
                  </div>
                </div>
              </td>
              <td class="px-3 py-3">
                <div v-if="row.row.column3?.length" class="grid grid-cols-4 gap-2">
                  <div
                    v-for="(option, optionIdx) in row.row.column3"
                    :key="option.id"
                    class="relative flex h-12 w-12 items-center justify-center rounded-md border bg-muted/20"
                  >
                    <img :src="option.src" class="h-10 w-10" alt="" />
                    <span
                      v-if="row.response.col3?.[optionIdx]"
                      class="absolute text-lg font-bold leading-none text-destructive"
                    >
                      /
                    </span>
                  </div>
                </div>
                <div v-else class="text-center text-muted-foreground">–</div>
              </td>
              <td class="px-3 py-3 text-center text-muted-foreground">–</td>
              <td class="px-3 py-3 text-center text-muted-foreground">–</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
      <div>Benötigte Zeit: {{ formatTime(totalTime) }}</div>
    </div>

    <div v-if="isLpsB" class="space-y-4 rounded-lg border bg-background p-4">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <h3 class="text-base font-semibold">LPS-B Auswertung</h3>
        <div class="text-sm text-muted-foreground">
          Altersgruppe: <span class="font-semibold text-foreground">{{ ageGroupLabel }}</span>
          <span v-if="participantProfile?.age !== undefined"> (Alter {{ participantProfile?.age }})</span>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-3 text-sm">
        <label class="flex items-center gap-2">
          <span class="text-muted-foreground">Spalte 6 (manuell)</span>
          <input
            v-model.number="column6Manual"
            type="number"
            class="h-8 w-24 rounded-md border border-input bg-background px-2 py-1 text-sm"
            min="0"
          />
        </label>
        <Button v-if="canSaveManual" size="sm" :disabled="isSavingManual" @click="saveManualColumn6">
          {{ isSavingManual ? 'Speichern...' : 'Speichern' }}
        </Button>
        <span v-if="manualSaveMessage" class="text-xs text-emerald-600">{{ manualSaveMessage }}</span>
        <span v-if="manualSaveError" class="text-xs text-red-600">{{ manualSaveError }}</span>
        <span class="text-xs text-muted-foreground">
          Tragen Sie den Wert aus der Ergebnisansicht für Spalte 6 ein.
        </span>
      </div>

      <div class="overflow-x-auto">
        <div class="lpsb-top" :style="{ width: `${lpsbTopWidth + 24}px` }">
<svg xmlns="http://www.w3.org/2000/svg" class="lpsb-svg" viewBox="0 0 790 332">
  <path d="m612.62 123.25 3.18 19.91-.21.59-.04.06-.06.05-.07.01-.07-.01-.02-.01-.25-.15-.11-1.73-.16-1.45-.09-.63-.11-.56-.11-.49-.13-.42-1.27-3.58-1.32-3.52-1.37-3.46-1.41-3.41-1.46-3.36-1.51-3.29-1.55-3.24-1.6-3.19-.83-1.63-.74-1.53-.66-1.42-.58-1.32-.53-1.23-.54-1.2-.83-1.76-.92-1.83-1.05-2.02-1.55-2.89-2.33-4.35-.51-.91-.59-.98-.69-1.04-.77-1.11-.87-1.18-.95-1.25-1.05-1.32-1.13-1.38-1.09-1.32-1.14-1.42-2.8-3.6-2.51-3.21-1.87-2.31-.9-1.06-.88-1-.87-.96-.87-.93-1.97-2.02-2.03-2-2.1-2-2.15-1.98-2.21-1.98-2.27-1.97-2.34-1.96-2.39-1.95-8.86-5.62-1.24-.75-.86-.47-.87-.45-.91-.43-.95-.42-.98-.41-1.09-.42-2.93-1.07-2.71-.98-2.12-.81-.59-.22-.61-.18-.62-.15-.62-.11-2.44-.38-3.07-.48-2.4-.35-2.21-.27-2.02-.2-1.91-.14-1.76-.07-1.63-.01-1.47.07-2.76.22-2.76.25-2.76.3-2.75.33-2.77.36-2.76.41-2.76.44-2.76.48-.37.08-.35.1-.36.13-.34.15-1.61.73-2.23.92-2.01.82-1.48.65-1.37.66-1.26.68-1.05.59-5.12 2.91-3.57 2.08-3.11 1.89-1.42.89-1.35.89-1.35.9-1.29.9-1.25.9-1.21.91-1.18.92-1.15.93-1.12.94-1.1.97-.76.68-2.26 2.09-2.07 1.94-1.92 1.85-1.8 1.76-1.71 1.73-1.63 1.67-1.54 1.64-1.48 1.62-1.43 1.62-1.36 1.6-1.3 1.59-1.25 1.58-1.2 1.57-1.14 1.58-1.09 1.57-1.04 1.57-1.59 2.44-1.98 3.05-1.4 2.21-1.22 1.99-1.08 1.83-.99 1.77-.87 1.69-.79 1.63-.69 1.58-.42.98-.49 1.06-1.31 2.68-1.16 2.36-.84 1.82-.74 1.76-.65 1.74-.18.53-.58 1.67-.6 1.67-.63 1.68-.65 1.69-.68 1.69-.71 1.7-.73 1.71-.75 1.71-.13-.68-.08-.68-.01-.46.02-.47.05-.46.07-.47.54-2.77.22-2 .25-1.92.26-1.85.28-1.78 2.02-5.99 1.87-5.66.2-.51.31-.71.44-.91.57-1.11 1.49-2.8 1.98-3.59.68-1.19.72-1.21.77-1.25.83-1.32 1.84-2.78 2.55-3.72 2.12-3.08 1.48-2.22 1.3-1.92 1.37-1.91 1.45-1.92 1.53-1.91 1.61-1.92 1.67-1.9 1.76-1.91 1.82-1.89 1.91-1.89 1.97-1.88 2.04-1.87 2.1-1.86 2.17-1.84 2.23-1.82 2.29-1.81 2.33-1.77 1.09-.8 1.12-.79 1.15-.79 1.18-.78 1.22-.79 1.25-.78 2.65-1.57 2.77-1.56 3.08-1.66 3.46-1.8 4.85-2.46.65-.33 1.34-.65 1.33-.6 1.32-.55 1.31-.5 1.31-.44 1.29-.39 1.29-.34 1.27-.28 2.89-.56 2.81-.49 2.75-.43 2.67-.36 2.61-.3 2.53-.24 2.47-.18 2.39-.11 1.39-.03.96.01.96.04.96.05.97.08.98.1 1.01.12 2.09.32 2.14.4 2.39.51 2.71.65 3.91.98 3.03.77 1.51.4 1.49.45 1.46.49 1.44.54 1.41.58 1.39.63 1.36.66 1.34.72.85.48.84.5.83.51.82.54 1.81 1.23 1.8 1.26 1.77 1.27 1.76 1.3 1.74 1.33 1.72 1.34 1.7 1.37 1.68 1.39 1.56 1.33 1.54 1.35 1.53 1.37 1.51 1.39 1.5 1.4 1.48 1.43 1.46 1.45 1.44 1.47 1.03 1.08 1.03 1.11 1.02 1.14 1.02 1.17 1 1.2 1.01 1.22 1 1.26.99 1.28 3.12 4.02 2.13 2.73 1.46 1.95 1.28 1.82 1.16 1.77.69 1.12 1.15 1.96 1.14 1.96 1.11 1.98 1.09 1.99 1.08 1.99 1.05 2.01 1.03 2.01 1.02 2.03 1.35 2.78 1.31 2.8 1.28 2.81 1.24 2.84 1.2 2.85 1.17 2.87 1.13 2.89z"/>
  <path d="m523.84 120.03-.15.73-.12.76-.09.77-.06.81-.03.82-.01.85.03.87.06.89.02.34-.01.32-.04.3-.05.28-.09.25-.1.23-.13.21-.15.19-.25.23-.29.18-.34.14-.38.1-.42.05-.47.02-.5-.03-.54-.07-.61-.12-.64-.17-.67-.2-.69-.25-.71-.29-.69-.32-.64-.33-.53-.31-.31-.23-.28-.27-.24-.31-.2-.33-.16-.35-.11-.38-.05-.38-.01-.39.13-2.8.1-2.73.07-2.65.05-2.58.01-2.51-.02-2.44-.04-2.37-.08-2.29-.01-.58.03-.63.05-.62.08-.62.1-.58.13-.55.15-.52.18-.48.19-.44.2-.39.24-.37.24-.31.27-.28.28-.24.3-.19.32-.16.43-.14.45-.07 1.48-.09 1.54-.02 1.55.03 1.52.09 1.43.15 1.38.2 1.29.25 1.2.31 1.07.34.99.4.45.21.43.22.4.23.39.25.35.26.33.27.3.28.28.29.25.3.23.31.2.32.17.33.23.52.21.52.19.52.16.52.13.53.11.52.08.53.06.54.04.92-.03.93-.11.94-.19.94-.26.95-.34.96-.41.96-.49.97-.21.46-.15.48-.06.31-.03.31v.32l.03.32.06.33.09.34.12.33.15.36.17.34.2.36.5.77.63.85.94 1.15.77.94.54.69.44.62.37.58.29.56.23.54.15.51.09.5v.42l-.07.41-.14.4-.21.37-.28.34-.33.29-.38.24-.41.18-.92.28-.87.21-.83.12-.78.04-.38-.01-.37-.03-.35-.05-.34-.07-.34-.09-.32-.1-.31-.13-.31-.15-.38-.24-.37-.26-.35-.31-.33-.34-.31-.38-.3-.41-.27-.45-.25-.49-.8-1.63-.76-1.5-.74-1.38-.71-1.26-.13-.17-.17-.14-.2-.09-.21-.04-.22.01-.21.07-.18.12-.15.15-.09.16zm2.32-9.65 1.28-2.48.05-.13.03-.13v-.14l-.02-.13-.05-.12-.07-.12-.09-.1-.11-.08-.05-.02-.25-.13-.81-.38-.41-.15-.41-.14-.4-.11-.39-.08-.37-.06-.36-.03-.34-.01-.31.02-.29.05-.25.08-.22.1-.19.12-.15.15-.11.17-.06.1-.07.19-.03.21v.23l.05.23.09.25.12.26.17.27.2.27.23.28.27.27.29.27.32.26.35.26.36.24.78.44.25.13.13.05.13.03h.14l.14-.03.12-.05.12-.08.1-.09.08-.12zm-52.47-13.32 3.49.27.29.04.29.06.28.1.27.12.26.14.24.17.22.19.21.22.18.23.16.25.13.26.11.28.08.28.05.29.03.29-.01.3-.12 1.85-.09 1.82-.07 1.78-.05 1.75-.02 1.71v1.68l.02 1.64.05 1.61.07 1.57.1 1.54.12 1.5.14 1.46.16 1.43.19 1.4.21 1.36.24 1.32.04.28.01.3-.02.31-.05.3-.08.3-.11.28-.14.27-.17.26-.2.24-.21.21-.24.19-.26.17-.28.14-.28.11-.3.08-.3.04-.31.02h-2.9l-.29-.01-.28-.04-.28-.06-.28-.09-.26-.11-.26-.13-.24-.15-.23-.17-.21-.2-.2-.21-.17-.22-.16-.24-.13-.25-.12-.27-.08-.27-.07-.28-1.06-5.66-.09-.32-.13-.29-.19-.27-.22-.24-.26-.2-.29-.15-.32-.11-.32-.05-5.77-.45h-.16l-.17.03-.15.06-.14.09-.12.1-.11.13-.08.14-.05.16-.22.96-.16.94-.12.93-.07.91-.02.9.03.89.07.88.13.86.03.23.01.28-.02.28-.05.28-.07.27-.11.26-.13.24-.15.24-.18.21-.2.2-.22.17-.24.16-.25.12-.26.1-.27.07-.28.05-.28.01h-1.61l-.28-.01-.28-.05-.27-.07-.27-.09-.25-.13-.24-.15-.23-.17-.2-.19-.19-.21-.16-.23-.14-.25-.11-.26-.09-.27-.06-.27-.03-.28-.01-.28.01-.11.43-5.71v-.27l-.73-21.56v-.09l.04-.34.11-.32.17-.29.23-.26.28-.2.31-.14.33-.08.34-.01 3.69.28.33.06.31.11.28.17.25.23.2.26.15.29.09.32.03.33v11.11l.01.12.03.11.06.1.07.09.09.07.1.06.11.03.11.01h7.74l.29-.03.28-.08.26-.14.23-.19.19-.23.14-.26.08-.28.03-.29V99.05l.02-.21.04-.2.06-.2.08-.19.1-.19.11-.17.14-.16.15-.15.16-.13.17-.11.19-.1.19-.08.21-.05.2-.04.21-.02zm76.28 20.55-.11-.03-.09-.01-.09.02-.08.04-.06.06-.06.07-.03.08-.02.09.01.09 1.41 7.52.04.3v.25l-.03.25-.05.24-.08.23-.1.23-.13.21-.14.2-.17.18-.19.17-.2.14-.22.12-.22.1-.24.07-.25.04-.24.03-.25-.01-5.3-.41-.26-.03-.24-.06-.24-.08-.23-.1-.22-.13-.21-.14-.19-.17-.18-.19-.15-.2-.14-.21-.11-.23-.09-.24-.07-.24-.04-.25-.02-.26.01-.25.13-1.91.11-2.13.09-2.36.08-2.58.05-2.82.04-3.04.01-6.76.01-.69.03-.66.06-.63.07-.59.1-.58.13-.54.14-.51.17-.48.12-.26.17-.24.2-.21.22-.18.26-.14.27-.1.28-.06.29-.01.62.04.59.07.57.1.56.14.53.17.51.21.49.24.47.27.58.41.55.47.51.52.48.59 1.67 2.26 1.49 2.07 1.3 1.85 1.13 1.66.33.43.35.35.19.14.2.13.2.1.21.08.25.07.28.03.28-.03.27-.09.24-.15.2-.19.15-.23.1-.27.04-.27-.01-.24-.02-.16-.3-2.02-.17-1.5-.05-.69-.02-.64v-.6l.02-.57.05-.55.07-.52.1-.49.12-.45.15-.42.18-.4.2-.36.23-.34.01-.01.2-.22.23-.19.27-.16.29-.13.33-.1.35-.07.38-.04.41-.01.43.02.45.06.46.1.44.12.44.15.4.17.37.2.32.21.26.22.24.26.22.31.22.36.19.4.17.44.16.5.15.53.13.59.1.63.1.67.07.7.05.74.03.76.01.77-.02.71-.05 1.85v1.83l.06 1.8.11 1.79.16 1.76.21 1.74.27 1.71.32 1.7.11.65.07.64.03.61-.02.6-.06.58-.1.56-.15.54-.2.52-.24.51-.28.49-.34.46-.18.2-.19.18-.2.16-.2.13-.22.12-.22.09-.33.1-.33.05-.36.02-.36-.03-.38-.07-.39-.11-.4-.15-.42-.19-.43-.24-.43-.27-.45-.32-.44-.35-.45-.39-.42-.41-.41-.43-.37-.43-1.05-1.23-1.09-1.16-.56-.54-.56-.53-.58-.52-.59-.49-.59-.47-.61-.46-.61-.43-.62-.42-.64-.4-.64-.38-.65-.36-.66-.34zm-39.91.09.014.551-.013.55-.04.55-.066.547-.093.542-.12.538-.146.53-.172.524-.198.514-.222.504-.247.492-.27.48-.294.465-.317.451-.338.435-.36.417-.378.4-.398.38-.417.36-.434.34-.45.318-.464.295-.479.272-.491.249-.503.224-.514.199-.523.173-.53.148-.537.121-.543.095-.546.069-.55.04-1.817.093-.55.014-.551-.012-.55-.04-.546-.067-.543-.093-.537-.12-.531-.146-.524-.172-.514-.197-.503-.223-.493-.246-.48-.271-.465-.294-.45-.317-.435-.338-.418-.359-.4-.379-.38-.398-.36-.416-.34-.434-.317-.45-.296-.465-.272-.478-.248-.492-.224-.503-.199-.514-.174-.522-.147-.53-.122-.538-.095-.542-.068-.547-.041-.549-.45-8.889-.015-.55.013-.55.04-.55.066-.547.093-.542.12-.538.146-.53.172-.524.198-.514.222-.504.247-.492.27-.48.294-.466.317-.45.338-.435.36-.417.378-.4.398-.38.417-.36.434-.34.45-.318.464-.295.479-.272.491-.249.503-.224.514-.199.523-.173.53-.148.537-.121.543-.095.546-.069.55-.04 1.817-.093.55-.014.551.012.55.04.546.067.543.093.537.12.531.146.524.172.514.197.503.223.493.246.48.271.465.294.45.317.435.338.418.359.4.379.38.398.36.417.34.433.317.45.296.465.272.479.248.491.224.503.199.514.174.522.147.53.122.538.095.542.068.547.041.549zm-7.73 1.59.28-.92.23-.95.19-.94.13-.94.08-.92.04-.9-.03-.87-.07-.82-.13-.77-.17-.72-.22-.69-.28-.62-.32-.58-.36-.52-.42-.47-.46-.41-.51-.37-.56-.3-.61-.25-.65-.18-.7-.13-.73-.06-.79-.01-.82.06-.3.05-.29.09-.27.14-.25.16-.23.2-.2.23-.16.26-.13.27-.25.7-.22.7-.19.7-.17.7-.13.7-.1.7-.07.71-.04.7-.02.9.04.9.09.91.13.9.19.85.12.41.13.39.16.37.16.36.19.34.2.33.22.3.23.28.24.25.25.23.27.21.28.19.29.16.3.14.29.11.31.09.3.06.32.04.32.02h.34l.33-.02.35-.05.69-.14.63-.16.56-.21.5-.23.43-.27.36-.31.29-.34.24-.37zm122.9 30.511-.003-.005c-1.15-1.768-2.166-6.537-3.977-10.709-1.704-3.925-5.68-10.869-7.862-15.348l-.058-.029-.71-.46 2.922 18.294q.084.102.173.227c1.135 1.606 2.454 7.024 4.13 10.556 1.76 3.708 4.396 8.359 6.272 11.167q.262.39.539.748l.144-.242-.25-.91-.22-.92-.21-.92-.18-.92-.23-1.35-.2-1.35-.14-1.36-.11-1.37-.07-1.38-.02-1.39.02-1.39Zm.152 66.511c-1.089.326-3.725.569-5.044.876-1.018.236-1.941.262-2.448.731-.448.415-.519 1.193-.619 1.773-.094.544-.185 1.206 0 1.603.164.352.525.592.928.732.484.168 1.287-.047 1.857.056.53.096.791.39 1.463.507 1.01.174 3.363.169 4.277.168q.16-.001.284-.017v-.001l-.21-.29-.19-.31-.15-.29-.14-.31-.11-.31-.1-.33-.15-.68-.07-.72.01-.76.07-.81.15-.84Zm.414 23.363c-.212.274-.435.577-.587.959-.382.961-.817 3.379-.81 3.779.002.109.063.017.068.135.019.495-.407 3.349-.878 5.332-.559 2.353-3.101 6.604-2.429 7.93.427.843 2.276.498 3.476.613l-.026-.093-.01-.29.03-.31.08-.34.33-1.16.27-1.07.21-.99.16-.9.11-.82.04-.74-.01-.66-.06-.57-.17-1.08-.13-1.08-.09-1.07-.06-1.07-.02-1.07.02-1.06.06-1.06.09-1.05.16-1.21Zm-.812 59.072c-.792 3.576-1.353 9.053-1.723 12.52-.349 3.261-.621 6.668-.498 8.453.064.923.039 1.386.374 1.885 1.137.078 2.158.187 2.917.333l-.174-.428-.2-.55-.16-.51-.12-.46-.09-.44-.06-.39-.01-.35.02-.31.13-1.01.11-1.04.16-2.11.09-2.19.01-2.26-.06-2.33-.13-2.4-.21-2.48-.29-2.54-.05-.54-.03-.53ZM405.74 123.67l-.22 2-.54 2.77-.07.47-.05.46-.02.47.01.46.08.68.054.284a34 34 0 0 0-1.908 3.742c-.927 2.145-1.076 4.597-2.213 7.103-1.322 2.917-4.128 6.4-5.589 9.316l-.154.308.24-1.243.27-1.69.22-1.73.17-1.77.12-1.8.03-.92-.03-.86-.08-.81-.03-.188c.901-1.477 1.166-4.693 2.505-7.356 1.489-2.963 5.473-7.96 7.379-11.034Zm.67 97.94-.18.53-.15.54-.12.56-.1.56-.06.57-.04.59v.59l.02.61.3 5.27-.1 4.21-.03 3.69.04 3.15.04 1.38.06 1.25.04 1.13v1.28l-.03 1.46-.08 2.04-.11 2.89-.02 2.13.02.99.04.95.06.89.08.87.01.24-.02.24-.04.25-.07.23-.26.76-.21.75-.16.74-.083.562c-.851.095-1.939.104-3.048.165-1.955.108-5.807-.108-8.323-.105l.094-.072.12-.12.12-.15.1-.18.09-.2.16-.48.21-.94.15-.84.07-.76v-.65l-.03-.3-.04-.27-.06-.25-.08-.22-.1-.21-.11-.17-.13-.16-.15-.13-.16-.1-.17-.09-.19-.06-.2-.04-.21-.02h-.118l.208-.04.24-.07.23-.08.2-.11.19-.13.18-.14.13-.15.13-.16.21-.36.15-.43.002-.01.129.017c.84.092 2.54-.055 3.649-.356 1.044-.283 2.199-.873 2.848-1.335.468-.333.633-.513.935-1.023.437-.741 1.117-2.183 1.246-3.293.125-1.086-.172-2.359-.445-3.293-.235-.803-.45-1.436-.979-2.047-.608-.701-1.672-1.508-2.626-1.825-.933-.309-2.108-.128-3.026-.089-.813.035-1.509.062-2.225.267-.224.064-.466.136-.705.218l-.153-.421-.32-.8-.35-.79-.37-.78-.11-.27-.07-.29-.03-.29.01-.29.08-.38.16-.42.24-.46.752-1.235.017.003c.554.092 1.393.019 2.063-.122q.237.087.453.13c.88.173 1.877.026 2.848-.178 1.043-.219 2.431-.524 3.204-1.157.707-.579 1.157-1.475 1.379-2.358.235-.931.1-2.313-.089-3.115-.144-.613-.298-.954-.712-1.424-.542-.616-1.532-1.317-2.536-1.825-1.133-.572-2.897-1.142-4.005-1.335-.844-.146-1.529-.129-2.136 0q-.15.032-.292.071l-.076-.215.52-.92.41-.87.31-.81.11-.4.09-.37.07-.37.03-.36.02-.34-.02-.33-.04-.31-.06-.31-.1-.29-.11-.28-.15-.28-.19-.27-.21-.26-.23-.24-.27-.23-.3-.21-.33-.2-.069-.037.059.017.12-.01.12-.05.12-.08.13-.11.079-.109c1.413.021 3.34-.198 4.054-.103.452.06.34.03.801.267 1.164.598 5.575 4.265 7.208 4.628q.192.042.367.055Zm-.33 71.21-.17.52-.13.52-.1.52-.07.52-.02.52v.51l.04.49.08.5.11.49.14.48.17.49.21.49.24.48.27.48-.33 1.49-.29 1.54-.24 1.61-.2 1.66-.15 1.72-.11 1.78-.06 1.84-.02 1.9.01.46.04.44.07.43.09.41.12.4.14.38.16.37.2.35.35.52.41.49-.27-.16-.24-.09-.23-.03-.2.03-.12.05-.12.08-.1.11-.1.14-.17.36-.13.47-.09.51-.04.5v.5l.05.49.09.44.12.43.16.43.2.42.24.42.28.41.32.41.35.4-.32-.08-.28-.02-.26.03-.196.071a4.4 4.4 0 0 1-.741-.184c-.991-.356-2.221-.833-3.026-1.602-.818-.781-1.381-1.783-1.824-3.07-.547-1.587-.901-5.277-.89-5.963.003-.201.084-.122.089-.267.009-.286-.217-.766-.267-1.513-.087-1.292-.047-4.355.089-6.051.109-1.365.046-2.042.534-3.471.709-2.079 2.827-6.537 4.094-7.965.72-.812 1.568-.755 2.092-1.169Zm-12.828-35.66-.052.01-.28.04-.3.01.51-.06Zm.109-40.077-.111-.033-.17-.12Z"/><path d="M 394.26 155.37 L 394.67 153.78 L 395.04 152.15 L 395.36 150.49 L 395.63 148.8 L 395.85 147.07 L 396.02 145.3 L 396.14 143.5 L 396.17 142.58 L 396.14 141.72 L 396.06 140.91 L 395.94 140.16 L 395.91 140.08 L 395.83 140.01 L 395.73 139.99 L 395.63 140.02 L 395.55 140.09 L 395.25 140.57 L 395.13 141.61 L 394.99 142.59 L 394.81 143.53 L 394.61 144.42 L 394.38 145.25 L 394.12 146.04 L 393.83 146.78 L 393.51 147.47 L 392.52 149.49 L 391.62 151.4 L 390.83 153.19 L 390.13 154.88 L 387.26 162.11 L 386 164.81 L 384.68 167.48 L 383.31 170.11 L 381.9 172.71 L 380.43 175.27 L 378.91 177.8 L 377.35 180.3 L 375.73 182.76 L 374.75 184.21 L 373.75 185.64 L 372.73 187.06 L 371.69 188.47 L 369.85 190.92 L 368.05 193.26 L 366.28 195.51 L 364.55 197.67 L 362.84 199.77 L 361.13 201.8 L 359.44 203.76 L 357.75 205.68 L 356.06 207.54 L 354.36 209.36 L 352.67 211.13 L 350.96 212.86 L 349.25 214.54 L 347.52 216.19 L 345.78 217.8 L 344.03 219.38 L 343.31 220.04 L 342.54 220.79 L 341.71 221.63 L 340.6 222.8 L 339.14 224.33 L 338.05 225.41 L 337.04 226.32 L 336.09 227.08 L 334.02 228.6 L 331.84 230.13 L 329.54 231.68 L 327.09 233.27 L 324.54 234.87 L 321.73 236.58 L 318.61 238.45 L 314.31 240.98 L 309.04 244.08 L 305.2 246.39 L 304.91 246.59 L 304.65 246.83 L 304.42 247.1 L 304.23 247.4 L 304.08 247.72 L 303.97 248.06 L 303.9 248.41 L 303.88 248.76 L 303.88 249.37 L 303.92 249.7 L 304.03 250.02 L 304.21 250.3 L 304.45 250.53 L 304.73 250.7 L 305.05 250.8 L 305.38 250.83 L 305.71 250.78 L 305.89 250.72 L 306.06 250.64 L 307.45 249.86 L 308.97 249.06 L 310.66 248.2 L 312.96 247.06 L 315.79 245.65 L 317.82 244.6 L 319.64 243.59 L 321.26 242.61 L 324.39 240.59 L 327.45 238.54 L 330.42 236.46 L 333.32 234.35 L 336.13 232.21 L 338.87 230.03 L 341.53 227.83 L 344.11 225.59 L 345.8 224.07 L 347.55 222.44 L 349.36 220.71 L 351.24 218.89 L 353.16 216.95 L 355.15 214.92 L 357.2 212.79 L 359.31 210.55 L 360.81 208.92 L 362.3 207.27 L 363.76 205.61 L 365.2 203.92 L 366.62 202.22 L 368.03 200.5 L 369.41 198.76 L 370.77 197 L 372.12 195.22 L 373.44 193.42 L 374.74 191.6 L 376.03 189.76 L 377.29 187.91 L 378.53 186.03 L 379.76 184.14 L 380.96 182.23 L 388.1 170.36 L 388.78 169.17 L 389.46 167.84 L 390.16 166.37 L 390.86 164.77 L 391.58 163.01 L 392.31 161.12 L 393.04 159.09 L 393.79 156.92 Z M 625.19 150.74 L 625.17 152.13 L 625.19 153.52 L 625.26 154.9 L 625.37 156.27 L 625.51 157.63 L 625.71 158.98 L 625.94 160.33 L 626.12 161.25 L 626.33 162.17 L 626.55 163.09 L 626.8 164 L 627.66 165.87 L 628.54 167.72 L 629.44 169.54 L 630.36 171.35 L 631.31 173.13 L 632.28 174.88 L 633.27 176.62 L 634.29 178.33 L 635.32 180.02 L 636.38 181.68 L 637.47 183.33 L 638.57 184.95 L 639.7 186.54 L 640.85 188.12 L 642.02 189.67 L 643.22 191.2 L 644.25 192.47 L 645.42 193.82 L 646.73 195.29 L 648.55 197.28 L 650.94 199.9 L 652.63 201.84 L 653.83 203.22 L 655.07 204.59 L 656.37 205.96 L 657.77 207.38 L 659.23 208.79 L 660.87 210.33 L 662.74 212.05 L 665.42 214.47 L 668.83 217.57 L 670.11 218.73 L 671.37 219.83 L 672.61 220.89 L 673.82 221.9 L 675 222.85 L 676.16 223.76 L 677.3 224.61 L 678.41 225.42 L 682.06 228.03 L 685.24 230.37 L 687.96 232.43 L 690.23 234.21 L 690.59 234.48 L 690.97 234.74 L 691.36 234.97 L 691.76 235.18 L 692.77 235.68 L 693.8 236.22 L 694.84 236.79 L 695.9 237.41 L 696.97 238.06 L 698.05 238.74 L 699.14 239.47 L 700.25 240.23 L 700.75 240.53 L 701.59 240.98 L 704.31 242.33 L 708.4 244.27 L 713.86 246.8 L 713.95 246.84 L 714.33 246.95 L 714.71 246.97 L 715.1 246.92 L 715.46 246.8 L 715.8 246.6 L 716.08 246.34 L 716.31 246.02 L 716.47 245.67 L 716.57 245.38 L 716.64 245.15 L 716.68 244.91 L 716.71 244.67 L 716.71 244.42 L 716.69 244.18 L 716.65 243.94 L 716.59 243.71 L 716.51 243.48 L 716.41 243.26 L 716.28 243.05 L 716.14 242.85 L 715.99 242.66 L 715.81 242.49 L 715.62 242.34 L 715.42 242.2 L 715.21 242.08 L 707.37 238.16 L 704.04 236.47 L 702.46 235.55 L 699.58 233.8 L 689.88 227.84 L 688.36 226.88 L 686.79 225.84 L 685.18 224.74 L 683.53 223.58 L 681.84 222.34 L 680.1 221.03 L 678.32 219.66 L 676.5 218.22 L 674.38 216.47 L 672.15 214.55 L 669.81 212.46 L 667.36 210.18 L 664.8 207.73 L 662.13 205.11 L 659.35 202.31 L 656.46 199.33 L 654.13 196.85 L 651.9 194.38 L 649.78 191.9 L 647.75 189.43 L 645.83 186.97 L 644.91 185.73 L 644.02 184.5 L 643.15 183.27 L 642.3 182.04 L 641.48 180.81 L 640.69 179.58 L 638.48 176.14 L 635.9 172.13 L 634.95 170.6 L 634.11 169.2 L 633.32 167.86 L 632.62 166.61 L 631.99 165.43 L 631.42 164.3 L 630.56 162.48 L 629.72 160.65 L 628.91 158.8 L 628.12 156.94 L 627.36 155.06 L 626.63 153.17 L 625.93 151.26 L 625.25 149.34 Z M 741.68 244.6 L 741.68 245.35 L 741.61 246.11 L 741.47 246.85 L 741.26 247.6 L 741 248.32 L 740.67 249.02 L 740.28 249.67 L 739.85 250.28 L 739.38 250.83 L 738.86 251.32 L 738.31 251.75 L 737.73 252.11 L 737.13 252.39 L 736.5 252.6 L 735.87 252.74 L 735.36 252.79 L 734.85 252.79 L 734.33 252.75 L 733.8 252.66 L 733.28 252.52 L 732.76 252.34 L 732.24 252.11 L 731.71 251.83 L 731.49 251.7 L 730.05 250.87 L 729.01 250.29 L 728.08 249.81 L 727.24 249.4 L 726.43 249.06 L 725.67 248.77 L 724.95 248.56 L 724.26 248.4 L 724.01 248.38 L 723.77 248.4 L 723.53 248.48 L 723.32 248.6 L 723.13 248.75 L 722.97 248.95 L 722.86 249.16 L 722.79 249.4 L 722.79 249.41 L 722.65 250.39 L 722.58 251.32 L 722.59 252.21 L 722.67 253.06 L 722.81 253.86 L 722.91 254.24 L 723.03 254.61 L 723.17 254.97 L 723.32 255.32 L 723.5 255.66 L 723.69 255.99 L 724.05 256.52 L 724.45 257.01 L 724.91 257.47 L 725.42 257.9 L 726.09 258.4 L 726.76 258.87 L 727.44 259.29 L 728.13 259.69 L 728.81 260.04 L 729.49 260.35 L 730.18 260.63 L 730.87 260.87 L 731.55 261.07 L 732.23 261.23 L 732.91 261.34 L 733.6 261.42 L 734.27 261.46 L 734.94 261.46 L 735.6 261.42 L 736.26 261.34 L 737.01 261.2 L 737.76 261 L 738.49 260.76 L 739.22 260.45 L 739.93 260.1 L 740.64 259.69 L 741.33 259.23 L 742.01 258.72 L 742.84 258.02 L 743.63 257.28 L 744.36 256.52 L 745.06 255.7 L 745.55 255.07 L 746.01 254.42 L 746.44 253.75 L 746.85 253.07 L 747.24 252.35 L 747.6 251.62 L 747.93 250.87 L 748.23 250.1 L 748.52 249.3 L 748.77 248.49 L 749 247.66 L 749.2 246.81 L 749.38 245.94 L 749.53 245.04 L 749.66 244.14 L 749.76 243.2 L 749.83 242.25 L 749.88 241.27 L 749.9 240.29 L 749.9 239.27 L 749.87 238.25 L 749.81 237.2 L 749.73 236.13 L 749.62 235.04 L 749.48 233.94 L 749.32 232.81 L 749.14 231.67 L 748.93 230.51 L 748.69 229.33 L 748.42 228.14 L 748.13 226.93 L 747.82 225.7 L 747.5 224.58 L 747.16 223.55 L 746.79 222.61 L 746.39 221.76 L 745.97 221 L 745.53 220.34 L 745.06 219.76 L 744.82 219.51 L 744.57 219.28 L 743.9 218.71 L 743.23 218.18 L 742.57 217.69 L 741.92 217.23 L 741.27 216.82 L 740.62 216.43 L 739.97 216.08 L 739.33 215.77 L 738.69 215.49 L 738.04 215.25 L 737.41 215.04 L 736.76 214.87 L 736.13 214.73 L 735.49 214.63 L 734.85 214.56 L 734.21 214.52 L 733.7 214.52 L 733.19 214.54 L 732.69 214.58 L 732.19 214.64 L 731.68 214.73 L 731.16 214.83 L 730.65 214.96 L 730.14 215.11 L 729.12 215.47 L 728.08 215.92 L 727.04 216.45 L 725.98 217.08 L 725.6 217.34 L 725.23 217.65 L 724.87 217.99 L 724.51 218.37 L 724.17 218.8 L 723.83 219.26 L 723.49 219.77 L 723.17 220.32 L 722.85 220.9 L 722.54 221.52 L 722.24 222.19 L 721.94 222.9 L 721.34 224.46 L 720.64 226.51 L 720.49 226.95 L 720.34 227.44 L 720.21 227.93 L 720.1 228.43 L 720.02 228.94 L 719.96 229.45 L 719.92 229.96 L 719.91 230.48 L 719.92 231.01 L 719.95 231.53 L 720 232.05 L 720.08 232.58 L 720.18 233.11 L 720.3 233.64 L 720.44 234.15 L 720.61 234.67 L 720.8 235.17 L 721.01 235.68 L 721.24 236.18 L 721.49 236.66 L 721.76 237.14 L 722.04 237.6 L 722.35 238.06 L 722.68 238.5 L 723.02 238.92 L 723.37 239.33 L 723.74 239.72 L 724.13 240.09 L 724.53 240.45 L 724.94 240.79 L 725.37 241.11 L 725.8 241.4 L 726.25 241.68 L 728.39 242.91 L 730.06 243.81 L 730.71 244.13 L 731.25 244.37 L 731.67 244.53 L 731.97 244.6 L 732.99 244.69 L 734.03 244.71 L 735.09 244.65 L 736.16 244.53 L 737.25 244.33 L 738.36 244.05 L 739.48 243.71 L 740.62 243.29 L 740.7 243.26 L 740.85 243.24 L 741.01 243.25 L 741.15 243.29 L 741.29 243.36 L 741.41 243.46 L 741.5 243.58 L 741.57 243.72 L 741.61 243.87 Z M 727.83 226.2 L 728.06 225.7 L 728.32 225.22 L 728.61 224.76 L 728.91 224.33 L 729.24 223.92 L 729.59 223.55 L 729.96 223.21 L 730.35 222.89 L 730.75 222.61 L 731.17 222.37 L 731.6 222.16 L 732.05 221.98 L 732.5 221.84 L 732.96 221.75 L 733.42 221.68 L 734.03 221.66 L 734.64 221.71 L 735.25 221.82 L 735.86 221.99 L 736.46 222.23 L 737.05 222.53 L 737.64 222.9 L 738.2 223.32 L 738.56 223.65 L 738.93 224.05 L 739.3 224.53 L 739.66 225.06 L 740 225.63 L 740.33 226.25 L 740.62 226.88 L 740.87 227.51 L 741.07 228.12 L 741.24 228.71 L 741.36 229.29 L 741.43 229.83 L 741.45 230.34 L 741.43 230.81 L 741.37 231.25 L 741.26 231.64 L 741.09 232.01 L 740.86 232.4 L 740.58 232.77 L 740.23 233.15 L 739.83 233.52 L 739.37 233.89 L 738.87 234.24 L 738.32 234.57 L 737.72 234.9 L 737.09 235.2 L 736.45 235.46 L 735.82 235.69 L 735.21 235.86 L 734.62 235.99 L 734.08 236.07 L 733.58 236.09 L 732.88 236.06 L 732.2 235.97 L 731.55 235.82 L 730.94 235.62 L 730.36 235.36 L 729.82 235.06 L 729.32 234.7 L 728.87 234.29 L 728.55 233.95 L 728.26 233.59 L 728 233.19 L 727.77 232.77 L 727.57 232.33 L 727.41 231.88 L 727.28 231.4 L 727.18 230.91 L 727.11 230.41 L 727.08 229.9 L 727.09 229.38 L 727.13 228.85 L 727.2 228.32 L 727.31 227.78 L 727.45 227.25 L 727.63 226.72 Z M 626.07 257.34 L 626.67 255.3 L 627.27 253.31 L 627.9 251.37 L 628.59 249.33 L 629.31 247.28 L 630.09 245.17 L 630.95 242.95 L 631.87 240.64 L 632.92 238.05 L 635.94 230.83 L 636.56 229.29 L 637.1 227.79 L 637.58 226.33 L 637.98 224.87 L 638.3 223.54 L 638.57 222.18 L 638.8 220.76 L 639 219.28 L 639.01 218.88 L 638.96 218.48 L 638.84 218.09 L 638.66 217.73 L 638.43 217.41 L 638.14 217.13 L 637.81 216.9 L 637.44 216.73 L 636.81 216.51 L 636.16 216.32 L 635.5 216.16 L 634.82 216.01 L 634.13 215.9 L 633.42 215.8 L 632.69 215.73 L 631.95 215.69 L 631.19 215.67 L 630.41 215.67 L 629.62 215.7 L 628.81 215.75 L 627.98 215.83 L 627.14 215.93 L 626.28 216.05 L 625.41 216.2 L 625.19 217.09 L 625.04 217.93 L 624.97 218.74 L 624.96 219.5 L 625.03 220.22 L 625.18 220.9 L 625.28 221.23 L 625.39 221.54 L 625.53 221.85 L 625.68 222.14 L 625.87 222.45 L 626.08 222.74 L 629.23 223.05 L 629.34 223.07 L 629.58 223.14 L 629.79 223.26 L 629.97 223.42 L 630.12 223.62 L 630.22 223.84 L 630.27 224.08 L 630.28 224.32 L 630.23 224.56 L 626.38 237.14 L 626.08 238.33 L 625.82 239.53 L 625.62 240.73 L 625.46 241.94 L 625.37 242.99 L 625.31 244.05 L 625.29 245.11 L 625.31 246.18 L 625.37 247.25 L 625.46 248.32 L 625.59 249.4 L 625.76 250.48 L 625.82 251.05 L 625.83 251.71 L 625.79 252.45 L 625.68 253.27 L 625.52 254.17 L 625.31 255.16 L 625.04 256.23 L 624.71 257.39 L 624.63 257.73 L 624.6 258.04 L 624.61 258.33 L 624.68 258.58 L 624.78 258.81 L 624.91 259.02 L 625.06 259.18 L 625.23 259.32 L 625.49 259.45 Z M 504.13 225.07 L 504.22 224.76 L 504.38 224.48 L 504.58 224.23 L 504.83 224.03 L 505.11 223.87 L 505.42 223.78 L 505.74 223.75 L 520.44 223.75 L 520.69 223.74 L 520.94 223.7 L 521.18 223.64 L 521.41 223.56 L 521.64 223.45 L 521.85 223.32 L 522.05 223.17 L 522.24 223 L 522.41 222.82 L 522.55 222.62 L 522.68 222.4 L 522.79 222.18 L 522.87 221.95 L 522.93 221.7 L 522.97 221.46 L 522.98 221.21 L 522.98 219.22 L 522.96 218.99 L 522.92 218.76 L 522.85 218.54 L 522.77 218.32 L 522.66 218.12 L 522.53 217.92 L 522.39 217.74 L 522.22 217.58 L 522.05 217.43 L 521.85 217.3 L 521.65 217.2 L 521.43 217.11 L 521.21 217.04 L 520.98 217 L 520.75 216.98 L 520.52 216.98 L 518.68 217.05 L 515.4 217.1 L 510.67 217.13 L 504.49 217.14 L 503.11 217.18 L 502.46 217.22 L 501.83 217.29 L 501.23 217.37 L 500.66 217.47 L 500.11 217.58 L 499.58 217.72 L 499.29 217.82 L 499.03 217.98 L 498.79 218.17 L 498.58 218.4 L 498.42 218.65 L 498.3 218.94 L 498.23 219.23 L 498.21 219.54 L 498.21 219.87 L 498.22 221.91 L 498.19 224.35 L 498.16 227.22 L 498.18 228.34 L 498.22 229.41 L 498.32 230.96 L 498.47 232.57 L 498.52 233.18 L 498.55 233.82 L 498.56 234.48 L 498.55 235.17 L 498.47 236.63 L 498.3 238.19 L 498.24 238.87 L 498.23 239.5 L 498.29 240.09 L 498.4 240.62 L 498.57 241.1 L 498.79 241.52 L 498.93 241.71 L 499.07 241.88 L 499.24 242.03 L 499.41 242.17 L 499.62 242.3 L 499.84 242.41 L 500.07 242.5 L 500.32 242.56 L 500.58 242.6 L 500.85 242.62 L 501.14 242.62 L 501.43 242.6 L 501.73 242.55 L 502.04 242.48 L 502.69 242.28 L 503.36 241.98 L 504.04 241.61 L 504.76 241.14 L 505.78 240.37 L 506.83 239.58 L 507.65 239.02 L 508.42 238.54 L 509.16 238.15 L 509.91 237.84 L 510.66 237.59 L 511.41 237.43 L 512.16 237.34 L 512.75 237.33 L 512.99 237.35 L 513.23 237.4 L 513.46 237.48 L 513.68 237.59 L 514.37 238.03 L 515.08 238.56 L 515.79 239.16 L 516.48 239.81 L 517.12 240.5 L 517.71 241.21 L 518.24 241.93 L 518.69 242.66 L 519.06 243.35 L 519.36 244.04 L 519.58 244.71 L 519.73 245.37 L 519.81 246.01 L 519.81 246.62 L 519.73 247.22 L 519.58 247.79 L 519.47 248.08 L 519.34 248.36 L 518.63 249.7 L 518.3 250.27 L 517.97 250.81 L 517.63 251.3 L 517.3 251.76 L 516.96 252.18 L 516.61 252.57 L 516.26 252.93 L 515.9 253.25 L 515.53 253.54 L 515.15 253.8 L 514.77 254.03 L 514.38 254.23 L 513.98 254.39 L 513.56 254.52 L 513.06 254.64 L 512.54 254.72 L 512.03 254.78 L 511.53 254.8 L 511.04 254.8 L 510.56 254.76 L 510.08 254.7 L 509.62 254.61 L 509.16 254.48 L 508.62 254.3 L 508.1 254.07 L 507.59 253.79 L 507.09 253.48 L 506.6 253.12 L 506.13 252.71 L 505.67 252.27 L 505.22 251.78 L 504.93 251.46 L 504.63 251.18 L 504.32 250.93 L 504.01 250.71 L 503.7 250.53 L 503.37 250.37 L 503.04 250.26 L 502.7 250.17 L 502.24 250.11 L 501.77 250.11 L 501.29 250.17 L 500.79 250.29 L 500.29 250.47 L 499.77 250.71 L 499.24 251 L 498.7 251.36 L 498.49 251.55 L 498.34 251.79 L 498.25 252.06 L 498.23 252.34 L 498.28 252.97 L 498.35 253.55 L 498.45 254.08 L 498.56 254.56 L 498.69 254.99 L 498.84 255.37 L 499.02 255.71 L 499.21 255.99 L 499.84 256.76 L 500.47 257.47 L 501.11 258.11 L 501.75 258.69 L 502.42 259.21 L 503.09 259.67 L 503.78 260.08 L 504.49 260.43 L 504.97 260.63 L 505.45 260.81 L 505.96 260.97 L 506.47 261.1 L 507.01 261.22 L 507.55 261.31 L 508.13 261.39 L 508.71 261.44 L 509.31 261.48 L 509.95 261.49 L 511.31 261.47 L 512.81 261.36 L 514.54 261.18 L 515.35 261.06 L 516.13 260.89 L 516.88 260.69 L 517.6 260.44 L 518.3 260.14 L 518.96 259.81 L 519.6 259.43 L 520.2 259.02 L 520.69 258.63 L 521.15 258.23 L 521.59 257.79 L 522 257.33 L 522.4 256.84 L 522.77 256.32 L 523.12 255.77 L 523.44 255.21 L 523.74 254.61 L 524.01 253.99 L 524.26 253.35 L 524.48 252.69 L 524.68 252 L 524.85 251.3 L 525 250.57 L 525.11 249.83 L 525.21 249.06 L 525.27 248.28 L 525.3 247.48 L 525.31 246.68 L 525.29 245.86 L 525.24 245.05 L 525.15 244.23 L 525.05 243.41 L 524.91 242.6 L 524.74 241.79 L 524.55 240.99 L 524.34 240.21 L 524.1 239.45 L 523.83 238.71 L 523.54 237.99 L 523.23 237.29 L 522.99 236.83 L 522.69 236.36 L 522.34 235.91 L 521.95 235.46 L 521.51 235.02 L 521.03 234.59 L 520.49 234.16 L 519.91 233.75 L 519.28 233.33 L 518.61 232.94 L 517.89 232.55 L 517.15 232.19 L 516.37 231.84 L 515.58 231.53 L 514.79 231.25 L 514.04 231.01 L 513.72 230.94 L 513.4 230.92 L 505.93 230.8 L 505.57 230.76 L 505.23 230.65 L 504.91 230.48 L 504.63 230.24 L 504.41 229.96 L 504.24 229.64 L 504.14 229.3 L 504.1 228.94 L 504.1 225.39 Z M 391.47 217.77 L 390.71 218.19 L 389.99 218.62 L 389.31 219.05 L 388.66 219.49 L 388.04 219.93 L 387.46 220.38 L 386.92 220.82 L 386.41 221.28 L 385.93 221.73 L 385.49 222.19 L 385.08 222.66 L 384.71 223.12 L 384.37 223.59 L 384.07 224.07 L 383.83 224.52 L 383.64 224.96 L 383.5 225.4 L 383.41 225.83 L 383.38 226.26 L 383.39 226.67 L 383.46 227.08 L 383.58 227.46 L 383.71 227.74 L 383.86 228 L 384.03 228.25 L 384.24 228.47 L 384.46 228.69 L 384.71 228.89 L 384.98 229.06 L 385.27 229.22 L 385.57 229.36 L 385.89 229.47 L 386.23 229.56 L 386.58 229.63 L 386.93 229.67 L 387.3 229.69 L 387.67 229.68 L 388.04 229.65 L 388.24 229.62 L 388.44 229.56 L 388.62 229.49 L 388.8 229.39 L 388.97 229.28 L 389.13 229.15 L 389.27 229 L 389.81 228.28 L 390.26 227.73 L 390.76 227.2 L 391.3 226.68 L 391.88 226.18 L 392.51 225.7 L 393.17 225.23 L 393.88 224.77 L 394.4 223.85 L 394.81 222.98 L 395.12 222.17 L 395.23 221.77 L 395.32 221.4 L 395.39 221.03 L 395.42 220.67 L 395.44 220.33 L 395.42 220 L 395.38 219.69 L 395.32 219.38 L 395.22 219.09 L 395.11 218.81 L 394.96 218.53 L 394.77 218.26 L 394.56 218 L 394.33 217.76 L 394.06 217.53 L 393.76 217.32 L 393.43 217.12 L 393.08 216.93 Z M 280.3 258.48 L 280.18 258.61 L 280.08 258.76 L 280.01 258.93 L 279.84 259.55 L 279.72 260.13 L 279.65 260.68 L 279.64 261.2 L 279.68 261.7 L 279.78 262.15 L 279.93 262.58 L 280.13 262.97 L 280.42 263.38 L 280.78 263.75 L 281.21 264.07 L 281.7 264.35 L 282.25 264.59 L 282.87 264.79 L 283.56 264.94 L 284.3 265.05 L 285.13 265.12 L 286.01 265.14 L 286.95 265.11 L 287.93 265.04 L 288.95 264.92 L 289.98 264.75 L 291 264.54 L 291.94 264.31 L 292.26 264.2 L 292.55 264.05 L 292.83 263.86 L 293.08 263.64 L 293.3 263.38 L 293.48 263.1 L 293.62 262.8 L 293.72 262.48 L 293.83 261.95 L 293.9 261.41 L 293.93 260.85 L 293.94 260.28 L 293.9 259.68 L 293.84 259.07 L 293.74 258.44 L 293.6 257.8 L 293.54 257.58 L 293.46 257.37 L 293.22 256.94 L 292.89 256.48 L 292.38 255.88 L 291.75 255.1 L 291.53 254.8 L 291.36 254.5 L 291.25 254.27 L 291.17 254.02 L 291.13 253.77 L 291.11 253.52 L 291.06 247.79 L 290.93 242.25 L 290.84 239.55 L 290.73 236.9 L 290.61 234.3 L 290.46 231.75 L 290.44 230.84 L 290.48 229.25 L 290.53 227.01 L 290.51 225.32 L 290.47 224.57 L 290.4 223.87 L 290.32 223.22 L 290.22 222.63 L 290.09 222.09 L 289.95 221.59 L 289.77 221.14 L 289.58 220.75 L 289.37 220.42 L 289.12 220.12 L 288.86 219.89 L 288.57 219.7 L 288.1 219.47 L 287.64 219.28 L 287.19 219.13 L 286.75 219.03 L 286.32 218.98 L 285.91 218.97 L 285.51 219.01 L 285.12 219.09 L 284.87 219.16 L 284.63 219.26 L 284.15 219.51 L 283.7 219.84 L 283.27 220.25 L 280.21 223.45 L 276.61 227.14 L 276.41 227.38 L 276.24 227.65 L 276.11 227.93 L 276.02 228.23 L 275.98 228.55 L 275.98 228.86 L 276.03 229.17 L 276.12 229.47 L 276.28 229.85 L 276.46 230.21 L 276.79 230.73 L 277.19 231.22 L 277.67 231.7 L 278.21 232.15 L 278.83 232.59 L 279.55 233.03 L 280.4 233.51 L 281.65 234.16 L 282.48 234.59 L 282.7 234.73 L 282.89 234.9 L 283.05 235.1 L 283.17 235.33 L 283.38 235.88 L 283.57 236.46 L 283.74 237.08 L 283.88 237.76 L 284 238.47 L 284.1 239.23 L 284.19 240.05 L 284.25 240.93 L 284.31 242.79 L 284.31 244.95 L 284.24 247.47 L 284.08 251.11 L 283.89 255.7 L 283.87 255.86 L 283.84 256.01 L 283.78 256.16 L 283.71 256.31 L 283.61 256.44 L 283.51 256.56 L 283.38 256.66 L 283.25 256.75 L 280.45 258.37 Z M 214.6 251.34 L 214.46 251.2 L 214.31 251.07 L 214.14 250.96 L 213.97 250.86 L 213.79 250.79 L 213.6 250.73 L 213.4 250.7 L 213.2 250.68 L 213 250.68 L 212.81 250.71 L 212.61 250.75 L 212.42 250.82 L 212.24 250.9 L 212.07 251.01 L 211.92 251.13 L 211.79 251.24 L 210.45 252.63 L 209.09 254.05 L 208.05 255.08 L 207.09 255.94 L 206.22 256.63 L 205.79 256.93 L 205.39 257.19 L 204.99 257.41 L 204.61 257.6 L 204.24 257.75 L 203.88 257.86 L 203.54 257.93 L 203.2 257.97 L 202.08 258 L 200.99 257.95 L 199.96 257.82 L 199.45 257.73 L 198.95 257.62 L 198.46 257.5 L 197.99 257.35 L 197.51 257.18 L 197.06 257 L 196.61 256.8 L 196.18 256.58 L 195.76 256.34 L 195.35 256.08 L 194.95 255.8 L 194.57 255.51 L 194.19 255.2 L 193.83 254.87 L 193.48 254.53 L 193.15 254.17 L 192.83 253.79 L 192.52 253.39 L 192.22 252.98 L 191.94 252.55 L 191.67 252.11 L 191.42 251.65 L 190.97 250.7 L 190.57 249.68 L 190.23 248.61 L 189.96 247.51 L 189.75 246.35 L 189.62 245.18 L 189.55 243.96 L 189.55 242.73 L 189.61 241.49 L 189.75 240.25 L 189.94 238.98 L 190.14 237.82 L 190.35 236.75 L 190.59 235.74 L 190.85 234.8 L 191.14 233.92 L 191.45 233.09 L 191.78 232.32 L 192.15 231.59 L 192.54 230.92 L 192.96 230.3 L 193.4 229.74 L 193.88 229.22 L 194.38 228.76 L 194.91 228.34 L 195.47 227.97 L 195.87 227.75 L 196.28 227.56 L 196.7 227.39 L 197.14 227.23 L 197.58 227.11 L 198.05 227 L 198.52 226.92 L 199.01 226.86 L 199.72 226.82 L 200.44 226.84 L 201.17 226.91 L 201.91 227.05 L 202.65 227.24 L 203.4 227.49 L 204.15 227.8 L 204.92 228.16 L 205.68 228.58 L 206.46 229.06 L 207.25 229.6 L 208.04 230.2 L 208.83 230.85 L 209.64 231.56 L 210.45 232.33 L 211.27 233.16 L 211.51 233.38 L 211.78 233.55 L 212.01 233.66 L 212.25 233.74 L 212.5 233.8 L 212.75 233.82 L 213 233.81 L 213.25 233.78 L 213.49 233.72 L 213.72 233.62 L 213.95 233.5 L 214.15 233.36 L 214.34 233.19 L 214.51 233.01 L 214.65 232.8 L 214.77 232.58 L 214.87 232.34 L 214.93 232.1 L 215.33 230.1 L 215.39 229.72 L 215.43 229.33 L 215.44 228.94 L 215.43 228.55 L 215.39 228.17 L 215.32 227.78 L 215.23 227.41 L 215.11 227.04 L 214.97 226.67 L 214.8 226.32 L 214.61 225.99 L 214.4 225.66 L 214.16 225.35 L 213.91 225.06 L 213.63 224.79 L 213.34 224.53 L 212.21 223.66 L 211.04 222.85 L 209.85 222.11 L 208.64 221.44 L 207.41 220.86 L 206.18 220.35 L 205.56 220.13 L 204.94 219.93 L 204.33 219.75 L 203.71 219.6 L 203.1 219.46 L 202.5 219.35 L 201.9 219.26 L 201.3 219.19 L 200.7 219.15 L 200.11 219.12 L 199.52 219.12 L 198.94 219.14 L 198.37 219.18 L 197.8 219.24 L 197.24 219.32 L 196.69 219.43 L 196.14 219.56 L 195.6 219.71 L 195.07 219.88 L 194.55 220.07 L 193.84 220.37 L 193.14 220.72 L 192.47 221.1 L 191.8 221.53 L 191.16 221.99 L 190.54 222.5 L 189.94 223.04 L 189.35 223.63 L 188.53 224.53 L 187.77 225.44 L 187.07 226.35 L 186.42 227.28 L 185.84 228.22 L 185.3 229.17 L 184.82 230.15 L 184.39 231.14 L 184.14 231.81 L 183.91 232.48 L 183.7 233.16 L 183.51 233.85 L 183.35 234.55 L 183.21 235.27 L 183.08 236 L 182.98 236.74 L 182.9 237.5 L 182.84 238.27 L 182.8 239.06 L 182.78 239.86 L 182.78 240.69 L 182.8 241.53 L 182.91 243.28 L 183.04 244.76 L 183.19 246.1 L 183.35 247.35 L 183.54 248.54 L 183.74 249.65 L 183.97 250.72 L 184.22 251.72 L 184.5 252.69 L 184.82 253.62 L 185.16 254.52 L 185.52 255.37 L 185.92 256.19 L 186.36 256.98 L 186.82 257.73 L 187.32 258.46 L 187.85 259.16 L 188.24 259.63 L 188.65 260.08 L 189.07 260.52 L 189.51 260.95 L 189.97 261.37 L 190.45 261.77 L 190.94 262.16 L 191.45 262.54 L 192.42 263.19 L 193.4 263.76 L 194.42 264.27 L 195.45 264.7 L 196.51 265.06 L 197.59 265.35 L 198.68 265.56 L 199.77 265.69 L 200.67 265.75 L 201.56 265.75 L 202.45 265.71 L 203.33 265.62 L 204.22 265.48 L 205.1 265.29 L 205.97 265.05 L 206.83 264.77 L 207.68 264.44 L 208.52 264.06 L 209.34 263.64 L 210.15 263.17 L 210.93 262.66 L 211.7 262.11 L 212.44 261.51 L 213.16 260.88 L 213.58 260.46 L 213.97 260.02 L 214.33 259.55 L 214.65 259.07 L 214.94 258.56 L 215.19 258.04 L 215.4 257.51 L 215.57 256.97 L 215.7 256.43 L 215.79 255.88 L 215.83 255.34 L 215.83 254.8 L 215.79 254.28 L 215.7 253.76 L 215.58 253.26 L 215.41 252.77 L 215.26 252.44 L 215.1 252.11 L 214.92 251.8 L 214.72 251.5 Z M 406.411 221.609 L 406.231 222.139 L 406.081 222.679 L 405.961 223.239 L 405.861 223.799 L 405.801 224.369 L 405.761 224.959 L 405.761 225.549 L 405.781 226.159 L 406.081 231.429 L 405.981 235.639 L 405.951 239.329 L 405.991 242.479 L 406.031 243.859 L 406.091 245.109 L 406.131 246.239 L 406.131 247.519 L 406.101 248.979 L 406.021 251.019 L 405.911 253.909 L 405.891 256.039 L 405.911 257.029 L 405.951 257.979 L 406.011 258.869 L 406.091 259.739 L 406.101 259.979 L 406.081 260.219 L 406.041 260.469 L 405.971 260.699 L 405.711 261.459 L 405.501 262.209 L 405.341 262.949 L 405.264 263.464 C 405.456 263.447 405.66 263.435 405.875 263.426 C 405.973 263.232 406.087 263.047 406.215 262.872 L 406.25 262.69 L 406.52 261.66 L 406.66 261.19 L 406.82 260.76 L 406.99 260.35 L 407.17 259.98 L 407.37 259.64 L 407.57 259.33 L 408.07 258.6 L 408.52 257.87 L 408.94 257.12 L 409.32 256.37 L 409.66 255.6 L 409.96 254.82 L 410.23 254.04 L 410.45 253.24 L 410.67 252.28 L 410.83 251.3 L 410.94 250.31 L 410.99 249.3 L 410.99 248.28 L 410.93 247.25 L 410.82 246.2 L 410.65 245.14 L 410.55 244.69 L 410.4 244.26 L 410.22 243.84 L 410 243.41 L 409.69 242.87 L 409.39 242.37 L 409.22 242.01 L 408.95 241.27 L 408.76 240.53 L 408.65 239.79 L 408.62 239.05 L 408.63 238.6 L 408.68 238.14 L 408.75 237.69 L 408.86 237.24 L 409.11 236.27 L 409.31 235.39 L 409.46 234.59 L 409.59 233.83 L 409.67 233.11 L 409.73 232.42 L 409.74 231.75 L 409.73 231.08 L 409.69 230.49 L 409.62 229.89 L 409.53 229.29 L 409.41 228.67 L 409.26 228.04 L 409.08 227.38 L 408.86 226.69 L 408.62 225.98 L 407.71 223.55 L 406.68 220.96 Z M 686.46 247.88 L 686.36 247.71 L 686.28 247.53 L 686.23 247.34 L 686.21 247.15 L 686.21 246.96 L 686.24 246.77 L 686.29 246.58 L 686.51 245.9 L 686.68 245.17 L 686.79 244.4 L 686.84 243.6 L 686.84 242.8 L 686.78 242.01 L 686.66 241.22 L 686.49 240.47 L 686.27 239.77 L 686 239.11 L 685.68 238.48 L 685.32 237.92 L 684.92 237.4 L 684.48 236.94 L 684 236.53 L 683.48 236.19 L 682.89 235.89 L 681.67 235.4 L 681.1 235.2 L 680.55 235.02 L 680.01 234.88 L 679.5 234.76 L 678.99 234.68 L 678.5 234.62 L 678.02 234.58 L 677.57 234.58 L 677.12 234.6 L 676.68 234.65 L 676.26 234.73 L 675.85 234.83 L 675.46 234.97 L 675.07 235.13 L 674.63 235.36 L 674.21 235.63 L 673.8 235.93 L 673.42 236.24 L 673.07 236.57 L 672.76 236.89 L 672.47 237.24 L 672.21 237.59 L 671.98 237.95 L 671.79 238.33 L 671.65 238.65 L 671.53 238.98 L 671.42 239.32 L 671.34 239.67 L 671.27 240.04 L 671.23 240.42 L 671.18 241.22 L 671.21 242.09 L 671.31 243.04 L 671.48 244.13 L 671.74 245.5 L 671.84 246.07 L 671.89 246.65 L 671.91 247.23 L 671.88 247.81 L 671.79 248.79 L 671.63 250.12 L 671.45 251.7 L 671.35 252.84 L 671.33 253.84 L 671.37 254.71 L 671.43 255.14 L 671.5 255.53 L 671.58 255.91 L 671.7 256.28 L 671.82 256.62 L 671.98 256.95 L 672.15 257.26 L 672.34 257.56 L 672.63 257.93 L 672.97 258.28 L 673.35 258.6 L 673.77 258.91 L 674.22 259.18 L 674.73 259.43 L 675.27 259.65 L 675.87 259.85 L 677.51 260.31 L 678.24 260.48 L 678.93 260.63 L 679.58 260.73 L 680.21 260.81 L 680.81 260.86 L 681.39 260.87 L 681.95 260.86 L 682.48 260.81 L 683 260.73 L 683.49 260.62 L 683.97 260.47 L 684.41 260.3 L 684.83 260.09 L 685.25 259.85 L 685.73 259.5 L 686.17 259.11 L 686.58 258.66 L 686.96 258.17 L 687.45 257.41 L 687.86 256.63 L 688.19 255.85 L 688.43 255.07 L 688.53 254.67 L 688.6 254.28 L 688.65 253.89 L 688.68 253.5 L 688.69 253.11 L 688.68 252.73 L 688.64 252.34 L 688.59 251.96 L 688.48 251.44 L 688.33 250.91 L 688.13 250.41 L 687.9 249.91 L 687.63 249.42 L 687.32 248.94 L 686.97 248.48 L 686.58 248.03 Z M 677.873 243.008 L 677.993 242.845 L 678.181 242.7 L 678.428 242.578 L 678.726 242.484 L 679.063 242.422 L 679.425 242.395 L 679.8 242.402 L 680.173 242.444 L 680.529 242.52 L 680.855 242.626 L 681.137 242.758 L 681.366 242.911 L 681.533 243.08 L 681.631 243.257 L 681.656 243.437 L 681.607 243.612 L 681.487 243.775 L 681.299 243.92 L 681.052 244.042 L 680.754 244.136 L 680.417 244.198 L 680.055 244.225 L 679.68 244.218 L 679.307 244.176 L 678.951 244.1 L 678.626 243.994 L 678.343 243.862 L 678.114 243.709 L 677.947 243.54 L 677.849 243.363 L 677.824 243.183 Z M 683.305 253.617 L 683.278 253.864 L 683.226 254.107 L 683.15 254.344 L 683.052 254.572 L 682.932 254.789 L 682.791 254.993 L 682.631 255.183 L 682.453 255.356 L 682.259 255.511 L 682.051 255.646 L 681.83 255.76 L 681.6 255.852 L 681.361 255.921 L 681.117 255.966 L 680.87 255.987 L 680.621 255.983 L 678.426 255.845 L 678.179 255.818 L 677.936 255.766 L 677.7 255.69 L 677.471 255.592 L 677.254 255.472 L 677.05 255.332 L 676.86 255.172 L 676.687 254.994 L 676.532 254.799 L 676.397 254.591 L 676.283 254.37 L 676.191 254.14 L 676.122 253.901 L 676.077 253.657 L 676.056 253.41 L 676.06 253.161 L 676.095 252.603 L 676.122 252.356 L 676.174 252.113 L 676.25 251.876 L 676.348 251.648 L 676.468 251.431 L 676.609 251.227 L 676.769 251.037 L 676.947 250.864 L 677.141 250.709 L 677.349 250.574 L 677.57 250.46 L 677.8 250.368 L 678.039 250.299 L 678.283 250.254 L 678.53 250.233 L 678.779 250.237 L 680.974 250.375 L 681.221 250.402 L 681.464 250.454 L 681.7 250.53 L 681.929 250.628 L 682.146 250.748 L 682.35 250.888 L 682.54 251.048 L 682.713 251.226 L 682.868 251.421 L 683.003 251.629 L 683.117 251.85 L 683.209 252.08 L 683.278 252.319 L 683.323 252.563 L 683.344 252.81 L 683.34 253.059 Z M 454.64 250.28 L 454.74 249.91 L 454.87 249.51 L 455.04 249.08 L 455.52 248.08 L 456.31 246.63 L 457.16 245.05 L 457.74 243.87 L 457.97 243.32 L 458.18 242.8 L 458.35 242.3 L 458.49 241.81 L 458.6 241.33 L 458.68 240.87 L 458.72 240.42 L 458.74 239.99 L 458.72 239.57 L 458.67 239.16 L 458.58 238.76 L 458.46 238.37 L 458.34 238.06 L 458.19 237.75 L 457.98 237.41 L 457.71 237.11 L 457.42 236.86 L 457.1 236.67 L 456.77 236.56 L 456.45 236.52 L 456.14 236.56 L 456 236.61 L 455.86 236.67 L 455.79 236.72 L 455.5 236.93 L 455.23 237.17 L 454.79 237.62 L 454.41 238.13 L 454.07 238.7 L 453.79 239.32 L 452.39 242.79 L 450.97 246.11 L 449.55 249.3 L 448.11 252.35 L 447.82 253 L 447.59 253.65 L 447.41 254.31 L 447.29 254.98 L 447.22 255.65 L 447.2 256.33 L 447.22 256.56 L 447.28 256.79 L 447.38 257 L 447.51 257.19 L 447.67 257.36 L 447.86 257.49 L 448.07 257.59 L 448.29 257.66 L 453.09 258.64 L 453.24 258.68 L 453.38 258.74 L 453.52 258.82 L 453.64 258.91 L 454.48 259.64 L 455.42 260.38 L 456.47 261.11 L 457.63 261.84 L 458.06 262.06 L 458.51 262.22 L 458.97 262.32 L 459.44 262.35 L 459.89 262.31 L 460.32 262.2 L 460.73 262.02 L 461.09 261.79 L 461.44 261.48 L 461.77 261.12 L 462.04 260.71 L 462.29 260.25 L 462.5 259.74 L 462.67 259.18 L 462.8 258.58 L 462.89 257.92 L 462.95 257.21 L 462.96 256.47 L 462.93 255.68 L 462.86 254.88 L 462.75 254.04 L 462.6 253.22 L 462.41 252.4 L 462.2 251.64 L 462.07 251.32 L 461.88 251.03 L 461.64 250.78 L 461.36 250.58 L 461.05 250.43 L 460.72 250.34 L 460.38 250.31 L 460.04 250.34 L 455.26 251.23 L 455.18 251.24 L 455.06 251.23 L 454.94 251.2 L 454.83 251.14 L 454.74 251.06 L 454.67 250.96 L 454.62 250.85 L 454.59 250.73 L 454.59 250.61 Z M 570.59 243.02 L 570.7 242.88 L 570.82 242.75 L 570.96 242.64 L 571.12 242.55 L 574.18 241.04 L 574.52 240.83 L 574.82 240.57 L 575.07 240.26 L 575.26 239.91 L 575.4 239.53 L 575.46 239.14 L 575.46 238.74 L 575.39 238.35 L 575.31 238.1 L 575.21 237.88 L 575.08 237.66 L 574.94 237.47 L 574.73 237.26 L 574.49 237.09 L 574.22 236.94 L 573.93 236.82 L 573.6 236.73 L 573.23 236.66 L 572.85 236.63 L 572.43 236.63 L 571.98 236.65 L 571.51 236.71 L 571.01 236.81 L 570.51 236.93 L 570.01 237.08 L 569.52 237.26 L 569.07 237.45 L 568.66 237.65 L 568.12 237.96 L 567.59 238.29 L 567.09 238.63 L 566.6 238.99 L 566.14 239.37 L 565.69 239.77 L 565.26 240.18 L 564.85 240.61 L 564.38 241.15 L 563.94 241.71 L 563.53 242.29 L 563.15 242.9 L 562.79 243.53 L 562.46 244.19 L 562.16 244.87 L 561.88 245.58 L 561.42 246.91 L 561.04 248.14 L 560.75 249.3 L 560.53 250.4 L 560.39 251.46 L 560.32 252.49 L 560.33 253.5 L 560.41 254.48 L 560.5 255.14 L 560.63 255.79 L 560.8 256.44 L 560.99 257.1 L 561.23 257.76 L 561.5 258.43 L 561.8 259.09 L 562.14 259.77 L 562.34 260.08 L 562.59 260.36 L 562.88 260.59 L 563.2 260.77 L 563.91 261.06 L 564.63 261.31 L 565.38 261.51 L 566.15 261.66 L 566.94 261.77 L 567.76 261.83 L 568.59 261.84 L 569.45 261.81 L 570.07 261.76 L 570.66 261.67 L 571.22 261.55 L 571.75 261.41 L 572.26 261.23 L 572.75 261.02 L 573.21 260.77 L 573.64 260.49 L 574.18 260.07 L 574.69 259.58 L 575.16 259.03 L 575.59 258.41 L 575.99 257.72 L 576.36 256.96 L 576.71 256.11 L 577.04 255.17 L 577.16 254.74 L 577.26 254.32 L 577.32 253.89 L 577.35 253.46 L 577.35 252.92 L 577.31 252.38 L 577.22 251.83 L 577.08 251.29 L 576.9 250.74 L 576.67 250.19 L 576.39 249.63 L 576.07 249.08 L 575.86 248.77 L 575.5 248.31 L 575.08 247.88 L 574.6 247.47 L 574.07 247.09 L 573.47 246.72 L 572.8 246.36 L 572 245.98 L 570.87 245.47 L 570.4 245.26 L 570.34 245.23 L 570.18 245.12 L 570.05 244.98 L 569.95 244.82 L 569.89 244.65 L 569.86 244.46 L 569.87 244.27 L 569.92 244.09 L 570.01 243.92 Z M 571.334 253.325 L 571.348 253.495 L 571.338 253.669 L 571.306 253.844 L 571.252 254.019 L 571.176 254.192 L 570.964 254.525 L 570.676 254.831 L 570.324 255.098 L 569.921 255.314 L 569.484 255.474 L 569.028 255.569 L 568.572 255.596 L 568.132 255.556 L 567.727 255.447 L 567.37 255.276 L 567.215 255.169 L 567.077 255.049 L 566.957 254.917 L 566.857 254.774 L 566.779 254.622 L 566.721 254.462 L 566.686 254.295 L 566.672 254.125 L 566.682 253.951 L 566.714 253.776 L 566.768 253.601 L 566.843 253.428 L 567.056 253.095 L 567.344 252.789 L 567.696 252.522 L 568.099 252.306 L 568.536 252.146 L 568.992 252.051 L 569.448 252.024 L 569.888 252.064 L 570.293 252.173 L 570.65 252.344 L 570.805 252.451 L 570.943 252.571 L 571.063 252.703 L 571.163 252.846 L 571.241 252.998 L 571.299 253.158 Z M 125.28 246.46 L 125.21 246.6 L 125.12 246.72 L 125.02 246.83 L 124.89 246.92 L 124.76 246.99 L 124.61 247.03 L 124.46 247.05 L 123.57 247.1 L 122.7 247.19 L 121.85 247.3 L 121.02 247.45 L 120.21 247.64 L 119.42 247.86 L 118.64 248.11 L 117.89 248.39 L 117.59 248.53 L 117.29 248.7 L 117 248.9 L 116.72 249.13 L 116.45 249.38 L 116.2 249.65 L 115.98 249.93 L 115.78 250.22 L 115.62 250.52 L 115.48 250.81 L 115.38 251.11 L 115.32 251.41 L 115.28 251.69 L 115.28 251.97 L 115.31 252.24 L 115.38 252.5 L 115.49 252.77 L 115.64 253.04 L 115.84 253.29 L 116.07 253.52 L 116.41 253.8 L 116.75 254.07 L 117.13 254.33 L 117.51 254.57 L 118.36 255.02 L 119.29 255.43 L 120.3 255.8 L 121.44 256.14 L 122.72 256.46 L 124.29 256.8 L 124.49 256.86 L 124.67 256.95 L 124.83 257.07 L 124.98 257.21 L 125.1 257.38 L 125.19 257.56 L 125.24 257.76 L 125.27 257.96 L 125.28 258.34 L 125.34 259.74 L 125.43 260.76 L 125.56 261.65 L 125.75 262.41 L 125.86 262.77 L 125.99 263.1 L 126.13 263.4 L 126.3 263.68 L 126.47 263.94 L 126.67 264.17 L 126.88 264.38 L 127.11 264.57 L 127.43 264.78 L 127.79 264.95 L 128.17 265.08 L 128.59 265.18 L 129.03 265.24 L 129.51 265.25 L 130.02 265.23 L 130.57 265.17 L 131 265.08 L 131.41 264.92 L 131.8 264.7 L 132.14 264.42 L 132.44 264.09 L 132.68 263.73 L 132.87 263.32 L 132.99 262.9 L 134.14 257.19 L 134.21 256.93 L 134.32 256.69 L 134.46 256.47 L 134.64 256.27 L 134.84 256.09 L 135.07 255.95 L 135.31 255.85 L 135.57 255.78 L 141.15 254.73 L 141.51 254.63 L 141.84 254.48 L 142.15 254.28 L 142.42 254.04 L 142.65 253.75 L 142.84 253.43 L 142.97 253.08 L 143.04 252.72 L 143.09 252.09 L 143.1 251.47 L 143.07 250.85 L 142.99 250.24 L 142.83 249.51 L 142.6 248.78 L 142.52 248.59 L 142.4 248.41 L 142.27 248.25 L 142.11 248.11 L 141.94 248 L 141.75 247.91 L 141.55 247.85 L 141.34 247.82 L 134.57 247.3 L 134.33 247.26 L 134.1 247.18 L 133.88 247.07 L 133.69 246.92 L 133.52 246.75 L 133.39 246.55 L 133.28 246.32 L 133.22 246.09 L 131.95 239.32 L 131.88 239.08 L 131.75 238.87 L 131.58 238.69 L 131.38 238.55 L 131.05 238.4 L 130.67 238.29 L 130.26 238.21 L 129.81 238.16 L 129.32 238.15 L 128.8 238.18 L 128.23 238.24 L 127.63 238.34 L 127.47 238.38 L 127.33 238.44 L 127.19 238.53 L 127.07 238.63 L 126.96 238.75 L 126.88 238.88 L 126.81 239.03 L 126.77 239.18 L 125.32 246.31 Z M 347.77 256.9 L 348.11 256.4 L 348.56 255.83 L 349.25 255.05 L 350.01 254.2 L 350.5 253.57 L 350.7 253.28 L 350.86 253.01 L 350.99 252.74 L 351.09 252.49 L 351.32 251.69 L 351.52 250.78 L 351.65 249.81 L 351.74 248.81 L 351.76 247.82 L 351.73 246.85 L 351.63 245.91 L 351.49 245.02 L 351.29 244.22 L 351.05 243.48 L 350.76 242.8 L 350.43 242.19 L 350.05 241.65 L 349.63 241.19 L 349.4 240.98 L 349.16 240.79 L 348.92 240.62 L 348.66 240.47 L 348.39 240.34 L 348.11 240.22 L 347.31 239.96 L 346.48 239.76 L 345.64 239.64 L 344.81 239.58 L 343.97 239.6 L 343.15 239.69 L 342.33 239.85 L 341.54 240.08 L 340.78 240.38 L 340.05 240.75 L 339.37 241.17 L 338.74 241.66 L 338.18 242.19 L 337.66 242.77 L 337.22 243.4 L 337.02 243.73 L 336.84 244.07 L 336.56 244.71 L 336.33 245.38 L 336.17 246.07 L 336.07 246.78 L 336.04 247.51 L 336.06 248.25 L 336.15 249.01 L 336.3 249.78 L 336.39 250.04 L 336.53 250.28 L 336.64 250.42 L 336.76 250.54 L 336.9 250.64 L 337.05 250.73 L 337.21 250.8 L 337.38 250.84 L 337.55 250.87 L 337.72 250.88 L 337.9 250.86 L 338.07 250.82 L 338.23 250.76 L 338.38 250.69 L 338.53 250.59 L 338.66 250.48 L 338.78 250.35 L 338.88 250.21 L 339.16 249.79 L 339.47 249.4 L 339.9 248.93 L 340.38 248.49 L 340.9 248.1 L 341.46 247.74 L 342.06 247.42 L 342.71 247.13 L 343.4 246.88 L 344.14 246.67 L 344.47 246.63 L 344.8 246.66 L 345.12 246.76 L 345.4 246.94 L 345.64 247.17 L 345.81 247.45 L 345.92 247.77 L 345.95 248.1 L 345.94 248.28 L 345.9 248.47 L 345.67 249.19 L 345.39 249.91 L 345.06 250.63 L 344.68 251.35 L 344.25 252.05 L 343.76 252.75 L 343.23 253.44 L 342.67 254.11 L 342.05 254.76 L 341.4 255.39 L 340.71 255.99 L 340 256.56 L 339.26 257.1 L 338.5 257.59 L 337.74 258.04 L 336.96 258.45 L 336.81 258.54 L 336.67 258.64 L 336.55 258.77 L 336.44 258.9 L 336.35 259.05 L 336.29 259.21 L 336.24 259.38 L 336.22 259.55 L 335.89 263.83 L 335.89 263.98 L 335.92 264.12 L 335.98 264.25 L 336.06 264.37 L 336.17 264.47 L 336.29 264.55 L 336.43 264.6 L 336.57 264.63 L 349.1 265.59 L 349.48 265.59 L 349.86 265.53 L 350.23 265.4 L 350.57 265.22 L 350.87 264.99 L 351.14 264.71 L 351.36 264.4 L 351.52 264.05 L 351.62 263.67 L 351.68 263.23 L 351.7 262.73 L 351.69 262.18 L 351.63 261.57 L 351.53 260.91 L 351.4 260.19 L 351.22 259.41 L 351.17 259.27 L 351.1 259.13 L 351.01 259.01 L 350.9 258.9 L 350.78 258.82 L 350.64 258.75 L 350.5 258.7 L 350.35 258.68 L 348.27 258.52 L 348.15 258.5 L 347.97 258.44 L 347.82 258.35 L 347.69 258.22 L 347.59 258.07 L 347.52 257.9 L 347.49 257.72 L 347.5 257.54 L 347.55 257.36 Z M 231.94 240.22 L 231.5 240.24 L 231.08 240.29 L 230.67 240.37 L 230.28 240.46 L 229.9 240.59 L 229.53 240.74 L 229.18 240.91 L 228.75 241.17 L 228.34 241.46 L 227.96 241.8 L 227.59 242.17 L 227.25 242.59 L 226.93 243.04 L 226.63 243.54 L 226.35 244.07 L 226.1 244.65 L 225.86 245.26 L 225.65 245.92 L 225.46 246.61 L 225.29 247.35 L 225.14 248.12 L 225.01 248.94 L 224.9 249.79 L 224.77 251.18 L 224.68 252.5 L 224.64 253.75 L 224.64 254.94 L 224.68 256.06 L 224.77 257.12 L 224.9 258.11 L 225.07 259.04 L 225.29 259.9 L 225.56 260.69 L 225.87 261.43 L 226.22 262.09 L 226.61 262.69 L 227.05 263.22 L 227.54 263.69 L 228.07 264.1 L 228.44 264.33 L 228.83 264.53 L 229.25 264.71 L 229.68 264.85 L 230.13 264.97 L 230.6 265.06 L 231.09 265.12 L 231.6 265.15 L 232.11 265.15 L 232.61 265.12 L 233.08 265.06 L 233.54 264.97 L 233.98 264.86 L 234.4 264.71 L 234.81 264.53 L 235.2 264.32 L 235.75 263.95 L 236.26 263.51 L 236.74 263.01 L 237.17 262.44 L 237.56 261.79 L 237.92 261.08 L 238.23 260.31 L 238.5 259.46 L 238.74 258.55 L 238.93 257.56 L 239.08 256.51 L 239.2 255.4 L 239.27 254.21 L 239.31 252.96 L 239.3 251.64 L 239.26 250.25 L 239.2 249.39 L 239.13 248.56 L 239.03 247.78 L 238.9 247.04 L 238.76 246.33 L 238.58 245.66 L 238.39 245.04 L 238.17 244.45 L 237.92 243.89 L 237.66 243.38 L 237.37 242.91 L 237.05 242.47 L 236.71 242.07 L 236.35 241.71 L 235.96 241.39 L 235.55 241.11 L 235.21 240.92 L 234.85 240.74 L 234.48 240.6 L 234.09 240.47 L 233.69 240.37 L 233.27 240.3 L 232.84 240.25 L 232.39 240.22 Z M 232.426 246.802 L 232.676 246.89 L 232.921 247.033 L 233.159 247.233 L 233.386 247.483 L 233.601 247.785 L 233.801 248.133 L 233.985 248.526 L 234.151 248.959 L 234.298 249.429 L 234.423 249.929 L 234.526 250.455 L 234.606 251.005 L 234.662 251.57 L 234.694 252.146 L 234.7 252.728 L 234.681 253.308 L 234.638 253.883 L 234.57 254.447 L 234.479 254.994 L 234.365 255.519 L 234.229 256.017 L 234.073 256.482 L 233.897 256.912 L 233.705 257.301 L 233.497 257.645 L 233.276 257.942 L 233.044 258.189 L 232.802 258.382 L 232.554 258.521 L 232.302 258.604 L 232.048 258.629 L 231.794 258.599 L 231.544 258.51 L 231.299 258.366 L 231.061 258.168 L 230.834 257.916 L 230.619 257.616 L 230.419 257.266 L 230.235 256.874 L 230.069 256.441 L 229.922 255.971 L 229.797 255.471 L 229.694 254.945 L 229.614 254.395 L 229.558 253.83 L 229.526 253.254 L 229.52 252.672 L 229.539 252.092 L 229.582 251.517 L 229.65 250.953 L 229.741 250.406 L 229.855 249.881 L 229.991 249.383 L 230.147 248.918 L 230.323 248.488 L 230.515 248.099 L 230.723 247.755 L 230.944 247.458 L 231.176 247.211 L 231.418 247.018 L 231.666 246.879 L 231.918 246.796 L 232.172 246.77 Z M 392.04 256.96 L 391.5 256.67 L 391 256.36 L 390.55 256.03 L 390.14 255.67 L 389.77 255.28 L 389.44 254.87 L 389.16 254.43 L 388.97 254.09 L 388.81 253.73 L 388.67 253.35 L 388.56 252.96 L 388.44 252.61 L 388.26 252.27 L 388.03 251.97 L 387.76 251.71 L 387.44 251.49 L 387.09 251.32 L 386.72 251.21 L 386.33 251.15 L 385.94 251.14 L 385.57 251.15 L 385.23 251.2 L 384.91 251.27 L 384.61 251.37 L 384.34 251.5 L 384.09 251.66 L 383.87 251.85 L 383.66 252.08 L 383.49 252.34 L 383.35 252.63 L 383.24 252.95 L 383.16 253.3 L 383.12 253.66 L 383.11 254.05 L 383.13 254.45 L 383.2 254.88 L 383.29 255.31 L 383.43 255.74 L 383.59 256.16 L 383.78 256.56 L 384 256.94 L 384.23 257.28 L 384.48 257.58 L 385 258.13 L 385.53 258.67 L 386.08 259.18 L 386.65 259.68 L 387.37 260.28 L 388.12 260.86 L 388.89 261.4 L 389.69 261.92 L 390.51 262.41 L 391.35 262.87 L 392.21 263.31 L 393.1 263.72 L 393.26 263.74 L 393.42 263.74 L 393.56 263.71 L 393.7 263.67 L 393.85 263.6 L 393.98 263.5 L 394.1 263.38 L 394.22 263.23 L 394.32 263.05 L 394.41 262.85 L 394.57 262.37 L 394.78 261.43 L 394.93 260.59 L 395 259.83 L 395 259.18 L 394.97 258.88 L 394.93 258.61 L 394.87 258.36 L 394.79 258.14 L 394.69 257.93 L 394.58 257.76 L 394.45 257.6 L 394.3 257.47 L 394.14 257.37 L 393.97 257.28 L 393.78 257.22 L 393.58 257.18 L 393.37 257.16 L 393.13 257.16 L 392.62 257.22 Z M 788.93 255.72 L 788.84 256.13 L 788.71 256.53 L 788.56 256.91 L 788.41 257.21 L 788.24 257.5 L 788.06 257.78 L 787.85 258.05 L 787.69 258.2 L 787.5 258.32 L 787.29 258.38 L 787.07 258.4 L 785.3 258.3 L 783.5 258.18 L 781.65 258.03 L 779.77 257.85 L 777.84 257.64 L 775.88 257.4 L 773.87 257.14 L 771.83 256.85 L 769.74 256.53 L 767.61 256.18 L 765.45 255.81 L 763.24 255.4 L 758.7 254.51 L 754.01 253.51 L 753.75 253.46 L 753.5 253.44 L 753.24 253.44 L 752.98 253.46 L 752.72 253.5 L 752.47 253.57 L 752.22 253.65 L 751.98 253.76 L 751.76 253.88 L 751.54 254.02 L 751.34 254.18 L 751.15 254.36 L 750.97 254.55 L 750.82 254.76 L 750.68 254.98 L 750.56 255.21 L 750.46 255.46 L 750.39 255.68 L 750.34 255.91 L 750.31 256.15 L 750.31 256.38 L 750.33 256.62 L 750.38 256.85 L 750.45 257.07 L 750.54 257.29 L 750.66 257.5 L 750.79 257.69 L 750.95 257.87 L 751.12 258.03 L 751.3 258.17 L 751.51 258.3 L 751.72 258.4 L 751.94 258.48 L 753.82 259.02 L 755.81 259.52 L 757.89 259.99 L 760.1 260.43 L 762.4 260.84 L 764.92 261.24 L 767.71 261.64 L 771.52 262.16 L 776.9 262.89 L 778.91 263.2 L 780.74 263.5 L 781.28 263.58 L 781.82 263.64 L 782.36 263.68 L 782.9 263.69 L 785 263.72 L 787.21 263.81 L 787.52 263.85 L 787.79 263.92 L 788.01 264.03 L 788.18 264.18 L 788.47 264.48 L 788.72 264.67 L 788.84 264.73 L 788.95 264.77 L 789.05 264.78 L 789.14 264.76 L 789.28 264.68 L 789.39 264.54 L 789.49 264.33 L 789.56 264.06 L 789.57 263.94 L 789.24 256.08 L 789.22 255.85 L 789.18 255.64 L 789.1 255.46 L 789 255.29 Z M 272.64 264.08 L 272.93 263.89 L 273.2 263.65 L 273.43 263.37 L 273.61 263.07 L 273.74 262.73 L 273.82 262.39 L 273.85 262.03 L 273.85 259.98 L 273.82 259.7 L 273.73 259.4 L 273.57 259.13 L 273.36 258.9 L 273.1 258.73 L 272.8 258.61 L 272.49 258.56 L 272.18 258.58 L 271.88 258.67 L 266.74 260.8 L 266.56 260.85 L 264.05 261.35 L 261.67 261.86 L 259.42 262.39 L 257.3 262.93 L 255.15 263.48 L 253.06 263.95 L 251.41 264.28 L 249.67 264.59 L 247.78 264.89 L 245.13 265.28 L 240.71 265.94 L 240.42 266 L 240.13 266.08 L 239.85 266.18 L 239.58 266.3 L 239.32 266.44 L 239.06 266.6 L 238.83 266.78 L 238.6 266.98 L 238.39 267.19 L 238.2 267.42 L 238.02 267.66 L 237.87 267.91 L 237.73 268.18 L 237.61 268.45 L 237.52 268.73 L 237.44 269.02 L 237.42 269.11 L 237.37 269.47 L 237.38 269.68 L 237.4 269.9 L 237.45 270.11 L 237.53 270.31 L 237.62 270.51 L 237.74 270.69 L 237.88 270.86 L 238.03 271.01 L 238.2 271.15 L 238.38 271.26 L 238.58 271.36 L 238.78 271.43 L 238.99 271.49 L 239.21 271.51 L 239.42 271.52 L 239.64 271.5 L 241.54 271.19 L 243.62 270.79 L 245.92 270.28 L 249.06 269.54 L 251.8 268.89 L 253.79 268.44 L 255.6 268.07 L 257.25 267.77 L 261.33 267.07 L 264.25 266.5 L 265.54 266.22 L 266.76 265.93 L 267.93 265.63 L 269.06 265.3 L 270.67 264.8 L 272.31 264.23 Z M 614.51 290.53 L 614.04 290.28 L 613.58 290.07 L 613.12 289.87 L 612.68 289.71 L 612.23 289.56 L 611.79 289.45 L 611.35 289.35 L 610.93 289.29 L 610.5 289.24 L 610.09 289.23 L 609.68 289.23 L 609.28 289.27 L 608.88 289.32 L 608.49 289.41 L 608.04 289.54 L 607.6 289.7 L 607.16 289.9 L 606.74 290.13 L 606.32 290.4 L 605.91 290.7 L 605.51 291.03 L 605.12 291.4 L 604.73 291.81 L 604.36 292.25 L 604 292.72 L 603.64 293.23 L 603.3 293.77 L 602.96 294.35 L 602.63 294.95 L 602.31 295.6 L 602.01 296.27 L 601.71 296.98 L 601.42 297.72 L 601.13 298.5 L 600.61 300.14 L 600.12 301.92 L 599.67 303.82 L 599.26 305.83 L 598.9 307.97 L 598.57 310.26 L 598.41 311.75 L 598.37 312.45 L 598.34 313.13 L 598.34 313.78 L 598.36 314.4 L 598.4 315 L 598.47 315.58 L 598.55 316.12 L 598.66 316.64 L 598.78 317.14 L 598.93 317.61 L 599.11 318.05 L 599.3 318.47 L 599.51 318.86 L 599.75 319.22 L 600.17 319.76 L 600.62 320.25 L 601.1 320.69 L 601.62 321.08 L 601.94 321.29 L 602.27 321.48 L 602.62 321.65 L 602.97 321.8 L 603.34 321.94 L 603.72 322.06 L 604.52 322.24 L 605.36 322.36 L 606.25 322.4 L 607.19 322.37 L 608.18 322.28 L 608.68 322.2 L 609.16 322.07 L 609.64 321.91 L 610.09 321.71 L 610.52 321.47 L 610.96 321.19 L 611.37 320.88 L 611.79 320.52 L 612.18 320.12 L 612.57 319.69 L 612.94 319.22 L 613.3 318.71 L 613.65 318.15 L 614 317.56 L 614.33 316.92 L 614.66 316.24 L 614.98 315.52 L 615.29 314.74 L 615.34 313.09 L 615.32 311.51 L 615.23 309.99 L 615.08 308.53 L 614.85 307.15 L 614.72 306.48 L 614.56 305.83 L 614.39 305.19 L 614.21 304.57 L 614 303.97 L 613.78 303.38 L 607.9 300.49 L 607.71 300.37 L 607.54 300.22 L 607.4 300.04 L 607.3 299.83 L 607.19 299.5 L 607.11 299.18 L 607.05 298.88 L 607.03 298.59 L 607.03 298.32 L 607.07 298.06 L 607.13 297.81 L 607.22 297.57 L 607.34 297.35 L 607.49 297.15 L 607.67 296.95 L 607.88 296.77 L 608.11 296.61 L 608.38 296.45 L 608.68 296.32 L 609 296.19 L 609.3 296.1 L 609.61 296.02 L 609.92 295.97 L 610.25 295.93 L 610.91 295.91 L 611.61 295.98 L 612.33 296.12 L 613.08 296.33 L 613.86 296.63 L 614.67 297 L 614.93 297.01 L 615.16 296.95 L 615.34 296.85 L 615.35 296.85 L 615.45 296.75 L 615.52 296.61 L 615.58 296.45 L 615.61 296.26 L 615.69 294.92 L 615.7 293.61 L 615.67 292.97 L 615.63 292.34 L 615.56 291.71 L 615.47 291.09 Z M 608.495 307.05 L 608.82 307.191 L 609.128 307.374 L 609.417 307.597 L 609.685 307.859 L 609.928 308.155 L 610.144 308.485 L 610.331 308.843 L 610.487 309.228 L 610.612 309.635 L 610.702 310.06 L 610.758 310.5 L 610.78 310.95 L 610.766 311.405 L 610.718 311.861 L 610.635 312.315 L 610.519 312.761 L 610.37 313.195 L 610.19 313.613 L 609.98 314.012 L 609.743 314.386 L 609.481 314.734 L 609.196 315.05 L 608.892 315.332 L 608.571 315.579 L 608.236 315.786 L 607.89 315.952 L 607.538 316.076 L 607.181 316.156 L 606.824 316.192 L 606.47 316.182 L 606.123 316.129 L 605.785 316.031 L 605.46 315.889 L 605.152 315.706 L 604.863 315.483 L 604.595 315.221 L 604.352 314.925 L 604.136 314.595 L 603.949 314.237 L 603.793 313.852 L 603.668 313.445 L 603.578 313.02 L 603.522 312.58 L 603.5 312.13 L 603.514 311.675 L 603.562 311.219 L 603.645 310.765 L 603.761 310.319 L 603.91 309.885 L 604.09 309.467 L 604.3 309.068 L 604.537 308.694 L 604.799 308.346 L 605.084 308.03 L 605.388 307.748 L 605.709 307.501 L 606.044 307.294 L 606.39 307.128 L 606.742 307.004 L 607.099 306.924 L 607.456 306.888 L 607.81 306.898 L 608.157 306.951 Z M 759.4 298.24 L 759.39 297.95 L 759.35 297.65 L 759.29 297.36 L 759.07 296.62 L 758.81 295.87 L 758.5 295.15 L 758.17 294.45 L 757.8 293.78 L 757.4 293.16 L 756.98 292.56 L 756.53 292.02 L 756.06 291.52 L 755.57 291.07 L 755.06 290.67 L 754.54 290.31 L 754 290.01 L 753.46 289.76 L 752.89 289.56 L 752.31 289.41 L 751.91 289.34 L 751.49 289.3 L 751.07 289.28 L 750.65 289.28 L 750.06 289.32 L 749.5 289.39 L 748.94 289.48 L 748.41 289.6 L 747.88 289.76 L 747.38 289.94 L 746.88 290.15 L 746.41 290.38 L 745.72 290.79 L 745.07 291.26 L 744.45 291.8 L 743.85 292.41 L 743.3 293.08 L 742.77 293.82 L 742.28 294.62 L 741.81 295.5 L 741.39 296.42 L 740.99 297.41 L 740.61 298.49 L 740.25 299.64 L 739.91 300.85 L 739.58 302.2 L 739.25 303.68 L 738.84 305.68 L 738.44 307.6 L 738.17 308.92 L 737.94 310.13 L 737.76 311.22 L 737.64 312.19 L 737.55 313.05 L 737.52 313.79 L 737.54 314.42 L 737.6 314.93 L 737.69 315.32 L 737.79 315.7 L 737.93 316.1 L 738.08 316.49 L 738.25 316.87 L 738.44 317.25 L 738.88 317.99 L 739.4 318.7 L 739.98 319.36 L 740.61 319.97 L 741.3 320.53 L 742.01 321.01 L 742.74 321.41 L 743.12 321.58 L 743.5 321.73 L 743.88 321.86 L 744.27 321.97 L 744.65 322.06 L 745.04 322.12 L 745.41 322.17 L 745.8 322.19 L 746.18 322.2 L 746.56 322.18 L 746.93 322.14 L 747.3 322.07 L 747.92 321.92 L 748.51 321.71 L 749.1 321.43 L 749.66 321.1 L 750.26 320.69 L 750.82 320.28 L 751.36 319.85 L 751.86 319.4 L 752.33 318.95 L 752.77 318.47 L 753.19 317.98 L 753.57 317.47 L 754.11 316.65 L 754.59 315.8 L 755.01 314.88 L 755.39 313.9 L 755.72 312.87 L 756.02 311.72 L 756.3 310.43 L 756.65 308.63 L 757.05 306.58 L 757.39 305.06 L 757.77 303.67 L 758.19 302.4 L 758.67 301.03 L 759.03 299.94 L 759.27 299.1 L 759.38 298.54 Z M 749.616 312.856 L 749.521 313.144 L 749.399 313.422 L 749.249 313.686 L 749.075 313.934 L 748.878 314.163 L 748.658 314.373 L 748.419 314.56 L 748.162 314.723 L 747.892 314.859 L 747.609 314.969 L 747.317 315.05 L 747.019 315.102 L 746.716 315.125 L 746.413 315.118 L 746.112 315.081 L 745.816 315.015 L 745.585 314.951 L 745.297 314.857 L 745.019 314.734 L 744.755 314.585 L 744.508 314.41 L 744.278 314.213 L 744.068 313.993 L 743.881 313.755 L 743.718 313.498 L 743.582 313.228 L 743.472 312.945 L 743.391 312.653 L 743.339 312.354 L 743.316 312.052 L 743.323 311.748 L 743.36 311.448 L 743.426 311.152 L 747.064 297.943 L 747.159 297.656 L 747.281 297.378 L 747.431 297.114 L 747.605 296.866 L 747.802 296.637 L 748.022 296.427 L 748.261 296.24 L 748.518 296.077 L 748.788 295.941 L 749.071 295.831 L 749.363 295.75 L 749.661 295.698 L 749.964 295.675 L 750.267 295.682 L 750.568 295.719 L 750.864 295.785 L 751.095 295.849 L 751.383 295.943 L 751.661 296.066 L 751.925 296.215 L 752.172 296.39 L 752.402 296.587 L 752.612 296.807 L 752.799 297.045 L 752.962 297.302 L 753.098 297.572 L 753.208 297.855 L 753.289 298.147 L 753.341 298.446 L 753.364 298.748 L 753.357 299.052 L 753.32 299.352 L 753.254 299.648 Z M 191.9 322.15 L 192.46 321.83 L 192.96 321.45 L 193.42 321.02 L 193.67 320.73 L 193.91 320.41 L 194.12 320.07 L 194.32 319.71 L 194.49 319.34 L 194.64 318.94 L 194.76 318.52 L 194.87 318.08 L 196.01 312.85 L 197.21 307.61 L 198.47 302.37 L 199.79 297.13 L 199.88 296.85 L 200.01 296.58 L 200.18 296.33 L 200.38 296.1 L 200.6 295.91 L 200.86 295.75 L 201.13 295.62 L 201.41 295.53 L 202.27 295.33 L 203.31 295.09 L 204.05 294.88 L 204.69 294.66 L 205.23 294.42 L 205.7 294.14 L 206.09 293.83 L 206.26 293.67 L 206.4 293.49 L 206.52 293.31 L 206.63 293.12 L 206.74 292.85 L 206.82 292.55 L 206.86 292.25 L 206.87 291.92 L 206.84 291.58 L 206.78 291.21 L 206.69 290.83 L 206.56 290.43 L 206.47 290.22 L 206.36 290.03 L 206.24 289.87 L 206.09 289.72 L 205.94 289.61 L 205.76 289.51 L 205.57 289.43 L 205.37 289.38 L 205.14 289.34 L 204.9 289.32 L 204.37 289.35 L 202.48 289.56 L 200.64 289.72 L 198.87 289.85 L 197.16 289.93 L 195.51 289.97 L 193.92 289.97 L 192.4 289.93 L 190.93 289.85 L 190.7 289.86 L 190.48 289.9 L 190.27 289.99 L 190.09 290.12 L 189.93 290.28 L 189.8 290.46 L 189.71 290.67 L 189.66 290.89 L 189.62 291.3 L 189.6 291.69 L 189.61 292.05 L 189.64 292.4 L 189.7 292.73 L 189.78 293.03 L 189.88 293.32 L 190.01 293.59 L 190.18 293.87 L 190.38 294.14 L 190.62 294.4 L 190.89 294.65 L 191.2 294.89 L 191.55 295.14 L 192.57 295.76 L 193.66 296.42 L 193.84 296.57 L 193.99 296.76 L 194.08 296.98 L 194.11 297.22 L 194.11 297.86 L 194.08 298.52 L 194.02 299.21 L 193.92 299.94 L 193.8 300.68 L 193.64 301.5 L 193.16 303.57 L 192.82 304.98 L 192.62 305.96 L 192.49 306.79 L 192.43 307.52 L 192.39 308.11 L 192.31 308.77 L 192.2 309.51 L 192.05 310.32 L 191.63 312.16 L 191.07 314.3 L 190.84 315.19 L 190.65 316.1 L 190.5 317.01 L 190.39 317.94 L 190.31 318.89 L 190.27 319.84 L 190.27 320.81 L 190.3 321.79 L 190.32 321.94 L 190.38 322.09 L 190.47 322.22 L 190.58 322.33 L 190.71 322.41 L 190.86 322.46 L 191.02 322.48 L 191.17 322.46 L 191.28 322.43 Z M 626.76 322.24 L 627.39 322.36 L 628 322.46 L 628.6 322.52 L 629.18 322.57 L 629.75 322.59 L 630.3 322.58 L 630.84 322.55 L 631.36 322.49 L 631.86 322.4 L 632.35 322.3 L 632.82 322.16 L 633.28 322 L 633.73 321.82 L 634.15 321.61 L 634.57 321.38 L 634.97 321.11 L 635.35 320.83 L 635.72 320.52 L 636.07 320.18 L 636.4 319.82 L 636.73 319.43 L 637.03 319.02 L 637.32 318.58 L 637.59 318.11 L 637.85 317.62 L 638.1 317.11 L 638.32 316.57 L 638.54 316 L 638.73 315.41 L 638.91 314.79 L 639.08 314.15 L 639.23 313.34 L 639.36 312.31 L 639.47 311.06 L 639.55 309.58 L 639.59 309.12 L 639.67 308.66 L 639.79 308.21 L 639.94 307.78 L 640.75 305.8 L 641.36 304.26 L 641.76 303.15 L 641.96 302.47 L 642.05 301.98 L 642.12 301.47 L 642.16 300.95 L 642.17 300.41 L 642.16 299.86 L 642.13 299.29 L 642.07 298.71 L 641.98 298.11 L 641.86 297.5 L 641.72 296.87 L 641.56 296.23 L 641.37 295.57 L 640.91 294.21 L 640.35 292.79 L 640.25 292.58 L 640.13 292.38 L 640 292.2 L 639.84 292.03 L 639.67 291.87 L 639.49 291.73 L 639.29 291.61 L 639.08 291.51 L 637.71 290.96 L 636.48 290.54 L 635.9 290.37 L 635.35 290.23 L 634.81 290.11 L 634.29 290.02 L 633.79 289.96 L 633.3 289.92 L 632.82 289.91 L 632.36 289.93 L 631.91 289.97 L 631.47 290.04 L 631.04 290.13 L 630.62 290.25 L 630.02 290.47 L 629.43 290.76 L 628.85 291.11 L 628.29 291.51 L 627.74 291.98 L 627.2 292.51 L 626.67 293.11 L 626.15 293.77 L 625.91 294.29 L 625.7 294.82 L 625.52 295.34 L 625.37 295.87 L 625.24 296.4 L 625.13 296.93 L 625.06 297.47 L 625.01 298 L 624.98 298.53 L 624.99 299.07 L 625.02 299.6 L 625.07 300.14 L 625.36 302.68 L 625.57 305.16 L 625.7 307.56 L 625.76 309.89 L 625.75 312.15 L 625.66 314.34 L 625.5 316.45 L 625.39 317.49 L 625.26 318.5 L 625.24 318.81 L 625.25 319.16 L 625.31 319.55 L 625.4 319.99 L 625.52 320.45 L 625.68 320.96 L 625.88 321.51 L 626.12 322.1 Z M 633.35 313.32 L 633.258 313.62 L 633.138 313.91 L 632.99 314.187 L 632.816 314.448 L 632.616 314.691 L 632.394 314.912 L 632.151 315.112 L 631.889 315.286 L 631.612 315.434 L 631.322 315.554 L 631.022 315.645 L 630.714 315.706 L 630.401 315.736 L 630.087 315.736 L 629.775 315.705 L 629.467 315.644 L 629.233 315.585 L 628.933 315.493 L 628.643 315.373 L 628.366 315.225 L 628.105 315.051 L 627.863 314.851 L 627.641 314.629 L 627.442 314.386 L 627.268 314.125 L 627.12 313.847 L 627 313.557 L 626.908 313.257 L 626.848 312.949 L 626.818 312.637 L 626.818 312.323 L 626.848 312.01 L 626.91 311.702 L 630.13 298.88 L 630.222 298.58 L 630.342 298.29 L 630.49 298.013 L 630.664 297.752 L 630.864 297.509 L 631.087 297.288 L 631.329 297.088 L 631.591 296.914 L 631.868 296.766 L 632.158 296.646 L 632.458 296.555 L 632.766 296.494 L 633.079 296.464 L 633.393 296.464 L 633.706 296.495 L 634.014 296.556 L 634.247 296.615 L 634.547 296.706 L 634.837 296.826 L 635.114 296.975 L 635.375 297.149 L 635.617 297.349 L 635.839 297.571 L 636.038 297.814 L 636.212 298.075 L 636.36 298.353 L 636.48 298.643 L 636.572 298.943 L 636.632 299.251 L 636.662 299.563 L 636.662 299.878 L 636.632 300.19 L 636.57 300.498 Z M 717.96 321.21 L 718.68 320.27 L 719.51 319.27 L 720.46 318.24 L 720.95 317.67 L 721.6 316.81 L 722.42 315.65 L 723.41 314.21 L 725.91 310.51 L 727.96 307.48 L 729.35 305.38 L 730.55 303.52 L 731.59 301.85 L 732.56 300.21 L 733.44 298.64 L 734.25 297.09 L 735.01 295.53 L 735.5 294.48 L 735.96 293.41 L 736.07 293.06 L 736.11 292.7 L 736.08 292.34 L 735.99 291.99 L 735.83 291.67 L 735.61 291.38 L 735.34 291.14 L 735.03 290.95 L 734.74 290.83 L 734.46 290.75 L 734.22 290.73 L 733.99 290.75 L 733.48 290.85 L 732.87 290.94 L 732.25 291.01 L 731.64 291.04 L 731.01 291.04 L 730.39 291.02 L 729.75 290.96 L 729.11 290.88 L 728.47 290.77 L 727.27 290.57 L 726.12 290.46 L 725.57 290.43 L 725.03 290.43 L 724.5 290.44 L 723.99 290.48 L 723.49 290.54 L 723 290.62 L 722.53 290.72 L 722.07 290.84 L 721.62 290.98 L 721.18 291.14 L 720.75 291.32 L 720.33 291.53 L 719.68 291.91 L 719.05 292.35 L 718.45 292.85 L 717.88 293.41 L 717.66 293.67 L 717.45 293.94 L 717.27 294.22 L 717.11 294.52 L 716.97 294.83 L 716.85 295.15 L 716.76 295.48 L 716.69 295.81 L 716.65 296.15 L 716.64 296.49 L 716.65 296.83 L 716.68 297.16 L 716.74 297.5 L 716.83 297.83 L 716.94 298.15 L 717.07 298.46 L 717.17 298.67 L 717.27 298.86 L 717.39 299.04 L 717.54 299.21 L 717.71 299.37 L 717.89 299.51 L 718.09 299.63 L 718.3 299.73 L 718.52 299.8 L 718.75 299.85 L 718.98 299.87 L 719.21 299.86 L 719.44 299.83 L 719.66 299.78 L 719.88 299.7 L 720.08 299.6 L 720.28 299.47 L 720.46 299.32 L 720.62 299.16 L 721.04 298.7 L 721.48 298.28 L 721.93 297.91 L 722.39 297.57 L 722.86 297.27 L 723.35 297.01 L 723.85 296.79 L 724.36 296.61 L 724.89 296.47 L 725.43 296.38 L 725.98 296.32 L 726.55 296.3 L 726.81 296.33 L 727.04 296.42 L 727.24 296.56 L 727.41 296.74 L 727.53 296.95 L 727.6 297.18 L 727.62 297.43 L 727.58 297.67 L 727.49 297.9 L 726.3 300.05 L 724.98 302.32 L 723.53 304.73 L 721.93 307.27 L 720.2 309.93 L 718.34 312.72 L 716.34 315.65 L 714.2 318.7 L 714.01 319 L 713.87 319.3 L 713.76 319.61 L 713.7 319.93 L 713.69 320.33 L 713.75 320.74 L 713.87 321.16 L 714.06 321.59 L 714.18 321.79 L 714.32 321.99 L 714.48 322.17 L 714.65 322.33 L 714.84 322.47 L 715.04 322.59 L 715.24 322.69 L 715.46 322.77 L 715.68 322.82 L 715.89 322.85 L 716.11 322.85 L 716.32 322.82 L 716.52 322.77 L 716.71 322.7 L 716.89 322.6 L 717.05 322.48 L 717.22 322.31 L 717.36 322.12 Z M 380.49 310.39 L 380.42 310.2 L 380.39 310.01 L 380.38 309.82 L 380.4 309.63 L 380.44 309.44 L 380.51 309.27 L 380.6 309.1 L 382.57 306.1 L 384.66 303.05 L 386.88 299.92 L 389.21 296.74 L 389.5 296.3 L 389.75 295.82 L 389.96 295.3 L 390.14 294.75 L 390.26 294.15 L 390.35 293.52 L 390.4 292.85 L 390.41 292.14 L 390.37 291.9 L 390.27 291.67 L 390.11 291.49 L 389.9 291.35 L 389.55 291.2 L 389.21 291.07 L 388.88 290.99 L 388.57 290.93 L 388.26 290.91 L 387.97 290.92 L 387.69 290.96 L 387.42 291.04 L 387.05 291.21 L 386.7 291.44 L 386.38 291.75 L 386.08 292.13 L 376.06 306.55 L 375.51 307.34 L 374.17 309.28 L 373.24 310.73 L 372.84 311.39 L 372.49 312.01 L 372.18 312.59 L 371.92 313.15 L 371.69 313.68 L 371.51 314.18 L 371.37 314.66 L 371.28 315.11 L 371.23 315.52 L 371.22 315.91 L 371.25 316.27 L 371.33 316.61 L 371.38 316.74 L 371.49 316.92 L 371.64 317.07 L 371.83 317.2 L 372.06 317.31 L 372.32 317.41 L 372.63 317.48 L 372.98 317.54 L 373.36 317.57 L 374.49 317.67 L 375.57 317.84 L 376.59 318.07 L 377.56 318.37 L 378.02 318.55 L 378.47 318.74 L 378.91 318.95 L 379.33 319.17 L 379.74 319.41 L 380.13 319.67 L 380.51 319.94 L 380.88 320.24 L 381.35 320.65 L 381.79 321.09 L 382.09 321.35 L 382.43 321.55 L 382.8 321.67 L 383.19 321.73 L 383.59 321.7 L 383.97 321.61 L 384.33 321.44 L 384.65 321.2 L 385.38 320.5 L 386.04 319.76 L 386.66 318.98 L 387.21 318.17 L 387.72 317.3 L 388.16 316.4 L 388.55 315.46 L 388.88 314.48 L 389.15 313.46 L 389.36 312.4 L 389.51 311.31 L 389.6 310.18 L 389.62 309.29 L 389.58 308.47 L 389.47 307.74 L 389.31 307.09 L 389.24 306.9 L 389.14 306.72 L 389.03 306.55 L 388.9 306.38 L 388.75 306.23 L 388.58 306.09 L 388.2 305.86 L 387.77 305.68 L 387.31 305.58 L 386.82 305.54 L 386.32 305.58 L 386.03 305.64 L 385.74 305.74 L 385.46 305.88 L 385.17 306.05 L 384.89 306.26 L 384.61 306.52 L 384.33 306.81 L 384.06 307.14 L 383.78 307.5 L 383.51 307.91 L 383.24 308.35 L 382.97 308.83 L 382.44 309.91 L 381.92 311.14 L 381.86 311.25 L 381.77 311.35 L 381.66 311.42 L 381.54 311.47 L 381.41 311.48 L 381.28 311.47 L 381.16 311.42 L 381.05 311.35 L 380.94 311.21 L 380.57 310.56 Z M 487.83 315.72 L 487.62 315.67 L 487.41 315.65 L 487.2 315.65 L 486.98 315.67 L 486.77 315.71 L 486.57 315.78 L 486.38 315.87 L 486.2 315.98 L 486.03 316.11 L 485.88 316.26 L 485.74 316.42 L 485.62 316.6 L 485.53 316.79 L 485.45 316.99 L 485.4 317.2 L 485.36 317.57 L 485.36 318.38 L 485.37 318.71 L 485.41 319.04 L 485.47 319.36 L 485.56 319.68 L 485.67 319.99 L 485.8 320.29 L 485.96 320.58 L 486.13 320.86 L 486.33 321.13 L 486.55 321.37 L 486.78 321.61 L 487.04 321.82 L 487.3 322.01 L 487.58 322.18 L 487.88 322.33 L 488.18 322.46 L 489.12 322.79 L 490.06 323.07 L 491.01 323.3 L 491.94 323.47 L 492.88 323.6 L 493.79 323.67 L 494.67 323.69 L 495.53 323.65 L 496.35 323.56 L 497.13 323.42 L 497.89 323.22 L 498.6 322.98 L 499.27 322.68 L 499.9 322.33 L 500.49 321.93 L 501.03 321.48 L 501.44 321.09 L 501.81 320.67 L 502.16 320.22 L 502.48 319.73 L 502.94 318.94 L 503.35 318.12 L 503.72 317.3 L 504.03 316.46 L 504.3 315.62 L 504.51 314.78 L 504.67 313.95 L 504.78 313.11 L 504.83 312.31 L 504.83 311.51 L 504.77 310.72 L 504.66 309.96 L 504.5 309.21 L 504.28 308.5 L 504.01 307.8 L 503.69 307.13 L 503.4 306.62 L 503.07 306.12 L 502.72 305.65 L 502.33 305.19 L 501.92 304.75 L 501.47 304.33 L 500.99 303.93 L 500.48 303.55 L 499.86 303.16 L 499.1 302.77 L 498.19 302.36 L 496.93 301.86 L 495.85 301.43 L 495.2 301.13 L 494.92 300.94 L 494.68 300.7 L 494.49 300.41 L 494.37 300.09 L 494.31 299.75 L 494.32 299.41 L 494.4 299.08 L 494.54 298.77 L 494.59 298.7 L 494.77 298.46 L 494.96 298.24 L 495.19 298.03 L 495.43 297.83 L 495.7 297.65 L 496 297.48 L 496.31 297.32 L 496.67 297.17 L 497.42 296.92 L 498.3 296.7 L 499.32 296.51 L 500.76 296.29 L 502.03 296.1 L 502.91 295.94 L 503.65 295.76 L 504.28 295.56 L 504.81 295.32 L 505.04 295.18 L 505.25 295.04 L 505.43 294.88 L 505.59 294.72 L 505.72 294.54 L 505.83 294.35 L 505.94 294.11 L 506.02 293.84 L 506.06 293.56 L 506.06 293.25 L 506.04 292.93 L 505.98 292.58 L 505.89 292.22 L 505.76 291.84 L 505.68 291.66 L 505.57 291.49 L 505.43 291.33 L 505.28 291.2 L 505.11 291.09 L 504.92 291.01 L 504.73 290.96 L 504.53 290.93 L 502.18 290.84 L 499.92 290.81 L 497.77 290.85 L 495.72 290.96 L 495.32 291 L 494.94 291.06 L 494.56 291.14 L 494.21 291.24 L 493.86 291.36 L 493.54 291.5 L 493.22 291.66 L 492.91 291.84 L 492.51 292.12 L 492.14 292.44 L 491.79 292.8 L 491.46 293.2 L 491.16 293.64 L 490.88 294.13 L 490.63 294.64 L 490.4 295.2 L 490.2 295.79 L 490.02 296.42 L 489.86 297.1 L 489.73 297.82 L 489.61 298.59 L 489.52 299.41 L 489.45 300.28 L 489.39 301.25 L 489.36 301.55 L 489.3 301.85 L 489.22 302.15 L 489.11 302.43 L 488.93 302.79 L 488.72 303.16 L 488.47 303.55 L 488.19 303.95 L 487.99 304.26 L 487.81 304.6 L 487.65 304.99 L 487.52 305.39 L 487.42 305.81 L 487.36 306.24 L 487.32 306.66 L 487.33 307.08 L 487.36 307.47 L 487.43 307.85 L 487.53 308.2 L 487.66 308.52 L 487.82 308.81 L 488 309.06 L 488.22 309.28 L 488.46 309.47 L 488.64 309.57 L 488.84 309.66 L 489.04 309.73 L 489.25 309.78 L 489.48 309.81 L 489.72 309.82 L 490.23 309.79 L 491.9 309.51 L 493.44 309.23 L 494.57 309.08 L 495.56 309.01 L 496 309 L 496.41 309.02 L 496.81 309.06 L 497.16 309.12 L 497.49 309.2 L 497.81 309.31 L 498.08 309.44 L 498.33 309.59 L 498.55 309.76 L 498.75 309.96 L 498.92 310.19 L 499.07 310.44 L 499.18 310.71 L 499.27 311.02 L 499.33 311.34 L 499.36 311.69 L 499.36 312.06 L 499.34 312.46 L 499.26 313.13 L 499.13 313.75 L 498.97 314.32 L 498.77 314.84 L 498.53 315.32 L 498.25 315.74 L 497.93 316.12 L 497.57 316.45 L 497.21 316.7 L 496.83 316.91 L 496.4 317.09 L 495.96 317.22 L 495.48 317.32 L 494.96 317.38 L 494.42 317.4 L 493.84 317.38 L 493.24 317.32 L 492.6 317.22 L 491.92 317.08 L 491.22 316.9 L 490.48 316.68 L 489.7 316.43 L 488.88 316.13 L 488.03 315.79 Z M 525.44 321.7 L 525.68 321.48 L 525.92 321.24 L 526.13 320.99 L 526.72 320.19 L 527.23 319.4 L 527.68 318.6 L 528.08 317.79 L 528.36 317.11 L 528.61 316.4 L 528.83 315.66 L 529.02 314.88 L 529.18 314.05 L 529.33 313.16 L 529.45 312.17 L 529.57 311.03 L 529.63 310.48 L 529.72 309.92 L 529.96 308.74 L 530.29 307.48 L 530.8 305.8 L 531.2 304.51 L 531.47 303.56 L 531.67 302.7 L 531.82 301.91 L 531.91 301.14 L 531.94 300.39 L 531.92 299.67 L 531.84 298.95 L 531.75 298.47 L 531.63 297.97 L 531.49 297.49 L 531.31 296.99 L 531.11 296.51 L 530.87 296.02 L 530.61 295.53 L 530.31 295.03 L 529.93 294.46 L 529.52 293.95 L 529.09 293.47 L 528.63 293.04 L 528.14 292.64 L 527.63 292.3 L 527.1 292 L 526.55 291.75 L 525.87 291.52 L 525.19 291.35 L 524.47 291.25 L 523.75 291.22 L 523.01 291.25 L 522.27 291.35 L 521.52 291.52 L 520.78 291.76 L 520.05 292.06 L 519.33 292.42 L 518.64 292.83 L 517.98 293.31 L 517.35 293.83 L 516.75 294.41 L 516.2 295.02 L 515.69 295.68 L 515.38 296.18 L 515.08 296.76 L 514.8 297.44 L 514.53 298.21 L 514.28 299.07 L 514.04 300.02 L 513.82 301.07 L 513.61 302.2 L 513.24 304.23 L 512.7 306.91 L 512.01 310.24 L 511.15 314.22 L 511.09 314.53 L 511.03 314.95 L 511 315.37 L 510.99 315.79 L 511.01 316.22 L 511.05 316.65 L 511.12 317.09 L 511.21 317.53 L 511.32 317.98 L 511.47 318.43 L 511.63 318.88 L 511.83 319.34 L 512.04 319.8 L 512.55 320.74 L 513.16 321.7 L 513.33 321.92 L 513.53 322.11 L 513.75 322.28 L 513.99 322.41 L 514.96 322.83 L 515.92 323.18 L 516.86 323.45 L 517.78 323.63 L 518.69 323.74 L 519.58 323.77 L 520.03 323.75 L 520.46 323.72 L 520.89 323.66 L 521.32 323.58 L 521.82 323.47 L 522.32 323.33 L 522.81 323.16 L 523.29 322.96 L 523.77 322.74 L 524.24 322.49 L 524.71 322.21 L 525.17 321.9 Z M 523.398 314.871 L 523.308 315.174 L 523.191 315.467 L 523.044 315.747 L 522.871 316.012 L 522.673 316.258 L 522.452 316.483 L 522.209 316.686 L 521.949 316.864 L 521.672 317.016 L 521.38 317.14 L 521.08 317.235 L 520.77 317.299 L 520.456 317.333 L 520.14 317.336 L 519.825 317.308 L 519.515 317.25 L 518.931 317.11 L 518.628 317.021 L 518.335 316.903 L 518.055 316.757 L 517.79 316.584 L 517.544 316.386 L 517.318 316.164 L 517.115 315.922 L 516.937 315.661 L 516.786 315.384 L 516.662 315.093 L 516.567 314.792 L 516.503 314.483 L 516.468 314.168 L 516.465 313.852 L 516.493 313.537 L 516.552 313.227 L 519.783 299.769 L 519.871 299.466 L 519.99 299.173 L 520.136 298.893 L 520.309 298.628 L 520.507 298.382 L 520.728 298.157 L 520.971 297.954 L 521.231 297.776 L 521.509 297.624 L 521.8 297.5 L 522.1 297.406 L 522.41 297.341 L 522.724 297.307 L 523.04 297.304 L 523.355 297.332 L 523.665 297.39 L 524.249 297.53 L 524.552 297.619 L 524.845 297.737 L 525.125 297.883 L 525.39 298.056 L 525.636 298.255 L 525.862 298.475 L 526.064 298.718 L 526.243 298.979 L 526.394 299.256 L 526.518 299.547 L 526.613 299.848 L 526.678 300.158 L 526.712 300.472 L 526.715 300.788 L 526.687 301.103 L 526.628 301.413 Z M 407.7 323.91 L 408.34 323.84 L 408.95 323.73 L 409.53 323.59 L 410.09 323.4 L 410.63 323.18 L 411.14 322.92 L 411.62 322.63 L 411.96 322.39 L 412.28 322.13 L 412.6 321.84 L 412.9 321.54 L 413.18 321.22 L 413.45 320.88 L 413.71 320.52 L 413.95 320.14 L 414.18 319.74 L 414.39 319.32 L 414.6 318.88 L 414.78 318.43 L 415.12 317.45 L 415.4 316.39 L 417.34 307.97 L 418.45 302.99 L 418.56 302.35 L 418.61 301.65 L 418.62 300.9 L 418.58 300.09 L 418.49 299.23 L 418.35 298.32 L 418.17 297.35 L 417.93 296.32 L 417.77 295.75 L 417.58 295.22 L 417.36 294.73 L 417.12 294.26 L 416.84 293.84 L 416.54 293.45 L 416.21 293.09 L 415.85 292.77 L 415.49 292.5 L 415.1 292.26 L 414.69 292.06 L 414.25 291.88 L 413.79 291.73 L 413.31 291.62 L 412.8 291.53 L 412.26 291.48 L 411.59 291.44 L 410.91 291.43 L 410.21 291.43 L 409.5 291.46 L 408.04 291.57 L 406.53 291.77 L 406.29 292.3 L 406.08 292.82 L 405.91 293.34 L 405.78 293.86 L 405.68 294.38 L 405.61 294.9 L 405.59 295.42 L 405.59 295.93 L 405.63 296.42 L 405.71 296.92 L 405.82 297.41 L 405.96 297.89 L 406.13 298.38 L 406.34 298.87 L 406.58 299.35 L 406.85 299.83 L 407.47 299 L 407.76 298.66 L 408.04 298.37 L 408.31 298.12 L 408.56 297.93 L 408.81 297.78 L 409.04 297.68 L 409.41 297.59 L 409.79 297.57 L 410.17 297.62 L 410.53 297.73 L 410.87 297.9 L 411.18 298.12 L 411.45 298.39 L 411.66 298.71 L 411.86 299.09 L 412.03 299.49 L 412.17 299.89 L 412.27 300.3 L 412.35 300.72 L 412.4 301.15 L 412.42 301.6 L 412.41 302.05 L 412.37 302.52 L 412.3 303 L 411.75 306.14 L 411.15 309.27 L 410.96 310.14 L 410.73 310.99 L 410.46 311.82 L 410.16 312.62 L 409.83 313.4 L 409.46 314.16 L 409.05 314.89 L 408.61 315.6 L 408.25 316.12 L 407.87 316.64 L 407.46 317.14 L 407.04 317.62 L 406.77 317.46 L 406.53 317.37 L 406.3 317.34 L 406.1 317.37 L 405.98 317.42 L 405.86 317.5 L 405.76 317.61 L 405.66 317.75 L 405.49 318.11 L 405.36 318.58 L 405.27 319.09 L 405.23 319.59 L 405.23 320.09 L 405.28 320.58 L 405.37 321.02 L 405.49 321.45 L 405.65 321.88 L 405.85 322.3 L 406.09 322.72 L 406.37 323.13 L 406.69 323.54 L 407.04 323.94 Z M 279.77 298.57 L 279.76 299.13 L 279.7 299.68 L 279.59 300.21 L 279.47 300.64 L 279.31 301.06 L 279.11 301.47 L 278.88 301.88 L 278.62 302.27 L 278.32 302.66 L 277.99 303.04 L 277.63 303.41 L 277.33 303.66 L 276.95 303.9 L 276.5 304.15 L 275.82 304.48 L 275.18 304.78 L 274.73 305.02 L 274.34 305.25 L 274.01 305.49 L 273.72 305.74 L 273.48 306 L 273.29 306.29 L 273.14 306.58 L 273.04 306.86 L 272.98 307.16 L 272.96 307.47 L 272.97 307.81 L 273.01 308.02 L 273.09 308.25 L 273.22 308.49 L 273.4 308.74 L 273.62 309.01 L 273.88 309.29 L 274.19 309.58 L 274.54 309.88 L 274.9 310.2 L 275.22 310.53 L 275.51 310.87 L 275.76 311.21 L 275.97 311.56 L 276.14 311.92 L 276.27 312.29 L 276.37 312.67 L 276.44 313.17 L 276.45 313.69 L 276.39 314.22 L 276.27 314.76 L 276.08 315.32 L 275.83 315.89 L 275.52 316.47 L 275.14 317.07 L 275.01 317.23 L 274.86 317.38 L 274.69 317.51 L 274.51 317.61 L 274.32 317.69 L 274.12 317.74 L 273.91 317.76 L 273.7 317.76 L 265.95 317.16 L 265.64 317.17 L 265.35 317.23 L 265.07 317.36 L 264.82 317.54 L 264.61 317.76 L 264.45 318.02 L 264.35 318.31 L 264.3 318.61 L 264.3 319.11 L 264.31 319.55 L 264.35 319.95 L 264.41 320.33 L 264.49 320.68 L 264.59 321 L 264.72 321.28 L 264.86 321.54 L 265.02 321.76 L 265.21 321.96 L 265.43 322.14 L 265.66 322.28 L 266.5 322.66 L 267.38 322.97 L 268.29 323.22 L 269.25 323.4 L 270.24 323.51 L 271.27 323.55 L 272.33 323.53 L 273.44 323.44 L 274.37 323.31 L 275.25 323.13 L 276.06 322.9 L 276.82 322.62 L 277.51 322.3 L 278.15 321.91 L 278.72 321.49 L 279.24 321 L 279.61 320.59 L 279.94 320.16 L 280.24 319.69 L 280.51 319.19 L 280.74 318.65 L 280.96 318.08 L 281.14 317.47 L 281.29 316.82 L 281.41 316.15 L 281.51 315.43 L 281.59 314.67 L 281.64 313.84 L 281.67 312.98 L 281.68 312.01 L 281.65 309.5 L 281.67 309.01 L 281.75 308.53 L 281.88 308.06 L 282.07 307.61 L 285.19 301.16 L 285.32 300.88 L 285.42 300.59 L 285.51 300.3 L 285.57 300 L 285.63 299.61 L 285.65 299.22 L 285.64 298.81 L 285.59 298.41 L 285.51 297.99 L 285.4 297.57 L 285.25 297.14 L 285.07 296.71 L 284.79 296.15 L 284.5 295.62 L 284.17 295.12 L 283.83 294.66 L 283.45 294.22 L 283.05 293.82 L 282.63 293.45 L 282.19 293.11 L 281.63 292.76 L 281.06 292.45 L 280.44 292.19 L 279.81 291.97 L 279.15 291.8 L 278.45 291.68 L 277.75 291.6 L 277.01 291.57 L 276.25 291.59 L 275.46 291.66 L 274.68 291.78 L 273.87 291.94 L 273.05 292.15 L 272.24 292.4 L 271.43 292.7 L 270.63 293.04 L 270.41 293.17 L 270.22 293.33 L 270.06 293.53 L 269.94 293.76 L 269.73 294.32 L 269.56 294.86 L 269.44 295.37 L 269.35 295.85 L 269.31 296.31 L 269.31 296.75 L 269.35 297.16 L 269.43 297.54 L 269.52 297.79 L 269.62 297.99 L 269.75 298.18 L 269.9 298.35 L 270.06 298.5 L 270.25 298.64 L 270.45 298.75 L 270.67 298.85 L 270.89 298.92 L 271.13 298.97 L 271.37 298.99 L 271.62 298.99 L 271.86 298.97 L 272.11 298.92 L 272.35 298.85 L 272.58 298.76 L 272.8 298.64 L 273.53 298.24 L 274.26 297.89 L 274.98 297.6 L 275.71 297.37 L 276.43 297.19 L 277.15 297.06 L 277.87 296.99 L 278.59 296.97 L 278.8 296.99 L 279 297.05 L 279.19 297.14 L 279.35 297.27 L 279.5 297.42 L 279.61 297.6 L 279.69 297.79 L 279.73 298 Z M 289.29 323.55 L 289.74 323.88 L 290.17 324.15 L 290.61 324.4 L 291.07 324.63 L 291.54 324.84 L 292.03 325.02 L 292.54 325.18 L 293.06 325.32 L 293.59 325.44 L 294.14 325.53 L 294.71 325.6 L 295.29 325.65 L 295.89 325.68 L 296.5 325.68 L 297.13 325.66 L 297.77 325.62 L 298.43 325.55 L 298.82 325.48 L 299.2 325.34 L 299.56 325.15 L 299.88 324.91 L 300.61 324.26 L 301.27 323.63 L 301.87 323.01 L 302.42 322.41 L 302.92 321.81 L 303.38 321.2 L 303.79 320.58 L 304.18 319.95 L 304.59 319.17 L 304.96 318.36 L 305.29 317.51 L 305.58 316.62 L 305.85 315.66 L 306.09 314.62 L 306.31 313.48 L 306.5 312.25 L 306.92 309.55 L 307.15 308.27 L 307.4 307.03 L 307.65 305.83 L 307.92 304.68 L 308.2 303.57 L 308.5 302.5 L 308.55 302.24 L 308.56 301.97 L 308.5 301.64 L 307.18 297 L 307.07 296.71 L 306.92 296.43 L 306.73 296.18 L 306.51 295.96 L 306.03 295.58 L 305.47 295.18 L 304.86 294.8 L 304.23 294.44 L 303.58 294.11 L 302.92 293.81 L 302.27 293.56 L 301.63 293.34 L 301.03 293.17 L 300.44 293.05 L 299.87 292.96 L 299.33 292.92 L 298.82 292.92 L 298.34 292.96 L 297.87 293.04 L 297.45 293.17 L 296.8 293.43 L 296.17 293.75 L 295.71 294.03 L 295.26 294.33 L 294.84 294.67 L 294.43 295.04 L 294.03 295.44 L 293.66 295.87 L 293.3 296.32 L 292.95 296.82 L 292.62 297.34 L 292.3 297.9 L 292.01 298.49 L 291.72 299.11 L 291.45 299.76 L 291.19 300.45 L 290.94 301.18 L 290.71 301.95 L 290.4 303.07 L 290.06 304.4 L 289.28 307.68 L 288.37 311.8 L 287.33 316.74 L 287.19 317.57 L 287.11 318.35 L 287.09 319.09 L 287.14 319.79 L 287.25 320.45 L 287.42 321.06 L 287.66 321.64 L 287.97 322.17 L 288.16 322.44 L 288.37 322.7 L 288.61 322.95 L 288.86 323.19 Z M 299.41 317.693 L 299.32 318.005 L 299.201 318.305 L 299.051 318.592 L 298.875 318.864 L 298.673 319.117 L 298.447 319.349 L 298.199 319.558 L 297.933 319.741 L 297.649 319.898 L 297.351 320.026 L 297.043 320.124 L 296.726 320.191 L 296.404 320.227 L 296.08 320.232 L 295.758 320.205 L 295.439 320.145 L 295.206 320.09 L 294.894 320.001 L 294.594 319.881 L 294.306 319.731 L 294.034 319.555 L 293.781 319.353 L 293.549 319.127 L 293.341 318.88 L 293.157 318.613 L 293.001 318.329 L 292.873 318.031 L 292.775 317.723 L 292.707 317.406 L 292.671 317.084 L 292.667 316.76 L 292.694 316.438 L 292.753 316.119 L 296.25 301.327 L 296.34 301.015 L 296.459 300.715 L 296.609 300.428 L 296.785 300.156 L 296.987 299.903 L 297.213 299.671 L 297.461 299.462 L 297.727 299.279 L 298.011 299.122 L 298.309 298.994 L 298.617 298.896 L 298.934 298.829 L 299.256 298.793 L 299.58 298.788 L 299.902 298.815 L 300.221 298.875 L 300.454 298.93 L 300.766 299.019 L 301.066 299.139 L 301.354 299.289 L 301.625 299.465 L 301.879 299.667 L 302.111 299.893 L 302.319 300.14 L 302.503 300.407 L 302.659 300.691 L 302.787 300.988 L 302.885 301.297 L 302.953 301.614 L 302.989 301.936 L 302.993 302.26 L 302.966 302.582 L 302.907 302.9 Z"/>
</svg>
        </div>
        <div class="lpsb-sheet">
          <div class="lpsb-grid">
            <template v-for="row in scoringRowsWithScores" :key="row.key">
              <div
                class="lpsb-cell lpsb-label"
                :class="{ 'lpsb-divider': lpsbDividerKeys.has(row.key as LpsbRowKey) }"
              >
                {{ row.name }}
              </div>
              <div
                class="lpsb-cell lpsb-raw"
                :class="{ 'lpsb-divider': lpsbDividerKeys.has(row.key as LpsbRowKey) }"
              >
                {{ row.raw ?? '–' }}
              </div>
              <div
                class="lpsb-cell lpsb-tvalue"
                :class="{
                  'lpsb-divider': lpsbDividerKeys.has(row.key as LpsbRowKey),
                  'lpsb-tvalue--primary': lpsbPrimaryKeys.has(row.key as LpsBScoreKey),
                  'lpsb-tvalue--secondary': lpsbSecondaryKeys.has(row.key as LpsBScoreKey),
                }"
              >
                {{ row.t ?? '–' }}
              </div>
              <div
                class="lpsb-cell lpsb-hatch"
                :class="{ 'lpsb-divider': lpsbDividerKeys.has(row.key as LpsbRowKey) }"
              ></div>
              <div
                v-for="(tValue, tIdx) in lpsbTValues"
                :key="`${row.key}-${tValue}`"
                class="lpsb-cell lpsb-score"
                :class="{
                  'lpsb-divider': lpsbDividerKeys.has(row.key as LpsbRowKey),
                  'lpsb-score--boundary':
                    row.key === 'total_raw' && glTickOffset === 0 && glTickCellIndex === tIdx,
                }"
              >
                <div v-if="row.key === 'total_raw'" class="lpsb-ticks">
                  <span
                    v-for="tickOffset in lpsbMinorTicks"
                    :key="`gl-tick-${tIdx}-${tickOffset}`"
                    class="lpsb-tick"
                    :class="{
                      'lpsb-tick--active': glTickCellIndex === tIdx && glTickOffset === tickOffset,
                    }"
                  ></span>
                </div>
              </div>
            </template>
            <div class="lpsb-overlay">
              <div class="lpsb-vertical" :style="{ left: `${lpsbVertical40X}px` }"></div>
              <div class="lpsb-vertical lpsb-vertical--mid" :style="{ left: `${lpsbVertical50X}px` }"></div>
              <div class="lpsb-vertical" :style="{ left: `${lpsbVertical60X}px` }"></div>
              <svg
                class="lpsb-chart"
                :width="lpsbTopWidth"
                :height="lpsbGridHeight"
                :viewBox="`0 0 ${lpsbTopWidth} ${lpsbGridHeight}`"
              >
                <polyline
                  v-if="lpsbPolylinePoints"
                  :points="lpsbPolylinePoints"
                  fill="none"
                  stroke="#065f46"
                  stroke-width="2"
                />
                <polyline
                  v-if="lpsbPrimaryPolylinePoints"
                  :points="lpsbPrimaryPolylinePoints"
                  fill="none"
                  stroke="#dc2626"
                  stroke-width="2"
                />
                <g v-for="(point, idx) in lpsbPointsFiltered" :key="`lpsb-point-${idx}`" stroke="#065f46" stroke-width="2">
                  <line
                    :x1="point.x - lpsbMarkerSize / 2"
                    :y1="point.y - lpsbMarkerSize / 2"
                    :x2="point.x + lpsbMarkerSize / 2"
                    :y2="point.y + lpsbMarkerSize / 2"
                  />
                  <line
                    :x1="point.x - lpsbMarkerSize / 2"
                    :y1="point.y + lpsbMarkerSize / 2"
                    :x2="point.x + lpsbMarkerSize / 2"
                    :y2="point.y - lpsbMarkerSize / 2"
                  />
                </g>
                <circle
                  v-for="(point, idx) in lpsbPrimaryPoints"
                  :key="`lpsb-primary-point-${idx}`"
                  :cx="point.x"
                  :cy="point.y"
                  r="4"
                  fill="none"
                  stroke="#dc2626"
                  stroke-width="2"
                />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table
          class="min-w-[320px] border-collapse text-sm"
          :style="{ width: `${lpsbTopWidth + 24}px` }"
        >
          <thead>
            <tr class="bg-muted/40 text-left"> 
              <th class="px-3 py-2 text-right font-semibold">Rohwert</th>
              <th class="px-3 py-2 text-right font-semibold">T-Wert</th>
              <!-- <th class="px-3 py-2 text-right font-semibold">C-Wert</th> -->
              <th class="px-3 py-2 text-right font-semibold">PR</th>
              <th class="px-3 py-2 text-right font-semibold">IQ</th>
            </tr>
          </thead>
          <tbody>
            <tr class="border-t">
              <td class="px-3 py-2 text-right font-mono">{{ totalScores.total }}</td>
              <td class="px-3 py-2 text-right">{{ totalScoreEntry?.t ?? '–' }}</td>
              <!-- <td class="px-3 py-2 text-right">{{ totalScoreEntry?.c ?? '–' }}</td> -->
              <td class="px-3 py-2 text-right">{{ totalScoreEntry?.pr ?? '–' }}</td>
              <td class="px-3 py-2 text-right">{{ iqFromT ?? '–' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
.lpsb-sheet {
  background: var(--background);
  border: 1px solid var(--border);
  display: inline-block;
  padding: 0 12px 12px;
}

.lpsb-grid {
  position: relative;
  display: grid;
  grid-template-columns: 44px 36px 26px 18px repeat(9, 26px);
  gap: 0;
  border: 1px solid var(--border);
  background: var(--background);
}

.lpsb-cell {
  border-right: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
  min-height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  padding: 2px 4px;
}

.lpsb-hatch {
  background: transparent;
}

.lpsb-label {
  justify-content: flex-start;
  font-weight: 600;
}

.lpsb-raw {
  font-family: ui-monospace, SFMono-Regular, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New',
    monospace;
  font-weight: 600;
}

.lpsb-tvalue {
  font-family: ui-monospace, SFMono-Regular, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New',
    monospace;
  font-weight: 600;
}

.lpsb-tvalue--primary {
  color: #dc2626;
}

.lpsb-tvalue--secondary {
  color: #065f46;
}

.lpsb-divider {
  border-top: 2px solid var(--foreground);
}

.lpsb-grid > :nth-child(13n) {
  border-right: none;
}

.lpsb-overlay {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.lpsb-svg {
  display: block;
  width: 100%;
  height: auto;
}

.lpsb-vertical {
  position: absolute;
  top: 0;
  height: 100%;
  width: 2px;
  background: #63b3ed;
}

.lpsb-vertical--mid {
  background: #111827;
}

.lpsb-chart {
  position: absolute;
  inset: 0;
}

.lpsb-ticks {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  align-items: stretch;
  height: 100%;
  width: 100%;
}

.lpsb-tick {
  border-right: 1px solid color-mix(in srgb, var(--foreground) 45%, transparent);
  height: 100%;
}

.lpsb-tick--active {
  border-right: 2px solid #dc2626;
}

.lpsb-score--boundary {
  box-shadow: inset 2px 0 0 #dc2626;
}

.lpsb-top {
  position: relative;
  margin-bottom: 0;
  padding: 0 12px;
  box-sizing: border-box;
}

</style>
