export type LpsPage1Row = {
  id: number;
  column1: string;
  column2: string;
  column3?: string;
  column4?: string;
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

export const LPS_PAGE1_COLUMN1: LpsColumnEntry[] = [
  { id: 1, word: 'Kreide', correctIndex: 2 },
  { id: 2, word: 'Raufer' },
  { id: 3, word: 'Artiest' },
  { id: 4, word: 'Eagrost' },
  { id: 5, word: 'Gameson' },
  { id: 6, word: 'Gruber' },
  { id: 7, word: 'Kammon' },
  { id: 8, word: 'Krumen' },
  { id: 9, word: 'Gulpe' },
  { id: 10, word: 'Blourd' },
  { id: 11, word: 'Dackke' },
  { id: 12, word: 'Grusan' },
  { id: 13, word: 'Kusper' },
  { id: 14, word: 'Menerl' },
  { id: 15, word: 'Peaot' },
  { id: 16, word: 'Trubste' },
  { id: 17, word: 'Craven' },
  { id: 18, word: 'Maswer' },
  { id: 19, word: 'Bierod' },
  { id: 20, word: 'Gold' },
  { id: 21, word: 'Ruchner' },
  { id: 22, word: 'Glauben' },
  { id: 23, word: 'Massen' },
];

export const LPS_PAGE1_COLUMN2: LpsColumnEntry[] = [
  { id: 1, word: 'Kreife' },
  { id: 2, word: 'Schreier' },
  { id: 3, word: 'Elucher' },
  { id: 4, word: 'Grehaud' },
  { id: 5, word: 'Regben' },
  { id: 6, word: 'Banresh' },
  { id: 7, word: 'Grobter' },
  { id: 8, word: 'Montins' },
  { id: 9, word: 'Schalusm' },
  { id: 10, word: 'Margt' },
  { id: 11, word: 'Morsure' },
  { id: 12, word: 'Malorn' },
  { id: 13, word: 'Monsier' },
  { id: 14, word: 'Aregaus' },
  { id: 15, word: 'Aragark' },
  { id: 16, word: 'Drage' },
  { id: 17, word: 'Midore' },
  { id: 18, word: 'Rangnor' },
  { id: 19, word: 'Aloron' },
  { id: 20, word: 'Kradka' },
  { id: 21, word: 'Osver' },
  { id: 22, word: 'Remberg' },
  { id: 23, word: 'Egrast' },
];

function buildRow(rowIdx: number): LpsPage1Row {
  const col1 = LPS_PAGE1_COLUMN1[rowIdx];
  const col2 = LPS_PAGE1_COLUMN2[rowIdx];
  const fallbackId = rowIdx + 1;
  return {
    id: col1?.id ?? col2?.id ?? fallbackId,
    column1: col1?.word ?? '',
    column2: col2?.word ?? '',
    column3: '',
    column4: '',
    column5: '',
  };
}

const rowCount = Math.max(
  LPS_PAGE1_COLUMN1.length,
  LPS_PAGE1_COLUMN2.length,
);

export const LPS_PAGE1_ROWS: LpsPage1Row[] = Array.from({ length: rowCount }, (_, idx) => buildRow(idx));

function extractSolution(entry?: LpsColumnEntry): number[] {
  if (!entry || typeof entry.correctIndex !== 'number') return [];
  if (entry.correctIndex < 0 || entry.correctIndex >= entry.word.length) return [];
  return [entry.correctIndex];
}

export const LPS_PAGE1_SOLUTIONS: LpsPage1Solution[] = LPS_PAGE1_ROWS.map((_, idx) => ({
  col1: extractSolution(LPS_PAGE1_COLUMN1[idx]),
  col2: extractSolution(LPS_PAGE1_COLUMN2[idx]),
  col3: [],
  col4: [],
  col5: [],
}));
