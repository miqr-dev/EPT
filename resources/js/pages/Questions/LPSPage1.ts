import { LPS_PAGE1_COLUMN3_B } from './lpsPage1SvgShapes';

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

export type LpsColumn3Row = {
  id: number;
  options: LpsColumn3Option[];
  correctIndex?: number;
  svgMeta?: { viewBox: string; width: number; height: number };
};

export type LpsDataset = {
  rows: LpsPage1Row[];
  solutions: LpsPage1Solution[];
};

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
const LPS_PAGE1_COLUMN5_A = LPS_PAGE1_COLUMN5.slice(0, 20);

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
  { id: 1, word: 'x x x x x x x o x', correctIndex: 7 },
  { id: 2, word: 'a b a b a b a B b', correctIndex: 8 },
  { id: 3, word: 'x o x o x o x o o', correctIndex: 8 },
  { id: 4, word: '1 2 3 1 2 3 4 2 3', correctIndex: 6 },
  { id: 5, word: '0 0 n 0 0 n 0 0 0', correctIndex: 8 },
  { id: 6, word: 'a b c d e f c h i', correctIndex: 6 },
  { id: 7, word: 'a B a B a B a B a', correctIndex: 7 },
  { id: 8, word: '2 4 6 8 9 12 14 16 18', correctIndex: 4 },
  { id: 9, word: 'a b c a b c d b c', correctIndex: 6 },
  { id: 10, word: '1 2 1 2 2 2 1 2 1', correctIndex: 4 },
  { id: 11, word: '5 10 5 20 5 30 6 40 5', correctIndex: 6 },
  { id: 12, word: '19 17 15 13 11 8 7 5 3', correctIndex: 5 },
  { id: 13, word: 'a b c d e F g h i', correctIndex: 5 },
  { id: 14, word: 'j k l m n o p q s', correctIndex: 8 },
  { id: 15, word: 'B B a B a B a B a', correctIndex: 0 },
  { id: 16, word: 'r q t u v w x y z', correctIndex: 1 },
  { id: 17, word: '3 4 9 12 15 18 21 24 27', correctIndex: 1 },
  { id: 18, word: 'x x o x x o x o o', correctIndex: 7 },
  { id: 19, word: 'i j k L m n o p q', correctIndex: 3 },
  { id: 20, word: 'bb c dd e f g hh i jj', correctIndex: 4 },
  { id: 21, word: 'i h g f e D c b a', correctIndex: 5 },
  { id: 22, word: 'a bb c dd ee ff g hh i', correctIndex: 4 },
  { id: 23, word: 'd c e d c b d c b', correctIndex: 2 },
  { id: 24, word: '10 12 8 14 6 16 3 18 2', correctIndex: 6 },
  { id: 25, word: 'b C d E F G h I j', correctIndex: 4 },
  { id: 26, word: '15 18 12 21 9 24 5 27 3', correctIndex: 6 },
  { id: 27, word: '2 3 4 5 6 7 4 3 2', correctIndex: 5 },
  { id: 28, word: '1 2 4 7 8 7 4 2 1', correctIndex: 4 },
  { id: 29, word: '1 f g 2 d c 3 b a', correctIndex: 2 },
  { id: 30, word: '1 2 5 3 4 4 3 5 2', correctIndex: 0 },
  { id: 31, word: 'a b e g i k m o q', correctIndex: 1 },
  { id: 32, word: 'aa c ee f ii k mm o qq', correctIndex: 3 },
  { id: 33, word: '5 a 4 c 3 d 2 e 1', correctIndex: 1 },
  { id: 34, word: 'r P m L j H f D b', correctIndex: 2 },
  { id: 35, word: '1 1 2 4 3 9 4 16 6', correctIndex: 8 },
  { id: 36, word: 'a b c c a d a e a', correctIndex: 2 },
  { id: 37, word: 'b a e d h g k l n', correctIndex: 7 },
  { id: 38, word: 'c b f e i h j k o', correctIndex: 6 },
  { id: 39, word: '1 1 2 3 7 11 16 22 29', correctIndex: 3 },
  { id: 40, word: 'a A b C d E g G h', correctIndex: 8 },
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


