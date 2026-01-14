export type LpsBAgeGroupKey = 'under_18' | '19_20' | '21_29' | '30_39' | '40_49' | '50_plus';

export type LpsBScoreRow = {
  raw: number;
  t: number;
  c: number;
};

export type LpsBScoreRangeRow = {
  min: number;
  max: number;
  t: number;
  c: number;
};

export type LpsBTotalScoreRow = (LpsBScoreRow | LpsBScoreRangeRow) & { pr?: number };

export type LpsBScoreKey =
  | 'a'
  | 'b'
  | 'c'
  | 'd'
  | 'e'
  | 'f'
  | 'g'
  | 'h'
  | 'i'
  | 'j'
  | 'k'
  | 'l'
  | 'm'
  | 'n'
  | 'o'
  | 'p'
  | 'q'
  | 'r'
  | 's';

export type LpsBScoreTables = Record<
  LpsBAgeGroupKey,
  {
    columns: Partial<Record<LpsBScoreKey, Array<LpsBScoreRow | LpsBScoreRangeRow>>>;
    total: LpsBTotalScoreRow[];
  }
>;

const LPS_B_T_VALUES = [30, 35, 40, 45, 50, 55, 60, 65, 70] as const;
const LPS_B_C_VALUES = [1, 2, 3, 4, 5, 6, 7, 8, 9] as const;

function buildScoreRows(values: Array<number | null>): LpsBScoreRow[] {
  return values.flatMap((raw, index) => {
    if (raw === null || raw === undefined) return [];
    return [
      {
        raw,
        t: LPS_B_T_VALUES[index] ?? 0,
        c: LPS_B_C_VALUES[index] ?? 0,
      },
    ];
  });
}

export const LPS_B_AGE_GROUP_LABELS: Record<LpsBAgeGroupKey, string> = {
  under_18: 'Bis 18',
  '19_20': '19–20',
  '21_29': '21–29',
  '30_39': '30–39',
  '40_49': '40–49',
  '50_plus': '50+',
};

export const LPS_B_SCORE_TABLES: LpsBScoreTables = {
  under_18: {
    columns: {
      a: buildScoreRows([6, 11, 18, 25, 33, 42, 51, 59, 66]),
      b: buildScoreRows([9, 12, 15, 19, 22, 26, 29, 31, 34]),
      c: buildScoreRows([11, 14, 18, 21, 24, 26, 28, 30, 33]),
      d: buildScoreRows([23, 30, 35, 41, 47, 52, 56, 61, 65]),
      e: buildScoreRows([1, 3, 6, 10, 17, 24, 29, 34, 38]),
      f: buildScoreRows([5, 10, 16, 21, 26, 31, 37, 43, 49]),
      g: buildScoreRows([7, 17, 25, 34, 43, 55, 65, 73, 81]),
      h: buildScoreRows([5, 7, 9, 12, 16, 19, 23, 27, 32]),
      i: buildScoreRows([null, 1, 5, 9, 14, 23, 33, 39, 40]),
      j: buildScoreRows([4, 9, 14, 18, 23, 26, 30, 32, 35]),
      k: buildScoreRows([3, 7, 11, 14, 18, 23, 27, 32, 37]),
      l: buildScoreRows([23, 33, 45, 57, 70, 85, 101, 118, 132]),
      m: buildScoreRows([7, 12, 16, 19, 22, 26, 29, 32, 35]),
      n: buildScoreRows([5, 10, 15, 19, 23, 27, 30, 33, 36]),
      o: buildScoreRows([14, 23, 33, 40, 46, 52, 58, 63, 69]),
      p: buildScoreRows([2, 4, 7, 9, 12, 15, 17, 21, 24]),
      q: buildScoreRows([10, 14, 18, 22, 24, 28, 31, 35, 39]),
      r: buildScoreRows([16, 23, 27, 32, 37, 42, 47, 52, 57]),
      s: buildScoreRows([21, 18, 15, 13, 10, 8, 5, 2, null]),
    },
    total: [],
  },
  '19_20': { columns: {}, total: [] },
  '21_29': { columns: {}, total: [] },
  '30_39': { columns: {}, total: [] },
  '40_49': { columns: {}, total: [] },
  '50_plus': { columns: {}, total: [] },
};

export const LPS_B_IQ_BY_PR_RANGES: Array<{ minPr: number; maxPr: number; iq: number }> = [];
