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
  | 'test_1_2'
  | 'test_3'
  | 'test_4'
  | 'test_3_4'
  | 'test_5'
  | 'test_6'
  | 'test_5_6'
  | 'test_7'
  | 'test_8'
  | 'test_9'
  | 'test_10'
  | 'test_7_10'
  | 'test_11'
  | 'test_12'
  | 'test_11_12'
  | 'test_13'
  | 'test_14'
  | 'test_13_14'
  | 'test_14_wrong';

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
      test_1_2: buildScoreRows([6, 11, 18, 25, 33, 42, 51, 59, 66]),
      test_3: buildScoreRows([9, 12, 15, 19, 22, 26, 29, 31, 34]),
      test_4: buildScoreRows([11, 14, 18, 21, 24, 26, 28, 30, 33]),
      test_3_4: buildScoreRows([23, 30, 35, 41, 47, 52, 56, 61, 65]),
      test_5: buildScoreRows([1, 3, 6, 10, 17, 24, 29, 34, 38]),
      test_6: buildScoreRows([5, 10, 16, 21, 26, 31, 37, 43, 49]),
      test_5_6: buildScoreRows([7, 17, 25, 34, 43, 55, 65, 73, 81]),
      test_7: buildScoreRows([5, 7, 9, 12, 16, 19, 23, 27, 32]),
      test_8: buildScoreRows([null, 1, 5, 9, 14, 23, 33, 39, 40]),
      test_9: buildScoreRows([4, 9, 14, 18, 23, 26, 30, 32, 35]),
      test_10: buildScoreRows([3, 7, 11, 14, 18, 23, 27, 32, 37]),
      test_7_10: buildScoreRows([23, 33, 45, 57, 70, 85, 101, 118, 132]),
      test_11: buildScoreRows([7, 12, 16, 19, 22, 26, 29, 32, 35]),
      test_12: buildScoreRows([5, 10, 15, 19, 23, 27, 30, 33, 36]),
      test_11_12: buildScoreRows([14, 23, 33, 40, 46, 52, 58, 63, 69]),
      test_13: buildScoreRows([2, 4, 7, 9, 12, 15, 17, 21, 24]),
      test_14: buildScoreRows([10, 14, 18, 22, 24, 28, 31, 35, 39]),
      test_13_14: buildScoreRows([16, 23, 27, 32, 37, 42, 47, 52, 57]),
      test_14_wrong: buildScoreRows([21, 18, 15, 13, 10, 8, 5, 2, null]),
    },
    total: [],
  },
  '19_20': {
    columns: {
      test_1_2: buildScoreRows([5, 11, 18, 26, 34, 44, 55, 63, 70]),
      test_3: buildScoreRows([9, 12, 15, 19, 23, 26, 29, 32, 35]),
      test_4: buildScoreRows([10, 14, 17, 21, 24, 27, 29, 31, 33]),
      test_3_4: buildScoreRows([21, 29, 35, 41, 47, 53, 57, 62, 67]),
      test_5: buildScoreRows([1, 3, 5, 10, 17, 25, 30, 35, 39]),
      test_6: buildScoreRows([3, 9, 15, 21, 26, 32, 38, 44, 50]),
      test_5_6: buildScoreRows([6, 15, 24, 33, 43, 55, 67, 76, 84]),
      test_7: buildScoreRows([5, 7, 9, 13, 16, 20, 24, 28, 33]),
      test_8: buildScoreRows([null, 1, 4, 9, 14, 24, 33, 39, 40]),
      test_9: buildScoreRows([3, 8, 13, 18, 23, 27, 30, 33, 35]),
      test_10: buildScoreRows([2, 6, 10, 14, 18, 23, 28, 33, 38]),
      test_7_10: buildScoreRows([21, 31, 43, 55, 68, 85, 103, 121, 136]),
      test_11: buildScoreRows([6, 11, 15, 19, 22, 26, 29, 32, 35]),
      test_12: buildScoreRows([5, 10, 15, 20, 23, 27, 31, 34, 37]),
      test_11_12: buildScoreRows([12, 22, 32, 40, 47, 53, 59, 65, 70]),
      test_13: buildScoreRows([2, 4, 7, 9, 12, 15, 18, 21, 24]),
      test_14: buildScoreRows([10, 14, 18, 22, 25, 28, 32, 36, 40]),
      test_13_14: buildScoreRows([16, 23, 27, 32, 37, 42, 47, 53, 59]),
      test_14_wrong: buildScoreRows([21, 18, 15, 13, 10, 7, 4, 1, null]),
    },
    total: [],
  },
  '21_29': {
    columns: {
      test_1_2: buildScoreRows([3, 9, 18, 28, 37, 47, 58, 67, 75]),
      test_3: buildScoreRows([8, 11, 15, 19, 23, 26, 29, 33, 36]),
      test_4: buildScoreRows([9, 13, 16, 21, 24, 27, 29, 31, 34]),
      test_3_4: buildScoreRows([19, 27, 33, 40, 46, 53, 58, 64, 69]),
      test_5: buildScoreRows([1, 2, 5, 9, 17, 25, 31, 36, 40]),
      test_6: buildScoreRows([3, 7, 13, 19, 25, 32, 39, 45, 51]),
      test_5_6: buildScoreRows([3, 11, 20, 29, 39, 53, 67, 79, 87]),
      test_7: buildScoreRows([5, 7, 9, 13, 17, 20, 25, 30, 35]),
      test_8: buildScoreRows([null, 1, 3, 8, 13, 23, 32, 39, 40]),
      test_9: buildScoreRows([1, 7, 12, 18, 23, 27, 31, 33, 36]),
      test_10: buildScoreRows([1, 5, 9, 14, 18, 23, 28, 34, 39]),
      test_7_10: buildScoreRows([16, 27, 38, 50, 64, 83, 102, 123, 140]),
      test_11: buildScoreRows([5, 11, 15, 18, 22, 26, 29, 32, 35]),
      test_12: buildScoreRows([4, 9, 14, 19, 24, 28, 32, 35, 38]),
      test_11_12: buildScoreRows([7, 18, 29, 39, 46, 54, 61, 66, 72]),
      test_13: buildScoreRows([1, 3, 6, 9, 12, 15, 18, 21, 25]),
      test_14: buildScoreRows([9, 13, 18, 22, 24, 28, 32, 36, 40]),
      test_13_14: buildScoreRows([15, 22, 27, 32, 37, 42, 47, 54, 60]),
      test_14_wrong: buildScoreRows([21, 18, 15, 12, 9, 6, 3, 1, null]),
    },
    total: [],
  },
  '30_39': {
    columns: {
      test_1_2: buildScoreRows([1, 8, 18, 29, 39, 50, 61, 70, 77]),
      test_3: buildScoreRows([6, 10, 13, 17, 21, 25, 29, 32, 36]),
      test_4: buildScoreRows([8, 11, 15, 20, 23, 26, 29, 31, 34]),
      test_3_4: buildScoreRows([16, 23, 30, 37, 44, 51, 57, 63, 69]),
      test_5: buildScoreRows([null, 1, 4, 8, 15, 24, 31, 37, 40]),
      test_6: buildScoreRows([1, 4, 10, 17, 23, 29, 36, 42, 48]),
      test_5_6: buildScoreRows([1, 7, 16, 25, 35, 48, 63, 74, 84]),
      test_7: buildScoreRows([5, 7, 9, 12, 16, 21, 26, 31, 37]),
      test_8: buildScoreRows([null, null, 1, 7, 12, 22, 31, 38, 40]),
      test_9: buildScoreRows([1, 4, 10, 15, 21, 26, 30, 33, 35]),
      test_10: buildScoreRows([1, 2, 7, 12, 16, 21, 27, 33, 38]),
      test_7_10: buildScoreRows([11, 21, 32, 44, 57, 73, 90, 122, 140]),
      test_11: buildScoreRows([2, 9, 13, 17, 21, 25, 28, 32, 35]),
      test_12: buildScoreRows([2, 8, 13, 18, 23, 28, 32, 35, 38]),
      test_11_12: buildScoreRows([3, 12, 24, 34, 43, 51, 60, 67, 73]),
      test_13: buildScoreRows([1, 2, 5, 8, 11, 14, 18, 21, 24]),
      test_14: buildScoreRows([8, 12, 16, 20, 23, 27, 31, 35, 39]),
      test_13_14: buildScoreRows([13, 20, 25, 30, 35, 40, 45, 52, 59]),
      test_14_wrong: buildScoreRows([21, 17, 14, 11, 8, 5, 2, null, null]),
    },
    total: [],
  },
  '40_49': {
    columns: {
      test_1_2: buildScoreRows([1, 6, 17, 29, 40, 51, 62, 70, 77]),
      test_3: buildScoreRows([5, 9, 12, 16, 20, 24, 28, 31, 34]),
      test_4: buildScoreRows([6, 10, 14, 18, 22, 25, 28, 31, 34]),
      test_3_4: buildScoreRows([13, 20, 27, 34, 41, 48, 55, 61, 67]),
      test_5: buildScoreRows([null, 1, 3, 7, 14, 22, 30, 36, 40]),
      test_6: buildScoreRows([1, 2, 8, 14, 20, 26, 32, 38, 44]),
      test_5_6: buildScoreRows([1, 4, 13, 22, 32, 45, 58, 69, 80]),
      test_7: buildScoreRows([4, 6, 8, 11, 15, 19, 24, 29, 35]),
      test_8: buildScoreRows([null, null, 1, 5, 11, 20, 29, 36, 40]),
      test_9: buildScoreRows([1, 2, 7, 13, 18, 23, 28, 32, 35]),
      test_10: buildScoreRows([null, 1, 5, 10, 15, 20, 26, 32, 37]),
      test_7_10: buildScoreRows([6, 16, 26, 38, 50, 64, 79, 103, 123]),
      test_11: buildScoreRows([1, 6, 10, 14, 18, 23, 27, 31, 34]),
      test_12: buildScoreRows([1, 6, 11, 16, 21, 25, 30, 34, 38]),
      test_11_12: buildScoreRows([1, 9, 20, 29, 38, 45, 53, 61, 69]),
      test_13: buildScoreRows([null, 1, 4, 7, 10, 13, 16, 19, 23]),
      test_14: buildScoreRows([6, 10, 14, 18, 22, 26, 30, 34, 38]),
      test_13_14: buildScoreRows([11, 18, 23, 28, 32, 37, 43, 49, 56]),
      test_14_wrong: buildScoreRows([20, 16, 13, 10, 7, 4, 1, null, null]),
    },
    total: [],
  },
  '50_plus': {
    columns: {
      test_1_2: buildScoreRows([1, 4, 16, 29, 41, 52, 62, 70, 77]),
      test_3: buildScoreRows([4, 7, 11, 14, 18, 22, 25, 29, 32]),
      test_4: buildScoreRows([4, 8, 12, 16, 20, 24, 27, 30, 33]),
      test_3_4: buildScoreRows([8, 15, 22, 29, 37, 45, 51, 57, 63]),
      test_5: buildScoreRows([null, 1, 2, 5, 11, 18, 28, 34, 39]),
      test_6: buildScoreRows([null, 1, 6, 12, 18, 23, 28, 34, 39]),
      test_5_6: buildScoreRows([null, 1, 10, 19, 29, 41, 52, 63, 74]),
      test_7: buildScoreRows([3, 5, 7, 9, 12, 16, 21, 26, 32]),
      test_8: buildScoreRows([null, null, 1, 3, 9, 16, 24, 32, 39]),
      test_9: buildScoreRows([null, 1, 5, 9, 14, 19, 23, 26, 29]),
      test_10: buildScoreRows([null, 1, 2, 7, 12, 18, 23, 28, 34]),
      test_7_10: buildScoreRows([1, 9, 19, 29, 40, 52, 65, 76, 89]),
      test_11: buildScoreRows([1, 2, 6, 10, 15, 19, 24, 29, 33]),
      test_12: buildScoreRows([1, 2, 8, 13, 17, 21, 25, 30, 34]),
      test_11_12: buildScoreRows([1, 5, 15, 22, 29, 37, 44, 52, 59]),
      test_13: buildScoreRows([null, 1, 2, 5, 8, 11, 14, 17, 20]),
      test_14: buildScoreRows([4, 8, 12, 15, 19, 23, 28, 33, 37]),
      test_13_14: buildScoreRows([9, 15, 20, 25, 29, 33, 38, 43, 49]),
      test_14_wrong: buildScoreRows([18, 15, 11, 8, 5, 2, null, null, null]),
    },
    total: [],
  },
};

export const LPS_B_IQ_BY_PR_RANGES: Array<{ minPr: number; maxPr: number; iq: number }> = [];
