export type LpsColumn3Option = { id: string; src?: string; pathData?: string; transform?: string };

export type LpsPage1Row = {
  id: number;
  column1: string;
  column2: string;
  column3?: LpsColumn3Option[];
  column3SvgMeta?: { viewBox: string; width: number; height: number };
  column4: string;
  column5: string;
};

export type LpsColumnEntry = {
  id: number;
  word: string;
  /**
   * Zero-based index of the correct character within the word.
   * When omitted, the entry will not contribute to scoring.
   */
  correctIndex?: number;
};

export type LpsPage1Solution = {
  /**
   * Zero-based indices that represent the correct characters for each column entry.
   * Selecting one of these indices awards a point for the row/column.
   */
  col1?: number[];
  col2?: number[];
  col3?: number[];
  col4?: number[];
  col5?: number[];
};

type LpsColumn3Row = {
  id: number;
  options: LpsColumn3Option[];
  correctIndex?: number;
  svgMeta?: { viewBox: string; width: number; height: number };
};

export type LpsDataset = {
  rows: LpsPage1Row[];
  solutions: LpsPage1Solution[];
};

export const LPS_PAGE1_COLUMN1: LpsColumnEntry[] = [
  { id: 1, word: 'Auvzug', correctIndex: 0 },
  { id: 2, word: 'Freute', correctIndex: 0 },
  { id: 3, word: 'Alaske', correctIndex: 0 },
  { id: 4, word: 'Bezirg', correctIndex: 0 },
  { id: 5, word: 'Ardist', correctIndex: 0 },
  { id: 6, word: 'Waknis', correctIndex: 0 },
  { id: 7, word: 'Gewaih', correctIndex: 0 },
  { id: 8, word: 'Magmet', correctIndex: 0 },
  { id: 9, word: 'Legiom', correctIndex: 0 },
  { id: 10, word: 'Jasmim', correctIndex: 0 },
  { id: 11, word: 'Tiskus', correctIndex: 0 },
  { id: 12, word: 'Panema', correctIndex: 0 },
  { id: 13, word: 'Heliun', correctIndex: 0 },
  { id: 14, word: 'Ratium', correctIndex: 0 },
  { id: 15, word: 'Vugger', correctIndex: 0 },
  { id: 16, word: 'Etison', correctIndex: 0 },
  { id: 17, word: 'Vright', correctIndex: 0 },
  { id: 18, word: 'Prokat', correctIndex: 0 },
  { id: 19, word: 'Attilo', correctIndex: 0 },
  { id: 20, word: 'Dnjebr', correctIndex: 0 },
  { id: 21, word: 'Toulom', correctIndex: 0 },
  { id: 22, word: 'Amatom', correctIndex: 0 },
  { id: 23, word: 'Namtes', correctIndex: 0 },
  { id: 24, word: 'Naffia', correctIndex: 0 },
  { id: 25, word: 'Garies', correctIndex: 0 },
  { id: 26, word: 'Virkil', correctIndex: 0 },
  { id: 27, word: 'Grachd', correctIndex: 0 },
  { id: 28, word: 'Armika', correctIndex: 0 },
  { id: 29, word: 'Mohavk', correctIndex: 0 },
  { id: 30, word: 'Hamgar', correctIndex: 0 },
  { id: 31, word: 'Prodon', correctIndex: 0 },
  { id: 32, word: 'Emplem', correctIndex: 0 },
  { id: 33, word: 'Anemie', correctIndex: 0 },
  { id: 34, word: 'Osires', correctIndex: 0 },
  { id: 35, word: 'Sharge', correctIndex: 0 },
  { id: 36, word: 'Druite', correctIndex: 0 },
  { id: 37, word: 'Behain', correctIndex: 0 },
  { id: 38, word: 'Kaulin', correctIndex: 0 },
  { id: 39, word: 'Läsiom', correctIndex: 0 },
  { id: 40, word: 'Gounot', correctIndex: 0 }
];

export const LPS_PAGE1_COLUMN2: LpsColumnEntry[] = [
  { id: 1, word: 'Heimad', correctIndex: 0 },
  { id: 2, word: 'Herink', correctIndex: 0 },
  { id: 3, word: 'Expord', correctIndex: 0 },
  { id: 4, word: 'Pulwer', correctIndex: 0 },
  { id: 5, word: 'Jeguar', correctIndex: 0 },
  { id: 6, word: 'Penzin', correctIndex: 0 },
  { id: 7, word: 'Gasino', correctIndex: 0 },
  { id: 8, word: 'Disdel', correctIndex: 0 },
  { id: 9, word: 'Madred', correctIndex: 0 },
  { id: 10, word: 'Rikrut', correctIndex: 0 },
  { id: 11, word: 'Kegend', correctIndex: 0 },
  { id: 12, word: 'Barole', correctIndex: 0 },
  { id: 13, word: 'Rokoke', correctIndex: 0 },
  { id: 14, word: 'Ottawe', correctIndex: 0 },
  { id: 15, word: 'Spagad', correctIndex: 0 },
  { id: 16, word: 'Dewise', correctIndex: 0 },
  { id: 17, word: 'Riflex', correctIndex: 0 },
  { id: 18, word: 'Bombai', correctIndex: 0 },
  { id: 19, word: 'Tegris', correctIndex: 0 },
  { id: 20, word: 'Nimphe', correctIndex: 0 },
  { id: 21, word: 'Delfhi', correctIndex: 0 },
  { id: 22, word: 'Beuxit', correctIndex: 0 },
  { id: 23, word: 'Okolar', correctIndex: 0 },
  { id: 24, word: 'Gheops', correctIndex: 0 },
  { id: 25, word: 'Kripta', correctIndex: 0 },
  { id: 26, word: 'Netrat', correctIndex: 0 },
  { id: 27, word: 'Smirna', correctIndex: 0 },
  { id: 28, word: 'Akelai', correctIndex: 0 },
  { id: 29, word: 'Budgot', correctIndex: 0 },
  { id: 30, word: 'Amelfi', correctIndex: 0 },
  { id: 31, word: 'Alippo', correctIndex: 0 },
  { id: 32, word: 'Komdur', correctIndex: 0 },
  { id: 33, word: 'Bowist', correctIndex: 0 },
  { id: 34, word: 'Prokob', correctIndex: 0 },
  { id: 35, word: 'Honwed', correctIndex: 0 },
  { id: 36, word: 'Laudom', correctIndex: 0 },
  { id: 37, word: 'Pantur', correctIndex: 0 },
  { id: 38, word: 'Ascudo', correctIndex: 0 },
  { id: 39, word: 'Hoplid', correctIndex: 0 },
  { id: 40, word: 'Anubes', correctIndex: 0 },
];

export const LPS_PAGE1_COLUMN4: LpsColumnEntry[] = [
  { id: 1, word: 'xxxxxxxox', correctIndex: 0 },
  { id: 2, word: 'Herink', correctIndex: 0 },
  { id: 3, word: 'Expord', correctIndex: 0 },
  { id: 4, word: 'Pulwer', correctIndex: 0 },
  { id: 5, word: 'Jeguar', correctIndex: 0 },
  { id: 6, word: 'Penzin', correctIndex: 0 },
  { id: 7, word: 'Gasino', correctIndex: 0 },
  { id: 8, word: 'Disdel', correctIndex: 0 },
  { id: 9, word: 'Madred', correctIndex: 0 },
  { id: 10, word: 'Rikrut', correctIndex: 0 },
  { id: 11, word: 'Kegend', correctIndex: 0 },
  { id: 12, word: 'Barole', correctIndex: 0 },
  { id: 13, word: 'Rokoke', correctIndex: 0 },
  { id: 14, word: 'Ottawe', correctIndex: 0 },
  { id: 15, word: 'Spagad', correctIndex: 0 },
  { id: 16, word: 'Dewise', correctIndex: 0 },
  { id: 17, word: 'Riflex', correctIndex: 0 },
  { id: 18, word: 'Bombai', correctIndex: 0 },
  { id: 19, word: 'Tegris', correctIndex: 0 },
  { id: 20, word: 'Nimphe', correctIndex: 0 },
  { id: 21, word: 'Delfhi', correctIndex: 0 },
  { id: 22, word: 'Beuxit', correctIndex: 0 },
  { id: 23, word: 'Okolar', correctIndex: 0 },
  { id: 24, word: 'Gheops', correctIndex: 0 },
  { id: 25, word: 'Kripta', correctIndex: 0 },
  { id: 26, word: 'Netrat', correctIndex: 0 },
  { id: 27, word: 'Smirna', correctIndex: 0 },
  { id: 28, word: 'Akelai', correctIndex: 0 },
  { id: 29, word: 'Budgot', correctIndex: 0 },
  { id: 30, word: 'Amelfi', correctIndex: 0 },
  { id: 31, word: 'Alippo', correctIndex: 0 },
  { id: 32, word: 'Komdur', correctIndex: 0 },
  { id: 33, word: 'Bowist', correctIndex: 0 },
  { id: 34, word: 'Prokob', correctIndex: 0 },
  { id: 35, word: 'Honwed', correctIndex: 0 },
  { id: 36, word: 'Laudom', correctIndex: 0 },
  { id: 37, word: 'Pantur', correctIndex: 0 },
  { id: 38, word: 'Ascudo', correctIndex: 0 },
  { id: 39, word: 'Hoplid', correctIndex: 0 },
  { id: 40, word: 'Anubes', correctIndex: 0 },
];

export const LPS_PAGE1_COLUMN5: LpsColumnEntry[] = [
  { id: 1, word: 'xxxxxxxox', correctIndex: 0 },
  { id: 2, word: 'xxxxxxxox', correctIndex: 0 },
  { id: 3, word: 'xxxxxxxox', correctIndex: 0 },
  { id: 4, word: 'xxxxxxxox', correctIndex: 0 },
];

const LPS_PAGE1_COLUMN3_B_ROW1_SHAPES = [
  `
        M 42.3723 41.5322
        A 1.64 1.64 0.0 0 1 40.7294 43.1693
        L 7.0694 43.1106
        A 1.64 1.64 0.0 0 1 5.4323 41.4677
        L 5.4877 9.7078
        A 1.64 1.64 0.0 0 1 7.1306 8.0707
        L 40.7906 8.1294
        A 1.64 1.64 0.0 0 1 42.4277 9.7723
        L 42.3723 41.5322
        Z
        M 39.55 13.47
        A 1.62 1.62 0.0 0 0 37.93 11.85
        L 10.01 11.85
        A 1.62 1.62 0.0 0 0 8.39 13.47
        L 8.39 37.91
        A 1.62 1.62 0.0 0 0 10.01 39.53
        L 37.93 39.53
        A 1.62 1.62 0.0 0 0 39.55 37.91
        L 39.55 13.47
        Z
      `,
  `
        M 98.0866 41.7788
        A 1.24 1.24 0.0 0 1 96.8510 43.0232
        L 64.3512 43.1366
        A 1.24 1.24 0.0 0 1 63.1068 41.9010
        L 62.9934 9.4012
        A 1.24 1.24 0.0 0 1 64.2290 8.1568
        L 96.7288 8.0434
        A 1.24 1.24 0.0 0 1 97.9732 9.2790
        L 98.0866 41.7788
        Z
        M 95.0540 13.0137
        A 1.42 1.42 0.0 0 0 93.6266 11.6012
        L 67.2669 11.7392
        A 1.42 1.42 0.0 0 0 65.8544 13.1666
        L 65.9860 38.3063
        A 1.42 1.42 0.0 0 0 67.4134 39.7188
        L 93.7731 39.5808
        A 1.42 1.42 0.0 0 0 95.1856 38.1534
        L 95.0540 13.0137
        Z
      `,
  `
        M 156.06 41.78
        A 1.34 1.34 0.0 0 1 154.72 43.12
        L 120.52 43.12
        A 1.34 1.34 0.0 0 1 119.18 41.78
        L 119.18 9.52
        A 1.34 1.34 0.0 0 1 120.52 8.18
        L 154.72 8.18
        A 1.34 1.34 0.0 0 1 156.06 9.52
        L 156.06 41.78
        Z
        M 153.2329 13.4147
        A 1.59 1.59 0.0 0 0 151.6484 11.8191
        L 123.5486 11.7210
        A 1.59 1.59 0.0 0 0 121.9531 13.3055
        L 121.8671 37.9253
        A 1.59 1.59 0.0 0 0 123.4516 39.5209
        L 151.5514 39.6190
        A 1.59 1.59 0.0 0 0 153.1469 38.0345
        L 153.2329 13.4147
        Z
      `,
  `
        M 213.4653 41.2760
        A 1.73 1.73 0.0 0 1 211.7413 43.0120
        L 178.5815 43.1278
        A 1.73 1.73 0.0 0 1 176.8455 41.4038
        L 176.7347 9.6840
        A 1.73 1.73 0.0 0 1 178.4587 7.9480
        L 211.6185 7.8322
        A 1.73 1.73 0.0 0 1 213.3545 9.5562
        L 213.4653 41.2760
        Z
        M 210.63 12.81
        A 1.29 1.29 0.0 0 0 209.34 11.52
        L 181.00 11.52
        A 1.29 1.29 0.0 0 0 179.71 12.81
        L 179.71 38.26
        A 1.29 1.29 0.0 0 0 181.00 39.55
        L 209.34 39.55
        A 1.29 1.29 0.0 0 0 210.63 38.26
        L 210.63 12.81
        Z
      `,
  `
        M 269.2012 41.7703
        A 1.13 1.13 0.0 0 1 268.0693 42.8983
        L 235.5693 42.8416
        A 1.13 1.13 0.0 0 1 234.4413 41.7096
        L 234.4988 8.7897
        A 1.13 1.13 0.0 0 1 235.6307 7.6617
        L 268.1307 7.7184
        A 1.13 1.13 0.0 0 1 269.2587 8.8504
        L 269.2012 41.7703
        Z
        M 266.2924 12.5053
        A 1.23 1.23 0.0 0 0 265.0646 11.2731
        L 238.5646 11.2269
        A 1.23 1.23 0.0 0 0 237.3325 12.4547
        L 237.2876 38.1947
        A 1.23 1.23 0.0 0 0 238.5154 39.4269
        L 265.0154 39.4731
        A 1.23 1.23 0.0 0 0 266.2475 38.2453
        L 266.2924 12.5053
        Z
      `,
  `
        M 326.4384 41.3385
        A 1.43 1.43 0.0 0 1 325.0109 42.7710
        L 291.8109 42.8289
        A 1.43 1.43 0.0 0 1 290.3784 41.4014
        L 290.3216 8.8615
        A 1.43 1.43 0.0 0 1 291.7491 7.4290
        L 324.9491 7.3711
        A 1.43 1.43 0.0 0 1 326.3816 8.7986
        L 326.4384 41.3385
        Z
        M 323.5576 12.9372
        A 1.85 1.85 0.0 0 0 321.7011 11.0937
        L 295.1013 11.1865
        A 1.85 1.85 0.0 0 0 293.2578 13.0430
        L 293.3424 37.3028
        A 1.85 1.85 0.0 0 0 295.1989 39.1463
        L 321.7987 39.0535
        A 1.85 1.85 0.0 0 0 323.6422 37.1970
        L 323.5576 12.9372
        Z
      `,
  `
        M 384.2911 41.7612
        A 1.14 1.14 0.0 0 1 383.1492 42.8992
        L 349.6492 42.8407
        A 1.14 1.14 0.0 0 1 348.5112 41.6988
        L 348.5689 8.6588
        A 1.14 1.14 0.0 0 1 349.7108 7.5208
        L 383.2108 7.5793
        A 1.14 1.14 0.0 0 1 384.3488 8.7212
        L 384.2911 41.7612
        Z
        M 381.6919 12.7064
        A 1.42 1.42 0.0 0 0 380.2744 11.2839
        L 352.9145 11.2361
        A 1.42 1.42 0.0 0 0 351.4920 12.6537
        L 351.4481 37.8136
        A 1.42 1.42 0.0 0 0 352.8656 39.2361
        L 380.2255 39.2839
        A 1.42 1.42 0.0 0 0 381.6480 37.8663
        L 381.6919 12.7064
        Z
      `,
  `
        M 440.9968 25.3109
        A 2.28 2.28 0.0 0 1 440.9799 28.5353
        L 425.3707 43.9818
        A 2.28 2.28 0.0 0 1 422.1464 43.9649
        L 405.7432 27.3891
        A 2.28 2.28 0.0 0 1 405.7601 24.1647
        L 421.3693 8.7182
        A 2.28 2.28 0.0 0 1 424.5936 8.7351
        L 440.9968 25.3109
        Z
        M 436.34 28.41
        A 2.11 2.11 0.0 0 0 436.34 25.43
        L 424.37 13.46
        A 2.11 2.11 0.0 0 0 421.39 13.46
        L 410.50 24.35
        A 2.11 2.11 0.0 0 0 410.50 27.33
        L 422.48 39.30
        A 2.11 2.11 0.0 0 0 425.46 39.30
        L 436.34 28.41
        Z
      `,
];

const BASE_CIRCLE_PATH = `
  M 50 32
  A 18 18 0 1 1 14 32
  A 18 18 0 1 1 50 32
  Z
`;

const LPS_PAGE1_COLUMN3_B_ROW2_SHAPES = [
  `${BASE_CIRCLE_PATH} M 32 10 L 24 22 L 40 22 Z`,
  `${BASE_CIRCLE_PATH} M 24 42 L 40 42 L 32 54 Z`,
  `${BASE_CIRCLE_PATH} M 10 32 L 22 24 L 22 40 Z`,
  `${BASE_CIRCLE_PATH} M 54 32 L 42 24 L 42 40 Z`,
  `${BASE_CIRCLE_PATH} M 32 20 L 24 32 L 40 32 Z`,
  `${BASE_CIRCLE_PATH} M 24 32 L 40 32 L 32 44 Z`,
  `${BASE_CIRCLE_PATH} M 24 22 L 40 22 L 32 14 Z M 24 42 L 40 42 L 32 50 Z`,
  `${BASE_CIRCLE_PATH}`,
];

const LPS_PAGE1_COLUMN3_B: LpsColumn3Row[] = [
  {
    id: 1,
    options: LPS_PAGE1_COLUMN3_B_ROW1_SHAPES.map((pathData, idx) => ({
      id: `lps-b-r1-shape${idx + 1}`,
      pathData,
    })),
    correctIndex: 7,
    svgMeta: { viewBox: '0 0 446 54', width: 446, height: 54 },
  },
  {
    id: 2,
    options: LPS_PAGE1_COLUMN3_B_ROW2_SHAPES.map((pathData, idx) => ({
      id: `lps-b-r2-shape${idx + 1}`,
      pathData,
      transform: `translate(${idx * 64} 0)`,
    })),
    correctIndex: 0,
    svgMeta: { viewBox: '0 0 512 64', width: 512, height: 64 },
  },
];

function buildRow(
  rowIdx: number,
  column1: LpsColumnEntry[],
  column2: LpsColumnEntry[],
  column4: LpsColumnEntry[],
  column5: LpsColumnEntry[],
  column3Rows?: LpsColumn3Row[],
): LpsPage1Row {
  const col1 = column1[rowIdx];
  const col2 = column2[rowIdx];
  const col4 = column4[rowIdx];
  const col5 = column5[rowIdx];
  const col3 = column3Rows?.[rowIdx];
  const fallbackId = rowIdx + 1;
  return {
    id: col1?.id ?? col2?.id ?? col4?.id ?? col5?.id ?? col3?.id ?? fallbackId,
    column1: col1?.word ?? '',
    column2: col2?.word ?? '',
    column3: col3?.options,
    column3SvgMeta: col3?.svgMeta,
    column4: col4?.word ?? '',
    column5: col5?.word ?? '',
  };
}

function buildRows(
  column1: LpsColumnEntry[],
  column2: LpsColumnEntry[],
  column4: LpsColumnEntry[],
  column5: LpsColumnEntry[],
  column3Rows?: LpsColumn3Row[],
) {
  const rowCount = Math.max(column1.length, column2.length, column4.length, column5.length, column3Rows?.length ?? 0);
  return Array.from({ length: rowCount }, (_, idx) => buildRow(idx, column1, column2, column4, column5, column3Rows));
}

function extractSolution(entry?: LpsColumnEntry): number[] {
  if (!entry || typeof entry.correctIndex !== 'number') return [];
  if (entry.correctIndex < 0 || entry.correctIndex >= entry.word.length) return [];
  return [entry.correctIndex];
}

function extractColumn3Solution(entry?: LpsColumn3Row): number[] {
  if (!entry || typeof entry.correctIndex !== 'number') return [];
  if (entry.correctIndex < 0 || entry.correctIndex >= entry.options.length) return [];
  return [entry.correctIndex];
}

function buildSolutions(
  column1: LpsColumnEntry[],
  column2: LpsColumnEntry[],
  column4: LpsColumnEntry[],
  column5: LpsColumnEntry[],
  column3Rows?: LpsColumn3Row[],
) {
  return buildRows(column1, column2, column4, column5, column3Rows).map((_, idx) => ({
    col1: extractSolution(column1[idx]),
    col2: extractSolution(column2[idx]),
    col3: extractColumn3Solution(column3Rows?.[idx]),
    col4: extractSolution(column4[idx]),
    col5: extractSolution(column5[idx]),
  }));
}

export const LPS_PAGE1_ROWS: LpsPage1Row[] = buildRows(LPS_PAGE1_COLUMN1, LPS_PAGE1_COLUMN2, LPS_PAGE1_COLUMN4, LPS_PAGE1_COLUMN5);

export const LPS_PAGE1_SOLUTIONS: LpsPage1Solution[] = buildSolutions(
  LPS_PAGE1_COLUMN1,
  LPS_PAGE1_COLUMN2,
  LPS_PAGE1_COLUMN4,
  LPS_PAGE1_COLUMN5,
);

const LPS_PAGE1_COLUMN1_A = LPS_PAGE1_COLUMN1.slice(0, 20);
const LPS_PAGE1_COLUMN2_A = LPS_PAGE1_COLUMN2.slice(0, 20);
const LPS_PAGE1_COLUMN4_A = LPS_PAGE1_COLUMN4.slice(0, 20);
const LPS_PAGE1_COLUMN5_A = LPS_PAGE1_COLUMN4.slice(0, 20);

export const LPS_PAGE1_ROWS_A: LpsPage1Row[] = buildRows(
  LPS_PAGE1_COLUMN1_A,
  LPS_PAGE1_COLUMN2_A,
  LPS_PAGE1_COLUMN4_A,
  LPS_PAGE1_COLUMN5_A,
);

export const LPS_PAGE1_SOLUTIONS_A: LpsPage1Solution[] = buildSolutions(
  LPS_PAGE1_COLUMN1_A,
  LPS_PAGE1_COLUMN2_A,
  LPS_PAGE1_COLUMN4_A,
  LPS_PAGE1_COLUMN5_A,
);

const LPS_PAGE1_COLUMN1_B: LpsColumnEntry[] = [
  { id: 1, word: 'Auyzug', correctIndex: 2 },
  { id: 2, word: 'Freute', correctIndex: 4 },
  { id: 3, word: 'Alaske', correctIndex: 5 },
  { id: 4, word: 'Bezirg', correctIndex: 5 },
  { id: 5, word: 'Ardist', correctIndex: 2 },
  { id: 6, word: 'Waknis', correctIndex: 2 },
  { id: 7, word: 'Gewaih', correctIndex: 3 },
  { id: 8, word: 'Magmet', correctIndex: 3 },
  { id: 9, word: 'Legiom', correctIndex: 5 },
  { id: 10, word: 'Jasmim', correctIndex: 5 },
  { id: 11, word: 'Tiskus', correctIndex: 0 },
  { id: 12, word: 'Panema', correctIndex: 3 },
  { id: 13, word: 'Heliun', correctIndex: 5 },
  { id: 14, word: 'Ratium', correctIndex: 2 },
  { id: 15, word: 'Vugger', correctIndex: 0 },
  { id: 16, word: 'Etison', correctIndex: 1 },
  { id: 17, word: 'Vright', correctIndex: 0 },
  { id: 18, word: 'Prokat', correctIndex: 0 },
  { id: 19, word: 'Attilo', correctIndex: 5 },
  { id: 20, word: 'Dnjebr', correctIndex: 4 },
  { id: 21, word: 'Toulom', correctIndex: 5 },
  { id: 22, word: 'Amatom', correctIndex: 1 },
  { id: 23, word: 'Namtes', correctIndex: 2 },
  { id: 24, word: 'Naffia', correctIndex: 0 },
  { id: 25, word: 'Garies', correctIndex: 0 },
  { id: 26, word: 'Virkil', correctIndex: 3 },
  { id: 27, word: 'Grachd', correctIndex: 5 },
  { id: 28, word: 'Armika', correctIndex: 2 },
  { id: 29, word: 'Mohavk', correctIndex: 4 },
  { id: 30, word: 'Hamgar', correctIndex: 2 },
  { id: 31, word: 'Prodon', correctIndex: 3 },
  { id: 32, word: 'Emplem', correctIndex: 2 },
  { id: 33, word: 'Anemie', correctIndex: 2 },
  { id: 34, word: 'Osires', correctIndex: 4 },
  { id: 35, word: 'Sharge', correctIndex: 0 },
  { id: 36, word: 'Druite', correctIndex: 4 },
  { id: 37, word: 'Behain', correctIndex: 5 },
  { id: 38, word: 'Kaulin', correctIndex: 2 },
  { id: 39, word: 'Läsiom', correctIndex: 5 },
  { id: 40, word: 'Gounot', correctIndex: 5 }
];

const LPS_PAGE1_COLUMN2_B: LpsColumnEntry[] = [
  { id: 1, word: 'Heimad', correctIndex: 5 },
  { id: 2, word: 'Herink', correctIndex: 5 },
  { id: 3, word: 'Expord', correctIndex: 5 },
  { id: 4, word: 'Pulwer', correctIndex: 3 },
  { id: 5, word: 'Jeguar', correctIndex: 1 },
  { id: 6, word: 'Penzin', correctIndex: 0 },
  { id: 7, word: 'Gasino', correctIndex: 0 },
  { id: 8, word: 'Disdel', correctIndex: 3 },
  { id: 9, word: 'Madred', correctIndex: 4 },
  { id: 10, word: 'Rikrut', correctIndex: 1 },
  { id: 11, word: 'Kegend', correctIndex: 0 },
  { id: 12, word: 'Barole', correctIndex: 0 },
  { id: 13, word: 'Rokoke', correctIndex: 5 },
  { id: 14, word: 'Ottawe', correctIndex: 5 },
  { id: 15, word: 'Spagad', correctIndex: 5 },
  { id: 16, word: 'Dewise', correctIndex: 2 },
  { id: 17, word: 'Riflex', correctIndex: 1 },
  { id: 18, word: 'Bombai', correctIndex: 5 },
  { id: 19, word: 'Tegris', correctIndex: 1 },
  { id: 20, word: 'Nimphe', correctIndex: 1 },
  { id: 21, word: 'Delfhi', correctIndex: 3 },
  { id: 22, word: 'Beuxit', correctIndex: 1 },
  { id: 23, word: 'Okolar', correctIndex: 2 },
  { id: 24, word: 'Gheops', correctIndex: 0 },
  { id: 25, word: 'Kripta', correctIndex: 2 },
  { id: 26, word: 'Netrat', correctIndex: 1 },
  { id: 27, word: 'Smirna', correctIndex: 2 },
  { id: 28, word: 'Akelai', correctIndex: 4 },
  { id: 29, word: 'Budgot', correctIndex: 4 },
  { id: 30, word: 'Amelfi', correctIndex: 2 },
  { id: 31, word: 'Alippo', correctIndex: 2 },
  { id: 32, word: 'Komdur', correctIndex: 3 },
  { id: 33, word: 'Bowist', correctIndex: 2 },
  { id: 34, word: 'Prokob', correctIndex: 5 },
  { id: 35, word: 'Honwed', correctIndex: 3 },
  { id: 36, word: 'Laudom', correctIndex: 5 },
  { id: 37, word: 'Pantur', correctIndex: 3 },
  { id: 38, word: 'Ascudo', correctIndex: 0 },
  { id: 39, word: 'Hoplid', correctIndex: 5 },
  { id: 40, word: 'Anubes', correctIndex: 4 },
];
const LPS_PAGE1_COLUMN4_B: LpsColumnEntry[] = [
  { id: 1, word: 'xxxxxxxox', correctIndex: 7 },
  { id: 2, word: 'ababababb', correctIndex: 8 },
  { id: 3, word: 'xoxoxoxoo', correctIndex: 8 },
  { id: 4, word: '123123423', correctIndex: 6 },
  { id: 5, word: '00n00n000', correctIndex: 8 },
  { id: 6, word: 'abcdefchi', correctIndex: 6 },
  { id: 7, word: 'aBaBaBaba', correctIndex: 7 },
  { id: 8, word: '2468912141618', correctIndex: 4 },
  { id: 9, word: 'abcabcdbc', correctIndex: 6 },
  { id: 10, word: '121222121', correctIndex: 4 },
  { id: 11, word: '5105205306405', correctIndex: 6 },
  { id: 12, word: '19171513118753', correctIndex: 5 },
  { id: 13, word: 'abcdeFghi', correctIndex: 5 },
  { id: 14, word: 'jklmnopqs', correctIndex: 8 },
  { id: 15, word: 'BBaBaBaBa', correctIndex: 0 },
  { id: 16, word: 'rqtuvwxyz', correctIndex: 1 },
  { id: 17, word: '349121518212427', correctIndex: 1 },
  { id: 18, word: 'xxoxxoxoo', correctIndex: 7 },
  { id: 19, word: 'ijkLmnopq', correctIndex: 3 },
  { id: 20, word: 'bbcddefghhijj', correctIndex: 4 },
  { id: 21, word: 'ihgfeDcba', correctIndex: 5 },
  { id: 22, word: 'abbcddeeffghhi', correctIndex: 4 },
  { id: 23, word: 'dcedcbdcb', correctIndex: 2 },
  { id: 24, word: '10128146163182', correctIndex: 6 },
  { id: 25, word: 'bCdEFGhIj', correctIndex: 4 },
  { id: 26, word: '151812219245273', correctIndex: 6 },
  { id: 27, word: '234567432', correctIndex: 5 },
  { id: 28, word: '124787421', correctIndex: 4 },
  { id: 29, word: '1fg2dc3ba', correctIndex: 2 },
  { id: 30, word: '125344352', correctIndex: 0 },
  { id: 31, word: 'abegikmoq', correctIndex: 1 },
  { id: 32, word: 'aaceefiikmmoqq', correctIndex: 3 },
  { id: 33, word: '5a4c3d2e1', correctIndex: 1 },
  { id: 34, word: 'rPmLjHfDb', correctIndex: 2 },
  { id: 35, word: '1124394166', correctIndex: 8 },
  { id: 36, word: 'abccadaea', correctIndex: 2 },
  { id: 37, word: 'baedhgkln', correctIndex: 7 },
  { id: 38, word: 'cbfeihjko', correctIndex: 6 },
  { id: 39, word: '1123711162229', correctIndex: 3 },
  { id: 40, word: 'aAbCdEgGh', correctIndex: 8 },
];

const LPS_PAGE1_COLUMN5_B: LpsColumnEntry[] = [
  { id: 1, word: 'LECKF', correctIndex: 4 },
  { id: 2, word: 'AGENW', correctIndex: 4 },
  { id: 3, word: 'TURMS', correctIndex: 4 },
  { id: 4, word: 'TBRAU', correctIndex: 1 },
  { id: 5, word: 'RBAUE', correctIndex: 1 },
  { id: 6, word: 'OCKST', correctIndex: 3 },
  { id: 7, word: 'FUCSH', correctIndex: 0 },
  { id: 8, word: 'BETAU', correctIndex: 2 },
  { id: 9, word: 'AUBST', correctIndex: 3 },
  { id: 10, word: 'EGURK', correctIndex: 1 },
  { id: 11, word: 'DENFA', correctIndex: 3 },
  { id: 12, word: 'RUSTB', correctIndex: 4 },
  { id: 13, word: 'NDFEI', correctIndex: 2 },
  { id: 14, word: 'AWODK', correctIndex: 1 },
  { id: 15, word: 'ELAUN', correctIndex: 1 },
  { id: 16, word: 'TIGRE', correctIndex: 0 },
  { id: 17, word: 'TOURS', correctIndex: 1 },
  { id: 18, word: 'MUSKI', correctIndex: 0 },
  { id: 19, word: 'ENLAK', correctIndex: 2 },
  { id: 20, word: 'NROMA', correctIndex: 1 },
  { id: 21, word: 'UMDAT', correctIndex: 2 },
  { id: 22, word: 'MAFIR', correctIndex: 2 },
  { id: 23, word: 'LHAGE', correctIndex: 1 },
  { id: 24, word: 'DRATH', correctIndex: 0 },
  { id: 25, word: 'ISTEK', correctIndex: 4 },
  { id: 26, word: 'CHEWA', correctIndex: 3 },
  { id: 27, word: 'MEBLU', correctIndex: 2 },
  { id: 28, word: 'RACKW', correctIndex: 4 },
  { id: 29, word: 'NESAH', correctIndex: 2 },
  { id: 30, word: 'ARBEF', correctIndex: 4 },
  { id: 31, word: 'TANGS', correctIndex: 1 },
  { id: 32, word: 'BUCEH', correctIndex: 0 },
  { id: 33, word: 'ARKEM', correctIndex: 4 },
  { id: 34, word: 'AMINK', correctIndex: 4 },
  { id: 35, word: 'BNETO', correctIndex: 0 },
  { id: 36, word: 'OMCHR', correctIndex: 2 },
  { id: 37, word: 'RKEBI', correctIndex: 3 },
  { id: 38, word: 'INRHE', correctIndex: 2 },
  { id: 39, word: 'ACHIN', correctIndex: 1 },
  { id: 40, word: 'IHGON', correctIndex: 1 },
];
export const LPS_PAGE1_ROWS_B: LpsPage1Row[] = buildRows(
  LPS_PAGE1_COLUMN1_B,
  LPS_PAGE1_COLUMN2_B,
  LPS_PAGE1_COLUMN4_B,
  LPS_PAGE1_COLUMN5_B,
  LPS_PAGE1_COLUMN3_B,
);

export const LPS_PAGE1_SOLUTIONS_B: LpsPage1Solution[] = buildSolutions(
  LPS_PAGE1_COLUMN1_B,
  LPS_PAGE1_COLUMN2_B,
  LPS_PAGE1_COLUMN4_B,
  LPS_PAGE1_COLUMN5_B,
  LPS_PAGE1_COLUMN3_B,
);

export function getLpsDataset(testName?: string): LpsDataset {
  if (testName === 'LPS-A') {
    return { rows: LPS_PAGE1_ROWS_A, solutions: LPS_PAGE1_SOLUTIONS_A };
  }
  if (testName === 'LPS-B') {
    return { rows: LPS_PAGE1_ROWS_B, solutions: LPS_PAGE1_SOLUTIONS_B };
  }
  return { rows: LPS_PAGE1_ROWS, solutions: LPS_PAGE1_SOLUTIONS };
}


