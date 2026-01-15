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
  return table.find((entry) => matchesScoreEntry(entry, raw)) ?? null;
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

const scoringRows = computed(() => [
  { key: 'test_1_2', name: 'Test 1 + 2', description: 'Gesamtpunkte', raw: totalScores.value.a },
  { key: 'test_3', name: 'Test 3', description: 'Einzelpunkte', raw: totalScores.value.b },
  { key: 'test_4', name: 'Test 4', description: 'Einzelpunkte', raw: totalScores.value.c },
  { key: 'test_3_4', name: 'Test 3 + 4', description: 'Gesamtpunkte', raw: totalScores.value.d },
  { key: 'test_5', name: 'Test 5', description: 'Einzelpunkte', raw: totalScores.value.e },
  { key: 'test_6', name: 'Test 6', description: 'manuell', raw: totalScores.value.f },
  { key: 'test_5_6', name: 'Test 5 + 6', description: 'Gesamtpunkte', raw: totalScores.value.g },
  { key: 'test_7', name: 'Test 7', description: 'Einzelpunkte', raw: totalScores.value.h },
  { key: 'test_8', name: 'Test 8', description: 'Einzelpunkte', raw: totalScores.value.i },
  { key: 'test_9', name: 'Test 9', description: 'Einzelpunkte', raw: totalScores.value.j },
  { key: 'test_10', name: 'Test 10', description: 'Einzelpunkte', raw: totalScores.value.k },
  { key: 'test_7_10', name: 'Test 7 + 8 + 9 + 10', description: 'Gesamtpunkte', raw: totalScores.value.l },
  { key: 'test_11', name: 'Test 11', description: 'Einzelpunkte', raw: totalScores.value.m },
  { key: 'test_12', name: 'Test 12', description: 'Einzelpunkte', raw: totalScores.value.n },
  { key: 'test_11_12', name: 'Test 11 + 12', description: 'Gesamtpunkte', raw: totalScores.value.o },
  { key: 'test_13', name: 'Test 13', description: 'Einzelpunkte', raw: totalScores.value.p },
  { key: 'test_14', name: 'Test 14', description: 'Einzelpunkte', raw: totalScores.value.q },
  { key: 'test_13_14', name: 'Test 13 + 14', description: 'Gesamtpunkte', raw: totalScores.value.r },
  { key: 'test_14_wrong', name: 'Test 14', description: 'Fehler insgesamt', raw: totalScores.value.s },
]);

const scoringRowsWithScores = computed(() =>
  scoringRows.value.map((row) => {
    const entry = lookupColumnScore(row.key as LpsBScoreKey, row.raw);
    return {
      ...row,
      t: entry?.t ?? null,
      c: entry?.c ?? null,
    };
  }),
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
        <div class="lpsb-sheet">
          <div class="lpsb-grid">
            <div class="lpsb-header lpsb-header-label">T</div>
            <div class="lpsb-header lpsb-header-raw">Roh</div>
            <div
              v-for="tValue in lpsbTValues"
              :key="`lpsb-t-${tValue}`"
              class="lpsb-header lpsb-header-score"
            >
              {{ tValue }}
            </div>

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
                {{ row.raw }}
              </div>
              <div
                v-for="tValue in lpsbTValues"
                :key="`${row.key}-${tValue}`"
                class="lpsb-cell lpsb-score"
                :class="{ 'lpsb-divider': rowIdx === 0 || lpsbDividerKeys.has(row.key as LpsBScoreKey) }"
              >
                <span v-if="row.t === tValue" class="lpsb-marker">●</span>
              </div>
            </template>
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
  padding: 12px;
}

.lpsb-grid {
  display: grid;
  grid-template-columns: 110px 48px repeat(9, 36px);
  gap: 0;
  border: 1px solid var(--border);
  background: var(--background);
}

.lpsb-header {
  border-right: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
  background: color-mix(in srgb, var(--muted) 60%, transparent);
  color: var(--muted-foreground);
  font-size: 12px;
  font-weight: 600;
  padding: 4px 6px;
  text-align: center;
}

.lpsb-header-label,
.lpsb-label {
  text-align: left;
}

.lpsb-header-raw,
.lpsb-raw {
  text-align: center;
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

.lpsb-label {
  justify-content: flex-start;
  font-weight: 600;
}

.lpsb-raw {
  font-family: ui-monospace, SFMono-Regular, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New',
    monospace;
  font-weight: 600;
}

.lpsb-marker {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 18px;
  height: 18px;
  border-radius: 999px;
  background: var(--foreground);
  color: var(--background);
  font-size: 12px;
  line-height: 1;
}

.lpsb-divider {
  border-top: 2px solid var(--foreground);
}

.lpsb-grid > :nth-child(11n) {
  border-right: none;
}
</style>
