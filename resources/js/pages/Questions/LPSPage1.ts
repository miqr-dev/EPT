import { LPS_PAGE1_COLUMN3_B } from './lpsPage1SvgShapes';

export type LpsColumn3Option = {
  id: string;
  src?: string;
  /**
   * Options that share the same key will be grouped into a single selectable choice.
   * Falls back to the id when omitted.
   */
  optionKey?: string;
  /**
   * When grouping multiple SVG paths into a single selectable option, provide an array.
   * A single string is still supported for the common case of one path per option.
   */
  pathData?: string | string[];
  transform?: string;
};

export type LpsColumn3OptionGroup = {
  optionKey: string;
  id: string;
  transform?: string;
  segments: string[];
};

export function groupColumn3Options(options?: LpsColumn3Option[]): LpsColumn3OptionGroup[] {
  if (!options?.length) return [];

  const byKey = new Map<string, LpsColumn3OptionGroup>();

  options.forEach((option, idx) => {
    const optionKey = option.optionKey ?? option.id ?? `option-${idx + 1}`;
    const group = byKey.get(optionKey);
    const segments = Array.isArray(option.pathData) ? option.pathData : option.pathData ? [option.pathData] : [];

    if (group) {
      group.segments.push(...segments);
    } else {
      byKey.set(optionKey, {
        optionKey,
        id: option.id ?? optionKey,
        transform: option.transform,
        segments: [...segments],
      });
    }
  });

  return Array.from(byKey.values());
}

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
  const groups = groupColumn3Options(entry.options);
  if (entry.correctIndex < 0 || entry.correctIndex >= groups.length) return [];
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


