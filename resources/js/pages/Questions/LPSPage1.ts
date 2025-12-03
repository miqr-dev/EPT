export type LpsPage1Row = {
  id: number;
  column1: string;
  column2: string;
  column3?: string;
  column4: string;
  column5?: string;
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

export type LpsDataset = {
  rows: LpsPage1Row[];
  solutions: LpsPage1Solution[];
};

export const LPS_PAGE1_COLUMN1: LpsColumnEntry[] = [
  { id: 1, word: 'Auyzug', correctIndex: 0 },
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

function buildRow(rowIdx: number, column1: LpsColumnEntry[], column2: LpsColumnEntry[], column4: LpsColumnEntry[]): LpsPage1Row {
  const col1 = column1[rowIdx];
  const col2 = column2[rowIdx];
  const col4 = column4[rowIdx];
  const fallbackId = rowIdx + 1;
  return {
    id: col1?.id ?? col2?.id ?? fallbackId,
    column1: col1?.word ?? '',
    column2: col2?.word ?? '',
    column3: '',
    column4: col4?.word ?? '',
    column5: '',
  };
}

function buildRows(column1: LpsColumnEntry[], column2: LpsColumnEntry[], column4: LpsColumnEntry[]) {
  const rowCount = Math.max(column1.length, column2.length);
  return Array.from({ length: rowCount }, (_, idx) => buildRow(idx, column1, column2, column4));
}

function extractSolution(entry?: LpsColumnEntry): number[] {
  if (!entry || typeof entry.correctIndex !== 'number') return [];
  if (entry.correctIndex < 0 || entry.correctIndex >= entry.word.length) return [];
  return [entry.correctIndex];
}

function buildSolutions(column1: LpsColumnEntry[], column2: LpsColumnEntry[], column4: LpsColumnEntry[]) {
  return buildRows(column1, column2, column4).map((_, idx) => ({
    col1: extractSolution(column1[idx]),
    col2: extractSolution(column2[idx]),
    col3: [],
    col4: extractSolution(column4[idx]),
    col5: [],
  }));
}

export const LPS_PAGE1_ROWS: LpsPage1Row[] = buildRows(
  LPS_PAGE1_COLUMN1,
  LPS_PAGE1_COLUMN2,
  LPS_PAGE1_COLUMN4,
);

export const LPS_PAGE1_SOLUTIONS: LpsPage1Solution[] = buildSolutions(
  LPS_PAGE1_COLUMN1,
  LPS_PAGE1_COLUMN2,
  LPS_PAGE1_COLUMN4,
);

const LPS_PAGE1_COLUMN1_A = LPS_PAGE1_COLUMN1.slice(0, 20);
const LPS_PAGE1_COLUMN2_A = LPS_PAGE1_COLUMN2.slice(0, 20);
const LPS_PAGE1_COLUMN4_A = LPS_PAGE1_COLUMN4.slice(0, 20);

export const LPS_PAGE1_ROWS_A: LpsPage1Row[] = buildRows(
  LPS_PAGE1_COLUMN1_A,
  LPS_PAGE1_COLUMN2_A,
  LPS_PAGE1_COLUMN4_A,
);

export const LPS_PAGE1_SOLUTIONS_A: LpsPage1Solution[] = buildSolutions(
  LPS_PAGE1_COLUMN1_A,
  LPS_PAGE1_COLUMN2_A,
  LPS_PAGE1_COLUMN4_A,
);

const LPS_PAGE1_COLUMN1_B = LPS_PAGE1_COLUMN1.slice(20);
const LPS_PAGE1_COLUMN2_B = LPS_PAGE1_COLUMN2.slice(20);
const LPS_PAGE1_COLUMN4_B = LPS_PAGE1_COLUMN4.slice(20);

export const LPS_PAGE1_ROWS_B: LpsPage1Row[] = buildRows(
  LPS_PAGE1_COLUMN1_B,
  LPS_PAGE1_COLUMN2_B,
  LPS_PAGE1_COLUMN4_B,
);

export const LPS_PAGE1_SOLUTIONS_B: LpsPage1Solution[] = buildSolutions(
  LPS_PAGE1_COLUMN1_B,
  LPS_PAGE1_COLUMN2_B,
  LPS_PAGE1_COLUMN4_B,
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
