import {
  LPS_PAGE10_CORRECT_INDICES_B,
  LPS_PAGE10_OPTIONS_BY_ROW,
} from './lpsPage10SvgShapes';

export type LpsPage10Option = {
  id: string;
  svg: string;
  svgMeta: { viewBox: string; width: number; height: number };
};

export type LpsPage10Row = {
  id: number;
  options: LpsPage10Option[];
  correctIndex?: number | null;
  isExample?: boolean;
};

export type LpsPage10Solution = { correctIndex: number | null; isExample?: boolean };

export type LpsPage10Dataset = { rows: LpsPage10Row[]; solutions: LpsPage10Solution[] };

function buildRow(idx: number, options?: Omit<LpsPage10Option, 'id'>[], correctIndex?: number | null): LpsPage10Row {
  const parsedOptions = (options ?? []).map((option, optionIdx) => ({
    ...option,
    id: `lps-page10-row-${idx + 1}-opt-${optionIdx + 1}`,
  }));

  return {
    id: idx + 1,
    options: parsedOptions,
    correctIndex: typeof correctIndex === 'number' ? correctIndex : null,
    isExample: idx < 2,
  };
}

function buildRows(optionsByRow?: Omit<LpsPage10Option, 'id'>[][], correctIndices?: (number | null)[]) {
  const rowCount = Math.max(optionsByRow?.length ?? 0, 42);
  return Array.from({ length: rowCount }, (_, idx) =>
    buildRow(idx, optionsByRow?.[idx] ?? optionsByRow?.[0], correctIndices?.[idx]),
  );
}

function buildSolutions(rows: LpsPage10Row[]): LpsPage10Solution[] {
  return rows.map((row) => ({
    correctIndex: typeof row.correctIndex === 'number' ? row.correctIndex : null,
    isExample: row.isExample,
  }));
}

export const LPS_PAGE10_ROWS_B = buildRows(LPS_PAGE10_OPTIONS_BY_ROW, LPS_PAGE10_CORRECT_INDICES_B);
export const LPS_PAGE10_SOLUTIONS_B = buildSolutions(LPS_PAGE10_ROWS_B);

export function getLpsPage10Dataset(testName?: string): LpsPage10Dataset {
  if (testName === 'LPS-B') {
    return { rows: LPS_PAGE10_ROWS_B, solutions: LPS_PAGE10_SOLUTIONS_B };
  }

  return { rows: [], solutions: [] };
}
