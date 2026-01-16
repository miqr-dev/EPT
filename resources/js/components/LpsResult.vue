<script setup lang="ts">
import { computed } from 'vue';
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
}>();

const testLabel = computed(() => props.testName ?? 'LPS');
const isLpsB = computed(() => props.testName === 'LPS-B');
const lpsbTValues = [30, 35, 40, 45, 50, 55, 60, 65, 70];
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
const column6Manual = computed<number | null>({
  get: () => {
    const raw = props.results?.column6_score;
    const parsed = raw === '' || raw === null || raw === undefined ? null : Number(raw);
    return Number.isNaN(parsed) ? null : parsed;
  },
  set: (val) => {
    if (!props.results) return;
    props.results.column6_score = val ?? null;
  },
});

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
  const col14Wrong = col14Stats.negative;

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
const lpsbStepT = 5;

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
  lpsbPoints.value
    .map((point) => (point ? `${point.x},${point.y}` : null))
    .filter((point) => point !== null)
    .join(' '),
);

const lpsbPointsFiltered = computed(() => lpsbPoints.value.filter((point): point is { x: number; y: number } => !!point));
const lpsbPrimaryKeys = new Set<LpsBScoreKey>(['test_1_2', 'test_3_4', 'test_5_6', 'test_7_10', 'test_11_12', 'test_13_14']);
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

const lpsbDividerKeys = new Set<LpsBScoreKey>([
  'test_1_2',
  'test_3_4',
  'test_5_6',
  'test_7_10',
  'test_11_12',
  'test_13_14',
  'test_14_wrong',
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
        <span class="text-xs text-muted-foreground">
          Tragen Sie den Wert aus der Ergebnisansicht für Spalte 6 ein.
        </span>
      </div>

      <div class="overflow-x-auto">
        <div class="lpsb-top" :style="{ width: `${lpsbTopWidth + 24}px` }">
          <svg xmlns="http://www.w3.org/2000/svg" class="lpsb-svg" :width="lpsbTopWidth" viewBox="0 0 790 332"><path d="m612.62 123.25 3.18 19.91-.21.59-.04.06-.06.05-.07.01-.07-.01-.02-.01-.25-.15-.11-1.73-.16-1.45-.09-.63-.11-.56-.11-.49-.13-.42-1.27-3.58-1.32-3.52-1.37-3.46-1.41-3.41-1.46-3.36-1.51-3.29-1.55-3.24-1.6-3.19-.83-1.63-.74-1.53-.66-1.42-.58-1.32-.53-1.23-.54-1.2-.83-1.76-.92-1.83-1.05-2.02-1.55-2.89-2.33-4.35-.51-.91-.59-.98-.69-1.04-.77-1.11-.87-1.18-.95-1.25-1.05-1.32-1.13-1.38-1.09-1.32-1.14-1.42-2.8-3.6-2.51-3.21-1.87-2.31-.9-1.06-.88-1-.87-.96-.87-.93-1.97-2.02-2.03-2-2.1-2-2.15-1.98-2.21-1.98-2.27-1.97-2.34-1.96-2.39-1.95-8.86-5.62-1.24-.75-.86-.47-.87-.45-.91-.43-.95-.42-.98-.41-1.09-.42-2.93-1.07-2.71-.98-2.12-.81-.59-.22-.61-.18-.62-.15-.62-.11-2.44-.38-3.07-.48-2.4-.35-2.21-.27-2.02-.2-1.91-.14-1.76-.07-1.63-.01-1.47.07-2.76.22-2.76.25-2.76.3-2.75.33-2.77.36-2.76.41-2.76.44-2.76.48-.37.08-.35.1-.36.13-.34.15-1.61.73-2.23.92-2.01.82-1.48.65-1.37.66-1.26.68-1.05.59-5.12 2.91-3.57 2.08-3.11 1.89-1.42.89-1.35.89-1.35.9-1.29.9-1.25.9-1.21.91-1.18.92-1.15.93-1.12.94-1.1.97-.76.68-2.26 2.09-2.07 1.94-1.92 1.85-1.8 1.76-1.71 1.73-1.63 1.67-1.54 1.64-1.48 1.62-1.43 1.62-1.36 1.6-1.3 1.59-1.25 1.58-1.2 1.57-1.14 1.58-1.09 1.57-1.04 1.57-1.59 2.44-1.98 3.05-1.4 2.21-1.22 1.99-1.08 1.83-.99 1.77-.87 1.69-.79 1.63-.69 1.58-.42.98-.49 1.06-1.31 2.68-1.16 2.36-.84 1.82-.74 1.76-.65 1.74-.18.53-.58 1.67-.6 1.67-.63 1.68-.65 1.69-.68 1.69-.71 1.7-.73 1.71-.75 1.71-.13-.68-.08-.68-.01-.46.02-.47.05-.46.07-.47.54-2.77.22-2 .25-1.92.26-1.85.28-1.78 2.02-5.99 1.87-5.66.2-.51.31-.71.44-.91.57-1.11 1.49-2.8 1.98-3.59.68-1.19.72-1.21.77-1.25.83-1.32 1.84-2.78 2.55-3.72 2.12-3.08 1.48-2.22 1.3-1.92 1.37-1.91 1.45-1.92 1.53-1.91 1.61-1.92 1.67-1.9 1.76-1.91 1.82-1.89 1.91-1.89 1.97-1.88 2.04-1.87 2.1-1.86 2.17-1.84 2.23-1.82 2.29-1.81 2.33-1.77 1.09-.8 1.12-.79 1.15-.79 1.18-.78 1.22-.79 1.25-.78 2.65-1.57 2.77-1.56 3.08-1.66 3.46-1.8 4.85-2.46.65-.33 1.34-.65 1.33-.6 1.32-.55 1.31-.5 1.31-.44 1.29-.39 1.29-.34 1.27-.28 2.89-.56 2.81-.49 2.75-.43 2.67-.36 2.61-.3 2.53-.24 2.47-.18 2.39-.11 1.39-.03.96.01.96.04.96.05.97.08.98.1 1.01.12 2.09.32 2.14.4 2.39.51 2.71.65 3.91.98 3.03.77 1.51.4 1.49.45 1.46.49 1.44.54 1.41.58 1.39.63 1.36.66 1.34.72.85.48.84.5.83.51.82.54 1.81 1.23 1.8 1.26 1.77 1.27 1.76 1.3 1.74 1.33 1.72 1.34 1.7 1.37 1.68 1.39 1.56 1.33 1.54 1.35 1.53 1.37 1.51 1.39 1.5 1.4 1.48 1.43 1.46 1.45 1.44 1.47 1.03 1.08 1.03 1.11 1.02 1.14 1.02 1.17 1 1.2 1.01 1.22 1 1.26.99 1.28 3.12 4.02 2.13 2.73 1.46 1.95 1.28 1.82 1.16 1.77.69 1.12 1.15 1.96 1.14 1.96 1.11 1.98 1.09 1.99 1.08 1.99 1.05 2.01 1.03 2.01 1.02 2.03 1.35 2.78 1.31 2.8 1.28 2.81 1.24 2.84 1.2 2.85 1.17 2.87 1.13 2.89z"/><path d="m523.84 120.03-.15.73-.12.76-.09.77-.06.81-.03.82-.01.85.03.87.06.89.02.34-.01.32-.04.3-.05.28-.09.25-.1.23-.13.21-.15.19-.25.23-.29.18-.34.14-.38.1-.42.05-.47.02-.5-.03-.54-.07-.61-.12-.64-.17-.67-.2-.69-.25-.71-.29-.69-.32-.64-.33-.53-.31-.31-.23-.28-.27-.24-.31-.2-.33-.16-.35-.11-.38-.05-.38-.01-.39.13-2.8.1-2.73.07-2.65.05-2.58.01-2.51-.02-2.44-.04-2.37-.08-2.29-.01-.58.03-.63.05-.62.08-.62.1-.58.13-.55.15-.52.18-.48.19-.44.2-.39.24-.37.24-.31.27-.28.28-.24.3-.19.32-.16.43-.14.45-.07 1.48-.09 1.54-.02 1.55.03 1.52.09 1.43.15 1.38.2 1.29.25 1.2.31 1.07.34.99.4.45.21.43.22.4.23.39.25.35.26.33.27.3.28.28.29.25.3.23.31.2.32.17.33.23.52.21.52.19.52.16.52.13.53.11.52.08.53.06.54.04.92-.03.93-.11.94-.19.94-.26.95-.34.96-.41.96-.49.97-.21.46-.15.48-.06.31-.03.31v.32l.03.32.06.33.09.34.12.33.15.36.17.34.2.36.5.77.63.85.94 1.15.77.94.54.69.44.62.37.58.29.56.23.54.15.51.09.5v.42l-.07.41-.14.4-.21.37-.28.34-.33.29-.38.24-.41.18-.92.28-.87.21-.83.12-.78.04-.38-.01-.37-.03-.35-.05-.34-.07-.34-.09-.32-.1-.31-.13-.31-.15-.38-.24-.37-.26-.35-.31-.33-.34-.31-.38-.3-.41-.27-.45-.25-.49-.8-1.63-.76-1.5-.74-1.38-.71-1.26-.13-.17-.17-.14-.2-.09-.21-.04-.22.01-.21.07-.18.12-.15.15-.09.16zm2.32-9.65 1.28-2.48.05-.13.03-.13v-.14l-.02-.13-.05-.12-.07-.12-.09-.1-.11-.08-.05-.02-.25-.13-.81-.38-.41-.15-.41-.14-.4-.11-.39-.08-.37-.06-.36-.03-.34-.01-.31.02-.29.05-.25.08-.22.1-.19.12-.15.15-.11.17-.06.1-.07.19-.03.21v.23l.05.23.09.25.12.26.17.27.2.27.23.28.27.27.29.27.32.26.35.26.36.24.78.44.25.13.13.05.13.03h.14l.14-.03.12-.05.12-.08.1-.09.08-.12zm-52.47-13.32 3.49.27.29.04.29.06.28.1.27.12.26.14.24.17.22.19.21.22.18.23.16.25.13.26.11.28.08.28.05.29.03.29-.01.3-.12 1.85-.09 1.82-.07 1.78-.05 1.75-.02 1.71v1.68l.02 1.64.05 1.61.07 1.57.1 1.54.12 1.5.14 1.46.16 1.43.19 1.4.21 1.36.24 1.32.04.28.01.3-.02.31-.05.3-.08.3-.11.28-.14.27-.17.26-.2.24-.21.21-.24.19-.26.17-.28.14-.28.11-.3.08-.3.04-.31.02h-2.9l-.29-.01-.28-.04-.28-.06-.28-.09-.26-.11-.26-.13-.24-.15-.23-.17-.21-.2-.2-.21-.17-.22-.16-.24-.13-.25-.12-.27-.08-.27-.07-.28-1.06-5.66-.09-.32-.13-.29-.19-.27-.22-.24-.26-.2-.29-.15-.32-.11-.32-.05-5.77-.45h-.16l-.17.03-.15.06-.14.09-.12.1-.11.13-.08.14-.05.16-.22.96-.16.94-.12.93-.07.91-.02.9.03.89.07.88.13.86.03.23.01.28-.02.28-.05.28-.07.27-.11.26-.13.24-.15.24-.18.21-.2.2-.22.17-.24.16-.25.12-.26.1-.27.07-.28.05-.28.01h-1.61l-.28-.01-.28-.05-.27-.07-.27-.09-.25-.13-.24-.15-.23-.17-.2-.19-.19-.21-.16-.23-.14-.25-.11-.26-.09-.27-.06-.27-.03-.28-.01-.28.01-.11.43-5.71v-.27l-.73-21.56v-.09l.04-.34.11-.32.17-.29.23-.26.28-.2.31-.14.33-.08.34-.01 3.69.28.33.06.31.11.28.17.25.23.2.26.15.29.09.32.03.33v11.11l.01.12.03.11.06.1.07.09.09.07.1.06.11.03.11.01h7.74l.29-.03.28-.08.26-.14.23-.19.19-.23.14-.26.08-.28.03-.29V99.05l.02-.21.04-.2.06-.2.08-.19.1-.19.11-.17.14-.16.15-.15.16-.13.17-.11.19-.1.19-.08.21-.05.2-.04.21-.02zm76.28 20.55-.11-.03-.09-.01-.09.02-.08.04-.06.06-.06.07-.03.08-.02.09.01.09 1.41 7.52.04.3v.25l-.03.25-.05.24-.08.23-.1.23-.13.21-.14.2-.17.18-.19.17-.2.14-.22.12-.22.1-.24.07-.25.04-.24.03-.25-.01-5.3-.41-.26-.03-.24-.06-.24-.08-.23-.1-.22-.13-.21-.14-.19-.17-.18-.19-.15-.2-.14-.21-.11-.23-.09-.24-.07-.24-.04-.25-.02-.26.01-.25.13-1.91.11-2.13.09-2.36.08-2.58.05-2.82.04-3.04.01-6.76.01-.69.03-.66.06-.63.07-.59.1-.58.13-.54.14-.51.17-.48.12-.26.17-.24.2-.21.22-.18.26-.14.27-.1.28-.06.29-.01.62.04.59.07.57.1.56.14.53.17.51.21.49.24.47.27.58.41.55.47.51.52.48.59 1.67 2.26 1.49 2.07 1.3 1.85 1.13 1.66.33.43.35.35.19.14.2.13.2.1.21.08.25.07.28.03.28-.03.27-.09.24-.15.2-.19.15-.23.1-.27.04-.27-.01-.24-.02-.16-.3-2.02-.17-1.5-.05-.69-.02-.64v-.6l.02-.57.05-.55.07-.52.1-.49.12-.45.15-.42.18-.4.2-.36.23-.34.01-.01.2-.22.23-.19.27-.16.29-.13.33-.1.35-.07.38-.04.41-.01.43.02.45.06.46.1.44.12.44.15.4.17.37.2.32.21.26.22.24.26.22.31.22.36.19.4.17.44.16.5.15.53.13.59.1.63.1.67.07.7.05.74.03.76.01.77-.02.71-.05 1.85v1.83l.06 1.8.11 1.79.16 1.76.21 1.74.27 1.71.32 1.7.11.65.07.64.03.61-.02.6-.06.58-.1.56-.15.54-.2.52-.24.51-.28.49-.34.46-.18.2-.19.18-.2.16-.2.13-.22.12-.22.09-.33.1-.33.05-.36.02-.36-.03-.38-.07-.39-.11-.4-.15-.42-.19-.43-.24-.43-.27-.45-.32-.44-.35-.45-.39-.42-.41-.41-.43-.37-.43-1.05-1.23-1.09-1.16-.56-.54-.56-.53-.58-.52-.59-.49-.59-.47-.61-.46-.61-.43-.62-.42-.64-.4-.64-.38-.65-.36-.66-.34zm-39.91.09.014.551-.013.55-.04.55-.066.547-.093.542-.12.538-.146.53-.172.524-.198.514-.222.504-.247.492-.27.48-.294.465-.317.451-.338.435-.36.417-.378.4-.398.38-.417.36-.434.34-.45.318-.464.295-.479.272-.491.249-.503.224-.514.199-.523.173-.53.148-.537.121-.543.095-.546.069-.55.04-1.817.093-.55.014-.551-.012-.55-.04-.546-.067-.543-.093-.537-.12-.531-.146-.524-.172-.514-.197-.503-.223-.493-.246-.48-.271-.465-.294-.45-.317-.435-.338-.418-.359-.4-.379-.38-.398-.36-.416-.34-.434-.317-.45-.296-.465-.272-.478-.248-.492-.224-.503-.199-.514-.174-.522-.147-.53-.122-.538-.095-.542-.068-.547-.041-.549-.45-8.889-.015-.55.013-.55.04-.55.066-.547.093-.542.12-.538.146-.53.172-.524.198-.514.222-.504.247-.492.27-.48.294-.466.317-.45.338-.435.36-.417.378-.4.398-.38.417-.36.434-.34.45-.318.464-.295.479-.272.491-.249.503-.224.514-.199.523-.173.53-.148.537-.121.543-.095.546-.069.55-.04 1.817-.093.55-.014.551.012.55.04.546.067.543.093.537.12.531.146.524.172.514.197.503.223.493.246.48.271.465.294.45.317.435.338.418.359.4.379.38.398.36.417.34.433.317.45.296.465.272.479.248.491.224.503.199.514.174.522.147.53.122.538.095.542.068.547.041.549zm-7.73 1.59.28-.92.23-.95.19-.94.13-.94.08-.92.04-.9-.03-.87-.07-.82-.13-.77-.17-.72-.22-.69-.28-.62-.32-.58-.36-.52-.42-.47-.46-.41-.51-.37-.56-.3-.61-.25-.65-.18-.7-.13-.73-.06-.79-.01-.82.06-.3.05-.29.09-.27.14-.25.16-.23.2-.2.23-.16.26-.13.27-.25.7-.22.7-.19.7-.17.7-.13.7-.1.7-.07.71-.04.7-.02.9.04.9.09.91.13.9.19.85.12.41.13.39.16.37.16.36.19.34.2.33.22.3.23.28.24.25.25.23.27.21.28.19.29.16.3.14.29.11.31.09.3.06.32.04.32.02h.34l.33-.02.35-.05.69-.14.63-.16.56-.21.5-.23.43-.27.36-.31.29-.34.24-.37zm122.9 30.511-.003-.005c-1.15-1.768-2.166-6.537-3.977-10.709-1.704-3.925-5.68-10.869-7.862-15.348l-.058-.029-.71-.46 2.922 18.294q.084.102.173.227c1.135 1.606 2.454 7.024 4.13 10.556 1.76 3.708 4.396 8.359 6.272 11.167q.262.39.539.748l.144-.242-.25-.91-.22-.92-.21-.92-.18-.92-.23-1.35-.2-1.35-.14-1.36-.11-1.37-.07-1.38-.02-1.39.02-1.39Zm.152 66.511c-1.089.326-3.725.569-5.044.876-1.018.236-1.941.262-2.448.731-.448.415-.519 1.193-.619 1.773-.094.544-.185 1.206 0 1.603.164.352.525.592.928.732.484.168 1.287-.047 1.857.056.53.096.791.39 1.463.507 1.01.174 3.363.169 4.277.168q.16-.001.284-.017v-.001l-.21-.29-.19-.31-.15-.29-.14-.31-.11-.31-.1-.33-.15-.68-.07-.72.01-.76.07-.81.15-.84Zm.414 23.363c-.212.274-.435.577-.587.959-.382.961-.817 3.379-.81 3.779.002.109.063.017.068.135.019.495-.407 3.349-.878 5.332-.559 2.353-3.101 6.604-2.429 7.93.427.843 2.276.498 3.476.613l-.026-.093-.01-.29.03-.31.08-.34.33-1.16.27-1.07.21-.99.16-.9.11-.82.04-.74-.01-.66-.06-.57-.17-1.08-.13-1.08-.09-1.07-.06-1.07-.02-1.07.02-1.06.06-1.06.09-1.05.16-1.21Zm-.812 59.072c-.792 3.576-1.353 9.053-1.723 12.52-.349 3.261-.621 6.668-.498 8.453.064.923.039 1.386.374 1.885 1.137.078 2.158.187 2.917.333l-.174-.428-.2-.55-.16-.51-.12-.46-.09-.44-.06-.39-.01-.35.02-.31.13-1.01.11-1.04.16-2.11.09-2.19.01-2.26-.06-2.33-.13-2.4-.21-2.48-.29-2.54-.05-.54-.03-.53ZM405.74 123.67l-.22 2-.54 2.77-.07.47-.05.46-.02.47.01.46.08.68.054.284a34 34 0 0 0-1.908 3.742c-.927 2.145-1.076 4.597-2.213 7.103-1.322 2.917-4.128 6.4-5.589 9.316l-.154.308.24-1.243.27-1.69.22-1.73.17-1.77.12-1.8.03-.92-.03-.86-.08-.81-.03-.188c.901-1.477 1.166-4.693 2.505-7.356 1.489-2.963 5.473-7.96 7.379-11.034Zm.67 97.94-.18.53-.15.54-.12.56-.1.56-.06.57-.04.59v.59l.02.61.3 5.27-.1 4.21-.03 3.69.04 3.15.04 1.38.06 1.25.04 1.13v1.28l-.03 1.46-.08 2.04-.11 2.89-.02 2.13.02.99.04.95.06.89.08.87.01.24-.02.24-.04.25-.07.23-.26.76-.21.75-.16.74-.083.562c-.851.095-1.939.104-3.048.165-1.955.108-5.807-.108-8.323-.105l.094-.072.12-.12.12-.15.1-.18.09-.2.16-.48.21-.94.15-.84.07-.76v-.65l-.03-.3-.04-.27-.06-.25-.08-.22-.1-.21-.11-.17-.13-.16-.15-.13-.16-.1-.17-.09-.19-.06-.2-.04-.21-.02h-.118l.208-.04.24-.07.23-.08.2-.11.19-.13.18-.14.13-.15.13-.16.21-.36.15-.43.002-.01.129.017c.84.092 2.54-.055 3.649-.356 1.044-.283 2.199-.873 2.848-1.335.468-.333.633-.513.935-1.023.437-.741 1.117-2.183 1.246-3.293.125-1.086-.172-2.359-.445-3.293-.235-.803-.45-1.436-.979-2.047-.608-.701-1.672-1.508-2.626-1.825-.933-.309-2.108-.128-3.026-.089-.813.035-1.509.062-2.225.267-.224.064-.466.136-.705.218l-.153-.421-.32-.8-.35-.79-.37-.78-.11-.27-.07-.29-.03-.29.01-.29.08-.38.16-.42.24-.46.752-1.235.017.003c.554.092 1.393.019 2.063-.122q.237.087.453.13c.88.173 1.877.026 2.848-.178 1.043-.219 2.431-.524 3.204-1.157.707-.579 1.157-1.475 1.379-2.358.235-.931.1-2.313-.089-3.115-.144-.613-.298-.954-.712-1.424-.542-.616-1.532-1.317-2.536-1.825-1.133-.572-2.897-1.142-4.005-1.335-.844-.146-1.529-.129-2.136 0q-.15.032-.292.071l-.076-.215.52-.92.41-.87.31-.81.11-.4.09-.37.07-.37.03-.36.02-.34-.02-.33-.04-.31-.06-.31-.1-.29-.11-.28-.15-.28-.19-.27-.21-.26-.23-.24-.27-.23-.3-.21-.33-.2-.069-.037.059.017.12-.01.12-.05.12-.08.13-.11.079-.109c1.413.021 3.34-.198 4.054-.103.452.06.34.03.801.267 1.164.598 5.575 4.265 7.208 4.628q.192.042.367.055Zm-.33 71.21-.17.52-.13.52-.1.52-.07.52-.02.52v.51l.04.49.08.5.11.49.14.48.17.49.21.49.24.48.27.48-.33 1.49-.29 1.54-.24 1.61-.2 1.66-.15 1.72-.11 1.78-.06 1.84-.02 1.9.01.46.04.44.07.43.09.41.12.4.14.38.16.37.2.35.35.52.41.49-.27-.16-.24-.09-.23-.03-.2.03-.12.05-.12.08-.1.11-.1.14-.17.36-.13.47-.09.51-.04.5v.5l.05.49.09.44.12.43.16.43.2.42.24.42.28.41.32.41.35.4-.32-.08-.28-.02-.26.03-.196.071a4.4 4.4 0 0 1-.741-.184c-.991-.356-2.221-.833-3.026-1.602-.818-.781-1.381-1.783-1.824-3.07-.547-1.587-.901-5.277-.89-5.963.003-.201.084-.122.089-.267.009-.286-.217-.766-.267-1.513-.087-1.292-.047-4.355.089-6.051.109-1.365.046-2.042.534-3.471.709-2.079 2.827-6.537 4.094-7.965.72-.812 1.568-.755 2.092-1.169Zm-12.828-35.66-.052.01-.28.04-.3.01.51-.06Zm.109-40.077-.111-.033-.17-.12Z"/><path d="m393.79 156.92-.75 2.17-.73 2.03-.73 1.89-.72 1.76-.7 1.6-.7 1.47-.68 1.33-.68 1.19-7.14 11.87-1.2 1.91-1.23 1.89-1.24 1.88-1.26 1.85-1.29 1.84-1.3 1.82-1.32 1.8-1.35 1.78-1.36 1.76-1.38 1.74-1.41 1.72-1.42 1.7-1.44 1.69-1.46 1.66-1.49 1.65-1.5 1.63-2.11 2.24-2.05 2.13-1.99 2.03-1.92 1.94-1.88 1.82-1.81 1.73-1.75 1.63-1.69 1.52-2.58 2.24-2.66 2.2-2.74 2.18-2.81 2.14-2.9 2.11-2.97 2.08-3.06 2.05-3.13 2.02-1.62.98-1.82 1.01-2.03 1.05-2.83 1.41-2.3 1.14-1.69.86-1.52.8-1.39.78-.17.08-.18.06-.33.05-.33-.03-.32-.1-.28-.17-.24-.23-.18-.28-.11-.32-.04-.33v-.61l.02-.35.07-.35.11-.34.15-.32.19-.3.23-.27.26-.24.29-.2 3.84-2.31 5.27-3.1 4.3-2.53 3.12-1.87 2.81-1.71 2.55-1.6 2.45-1.59 2.3-1.55 2.18-1.53 2.07-1.52.95-.76 1.01-.91 1.09-1.08 1.46-1.53 1.11-1.17.83-.84.77-.75.72-.66 1.75-1.58 1.74-1.61 1.73-1.65 1.71-1.68 1.71-1.73 1.69-1.77 1.7-1.82 1.69-1.86 1.69-1.92 1.69-1.96 1.71-2.03 1.71-2.1 1.73-2.16 1.77-2.25 1.8-2.34 1.84-2.45 1.04-1.41 1.02-1.42 1-1.43.98-1.45 1.62-2.46 1.56-2.5 1.52-2.53 1.47-2.56 1.41-2.6 1.37-2.63 1.32-2.67 1.26-2.7 2.87-7.23.7-1.69.79-1.79.9-1.91.99-2.02.32-.69.29-.74.26-.79.23-.83.2-.89.18-.94.14-.98.12-1.04.3-.48.08-.07.1-.03.1.02.08.07.03.08.12.75.08.81.03.86-.03.92-.12 1.8-.17 1.77-.22 1.73-.27 1.69-.32 1.66-.37 1.63-.41 1.59zm155.15-12.31-13.44 42.96-.11.28-.14.27-.18.25-.22.22-.24.19-.26.16-.28.13-.29.08-2.05.46-.3.04-.3.01-.31-.03-.29-.08-.29-.11-.27-.14-.24-.18-.23-.22-.19-.23-.16-.26-.12-.28-.09-.3-.04-.3-.01-.31.03-.3.07-.3 14.07-45.06.12-.29.15-.27.18-.25.22-.23.24-.19.27-.16.29-.12.3-.08.31-.04h.31l.31.04.3.08.29.12.27.16.24.19.22.23 1.4 1.65.15.19.13.21.1.21.08.23.08.34.02.35-.03.34zm-35.99 9.35-.07.48v.48l.06.4.12.38.16.37.22.37.28.37.32.36.38.35.42.34.95.71 2.3 1.74.85.68.74.61.7.61.64.6.58.57.54.57.5.59.46.6.41.59.37.59.32.6.28.6.25.63.2.63.14.56.11.57.08.58.05.6.02.61-.01.62-.05.62-.07.65-.13.8-.18.76-.21.73-.25.68-.3.65-.34.61-.38.58-.42.54-.52.57-.58.52-.64.48-.69.43-.74.4-.8.34-.87.31-.91.26-1.05.25-1.03.2-.99.16-.96.12-.93.07-.89.02-.85-.02-.82-.06-.78-.11-.74-.16-.7-.2-.65-.24-.62-.29-.58-.33-.54-.38-.5-.42-.51-.52-.46-.58-.41-.64-.36-.69-.14-.33-.11-.32-.08-.32-.06-.3-.02-.3v-.29l.03-.29.06-.27.1-.28.12-.27.15-.26.19-.25.21-.24.25-.24.27-.22.31-.22.25-.14.26-.09.28-.04h.28l1.2.16 1.73.29 1.61.28 1.18.17 1.06.11.96.04.9-.02.84-.08.77-.16.36-.1.35-.12.19-.08.17-.11.15-.12.12-.13.12-.14.09-.16.08-.16.06-.18.04-.18.02-.18-.01-.18-.02-.18-.05-.18-.06-.17-.09-.16-.1-.16-.12-.13-.13-.13-2.12-1.7-1.96-1.58-1.44-1.2-1.25-1.11-1.08-1.04-.49-.52-.46-.5-.42-.49-.37-.47-.35-.47-.3-.46-.28-.46-.23-.44-.36-.76-.31-.75-.27-.75-.23-.73-.19-.74-.14-.72-.11-.72-.06-.71-.02-.7.02-.68.06-.68.11-.65.15-.65.18-.63.24-.61.27-.6.23-.44.25-.42.28-.41.29-.39.32-.39.34-.37.36-.35.38-.34.4-.32.42-.31.44-.29.45-.27.47-.25.48-.24.5-.21.51-.2.52-.17.53-.16.55-.13.56-.12.56-.09.57-.07.58-.06.59-.03.59-.01.59.02.59.03.6.05.6.08.61.1.6.11.6.14.29.09.27.11.27.16.26.18.24.21.22.24.2.28.19.3.16.32.15.35.12.38.1.4.08.42.04.41.02.43-.01.41-.03.39-.07.37-.09.29-.13.27-.16.26-.2.22-.23.2-.25.17-.28.12-.29.09-.72.15-.75.14-1.63.24-1.8.19-1.95.14-.3.04-.27.07-.24.1-.21.14-.22.21-.17.25-.15.3zm34.81 22.37.05-.31.09-.31.13-.29.18-.26.21-.24.25-.2.27-.17.29-.13 3.54-1.24.28-.14.24-.19.2-.25.13-.28.07-.3v-.31l-.07-.31-.14-.28-.19-.24-.33-.36-.27-.39-.23-.4-.17-.41-.11-.41-.07-.42-.03-.43.02-.45.07-.46.11-.48.15-.5.2-.51 4.04-9.41 4.22-9.67.2-.39.25-.36.3-.33.33-.29.37-.24.4-.2.42-.14.43-.09.59-.07.61-.05.61-.03h.59l.56.02.54.05.5.08.47.09.43.12.39.14.36.17.33.18.28.21.26.23.22.25.18.28.17.34 6.34 15.94 2.54 6.33 2.12 5.23.2.46.23.48.54.98.65 1.01.75 1.06.32.45.28.42.24.39.18.37.14.34.1.31.05.28v.26l-.07.47-.12.42-.18.38-.23.32-.26.27-.3.23-.35.19-.39.15-.43.11-.48.07-.52.04-.55-.01-.61-.04-.65-.09-.68-.13-.69-.17-.73-.2-.71-.24-.7-.27-.63-.28-.18-.1-.18-.12-.15-.13-.15-.15-.13-.16-.11-.18-.09-.19-.07-.19-.26-.8-.29-.8-.3-.78-.31-.73-.31-.69-.33-.65-.33-.6-.34-.55-.33-.49-.34-.44-.35-.39-.34-.34-.35-.29-.34-.23-.35-.19-.36-.13-.44-.12-.49-.1-1.11-.19-1.25-.12-1.32-.07-1.31-.01-1.25.05-1.13.11-.51.08-.48.09-.4.11-.38.16-.37.21-.33.24-.31.29-.26.31-.23.35-.19.37-.78 1.95-.56 1.41-.42.97-.41.84-.42.69-.44.62-.24.28-.24.25-.25.22-.27.21-.26.18-.29.17-.44.2-.47.16-.5.11-.53.07-.55.02-.6-.02-.62-.07-.66-.12-.35-.11-.33-.16-.3-.23-.25-.27-.19-.32-.14-.34-.07-.37-.01-.37zm20.33-13.18-.11-.52-.14-.49-.17-.47-.19-.44-.22-.42-.25-.4-.27-.37-.29-.34-.28-.29-.31-.26-.32-.25-.34-.23-.21-.1-.23-.06-.24-.02-.24.04-.22.08-.2.12-.18.16-.13.19-.02.03-.16.32-.14.35-.13.38-.11.42-.1.44-.08.48-.12 1.04v.23l.05.23.09.21.13.19.16.17.19.13.21.1.23.05 1.27.14 1.06.07.46-.01.4-.02.36-.04.3-.06.12-.04.11-.07.09-.08.08-.1.06-.12.03-.12.01-.12zm-88.01-18.31.88-.01.88.03.89.05.88.09.89.11.9.15.89.17.9.21.9.24.91.26.9.3.91.33.92.36.91.38.92.42.92.45.32.18.3.2.29.23.26.26.76.85.7.89.65.93.58.96.52.97.44 1 .38 1.02.31 1.03.23 1.02.15 1.03.09 1.02v1.02l-.07 1-.15.99-.22.96-.29.94-.33.83-.39.8-.44.78-.51.75-.55.72-.62.7-.67.67-.72.63-.21.16-.22.14-.24.12-.24.1-6.69 2.36-.23.1-.21.13-.18.16-.17.18-.13.21-.1.23-.07.24-.03.24-.07.96-.11 1.17-.35 2.98-.09.55-.15.52-.19.49-.23.45-.28.42-.33.37-.36.33-.4.28-.44.23-.45.17-.49.11-.48.05-.49-.02-.48-.08-.46-.14-.44-.2-.25-.15-.23-.17-.22-.19-.21-.2-.19-.22-.17-.24-.16-.25-.15-.27-.12-.29-.11-.3-.09-.3-.07-.33-.09-.67-.02-.72.08-4.06.05-4.06.03-4.08.01-4.09-.03-4.11-.05-4.11-.07-4.13-.1-4.14.02-.35.1-.34.16-.32.21-.28.27-.23.3-.18.34-.11zm6.19 11.92.31 6.73.03.26.09.24.13.22.17.2.21.15.24.11.25.07.25.01 1.03-.05.35-.03.35-.06.34-.1.32-.12.32-.15.29-.18.27-.21.26-.23.22-.25.2-.27.17-.29.13-.3.11-.31.07-.32.04-.32v-.33l-.13-2.75-.03-.33-.06-.32-.1-.31-.14-.3-.16-.28-.19-.27-.22-.25-.25-.23-.27-.21-.29-.18-.31-.15-.33-.13-.33-.09-.35-.07-.35-.03h-.35l-1.03.05-.26.04-.24.08-.22.14-.2.17-.15.21-.11.23-.07.25zm-37.4-9.63h4.29l.33.03.31.1.29.15.26.21.21.26.15.29.1.32.03.33v25.04l.02.23.07.23.1.21.15.19.17.16.21.12.22.08.23.04 11.25.87.22.03.22.06.21.08.19.11.2.15.18.17.16.19.12.22.1.23.06.24.03.24v.25l-.33 4.27-.06.37-.13.35-.2.32-.25.28-.29.23-.34.17-.36.11-.37.04-1.99.03-1.93.06-1.89.08-1.84.1-1.8.13-1.74.15-1.7.17-1.65.19-.82.08-.76.02-.68-.04-.62-.09-.55-.16-.49-.21-.21-.12-.21-.14-.18-.16-.17-.17-.17-.2-.14-.22-.13-.24-.11-.26-.09-.27-.07-.29-.05-.31-.04-.32v-.71l.06-.78.14-.87.22-.93.12-.49.1-.51.08-.52.06-.54.06-1.13-.01-1.18-.24-6.7-.16-6.43-.08-6.17-.01-2.98.01-2.92.04-.38.11-.36.18-.33.24-.29.29-.24.33-.17.36-.11zm176.38 2.21.68 1.92.7 1.91.73 1.89.76 1.88.79 1.86.81 1.85.84 1.83.86 1.82.57 1.13.63 1.18.7 1.25.79 1.34.84 1.4.95 1.53 2.58 4.01 2.21 3.44.79 1.23.82 1.23.85 1.23.87 1.23.89 1.23.92 1.24 1.92 2.46 2.03 2.47 2.12 2.48 2.23 2.47 2.33 2.48 2.89 2.98 2.78 2.8 2.67 2.62 2.56 2.45 2.45 2.28 2.34 2.09 2.23 1.92 2.12 1.75 1.82 1.44 1.78 1.37 1.74 1.31 1.69 1.24 1.65 1.16 1.61 1.1 1.57 1.04 1.52.96 9.7 5.96 2.88 1.75 1.58.92 3.33 1.69 7.84 3.92.21.12.2.14.19.15.18.17.15.19.14.2.13.21.1.22.08.23.06.23.04.24.02.24v.25l-.03.24-.04.24-.07.23-.1.29-.16.35-.23.32-.28.26-.34.2-.36.12-.39.05-.38-.02-.38-.11-.09-.04-5.46-2.53-4.09-1.94-2.72-1.35-.84-.45-.5-.3-1.11-.76-1.09-.73-1.08-.68-1.07-.65-1.06-.62-1.04-.57-1.03-.54-1.01-.5-.4-.21-.39-.23-.38-.26-.36-.27-2.27-1.78-2.72-2.06-3.18-2.34-3.65-2.61-1.11-.81-1.14-.85-1.16-.91-1.18-.95-1.21-1.01-1.24-1.06-1.26-1.1-1.28-1.16-3.41-3.1-2.68-2.42-1.87-1.72-1.64-1.54-1.46-1.41-1.4-1.42-1.3-1.37-1.24-1.37-1.2-1.38-1.69-1.94-2.39-2.62-1.82-1.99-1.31-1.47-1.17-1.35-1.03-1.27-1.2-1.53-1.17-1.55-1.15-1.58-1.13-1.59-1.1-1.62-1.09-1.65-1.06-1.66-1.03-1.69-1.02-1.71-.99-1.74-.97-1.75-.95-1.78-.92-1.81-.9-1.82-.88-1.85-.86-1.87-.25-.91-.22-.92-.21-.92-.18-.92-.23-1.35-.2-1.35-.14-1.36-.11-1.37-.07-1.38-.02-1.39.02-1.39zm116.36 94.53-.04-.15-.07-.14-.09-.12-.12-.1-.14-.07-.14-.04-.16-.01-.15.02-.08.03-1.14.42-1.12.34-1.11.28-1.09.2-1.07.12-1.06.06-1.04-.02-1.02-.09-.3-.07-.42-.16-.54-.24-.65-.32-1.67-.9-2.14-1.23-.45-.28-.43-.29-.43-.32-.41-.34-.4-.36-.39-.37-.37-.39-.35-.41-.34-.42-.33-.44-.31-.46-.28-.46-.27-.48-.25-.48-.23-.5-.21-.51-.19-.5-.17-.52-.14-.51-.12-.53-.1-.53-.08-.53-.05-.52-.03-.52-.01-.53.01-.52.04-.51.06-.51.08-.51.11-.5.13-.49.15-.49.15-.44.7-2.05.6-1.56.3-.71.3-.67.31-.62.32-.58.32-.55.34-.51.34-.46.34-.43.36-.38.36-.34.37-.31.38-.26 1.06-.63 1.04-.53 1.04-.45 1.02-.36.51-.15.51-.13.52-.1.51-.09.5-.06.5-.04.51-.02h.51l.64.04.64.07.64.1.63.14.65.17.63.21.65.24.64.28.64.31.65.35.65.39.65.41.65.46.66.49.67.53.67.57.25.23.24.25.47.58.44.66.42.76.4.85.37.94.34 1.03.32 1.12.31 1.23.29 1.21.27 1.19.24 1.18.21 1.16.18 1.14.16 1.13.14 1.1.11 1.09.08 1.07.06 1.05.03 1.02v1.02l-.02.98-.05.98-.07.95-.1.94-.13.9-.15.9-.18.87-.2.85-.23.83-.25.81-.29.8-.3.77-.33.75-.36.73-.39.72-.41.68-.43.67-.46.65-.49.63-.7.82-.73.76-.79.74-.83.7-.68.51-.69.46-.71.41-.71.35-.73.31-.73.24-.75.2-.75.14-.66.08-.66.04h-.67l-.67-.04-.69-.08-.68-.11-.68-.16-.68-.2-.69-.24-.69-.28-.68-.31-.68-.35-.69-.4-.68-.42-.67-.47-.67-.5-.51-.43-.46-.46-.4-.49-.36-.53-.19-.33-.18-.34-.15-.35-.14-.36-.12-.37-.1-.38-.14-.8-.08-.85-.01-.89.07-.93.14-.98v-.01l.07-.24.11-.21.16-.2.19-.15.21-.12.24-.08.24-.02.25.02.69.16.72.21.76.29.81.34.84.41.93.48 1.04.58 1.44.83.22.13.53.28.52.23.52.18.52.14.53.09.52.04h.51l.51-.05.63-.14.63-.21.6-.28.58-.36.55-.43.52-.49.47-.55.43-.61.39-.65.33-.7.26-.72.21-.75.14-.74.07-.76v-.75zm-13.98-17.15-.18.53-.14.53-.11.54-.07.53-.04.53-.01.52.03.51.07.5.1.49.13.48.16.45.2.44.23.42.26.4.29.36.32.34.45.41.5.36.54.3.58.26.61.2.65.15.68.09.7.03.5-.02.54-.08.59-.13.61-.17.63-.23.64-.26.63-.3.6-.33.55-.33.5-.35.46-.37.4-.37.35-.38.28-.37.23-.39.17-.37.11-.39.06-.44.02-.47-.02-.51-.07-.54-.12-.58-.17-.59-.2-.61-.25-.63-.29-.63-.33-.62-.34-.57-.36-.53-.37-.48-.37-.4-.36-.33-.56-.42-.59-.37-.59-.3-.6-.24-.61-.17-.61-.11-.61-.05-.61.02-.46.07-.46.09-.45.14-.45.18-.43.21-.42.24-.4.28-.39.32-.37.34-.35.37-.33.41-.3.43-.29.46-.26.48-.23.5zm-102.14 32.73-.26-.13-.17-.14-.15-.16-.13-.21-.1-.23-.07-.25-.01-.29.03-.31.08-.34.33-1.16.27-1.07.21-.99.16-.9.11-.82.04-.74-.01-.66-.06-.57-.17-1.08-.13-1.08-.09-1.07-.06-1.07-.02-1.07.02-1.06.06-1.06.09-1.05.16-1.21.2-1.2.26-1.2.3-1.19 3.85-12.58.05-.24-.01-.24-.05-.24-.1-.22-.15-.2-.18-.16-.21-.12-.24-.07-.11-.02-3.15-.31-.21-.29-.19-.31-.15-.29-.14-.31-.11-.31-.1-.33-.15-.68-.07-.72.01-.76.07-.81.15-.84.22-.89.87-.15.86-.12.84-.1.83-.08.81-.05.79-.03h.78l.76.02.74.04.73.07.71.1.69.11.68.15.66.16.65.19.63.22.37.17.33.23.29.28.23.32.18.36.12.39.05.4-.01.4-.2 1.48-.23 1.42-.27 1.36-.32 1.33-.4 1.46-.48 1.46-.54 1.5-.62 1.54-3.02 7.22-1.05 2.59-.92 2.31-.86 2.22-.78 2.11-.72 2.05-.69 2.04-.63 1.94-.6 1.99-.6 2.04zM504.1 225.39v3.55l.04.36.1.34.17.32.22.28.28.24.32.17.34.11.36.04 7.47.12.32.02.32.07.75.24.79.28.79.31.78.35.74.36.72.39.67.39.63.42.58.41.54.43.48.43.44.44.39.45.35.45.3.47.24.46.31.7.29.72.27.74.24.76.21.78.19.8.17.81.14.81.1.82.09.82.05.81.02.82-.01.8-.03.8-.06.78-.1.77-.11.74-.15.73-.17.7-.2.69-.22.66-.25.64-.27.62-.3.6-.32.56-.35.55-.37.52-.4.49-.41.46-.44.44-.46.4-.49.39-.6.41-.64.38-.66.33-.7.3-.72.25-.75.2-.78.17-.81.12-1.73.18-1.5.11-1.36.02-.64-.01-.6-.04-.58-.05-.58-.08-.54-.09-.54-.12-.51-.13-.51-.16-.48-.18-.48-.2-.71-.35-.69-.41-.67-.46-.67-.52-.64-.58-.64-.64-.63-.71-.63-.77-.19-.28-.18-.34-.15-.38-.13-.43-.11-.48-.1-.53-.07-.58-.05-.63.02-.28.09-.27.15-.24.21-.19.54-.36.53-.29.52-.24.5-.18.5-.12.48-.06h.47l.46.06.34.09.33.11.33.16.31.18.31.22.31.25.3.28.29.32.45.49.46.44.47.41.49.36.5.31.51.28.52.23.54.18.46.13.46.09.48.06.48.04h.49l.5-.02.51-.06.52-.08.5-.12.42-.13.4-.16.39-.2.38-.23.38-.26.37-.29.36-.32.35-.36.35-.39.34-.42.33-.46.34-.49.33-.54.33-.57.71-1.34.13-.28.11-.29.15-.57.08-.6v-.61l-.08-.64-.15-.66-.22-.67-.3-.69-.37-.69-.45-.73-.53-.72-.59-.71-.64-.69-.69-.65-.71-.6-.71-.53-.69-.44-.22-.11-.23-.08-.24-.05-.24-.02-.59.01-.75.09-.75.16-.75.25-.75.31-.74.39-.77.48-.82.56-1.05.79-1.02.77-.72.47-.68.37-.67.3-.65.2-.31.07-.3.05-.29.02h-.29l-.27-.02-.26-.04-.25-.06-.23-.09-.22-.11-.21-.13-.17-.14-.17-.15-.14-.17-.14-.19-.22-.42-.17-.48-.11-.53-.06-.59.01-.63.06-.68.17-1.56.08-1.46.01-.69-.01-.66-.03-.64-.05-.61-.15-1.61-.1-1.55-.04-1.07-.02-1.12.03-2.87.03-2.44-.01-2.04v-.33l.02-.31.07-.29.12-.29.16-.25.21-.23.24-.19.26-.16.29-.1.53-.14.55-.11.57-.1.6-.08.63-.07.65-.04 1.38-.04 6.18-.01 4.73-.03 3.28-.05 1.84-.07h.23l.23.02.23.04.22.07.22.09.2.1.2.13.17.15.17.16.14.18.13.2.11.2.08.22.07.22.04.23.02.23v1.99l-.01.25-.04.24-.06.25-.08.23-.11.22-.13.22-.14.2-.17.18-.19.17-.2.15-.21.13-.23.11-.23.08-.24.06-.25.04-.25.01h-14.7l-.32.03-.31.09-.28.16-.25.2-.2.25-.16.28-.09.31zm-111.02-8.46.35.19.33.2.3.21.27.23.23.24.21.26.19.27.15.28.11.28.1.29.06.31.04.31.02.33-.02.34-.03.36-.07.37-.09.37-.11.4-.31.81-.41.87-.52.92-.71.46-.66.47-.63.48-.58.5-.54.52-.5.53-.45.55-.42.56-.12.16-.14.15-.16.13-.17.11-.18.1-.18.07-.2.06-.2.03-.37.03-.37.01-.37-.02-.35-.04-.35-.07-.34-.09-.32-.11-.3-.14-.29-.16-.27-.17-.25-.2-.22-.22-.21-.22-.17-.25-.15-.26-.13-.28-.12-.38-.07-.41-.01-.41.03-.43.09-.43.14-.44.19-.44.24-.45.3-.48.34-.47.37-.46.41-.47.44-.46.48-.45.51-.46.54-.44.58-.45.62-.44.65-.44.68-.43.72-.43.76-.42zm-112.63 41.44 2.8-1.62.13-.09.13-.1.1-.12.1-.13.07-.15.06-.15.03-.15.02-.16.19-4.59.16-3.64.07-2.52v-2.16l-.06-1.86-.06-.88-.09-.82-.1-.76-.12-.71-.14-.68-.17-.62-.19-.58-.21-.55-.12-.23-.16-.2-.19-.17-.22-.14-.83-.43-1.25-.65-.85-.48-.72-.44-.62-.44-.54-.45-.48-.48-.4-.49-.33-.52-.18-.36-.16-.38-.09-.3-.05-.31v-.31l.04-.32.09-.3.13-.28.17-.27.2-.24 3.6-3.69 3.06-3.2.43-.41.45-.33.48-.25.24-.1.25-.07.39-.08.4-.04.41.01.43.05.44.1.45.15.46.19.47.23.29.19.26.23.25.3.21.33.19.39.18.45.14.5.13.54.1.59.08.65.07.7.04.75.02 1.69-.05 2.24-.04 1.59.02.91.15 2.55.12 2.6.11 2.65.09 2.7.13 5.54.05 5.73.02.25.04.25.08.25.11.23.17.3.22.3.63.78.51.6.33.46.24.43.08.21.06.22.14.64.1.63.06.61.04.6-.01.57-.03.56-.07.54-.11.53-.1.32-.14.3-.18.28-.22.26-.25.22-.28.19-.29.15-.32.11-.94.23-1.02.21-1.03.17-1.02.12-.98.07-.94.03-.88-.02-.83-.07-.74-.11-.69-.15-.62-.2-.55-.24-.49-.28-.43-.32-.36-.37-.29-.41-.2-.39-.15-.43-.1-.45-.04-.5.01-.52.07-.55.12-.58.17-.62.07-.17.1-.15.12-.13zm-65.73-6.87.2.3.18.31.16.33.15.33.17.49.12.5.09.52.04.52v.54l-.04.54-.09.55-.13.54-.17.54-.21.53-.25.52-.29.51-.32.48-.36.47-.39.44-.42.42-.72.63-.74.6-.77.55-.78.51-.81.47-.82.42-.84.38-.85.33-.86.28-.87.24-.88.19-.89.14-.88.09-.89.04h-.89l-.9-.06-1.09-.13-1.09-.21-1.08-.29-1.06-.36-1.03-.43-1.02-.51-.98-.57-.97-.65-.51-.38-.49-.39-.48-.4-.46-.42-.44-.43-.42-.44-.41-.45-.39-.47-.53-.7-.5-.73-.46-.75-.44-.79-.4-.82-.36-.85-.34-.9-.32-.93-.28-.97-.25-1-.23-1.07-.2-1.11-.19-1.19-.16-1.25-.15-1.34-.13-1.48-.11-1.75-.02-.84v-.83l.02-.8.04-.79.06-.77.08-.76.1-.74.13-.73.14-.72.16-.7.19-.69.21-.68.23-.67.25-.67.43-.99.48-.98.54-.95.58-.94.65-.93.7-.91.76-.91.82-.9.59-.59.6-.54.62-.51.64-.46.67-.43.67-.38.7-.35.71-.3.52-.19.53-.17.54-.15.55-.13.55-.11.56-.08.57-.06.57-.04.58-.02h.59l.59.03.6.04.6.07.6.09.6.11.61.14.62.15.61.18.62.2.62.22 1.23.51 1.23.58 1.21.67 1.19.74 1.17.81 1.13.87.29.26.28.27.25.29.24.31.21.33.19.33.17.35.14.37.12.37.09.37.07.39.04.38.01.39-.01.39-.04.39-.06.38-.4 2-.06.24-.1.24-.12.22-.14.21-.17.18-.19.17-.2.14-.23.12-.23.1-.24.06-.25.03-.25.01-.25-.02-.25-.06-.24-.08-.23-.11-.27-.17-.24-.22-.82-.83-.81-.77-.81-.71-.79-.65-.79-.6-.79-.54-.78-.48-.76-.42-.77-.36-.75-.31-.75-.25-.74-.19-.74-.14-.73-.07-.72-.02-.71.04-.49.06-.47.08-.47.11-.44.12-.44.16-.42.17-.41.19-.4.22-.56.37-.53.42-.5.46-.48.52-.44.56-.42.62-.39.67-.37.73-.33.77-.31.83-.29.88-.26.94-.24 1.01-.21 1.07-.2 1.16-.19 1.27-.14 1.24-.06 1.24v1.23l.07 1.22.13 1.17.21 1.16.27 1.1.34 1.07.4 1.02.45.95.25.46.27.44.28.43.3.41.31.4.32.38.33.36.35.34.36.33.38.31.38.29.4.28.41.26.42.24.43.22.45.2.45.18.48.17.47.15.49.12.5.11.51.09 1.03.13 1.09.05 1.12-.03.34-.04.34-.07.36-.11.37-.15.38-.19.4-.22.4-.26.43-.3.87-.69.96-.86 1.04-1.03 1.36-1.42 1.34-1.39.13-.11.15-.12.17-.11.18-.08.19-.07.2-.04.19-.03h.2l.2.02.2.03.19.06.18.07.17.1.17.11.15.13.14.14zm191.96-30.54 1.03 2.59.91 2.43.24.71.22.69.18.66.15.63.12.62.09.6.07.6.04.59.01.67-.01.67-.06.69-.08.72-.13.76-.15.8-.2.88-.25.97-.11.45-.07.45-.05.46-.01.45.03.74.11.74.19.74.27.74.17.36.3.5.31.54.22.43.18.42.15.43.1.45.17 1.06.11 1.05.06 1.03v1.02l-.05 1.01-.11.99-.16.98-.22.96-.22.8-.27.78-.3.78-.34.77-.38.75-.42.75-.45.73-.5.73-.2.31-.2.34-.18.37-.17.41-.16.43-.14.47-.27 1.03-.035.182a4 4 0 0 0-.34.554q-.323.013-.611.038l.077-.515.16-.74.21-.75.26-.76.07-.23.04-.25.02-.24-.01-.24-.08-.87-.06-.89-.04-.95-.02-.99.02-2.13.11-2.89.08-2.04.03-1.46v-1.28l-.04-1.13-.06-1.25-.04-1.38-.04-3.15.03-3.69.1-4.21-.3-5.27-.02-.61v-.59l.04-.59.06-.57.1-.56.12-.56.15-.54.18-.53Zm279.9 27.07.39.45.35.46.31.48.27.49.23.5.2.5.15.53.11.52.05.38.04.39.01.38-.01.39-.03.39-.05.39-.07.39-.1.4-.24.78-.33.78-.41.78-.49.76-.38.49-.41.45-.44.39-.48.35-.42.24-.42.21-.44.17-.48.15-.49.11-.52.08-.53.05-.56.01-.58-.01-.6-.05-.63-.08-.65-.1-.69-.15-.73-.17-1.64-.46-.6-.2-.54-.22-.51-.25-.45-.27-.42-.31-.38-.32-.34-.35-.29-.37-.19-.3-.17-.31-.16-.33-.12-.34-.12-.37-.08-.38-.07-.39-.06-.43-.04-.87.02-1 .1-1.14.18-1.58.16-1.33.09-.98.03-.58-.02-.58-.05-.58-.1-.57-.26-1.37-.17-1.09-.1-.95-.03-.87.05-.8.04-.38.07-.37.08-.35.11-.34.12-.33.14-.32.19-.38.23-.36.26-.35.29-.35.31-.32.35-.33.38-.31.41-.3.42-.27.44-.23.39-.16.39-.14.41-.1.42-.08.44-.05.45-.02h.45l.48.04.49.06.51.08.51.12.54.14.55.18.57.2 1.22.49.59.3.52.34.48.41.44.46.4.52.36.56.32.63.27.66.22.7.17.75.12.79.06.79v.8l-.05.8-.11.77-.17.73-.22.68-.05.19-.03.19v.19l.02.19.05.19.08.18.1.17zm-8.756-4.847.025.18.098.177.167.169.229.153.283.132.325.106.356.076.373.042.375.007.362-.027.337-.062.298-.094.247-.122.188-.145.12-.163.049-.175-.025-.18-.098-.177-.167-.169-.229-.153-.282-.132-.326-.106-.356-.076-.373-.042-.375-.007-.362.027-.337.062-.298.094-.247.122-.188.145-.12.163zm5.516 9.876.004-.249-.021-.247-.045-.244-.069-.239-.092-.23-.114-.221-.135-.208-.155-.195-.173-.178-.19-.16-.204-.14-.217-.12-.229-.098-.236-.076-.243-.052-.247-.027-2.195-.138-.249-.004-.247.021-.244.045-.239.069-.23.092-.221.114-.208.135-.194.155-.178.173-.16.19-.141.204-.12.217-.098.228-.076.237-.052.243-.027.247-.035.558-.004.249.021.247.045.244.069.239.092.23.114.221.135.208.155.195.173.178.19.16.204.14.217.12.229.098.236.076.243.052.247.027 2.195.138.249.004.247-.021.244-.045.239-.069.23-.092.221-.114.208-.135.194-.155.178-.173.16-.19.141-.204.12-.217.098-.228.076-.237.052-.243.027-.247zm-228.75-2.449v.12l.03.12.05.11.07.1.09.08.11.06.12.03.12.01.08-.01 4.78-.89.34-.03.34.03.33.09.31.15.28.2.24.25.19.29.13.32.21.76.19.82.15.82.11.84.07.8.03.79-.01.74-.06.71-.09.66-.13.6-.17.56-.21.51-.25.46-.27.41-.33.36-.35.31-.36.23-.41.18-.43.11-.45.04-.47-.03-.46-.1-.45-.16-.43-.22-1.16-.73-1.05-.73-.94-.74-.84-.73-.12-.09-.14-.08-.14-.06-.15-.04-4.8-.98-.22-.07-.21-.1-.19-.13-.16-.17-.13-.19-.1-.21-.06-.23-.02-.23.02-.68.07-.67.12-.67.18-.66.23-.65.29-.65 1.44-3.05 1.42-3.19 1.42-3.32 1.4-3.47.28-.62.34-.57.38-.51.44-.45.27-.24.29-.21.07-.05.14-.06.14-.05.31-.04.32.04.33.11.32.19.29.25.27.3.21.34.15.31.12.31.12.39.09.4.05.41.02.42-.02.43-.04.45-.08.46-.11.48-.14.49-.17.5-.21.52-.23.55-.58 1.18-.85 1.58-.79 1.45-.48 1-.17.43-.13.4-.1.37zm115.42-6.69-.09.17-.05.18-.01.19.03.19.06.17.1.16.13.14.16.11.06.03.47.21 1.13.51.8.38.67.36.6.37.53.38.48.41.42.43.36.46.21.31.32.55.28.56.23.55.18.55.14.54.09.55.04.54v.54l-.03.43-.06.43-.1.42-.12.43-.33.94-.35.85-.37.76-.4.69-.43.62-.47.55-.51.49-.54.42-.43.28-.46.25-.49.21-.51.18-.53.14-.56.12-.59.09-.62.05-.86.03-.83-.01-.82-.06-.79-.11-.77-.15-.75-.2-.72-.25-.71-.29-.32-.18-.29-.23-.25-.28-.2-.31-.34-.68-.3-.66-.27-.67-.24-.66-.19-.66-.17-.65-.13-.65-.09-.66-.08-.98-.01-1.01.07-1.03.14-1.06.22-1.1.29-1.16.38-1.23.46-1.33.28-.71.3-.68.33-.66.36-.63.38-.61.41-.58.44-.56.47-.54.41-.43.43-.41.45-.4.46-.38.49-.36.5-.34.53-.33.54-.31.41-.2.45-.19.49-.18.5-.15.5-.12.5-.1.47-.06.45-.02h.42l.38.03.37.07.33.09.29.12.27.15.24.17.21.21.14.19.13.22.1.22.08.25.07.39v.4l-.06.39-.14.38-.19.35-.25.31-.3.26-.34.21-3.06 1.51-.16.09-.14.11-.12.13-.11.14zm1.289 9.238-.058-.16-.078-.152-.1-.143-.12-.132-.138-.12-.155-.107-.357-.171-.405-.109-.44-.04-.456.027-.456.095-.437.16-.403.216-.352.267-.288.306-.213.333-.075.173-.054.175-.032.175-.01.174.014.17.035.167.058.16.078.152.1.143.12.132.138.12.155.107.357.171.405.109.44.04.456-.027.456-.095.437-.16.403-.216.352-.267.288-.306.212-.333.076-.173.054-.175.032-.175.01-.174-.014-.17zM125.32 246.31l1.45-7.13.04-.15.07-.15.08-.13.11-.12.12-.1.14-.09.14-.06.16-.04.6-.1.57-.06.52-.03.49.01.45.05.41.08.38.11.33.15.2.14.17.18.13.21.07.24 1.27 6.77.06.23.11.23.13.2.17.17.19.15.22.11.23.08.24.04 6.77.52.21.03.2.06.19.09.17.11.16.14.13.16.12.18.08.19.23.73.16.73.08.61.03.62-.01.62-.05.63-.07.36-.13.35-.19.32-.23.29-.27.24-.31.2-.33.15-.36.1-5.58 1.05-.26.07-.24.1-.23.14-.2.18-.18.2-.14.22-.11.24-.07.26-1.15 5.71-.12.42-.19.41-.24.36-.3.33-.34.28-.39.22-.41.16-.43.09-.55.06-.51.02-.48-.01-.44-.06-.42-.1-.38-.13-.36-.17-.32-.21-.23-.19-.21-.21-.2-.23-.17-.26-.17-.28-.14-.3-.13-.33-.11-.36-.19-.76-.13-.89-.09-1.02-.06-1.4-.01-.38-.03-.2-.05-.2-.09-.18-.12-.17-.15-.14-.16-.12-.18-.09-.2-.06-1.57-.34-1.28-.32-1.14-.34-1.01-.37-.93-.41-.85-.45-.38-.24-.38-.26-.34-.27-.34-.28-.23-.23-.2-.25-.15-.27-.11-.27-.07-.26-.03-.27v-.28l.04-.28.06-.3.1-.3.14-.29.16-.3.2-.29.22-.28.25-.27.27-.25.28-.23.29-.2.3-.17.3-.14.75-.28.78-.25.79-.22.81-.19.83-.15.85-.11.87-.09.89-.05.15-.02.15-.04.13-.07.13-.09.1-.11.09-.12.07-.14zm222.23 11.05-.05.18-.01.18.03.18.07.17.1.15.13.13.15.09.18.06.12.02 2.08.16.15.02.14.05.14.07.12.08.11.11.09.12.07.14.05.14.18.78.13.72.1.66.06.61.01.55-.02.5-.06.44-.1.38-.16.35-.22.31-.27.28-.3.23-.34.18-.37.13-.38.06h-.38l-12.53-.96-.14-.03-.14-.05-.12-.08-.11-.1-.08-.12-.06-.13-.03-.14v-.15l.33-4.28.02-.17.05-.17.06-.16.09-.15.11-.13.12-.13.14-.1.15-.09.78-.41.76-.45.76-.49.74-.54.71-.57.69-.6.65-.63.62-.65.56-.67.53-.69.49-.7.43-.7.38-.72.33-.72.28-.72.23-.72.04-.19.01-.18-.03-.33-.11-.32-.17-.28-.24-.23-.28-.18-.32-.1-.33-.03-.33.04-.74.21-.69.25-.65.29-.6.32-.56.36-.52.39-.48.44-.43.47-.31.39-.28.42-.1.14-.12.13-.13.11-.15.1-.15.07-.16.06-.17.04-.18.02-.17-.01-.17-.03-.17-.04-.16-.07-.15-.09-.14-.1-.12-.12-.11-.14-.14-.24-.09-.26-.15-.77-.09-.76-.02-.74.03-.73.1-.71.16-.69.23-.67.28-.64.18-.34.2-.33.44-.63.52-.58.56-.53.63-.49.68-.42.73-.37.76-.3.79-.23.82-.16.82-.09.84-.02.83.06.84.12.83.2.8.26.28.12.27.13.26.15.24.17.24.19.23.21.42.46.38.54.33.61.29.68.24.74.2.8.14.89.1.94.03.97-.02.99-.09 1-.13.97-.2.91-.23.8-.1.25-.13.27-.16.27-.2.29-.49.63-.76.85-.69.78-.45.57-.34.5zm-115.16-17.14.45.03.43.05.42.07.4.1.39.13.37.14.36.18.34.19.41.28.39.32.36.36.34.4.32.44.29.47.26.51.25.56.22.59.19.62.18.67.14.71.13.74.1.78.07.83.06.86.04 1.39.01 1.32-.04 1.25-.07 1.19-.12 1.11-.15 1.05-.19.99-.24.91-.27.85-.31.77-.36.71-.39.65-.43.57-.48.5-.51.44-.55.37-.39.21-.41.18-.42.15-.44.11-.46.09-.47.06-.5.03h-.51l-.51-.03-.49-.06-.47-.09-.45-.12-.43-.14-.42-.18-.39-.2-.37-.23-.53-.41-.49-.47-.44-.53-.39-.6-.35-.66-.31-.74-.27-.79-.22-.86-.17-.93-.13-.99-.09-1.06-.04-1.12v-1.19l.04-1.25.09-1.32.13-1.39.11-.85.13-.82.15-.77.17-.74.19-.69.21-.66.24-.61.25-.58.28-.53.3-.5.32-.45.34-.42.37-.37.38-.34.41-.29.43-.26.35-.17.37-.15.38-.13.39-.09.41-.08.42-.05.44-.02zm-.218 6.55-.254.026-.252.083-.248.139-.242.193-.232.247-.221.297-.208.344-.192.389-.176.43-.156.465-.136.498-.114.525-.091.547-.068.564-.043.575-.019.58.006.582.032.576.056.565.08.55.103.526.125.5.147.47.166.433.184.392.2.35.215.3.227.252.238.198.245.144.25.089.254.03.254-.025.252-.083.248-.139.242-.193.232-.247.221-.297.208-.344.192-.389.176-.43.156-.465.136-.498.114-.525.091-.547.068-.564.043-.575.019-.58-.006-.582-.032-.576-.056-.565-.08-.55-.103-.526-.125-.5-.147-.47-.166-.433-.184-.393-.2-.348-.215-.302-.227-.25-.238-.2-.245-.143-.25-.088zm160.448 10.45.51-.06h.24l.21.02.2.04.19.06.17.09.16.1.15.13.13.16.11.17.1.21.08.22.06.25.04.27.03.3v.65l-.07.76-.15.84-.21.94-.16.48-.09.2-.1.18-.12.15-.12.12-.13.1-.15.07-.14.04-.14.03h-.16l-.16-.02-.89-.41-.86-.44-.84-.46-.82-.49-.8-.52-.77-.54-.75-.58-.72-.6-.57-.5-.55-.51-.53-.54-.52-.55-.25-.3-.23-.34-.22-.38-.19-.4-.16-.42-.14-.43-.09-.43-.07-.43-.02-.4.01-.39.04-.36.08-.35.11-.32.14-.29.17-.26.21-.23.22-.19.25-.16.27-.13.3-.1.32-.07.34-.05.37-.01.39.01.39.06.37.11.35.17.32.22.27.26.23.3.18.34.12.35.11.39.14.38.16.36.19.34.28.44.33.41.37.39.41.36.45.33.5.31.54.29zM789 255.29l.1.17.08.18.04.21.02.23.33 7.86-.01.12-.07.27-.1.21-.11.14-.14.08-.09.02-.1-.01-.11-.04-.12-.06-.25-.19-.29-.3-.17-.15-.22-.11-.27-.07-.31-.04-2.21-.09-2.1-.03-.54-.01-.54-.04-.54-.06-.54-.08-1.83-.3-2.01-.31-5.38-.73-3.81-.52-2.79-.4-2.52-.4-2.3-.41-2.21-.44-2.08-.47-1.99-.5-1.88-.54-.22-.08-.21-.1-.21-.13-.18-.14-.17-.16-.16-.18-.13-.19-.12-.21-.09-.22-.07-.22-.05-.23-.02-.24v-.23l.03-.24.05-.23.07-.22.06-.15.04-.1.12-.23.14-.22.15-.21.18-.19.19-.18.2-.16.22-.14.22-.12.24-.11.25-.08.25-.07.26-.04.26-.02h.26l.25.02.26.05 4.69 1 4.54.89 2.21.41 2.16.37 2.13.35 2.09.32 2.04.29 2.01.26 1.96.24 1.93.21 1.88.18 1.85.15 1.8.12 1.77.1.22-.02.21-.06.19-.12.16-.15.21-.27.18-.28.17-.29.15-.3.15-.38.13-.4.09-.41zm-516.69 8.94-1.64.57-1.61.5-1.13.33-1.17.3-1.22.29-1.29.28-2.92.57-4.08.7-1.65.3-1.81.37-1.99.45-2.74.65-3.14.74-2.3.51-2.08.4-1.9.31-.22.02-.21-.01-.22-.02-.21-.06-.2-.07-.2-.1-.18-.11-.17-.14-.15-.15-.14-.17-.12-.18-.09-.2-.08-.2-.05-.21-.02-.22-.01-.21.05-.36.02-.09.08-.29.09-.28.12-.27.14-.27.15-.25.18-.24.19-.23.21-.21.23-.2.23-.18.26-.16.26-.14.27-.12.28-.1.29-.08.29-.06 4.42-.66 2.65-.39 1.89-.3 1.74-.31 1.65-.33 2.09-.47 2.15-.55 2.12-.54 2.25-.53 2.38-.51 2.51-.5.18-.05 5.14-2.13.3-.09.31-.02.31.05.3.12.26.17.21.23.16.27.09.3.03.28v2.05l-.03.36-.08.34-.13.34-.18.3-.23.28-.27.24-.29.19zm343.16 26.86.09.62.07.63.04.63.03.64-.01 1.31-.08 1.34-.03.19-.06.16-.07.14-.1.1h-.01l-.18.1-.23.06-.26-.01-.81-.37-.78-.3-.75-.21-.72-.14-.7-.07-.66.02-.33.04-.31.05-.31.08-.3.09-.32.13-.3.13-.27.16-.23.16-.21.18-.18.2-.15.2-.12.22-.09.24-.06.25-.04.26v.27l.02.29.06.3.08.32.11.33.1.21.14.18.17.15.19.12 5.88 2.89.22.59.21.6.18.62.17.64.16.65.13.67.23 1.38.15 1.46.09 1.52.02 1.58-.05 1.65-.31.78-.32.72-.33.68-.33.64-.35.59-.35.56-.36.51-.37.47-.39.43-.39.4-.42.36-.41.31-.44.28-.43.24-.45.2-.48.16-.48.13-.5.08-.99.09-.94.03-.89-.04-.84-.12-.8-.18-.38-.12-.37-.14-.35-.15-.35-.17-.33-.19-.32-.21-.52-.39-.48-.44-.45-.49-.42-.54-.24-.36-.21-.39-.19-.42-.18-.44-.15-.47-.12-.5-.11-.52-.08-.54-.07-.58-.04-.6-.02-.62v-.65l.03-.68.04-.7.16-1.49.33-2.29.36-2.14.41-2.01.45-1.9.49-1.78.52-1.64.29-.78.29-.74.3-.71.3-.67.32-.65.33-.6.34-.58.34-.54.36-.51.36-.47.37-.44.39-.41.39-.37.4-.33.41-.3.42-.27.42-.23.44-.2.44-.16.45-.13.39-.09.4-.05.4-.04h.41l.41.01.43.05.42.06.44.1.44.11.45.15.44.16.46.2.46.21.47.25zm-7.313 15.861-.347-.053-.354-.01-.357.036-.357.08-.352.124-.346.166-.335.207-.321.247-.304.282-.285.316-.262.348-.237.374-.21.399-.18.418-.149.434-.116.446-.083.454-.048.456-.014.455.022.45.056.44.09.425.125.407.156.385.187.358.216.33.243.296.268.262.289.223.308.183.325.142.338.098.347.053.354.01.357-.036.357-.08.352-.124.346-.166.335-.207.321-.247.304-.282.285-.316.262-.348.237-.374.21-.399.18-.418.149-.434.116-.446.083-.454.048-.456.014-.455-.022-.45-.056-.44-.09-.425-.125-.407-.156-.385-.187-.358-.216-.33-.243-.296-.268-.262-.289-.223-.308-.183-.325-.141zm151.223-8.411-.11.56-.24.84-.36 1.09-.48 1.37-.42 1.27-.38 1.39-.34 1.52-.4 2.05-.35 1.8-.28 1.29-.3 1.15-.33 1.03-.38.98-.42.92-.48.85-.54.82-.38.51-.42.49-.44.48-.47.45-.5.45-.54.43-.56.41-.6.41-.56.33-.59.28-.59.21-.62.15-.37.07-.37.04-.38.02-.38-.01-.39-.02-.37-.05-.39-.06-.38-.09-.39-.11-.38-.13-.38-.15-.38-.17-.73-.4-.71-.48-.69-.56-.63-.61-.58-.66-.52-.71-.44-.74-.19-.38-.17-.38-.15-.39-.14-.4-.1-.38-.09-.39-.06-.51-.02-.63.03-.74.09-.86.12-.97.18-1.09.23-1.21.27-1.32.4-1.92.41-2 .33-1.48.33-1.35.34-1.21.36-1.15.38-1.08.4-.99.42-.92.47-.88.49-.8.53-.74.55-.67.6-.61.62-.54.65-.47.69-.41.47-.23.5-.21.5-.18.53-.16.53-.12.56-.09.56-.07.59-.04h.42l.42.02.42.04.4.07.58.15.57.2.54.25.54.3.52.36.51.4.49.45.47.5.45.54.42.6.4.62.37.67.33.7.31.72.26.75.22.74.06.29.04.3.01.29zm-6.126 1.108.066-.296.037-.3.007-.304-.023-.302-.052-.299-.081-.292-.11-.283-.136-.27-.163-.257-.187-.238-.21-.22-.23-.197-.247-.175-.264-.149-.278-.123-.288-.094-.231-.064-.296-.066-.301-.037-.303-.007-.303.023-.298.052-.292.081-.283.11-.27.136-.257.163-.239.187-.22.21-.197.229-.174.248-.15.264-.122.278-.095.287-3.638 13.209-.066.296-.037.3-.007.304.023.302.052.299.081.292.11.283.136.27.163.257.187.238.21.22.23.197.247.175.264.149.278.123.288.094.231.064.296.066.301.037.303.007.303-.023.298-.052.292-.081.283-.11.27-.136.257-.163.239-.187.22-.21.197-.229.174-.248.15-.264.122-.278.095-.288zM191.28 322.43l-.11.03-.15.02-.16-.02-.15-.05-.13-.08-.11-.11-.09-.13-.06-.15-.02-.15-.03-.98v-.97l.04-.95.08-.95.11-.93.15-.91.19-.91.23-.89.56-2.14.42-1.84.15-.81.11-.74.08-.66.04-.59.06-.73.13-.83.2-.98.34-1.41.48-2.07.16-.82.12-.74.1-.73.06-.69.03-.66v-.64l-.03-.24-.09-.22-.15-.19-.18-.15-1.09-.66-1.02-.62-.35-.25-.31-.24-.27-.25-.24-.26-.2-.27-.17-.28-.13-.27-.1-.29-.08-.3-.06-.33-.03-.35-.01-.36.02-.39.04-.41.05-.22.09-.21.13-.18.16-.16.18-.13.21-.09.22-.04.23-.01 1.47.08 1.52.04h1.59l1.65-.04 1.71-.08 1.77-.13 1.84-.16 1.89-.21.53-.03.24.02.23.04.2.05.19.08.18.1.15.11.15.15.12.16.11.19.09.21.13.4.09.38.06.37.03.34-.01.33-.04.3-.08.3-.11.27-.11.19-.12.18-.14.18-.17.16-.39.31-.47.28-.54.24-.64.22-.74.21-1.04.24-.86.2-.28.09-.27.13-.26.16-.22.19-.2.23-.17.25-.13.27-.09.28-1.32 5.24-1.26 5.24-1.2 5.24-1.14 5.23-.11.44-.12.42-.15.4-.17.37-.2.36-.21.34-.24.32-.25.29-.46.43-.5.38-.56.32zm434.84-.33-.24-.59-.2-.55-.16-.51-.12-.46-.09-.44-.06-.39-.01-.35.02-.31.13-1.01.11-1.04.16-2.11.09-2.19.01-2.26-.06-2.33-.13-2.4-.21-2.48-.29-2.54-.05-.54-.03-.53-.01-.54.03-.53.05-.53.07-.54.11-.53.13-.53.15-.53.18-.52.21-.53.24-.52.52-.66.53-.6.54-.53.55-.47.56-.4.58-.35.59-.29.6-.22.42-.12.43-.09.44-.07.45-.04.46-.02.48.01.49.04.5.06.52.09.54.12.55.14.58.17 1.23.42 1.37.55.21.1.2.12.18.14.17.16.16.17.13.18.12.2.1.21.56 1.42.46 1.36.19.66.16.64.14.63.12.61.09.6.06.58.03.57.01.55-.01.54-.04.52-.07.51-.09.49-.2.68-.4 1.11-.61 1.54-.81 1.98-.15.43-.12.45-.08.46-.04.46-.08 1.48-.11 1.25-.13 1.03-.15.81-.17.64-.18.62-.19.59-.22.57-.22.54-.25.51-.26.49-.27.47-.29.44-.3.41-.33.39-.33.36-.35.34-.37.31-.38.28-.4.27-.42.23-.42.21-.45.18-.46.16-.47.14-.49.1-.5.09-.52.06-.54.03-.55.01-.57-.02-.58-.05-.6-.06-.61-.1-.63-.12zm10.45-21.602.062-.308.03-.312v-.315l-.03-.312-.06-.308-.092-.3-.12-.29-.148-.278-.174-.261-.199-.243-.222-.222-.242-.2-.261-.174-.277-.149-.29-.12-.3-.091-.233-.059-.308-.061-.313-.031h-.314l-.313.03-.308.061-.3.091-.29.12-.277.148-.262.174-.242.2-.223.221-.2.243-.174.261-.148.277-.12.29-.092.3-3.22 12.822-.062.308-.03.313v.314l.03.312.06.308.092.3.12.29.148.278.174.261.199.243.222.222.242.2.261.174.277.148.29.12.3.092.234.059.308.061.312.031h.314l.313-.03.308-.061.3-.091.29-.12.277-.148.262-.174.243-.2.222-.221.2-.243.174-.261.148-.277.12-.29.092-.3zm80.79 21.622-.14.19-.17.17-.16.12-.18.1-.19.07-.2.05-.21.03h-.22l-.21-.03-.22-.05-.22-.08-.2-.1-.2-.12-.19-.14-.17-.16-.16-.18-.14-.2-.12-.2-.19-.43-.12-.42-.06-.41.01-.4.06-.32.11-.31.14-.3.19-.3 2.14-3.05 2-2.93 1.86-2.79 1.73-2.66 1.6-2.54 1.45-2.41 1.32-2.27 1.19-2.15.09-.23.04-.24-.02-.25-.07-.23-.12-.21-.17-.18-.2-.14-.23-.09-.26-.03-.57.02-.55.06-.54.09-.53.14-.51.18-.5.22-.49.26-.47.3-.46.34-.45.37-.44.42-.42.46-.16.16-.18.15-.2.13-.2.1-.22.08-.22.05-.23.03-.23.01-.23-.02-.23-.05-.22-.07-.21-.1-.2-.12-.18-.14-.17-.16-.15-.17-.12-.18-.1-.19-.1-.21-.13-.31-.11-.32-.09-.33-.06-.34-.03-.33-.01-.34.01-.34.04-.34.07-.33.09-.33.12-.32.14-.31.16-.3.18-.28.21-.27.22-.26.57-.56.6-.5.63-.44.65-.38.42-.21.43-.18.44-.16.45-.14.46-.12.47-.1.49-.08.5-.06.51-.04.53-.01h.54l.55.03 1.15.11 1.2.2.64.11.64.08.64.06.62.02h.63l.61-.03.62-.07.61-.09.51-.1.23-.02.24.02.28.08.29.12.31.19.27.24.22.29.16.32.09.35.03.36-.04.36-.11.35-.46 1.07-.49 1.05-.76 1.56-.81 1.55-.88 1.57-.97 1.64-1.04 1.67-1.2 1.86-1.39 2.1-2.05 3.03-2.5 3.7-.99 1.44-.82 1.16-.65.86-.49.57-.95 1.03-.83 1-.72.94zm-336.79-11.56.37.65.11.14.11.07.12.05.13.01.13-.01.12-.05.11-.07.09-.1.06-.11.52-1.23.53-1.08.27-.48.27-.44.27-.41.28-.36.27-.33.28-.29.28-.26.28-.21.29-.17.28-.14.29-.1.29-.06.5-.04.49.04.46.1.43.18.38.23.17.14.15.15.13.17.11.17.1.18.07.19.16.65.11.73.04.82-.02.89-.09 1.13-.15 1.09-.21 1.06-.27 1.02-.33.98-.39.94-.44.9-.51.87-.55.81-.62.78-.66.74-.73.7-.32.24-.36.17-.38.09-.4.03-.39-.06-.37-.12-.34-.2-.3-.26-.44-.44-.47-.41-.37-.3-.38-.27-.39-.26-.41-.24-.42-.22-.44-.21-.45-.19-.46-.18-.97-.3-1.02-.23-1.08-.17-1.13-.1-.38-.03-.35-.06-.31-.07-.26-.1-.23-.11-.19-.13-.15-.15-.11-.18-.05-.13-.08-.34-.03-.36.01-.39.05-.41.09-.45.14-.48.18-.5.23-.53.26-.56.31-.58.35-.62.4-.66.93-1.45 1.34-1.94.55-.79 10.02-14.42.3-.38.32-.31.35-.23.37-.17.27-.08.28-.04.29-.01.31.02.31.06.33.08.34.13.35.15.21.14.16.18.1.23.04.24-.01.71-.05.67-.09.63-.12.6-.18.55-.21.52-.25.48-.29.44-2.33 3.18-2.22 3.13-2.09 3.05-1.97 3-.09.17-.07.17-.04.19-.02.19.01.19.03.19.07.19zm107.46 5.23.85.34.82.3.78.25.74.22.7.18.68.14.64.1.6.06.58.02.54-.02.52-.06.48-.1.44-.13.43-.18.38-.21.36-.25.36-.33.32-.38.28-.42.24-.48.2-.52.16-.57.13-.62.08-.67.02-.4v-.37l-.03-.35-.06-.32-.09-.31-.11-.27-.15-.25-.17-.23-.2-.2-.22-.17-.25-.15-.27-.13-.32-.11-.33-.08-.35-.06-.4-.04-.41-.02-.44.01-.99.07-1.13.15-1.54.28-1.67.28-.51.03-.24-.01-.23-.03-.21-.05-.2-.07-.2-.09-.18-.1-.24-.19-.22-.22-.18-.25-.16-.29-.13-.32-.1-.35-.07-.38-.03-.39-.01-.42.04-.42.06-.43.1-.42.13-.4.16-.39.18-.34.2-.31.28-.4.25-.39.21-.37.18-.36.11-.28.08-.3.06-.3.03-.3.06-.97.07-.87.09-.82.12-.77.13-.72.16-.68.18-.63.2-.59.23-.56.25-.51.28-.49.3-.44.33-.4.35-.36.37-.32.4-.28.31-.18.32-.16.32-.14.35-.12.35-.1.38-.08.38-.06.4-.04 2.05-.11 2.15-.04 2.26.03 2.35.09.2.03.19.05.19.08.17.11.15.13.14.16.11.17.08.18.13.38.09.36.06.35.02.32v.31l-.04.28-.08.27-.11.24-.11.19-.13.18-.16.16-.18.16-.21.14-.23.14-.53.24-.63.2-.74.18-.88.16-1.27.19-1.44.22-1.02.19-.88.22-.75.25-.36.15-.31.16-.3.17-.27.18-.24.2-.23.21-.19.22-.18.24-.05.07-.14.31-.08.33-.01.34.06.34.12.32.19.29.24.24.28.19.65.3 1.08.43 1.26.5.91.41.76.39.62.39.51.38.48.4.45.42.41.44.39.46.35.47.33.5.29.51.32.67.27.7.22.71.16.75.11.76.06.79v.8l-.05.8-.11.84-.16.83-.21.84-.27.84-.31.84-.37.82-.41.82-.46.79-.32.49-.35.45-.37.42-.41.39-.54.45-.59.4-.63.35-.67.3-.71.24-.76.2-.78.14-.82.09-.86.04-.88-.02-.91-.07-.94-.13-.93-.17-.95-.23-.94-.28-.94-.33-.3-.13-.3-.15-.28-.17-.26-.19-.26-.21-.23-.24-.22-.24-.2-.27-.17-.28-.16-.29-.13-.3-.11-.31-.09-.32-.06-.32-.04-.33-.01-.33v-.81l.04-.37.05-.21.08-.2.09-.19.12-.18.14-.16.15-.15.17-.13.18-.11.19-.09.2-.07.21-.04.22-.02h.21l.21.02.21.05zm37.14 6.11-.46.31-.47.28-.47.25-.48.22-.48.2-.49.17-.5.14-.5.11-.43.08-.43.06-.43.03-.45.02-.89-.03-.91-.11-.92-.18-.94-.27-.96-.35-.97-.42-.24-.13-.22-.17-.2-.19-.17-.22-.61-.96-.51-.94-.21-.46-.2-.46-.16-.45-.15-.45-.11-.45-.09-.44-.07-.44-.04-.43-.02-.43.01-.42.03-.42.06-.42.06-.31.86-3.98.69-3.33.54-2.68.37-2.03.21-1.13.22-1.05.24-.95.25-.86.27-.77.28-.68.3-.58.31-.5.51-.66.55-.61.6-.58.63-.52.66-.48.69-.41.72-.36.73-.3.74-.24.75-.17.74-.1.74-.03.72.03.72.1.68.17.68.23.55.25.53.3.51.34.49.4.46.43.43.48.41.51.38.57.3.5.26.49.24.49.2.48.18.5.14.48.12.5.09.48.08.72.02.72-.03.75-.09.77-.15.79-.2.86-.27.95-.4 1.29-.51 1.68-.33 1.26-.24 1.18-.09.56-.06.55-.12 1.14-.12.99-.15.89-.16.83-.19.78-.22.74-.25.71-.28.68-.4.81-.45.8-.51.79-.59.8-.21.25-.24.24-.24.22zm1.458-20.487.059-.31.028-.315-.003-.316-.034-.314-.065-.31-.095-.301-.124-.291-.151-.277-.179-.261-.202-.243-.226-.22-.246-.199-.265-.173-.28-.146-.293-.118-.303-.089-.584-.14-.31-.058-.315-.028-.316.003-.314.034-.31.065-.3.094-.291.124-.278.152-.26.178-.243.203-.221.225-.198.246-.173.265-.146.28-.119.293-.088.303-3.231 13.458-.059.31-.028.315.003.316.035.315.064.309.095.301.124.291.151.277.178.261.203.242.226.222.246.198.265.173.28.146.293.118.303.089.584.14.31.058.315.028.316-.003.314-.034.31-.064.3-.095.292-.124.277-.152.26-.178.243-.203.221-.225.198-.246.173-.265.147-.28.117-.293.09-.303zM407.04 323.94l-.35-.4-.32-.41-.28-.41-.24-.42-.2-.42-.16-.43-.12-.43-.09-.44-.05-.49v-.5l.04-.5.09-.51.13-.47.17-.36.1-.14.1-.11.12-.08.12-.05.2-.03.23.03.24.09.27.16.42-.48.41-.5.38-.52.36-.52.44-.71.41-.73.37-.76.33-.78.3-.8.27-.83.23-.85.19-.87.6-3.13.55-3.14.07-.48.04-.47.01-.45-.02-.45-.05-.43-.08-.42-.1-.41-.14-.4-.17-.4-.2-.38-.21-.32-.27-.27-.31-.22-.34-.17-.36-.11-.38-.05-.38.02-.37.09-.23.1-.25.15-.25.19-.27.25-.28.29-.29.34-.62.83-.27-.48-.24-.48-.21-.49-.17-.49-.14-.48-.11-.49-.08-.5-.04-.49v-.51l.02-.52.07-.52.1-.52.13-.52.17-.52.21-.52.24-.53 1.51-.2 1.46-.11.71-.03h.7l.68.01.67.04.54.05.51.09.48.11.46.15.44.18.41.2.39.24.36.27.36.32.33.36.3.39.28.42.24.47.22.49.19.53.16.57.24 1.03.18.97.14.91.09.86.04.81-.01.75-.05.7-.11.64-1.11 4.98-1.94 8.42-.28 1.06-.34.98-.18.45-.21.44-.21.42-.23.4-.24.38-.26.36-.27.34-.28.32-.3.3-.32.29-.32.26-.34.24-.48.29-.51.26-.54.22-.56.19-.58.14-.61.11-.64.07zM279.73 298l-.04-.21-.08-.19-.11-.18-.15-.15-.16-.13-.19-.09-.2-.06-.21-.02-.72.02-.72.07-.72.13-.72.18-.73.23-.72.29-.73.35-.73.4-.22.12-.23.09-.24.07-.25.05-.24.02h-.25l-.24-.02-.24-.05-.22-.07-.22-.1-.2-.11-.19-.14-.16-.15-.15-.17-.13-.19-.1-.2-.09-.25-.08-.38-.04-.41v-.44l.04-.46.09-.48.12-.51.17-.54.21-.56.12-.23.16-.2.19-.16.22-.13.8-.34.81-.3.81-.25.82-.21.81-.16.78-.12.79-.07.76-.02.74.03.7.08.7.12.66.17.63.22.62.26.57.31.56.35.44.34.42.37.4.4.38.44.34.46.33.5.29.53.28.56.18.43.15.43.11.42.08.42.05.4.01.41-.02.39-.06.39-.06.3-.09.29-.1.29-.13.28-3.12 6.45-.19.45-.13.47-.08.48-.02.49.03 2.51-.01.97-.03.86-.05.83-.08.76-.1.72-.12.67-.15.65-.18.61-.22.57-.23.54-.27.5-.3.47-.33.43-.37.41-.52.49-.57.42-.64.39-.69.32-.76.28-.81.23-.88.18-.93.13-1.11.09-1.06.02-1.03-.04-.99-.11-.96-.18-.91-.25-.88-.31-.84-.38-.23-.14-.22-.18-.19-.2-.16-.22-.14-.26-.13-.28-.1-.32-.08-.35-.06-.38-.04-.4-.01-.44v-.5l.05-.3.1-.29.16-.26.21-.22.25-.18.28-.13.29-.06.31-.01 7.75.6h.21l.21-.02.2-.05.19-.08.18-.1.17-.13.15-.15.13-.16.38-.6.31-.58.25-.57.19-.56.12-.54.06-.53-.01-.52-.07-.5-.1-.38-.13-.37-.17-.36-.21-.35-.25-.34-.29-.34-.32-.33-.36-.32-.35-.3-.31-.29-.26-.28-.22-.27-.18-.25-.13-.24-.08-.23-.04-.21-.01-.34.02-.31.06-.3.1-.28.15-.29.19-.29.24-.26.29-.25.33-.24.39-.23.45-.24.64-.3.68-.33.45-.25.38-.24.3-.25.36-.37.33-.38.3-.39.26-.39.23-.41.2-.41.16-.42.12-.43.11-.53.06-.55.01-.56zm9.13 25.19-.25-.24-.24-.25-.21-.26-.19-.27-.31-.53-.24-.58-.17-.61-.11-.66-.05-.7.02-.74.08-.78.14-.83 1.04-4.94.91-4.12.78-3.28.34-1.33.31-1.12.23-.77.25-.73.26-.69.27-.65.29-.62.29-.59.32-.56.33-.52.35-.5.36-.45.37-.43.4-.4.41-.37.42-.34.45-.3.46-.28.63-.32.65-.26.42-.13.47-.08.48-.04h.51l.54.04.57.09.59.12.6.17.64.22.65.25.66.3.65.33.63.36.61.38.56.4.48.38.22.22.19.25.15.28.11.29 1.32 4.64.06.33-.01.27-.05.26-.3 1.07-.28 1.11-.27 1.15-.25 1.2-.25 1.24-.23 1.28-.42 2.7-.19 1.23-.22 1.14-.24 1.04-.27.96-.29.89-.33.85-.37.81-.41.78-.39.63-.41.62-.46.61-.5.6-.55.6-.6.62-.66.63-.73.65-.32.24-.36.19-.38.14-.39.07-.66.07-.64.04-.63.02h-.61l-.6-.03-.58-.05-.57-.07-.55-.09-.53-.12-.52-.14-.51-.16-.49-.18-.47-.21-.46-.23-.44-.25-.43-.27-.45-.33zm14.047-20.29.059-.318.027-.322-.004-.324-.036-.322-.068-.317-.098-.309-.128-.297-.156-.284-.184-.267-.208-.247-.232-.226-.254-.202-.271-.176-.288-.15-.3-.12-.312-.089-.233-.055-.319-.06-.322-.027-.324.005-.322.036-.317.067-.308.098-.298.128-.284.157-.266.183-.248.209-.226.232-.202.253-.176.272-.15.287-.119.3-.09.312-3.497 14.792-.059.319-.027.322.004.324.036.322.068.317.098.308.128.298.156.284.184.267.208.247.232.226.253.202.272.176.288.15.3.12.312.089.233.055.319.06.322.027.324-.005.322-.036.317-.067.308-.098.298-.128.284-.157.266-.183.248-.209.226-.232.202-.253.176-.272.15-.287.119-.3.09-.312z"/></svg>
        </div>
        <div class="lpsb-sheet">
          <div class="lpsb-grid">
            <template v-for="(row, rowIdx) in scoringRowsWithScores" :key="row.key">
              <div
                class="lpsb-cell lpsb-label"
                :class="{ 'lpsb-divider': rowIdx === 0 || lpsbDividerKeys.has(row.key as LpsBScoreKey) }"
              >
                {{ row.name }}
              </div>
              <div
                class="lpsb-cell lpsb-raw"
                :class="{ 'lpsb-divider': rowIdx === 0 || lpsbDividerKeys.has(row.key as LpsBScoreKey) }"
              >
                {{ row.raw ?? '–' }}
              </div>
              <div
                class="lpsb-cell lpsb-tvalue"
                :class="{ 'lpsb-divider': rowIdx === 0 || lpsbDividerKeys.has(row.key as LpsBScoreKey) }"
              >
                {{ row.t ?? '–' }}
              </div>
              <div
                class="lpsb-cell lpsb-hatch"
                :class="{ 'lpsb-divider': rowIdx === 0 || lpsbDividerKeys.has(row.key as LpsBScoreKey) }"
              ></div>
              <div
                v-for="tValue in lpsbTValues"
                :key="`${row.key}-${tValue}`"
                class="lpsb-cell lpsb-score"
                :class="{
                  'lpsb-divider': rowIdx === 0 || lpsbDividerKeys.has(row.key as LpsBScoreKey),
                  'lpsb-score-ticks': row.key === 'total_raw',
                }"
              ></div>
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
        <table class="min-w-[640px] w-full border-collapse text-sm">
          <thead>
            <tr class="bg-muted/40 text-left">
              <th class="px-3 py-2 font-semibold">Gesamtwert</th>
              <th class="px-3 py-2 text-right font-semibold">Rohwert</th>
              <th class="px-3 py-2 text-right font-semibold">T-Wert</th>
              <th class="px-3 py-2 text-right font-semibold">C-Wert</th>
              <th class="px-3 py-2 text-right font-semibold">PR</th>
              <th class="px-3 py-2 text-right font-semibold">IQ</th>
            </tr>
          </thead>
          <tbody>
            <tr class="border-t">
              <td class="px-3 py-2 font-semibold">
                Summe (Test 1+2 + Test 3+4 + Test 5+6 + Test 7–10 + Test 11+12 + Test 13+14)
              </td>
              <td class="px-3 py-2 text-right font-mono">{{ totalScores.total }}</td>
              <td class="px-3 py-2 text-right">{{ totalScoreEntry?.t ?? '–' }}</td>
              <td class="px-3 py-2 text-right">{{ totalScoreEntry?.c ?? '–' }}</td>
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

.lpsb-score-ticks {
  background: repeating-linear-gradient(
    90deg,
    color-mix(in srgb, var(--foreground) 30%, transparent) 0 1px,
    transparent 1px 4px
  );
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

.lpsb-top {
  position: relative;
  margin-bottom: 0;
  padding: 0 12px;
  box-sizing: border-box;
}

</style>
