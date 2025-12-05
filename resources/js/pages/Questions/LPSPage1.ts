export type LpsPage1Row = {
    id: number;
    column1: string;
    column2: string;
    column3?: string | LpsColumnEntrySvg;
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

export type LpsColumnEntrySvg = {
  id: number;
  svg: string;
  /**
   * Zero-based index of the correct path within the SVG.
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

function buildRow(
  rowIdx: number,
  column1: LpsColumnEntry[],
  column2: LpsColumnEntry[],
  column3: (LpsColumnEntry | LpsColumnEntrySvg)[],
  column4: LpsColumnEntry[],
  column5: LpsColumnEntry[]
): LpsPage1Row {
  const col1 = column1[rowIdx];
  const col2 = column2[rowIdx];
  const col3 = column3[rowIdx];
  const col4 = column4[rowIdx];
  const col5 = column5[rowIdx];
  const fallbackId = rowIdx + 1;
  return {
    id: col1?.id ?? col2?.id ?? col3?.id ?? col4?.id ?? col5?.id ?? fallbackId,
    column1: col1?.word ?? '',
    column2: col2?.word ?? '',
    column3: col3 ? ('word' in col3 ? col3.word : col3) : '',
    column4: col4?.word ?? '',
    column5: col5?.word ?? '',
  };
}

function buildRows(
  column1: LpsColumnEntry[],
  column2: LpsColumnEntry[],
  column3: (LpsColumnEntry | LpsColumnEntrySvg)[],
  column4: LpsColumnEntry[],
  column5: LpsColumnEntry[]
) {
  const rowCount = Math.max(column1.length, column2.length, column3.length, column4.length, column5.length);
  return Array.from({ length: rowCount }, (_, idx) => buildRow(idx, column1, column2, column3, column4, column5));
}


function extractSolution(entry?: LpsColumnEntry): number[] {
    if (!entry || typeof entry.correctIndex !== 'number') return [];
    if (entry.correctIndex < 0 || entry.correctIndex >= entry.word.length) return [];
    return [entry.correctIndex];
}

function extractSolutionSvg(entry?: LpsColumnEntrySvg): number[] {
    if (!entry || typeof entry.correctIndex !== 'number') return [];
    // SVGs have 8 paths, so the index should be between 0 and 7
    if (entry.correctIndex < 0 || entry.correctIndex > 7) return [];
    return [entry.correctIndex];
}

function buildSolutions(
  column1: LpsColumnEntry[],
  column2: LpsColumnEntry[],
  column3: (LpsColumnEntry | LpsColumnEntrySvg)[],
  column4: LpsColumnEntry[],
  column5: LpsColumnEntry[]
) {
  const rowCount = Math.max(column1.length, column2.length, column3.length, column4.length, column5.length);
  return Array.from({ length: rowCount }, (_, idx) => {
    const col3 = column3[idx];
    return {
      col1: extractSolution(column1[idx]),
      col2: extractSolution(column2[idx]),
      col3: col3 ? ('word' in col3 ? extractSolution(col3 as LpsColumnEntry) : extractSolutionSvg(col3 as LpsColumnEntrySvg)) : [],
      col4: extractSolution(column4[idx]),
      col5: extractSolution(column5[idx]),
    };
  });
}

export const LPS_PAGE1_ROWS: LpsPage1Row[] = buildRows(
    LPS_PAGE1_COLUMN1,
    LPS_PAGE1_COLUMN2,
    [],
    LPS_PAGE1_COLUMN4,
    LPS_PAGE1_COLUMN5,
);

export const LPS_PAGE1_SOLUTIONS: LpsPage1Solution[] = buildSolutions(
    LPS_PAGE1_COLUMN1,
    LPS_PAGE1_COLUMN2,
    [],
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
    [],
    LPS_PAGE1_COLUMN4_A,
    LPS_PAGE1_COLUMN5_A,
);

export const LPS_PAGE1_SOLUTIONS_A: LpsPage1Solution[] = buildSolutions(
    LPS_PAGE1_COLUMN1_A,
    LPS_PAGE1_COLUMN2_A,
    [],
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

const LPS_PAGE1_COLUMN3_B: LpsColumnEntrySvg[] = [
  {
    id: 1,
    svg: `<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0.00 0.00 335.00 42.00" width="335.00" height="42.00">
<path fill="#050709" d="
  M 7.05 18.58
  L 20.01 5.62
  A 1.24 1.24 0.0 0 1 21.77 5.62
  L 35.09 18.94
  A 1.24 1.24 0.0 0 1 35.09 20.70
  L 22.13 33.66
  A 1.24 1.24 0.0 0 1 20.37 33.66
  L 7.05 20.34
  A 1.24 1.24 0.0 0 1 7.05 18.58
  Z
  M 21.5268 9.2996
  A 0.96 0.96 0.0 0 0 20.1692 9.2830
  L 10.4358 18.7815
  A 0.96 0.96 0.0 0 0 10.4192 20.1390
  L 13.9532 23.7604
  A 0.96 0.96 0.0 0 0 15.3108 23.7770
  L 25.0442 14.2785
  A 0.96 0.96 0.0 0 0 25.0608 12.9210
  L 21.5268 9.2996
  Z
  M 20.51 30.18
  A 1.10 1.10 0.0 0 0 22.07 30.18
  L 31.59 20.67
  A 1.10 1.10 0.0 0 0 31.59 19.11
  L 28.36 15.88
  A 1.10 1.10 0.0 0 0 26.80 15.88
  L 17.29 25.40
  A 1.10 1.10 0.0 0 0 17.29 26.96
  L 20.51 30.18
  Z"
/>
<path fill="#050709" d="
  M 204.1589 30.7330
  A 1.14 1.14 0.0 0 1 203.0170 31.8710
  L 178.9170 31.8289
  A 1.14 1.14 0.0 0 1 177.7790 30.6870
  L 177.8211 6.5870
  A 1.14 1.14 0.0 0 1 178.9630 5.4490
  L 203.0630 5.4911
  A 1.14 1.14 0.0 0 1 204.2010 6.6330
  L 204.1589 30.7330
  Z
  M 189.72 9.24
  A 1.04 1.04 0.0 0 0 188.68 8.20
  L 181.20 8.20
  A 1.04 1.04 0.0 0 0 180.16 9.24
  L 180.16 28.34
  A 1.04 1.04 0.0 0 0 181.20 29.38
  L 188.68 29.38
  A 1.04 1.04 0.0 0 0 189.72 28.34
  L 189.72 9.24
  Z
  M 201.8496 9.3158
  A 1.03 1.03 0.0 0 0 200.8250 8.2805
  L 193.0651 8.2398
  A 1.03 1.03 0.0 0 0 192.0297 9.2644
  L 191.9304 28.2242
  A 1.03 1.03 0.0 0 0 192.9550 29.2595
  L 200.7149 29.3002
  A 1.03 1.03 0.0 0 0 201.7503 28.2756
  L 201.8496 9.3158
  Z"
/>
<path fill="#050709" d="
  M 76.0183 31.4927
  A 0.99 0.99 0.0 0 1 75.0266 32.4810
  L 50.9466 32.4390
  A 0.99 0.99 0.0 0 1 49.9583 31.4472
  L 50.0017 6.5873
  A 0.99 0.99 0.0 0 1 50.9934 5.5990
  L 75.0734 5.6410
  A 0.99 0.99 0.0 0 1 76.0617 6.6328
  L 76.0183 31.4927
  Z
  M 62.5467 9.5492
  A 1.06 1.06 0.0 0 0 61.4885 8.4873
  L 53.0885 8.4727
  A 1.06 1.06 0.0 0 0 52.0267 9.5308
  L 51.9933 28.6508
  A 1.06 1.06 0.0 0 0 53.0515 29.7127
  L 61.4515 29.7273
  A 1.06 1.06 0.0 0 0 62.5133 28.6692
  L 62.5467 9.5492
  Z
  M 74.0905 9.6153
  A 1.12 1.12 0.0 0 0 72.9647 8.5012
  L 65.7248 8.5391
  A 1.12 1.12 0.0 0 0 64.6106 9.6649
  L 64.7095 28.5447
  A 1.12 1.12 0.0 0 0 65.8353 29.6588
  L 73.0752 29.6209
  A 1.12 1.12 0.0 0 0 74.1894 28.4951
  L 74.0905 9.6153
  Z"
/>
<path fill="#050709" d="
  M 162.6408 30.7760
  A 1.28 1.28 0.0 0 1 161.3631 32.0582
  L 136.4231 32.1017
  A 1.28 1.28 0.0 0 1 135.1409 30.8240
  L 135.0992 6.9440
  A 1.28 1.28 0.0 0 1 136.3769 5.6618
  L 161.3169 5.6183
  A 1.28 1.28 0.0 0 1 162.5991 6.8960
  L 162.6408 30.7760
  Z
  M 147.6341 9.1985
  A 0.86 0.86 0.0 0 0 146.7771 8.3355
  L 137.9571 8.3047
  A 0.86 0.86 0.0 0 0 137.0941 9.1617
  L 137.0259 28.7015
  A 0.86 0.86 0.0 0 0 137.8829 29.5645
  L 146.7029 29.5953
  A 0.86 0.86 0.0 0 0 147.5659 28.7383
  L 147.6341 9.1985
  Z
  M 160.59 9.35
  A 1.11 1.11 0.0 0 0 159.48 8.24
  L 150.74 8.24
  A 1.11 1.11 0.0 0 0 149.63 9.35
  L 149.63 28.35
  A 1.11 1.11 0.0 0 0 150.74 29.46
  L 159.48 29.46
  A 1.11 1.11 0.0 0 0 160.59 28.35
  L 160.59 9.35
  Z"
/>
<path fill="#050709" d="
  M 246.9631 31.0027
  A 0.83 0.83 0.0 0 1 246.1360 31.8356
  L 220.7562 31.9242
  A 0.83 0.83 0.0 0 1 219.9233 31.0971
  L 219.8369 6.3373
  A 0.83 0.83 0.0 0 1 220.6640 5.5044
  L 246.0438 5.4158
  A 0.83 0.83 0.0 0 1 246.8767 6.2429
  L 246.9631 31.0027
  Z
  M 244.7794 8.7956
  A 0.62 0.62 0.0 0 0 244.1638 8.1713
  L 235.2640 8.1092
  A 0.62 0.62 0.0 0 0 234.6397 8.7248
  L 234.5006 28.6444
  A 0.62 0.62 0.0 0 0 235.1162 29.2687
  L 244.0160 29.3308
  A 0.62 0.62 0.0 0 0 244.6403 28.7152
  L 244.7794 8.7956
  Z
  M 232.41 9.14
  A 0.96 0.96 0.0 0 0 231.45 8.18
  L 223.05 8.18
  A 0.96 0.96 0.0 0 0 222.09 9.14
  L 222.09 28.24
  A 0.96 0.96 0.0 0 0 223.05 29.20
  L 231.45 29.20
  A 0.96 0.96 0.0 0 0 232.41 28.24
  L 232.41 9.14
  Z"
/>
<path fill="#050709" d="
  M 119.2217 31.4160
  A 0.80 0.80 0.0 0 1 118.4231 32.2174
  L 92.5632 32.2625
  A 0.80 0.80 0.0 0 1 91.7618 31.4639
  L 91.7183 6.5240
  A 0.80 0.80 0.0 0 1 92.5169 5.7226
  L 118.3768 5.6775
  A 0.80 0.80 0.0 0 1 119.1782 6.4761
  L 119.2217 31.4160
  Z
  M 104.5367 9.5193
  A 1.05 1.05 0.0 0 0 103.4885 8.4675
  L 94.9486 8.4526
  A 1.05 1.05 0.0 0 0 93.8967 9.5007
  L 93.8633 28.6607
  A 1.05 1.05 0.0 0 0 94.9115 29.7125
  L 103.4514 29.7274
  A 1.05 1.05 0.0 0 0 104.5033 28.6793
  L 104.5367 9.5193
  Z
  M 117.0147 8.9521
  A 0.51 0.51 0.0 0 0 116.5030 8.4439
  L 107.2230 8.4763
  A 0.51 0.51 0.0 0 0 106.7148 8.9880
  L 106.7853 29.1679
  A 0.51 0.51 0.0 0 0 107.2970 29.6761
  L 116.5770 29.6437
  A 0.51 0.51 0.0 0 0 117.0852 29.1320
  L 117.0147 8.9521
  Z"
/>
<path fill="#050709" d="
  M 263.61 31.23
  L 263.61 6.74
  A 0.99 0.99 0.0 0 1 264.60 5.75
  L 289.09 5.75
  A 0.99 0.99 0.0 0 1 290.08 6.74
  L 290.08 31.23
  A 0.99 0.99 0.0 0 1 289.09 32.22
  L 264.60 32.22
  A 0.99 0.99 0.0 0 1 263.61 31.23
  Z
  M 266.63 8.42
  A 0.93 0.93 0.0 0 0 265.70 9.35
  L 265.70 28.62
  A 0.93 0.93 0.0 0 0 266.63 29.55
  L 274.99 29.55
  A 0.93 0.93 0.0 0 0 275.92 28.62
  L 275.92 9.35
  A 0.93 0.93 0.0 0 0 274.99 8.42
  L 266.63 8.42
  Z
  M 287.9269 9.4686
  A 0.76 0.76 0.0 0 0 287.1682 8.7073
  L 278.8482 8.6928
  A 0.76 0.76 0.0 0 0 278.0869 9.4514
  L 278.0531 28.8314
  A 0.76 0.76 0.0 0 0 278.8118 29.5927
  L 287.1318 29.6072
  A 0.76 0.76 0.0 0 0 287.8931 28.8486
  L 287.9269 9.4686
  Z"
/>
<path fill="#050709" d="
  M 332.9591 31.4234
  A 1.30 1.30 0.0 0 1 331.6569 32.7211
  L 307.4369 32.6788
  A 1.30 1.30 0.0 0 1 306.1392 31.3766
  L 306.1809 7.4766
  A 1.30 1.30 0.0 0 1 307.4831 6.1789
  L 331.7031 6.2212
  A 1.30 1.30 0.0 0 1 333.0008 7.5234
  L 332.9591 31.4234
  Z
  M 308.38 9.76
  L 308.38 28.61
  A 1.16 1.16 0.0 0 0 309.54 29.77
  L 316.90 29.77
  A 1.43 1.43 0.0 0 0 318.33 28.34
  L 318.33 10.36
  A 0.95 0.94 -75.3 0 0 317.86 9.54
  Q 315.18 8.00 313.96 10.49
  A 0.87 0.87 0.0 0 1 312.45 10.57
  Q 311.23 8.65 309.22 8.84
  A 0.93 0.92 -2.7 0 0 308.38 9.76
  Z
  M 330.7226 10.0177
  A 1.00 1.00 0.0 0 0 329.7261 9.0142
  L 321.6262 8.9859
  A 1.00 1.00 0.0 0 0 320.6227 9.9824
  L 320.5574 28.7023
  A 1.00 1.00 0.0 0 0 321.5539 29.7058
  L 329.6538 29.7341
  A 1.00 1.00 0.0 0 0 330.6573 28.7376
  L 330.7226 10.0177
  Z"
/>
</svg>`,
    correctIndex: 1,
  },
  {
    id: 2,
    svg: `<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0.00 0.00 331.00 37.00" width="331.00" height="37.00">
<path fill="#161a1c" d="
  M 3.10 18.23
  L 16.34 5.38
  A 1.53 1.53 0.0 0 1 18.47 5.39
  L 31.62 18.33
  A 1.53 1.53 0.0 0 1 30.54 20.95
  L 4.16 20.85
  A 1.53 1.53 0.0 0 1 3.10 18.23
  Z
  M 27.18 17.70
  L 18.63 9.15
  A 1.70 1.69 44.9 0 0 16.24 9.15
  L 7.90 17.49
  A 0.35 0.34 -67.3 0 0 8.14 18.08
  L 27.02 18.08
  A 0.22 0.22 0.0 0 0 27.18 17.70
  Z"
/>
<path fill="#161a1c" d="
  M 60.90 5.31
  L 73.70 18.11
  A 1.51 1.51 0.0 0 1 72.63 20.69
  L 47.03 20.69
  A 1.51 1.51 0.0 0 1 45.96 18.11
  L 58.76 5.31
  A 1.51 1.51 0.0 0 1 60.90 5.31
  Z
  M 50.54 17.42
  A 0.22 0.21 22.4 0 0 50.69 17.78
  L 68.83 17.80
  A 0.33 0.33 0.0 0 0 69.06 17.23
  L 61.06 9.21
  A 1.63 1.62 -44.8 0 0 58.76 9.21
  L 50.54 17.42
  Z"
/>
<path fill="#161a1c" d="
  M 217.39 17.04
  L 229.26 5.41
  A 1.72 1.72 0.0 0 1 231.66 5.40
  L 243.58 16.99
  A 1.72 1.72 0.0 0 1 242.38 19.94
  L 218.59 19.98
  A 1.72 1.72 0.0 0 1 217.39 17.04
  Z
  M 221.40 16.47
  A 0.31 0.31 0.0 0 0 221.62 17.01
  L 239.07 16.95
  A 0.31 0.31 0.0 0 0 239.28 16.41
  L 230.52 8.27
  A 0.31 0.31 0.0 0 0 230.10 8.28
  L 221.40 16.47
  Z"
/>
<path fill="#161a1c" d="
  M 259.83 17.14
  L 272.27 5.44
  A 1.65 1.65 0.0 0 1 274.57 5.47
  L 286.68 17.51
  A 1.65 1.65 0.0 0 1 285.50 20.33
  L 260.94 19.99
  A 1.65 1.65 0.0 0 1 259.83 17.14
  Z
  M 273.50 8.09
  A 0.38 0.38 0.0 0 0 272.96 8.09
  L 264.60 16.45
  A 0.38 0.38 0.0 0 0 264.87 17.10
  L 281.59 17.10
  A 0.38 0.38 0.0 0 0 281.86 16.45
  L 273.50 8.09
  Z"
/>
<path fill="#161a1c" d="
  M 174.50 17.19
  L 186.79 5.16
  A 1.68 1.68 0.0 0 1 189.14 5.17
  L 201.39 17.23
  A 1.68 1.68 0.0 0 1 200.21 20.11
  L 175.67 20.07
  A 1.68 1.68 0.0 0 1 174.50 17.19
  Z
  M 178.83 16.43
  A 0.37 0.37 0.0 0 0 179.09 17.06
  L 196.49 17.12
  A 0.37 0.37 0.0 0 0 196.75 16.49
  L 188.08 8.00
  A 0.37 0.37 0.0 0 0 187.56 8.00
  L 178.83 16.43
  Z"
/>
<path fill="#161a1c" d="
  M 302.98 17.66
  L 315.24 5.50
  A 1.58 1.58 0.0 0 1 317.48 5.51
  L 329.61 17.80
  A 1.58 1.58 0.0 0 1 328.48 20.49
  L 304.09 20.37
  A 1.58 1.58 0.0 0 1 302.98 17.66
  Z
  M 307.22 16.86
  A 0.32 0.32 0.0 0 0 307.43 17.41
  L 324.75 17.57
  A 0.32 0.32 0.0 0 0 324.98 17.02
  L 316.39 8.70
  A 0.32 0.32 0.0 0 0 315.95 8.70
  L 307.22 16.86
  Z"
/>
<path fill="#161a1c" d="
  M 88.72 17.96
  L 100.77 5.78
  A 1.62 1.62 0.0 0 1 103.06 5.76
  L 115.28 17.77
  A 1.62 1.62 0.0 0 1 114.16 20.55
  L 89.88 20.72
  A 1.62 1.62 0.0 0 1 88.72 17.96
  Z
  M 110.96 17.14
  L 103.03 9.49
  A 1.55 1.54 43.8 0 0 100.86 9.53
  L 93.40 17.27
  A 0.32 0.31 -68.4 0 0 93.63 17.80
  L 110.82 17.49
  A 0.20 0.20 0.0 0 0 110.96 17.14
  Z"
/>
<path fill="#161a1c" d="
  M 159.61 20.09
  L 146.82 32.76
  A 1.70 1.70 0.0 0 1 144.45 32.79
  L 131.39 20.38
  A 1.70 1.70 0.0 0 1 132.55 17.45
  L 158.40 17.18
  A 1.70 1.70 0.0 0 1 159.61 20.09
  Z
  M 155.26 20.86
  A 0.41 0.41 0.0 0 0 154.97 20.16
  L 136.07 20.33
  A 0.41 0.41 0.0 0 0 135.79 21.03
  L 145.32 30.21
  A 0.41 0.41 0.0 0 0 145.89 30.21
  L 155.26 20.86
  Z"
/>
</svg>`,
    correctIndex: 4,
  },
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
    LPS_PAGE1_COLUMN3_B,
    LPS_PAGE1_COLUMN4_B,
    LPS_PAGE1_COLUMN5_B,
);

export const LPS_PAGE1_SOLUTIONS_B: LpsPage1Solution[] = buildSolutions(
    LPS_PAGE1_COLUMN1_B,
    LPS_PAGE1_COLUMN2_B,
    LPS_PAGE1_COLUMN3_B,
    LPS_PAGE1_COLUMN4_B,
    LPS_PAGE1_COLUMN5_B,
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
