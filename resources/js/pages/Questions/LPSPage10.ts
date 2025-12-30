import {
  LPS_PAGE10_CORRECT_INDICES_B,
  LPS_PAGE10_PLACEHOLDER_META,
  LPS_PAGE10_SHAPES_B,
} from './lpsPage10SvgShapes';

export type LpsPage10Option = { id: string; pathData: string; transform?: string };

export type LpsPage10Row = {
  id: number;
  options: LpsPage10Option[];
  svgMeta?: { viewBox: string; width: number; height: number };
  correctIndex?: number | null;
  isExample?: boolean;
};

export type LpsPage10Solution = { correctIndex: number | null; isExample?: boolean };

export type LpsPage10Dataset = { rows: LpsPage10Row[]; solutions: LpsPage10Solution[] };

function buildRow(idx: number, paths?: string[], correctIndex?: number | null): LpsPage10Row {
  const options = (paths ?? []).map((pathData, pathIdx) => ({
    id: `lps-page10-row-${idx + 1}-opt-${pathIdx + 1}`,
    pathData,
  }));

  return {
    id: idx + 1,
    options,
    svgMeta: LPS_PAGE10_PLACEHOLDER_META,
    correctIndex: typeof correctIndex === 'number' ? correctIndex : null,
    isExample: idx < 2,
  };
}

function buildRows(pathsByRow?: string[][], correctIndices?: (number | null)[]) {
  const rowCount = Math.max(pathsByRow?.length ?? 0, 42);
  return Array.from({ length: rowCount }, (_, idx) =>
    buildRow(idx, pathsByRow?.[idx] ?? pathsByRow?.[0], correctIndices?.[idx]),
  );
}

function buildSolutions(rows: LpsPage10Row[]): LpsPage10Solution[] {
  return rows.map((row) => ({
    correctIndex: typeof row.correctIndex === 'number' ? row.correctIndex : null,
    isExample: row.isExample,
  }));
}

export const LPS_PAGE10_ROWS_B = buildRows(LPS_PAGE10_SHAPES_B, LPS_PAGE10_CORRECT_INDICES_B);
export const LPS_PAGE10_SOLUTIONS_B = buildSolutions(LPS_PAGE10_ROWS_B);

export function getLpsPage10Dataset(testName?: string): LpsPage10Dataset {
  if (testName === 'LPS-B') {
    return { rows: LPS_PAGE10_ROWS_B, solutions: LPS_PAGE10_SOLUTIONS_B };
  }

  return { rows: [], solutions: [] };
}
