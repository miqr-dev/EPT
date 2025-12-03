export type LpsPage1Row = {
  id: number;
  column1: string;
  column2: string;
  column3?: string;
  column4?: string;
  column5?: string;
};

export type LpsPage1Solution = {
  /**
   * Zero-based indices that represent the incorrect characters for each column entry.
   * Selecting one of these indices awards a point for the row/column.
   */
  col1?: number[];
  col2?: number[];
  col3?: number[];
  col4?: number[];
  col5?: number[];
};

export const LPS_PAGE1_ROWS: LpsPage1Row[] = [
  { id: 1, column1: 'Kreide', column2: 'Kreife' },
  { id: 2, column1: 'Raufer', column2: 'Schreier' },
  { id: 3, column1: 'Artiest', column2: 'Elucher' },
  { id: 4, column1: 'Eagrost', column2: 'Grehaud' },
  { id: 5, column1: 'Gameson', column2: 'Regben' },
  { id: 6, column1: 'Gruber', column2: 'Banresh' },
  { id: 7, column1: 'Kammon', column2: 'Grobter' },
  { id: 8, column1: 'Krumen', column2: 'Montins' },
  { id: 9, column1: 'Gulpe', column2: 'Schalusm' },
  { id: 10, column1: 'Blourd', column2: 'Margt' },
  { id: 11, column1: 'Dackke', column2: 'Morsure' },
  { id: 12, column1: 'Grusan', column2: 'Malorn' },
  { id: 13, column1: 'Kusper', column2: 'Monsier' },
  { id: 14, column1: 'Menerl', column2: 'Aregaus' },
  { id: 15, column1: 'Peaot', column2: 'Aragark' },
  { id: 16, column1: 'Trubste', column2: 'Drage' },
  { id: 17, column1: 'Craven', column2: 'Midore' },
  { id: 18, column1: 'Maswer', column2: 'Rangnor' },
  { id: 19, column1: 'Bierod', column2: 'Aloron' },
  { id: 20, column1: 'Gold', column2: 'Kradka' },
  { id: 21, column1: 'Ruchner', column2: 'Osver' },
  { id: 22, column1: 'Glauben', column2: 'Remberg' },
  { id: 23, column1: 'Massen', column2: 'Egrast' },
];

// TODO: populate the solution indices with the real answer key once provided.
// Each array entry corresponds to a row above and contains the indices that
// count as correct picks for that column.
export const LPS_PAGE1_SOLUTIONS: LpsPage1Solution[] = Array.from({ length: LPS_PAGE1_ROWS.length }, () => ({
  col1: [],
  col2: [],
  col3: [],
  col4: [],
  col5: [],
}));
