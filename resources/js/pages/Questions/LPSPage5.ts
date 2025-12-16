import type { LpsColumn3Option, LpsColumn3Row } from './LPSPage1';
import { LPS_PAGE5_COLUMN7_B } from './lpsPage5SvgShapes';

export type LpsPage5Row = {
  id: number;
  column7?: LpsColumn3Option[];
  column7SvgMeta?: { viewBox: string; width: number; height: number };
};

export type LpsPage5Solution = {
  col7?: number[];
};

export type LpsPage5Dataset = {
  rows: LpsPage5Row[];
  solutions: LpsPage5Solution[];
};

function buildRow(rowIdx: number, column7?: LpsColumn3Row[]): LpsPage5Row {
  const col7 = column7?.[rowIdx];
  const fallbackId = rowIdx + 1;

  return {
    id: col7?.id ?? fallbackId,
    column7: col7?.options,
    column7SvgMeta: col7?.svgMeta,
  };
}

function buildRows(column7?: LpsColumn3Row[]) {
  const rowCount = column7?.length ?? 0;
  return Array.from({ length: rowCount }, (_, idx) => buildRow(idx, column7));
}

function extractColumn7Solution(entry?: LpsColumn3Row): number[] {
  if (!entry || typeof entry.correctIndex !== 'number') return [];
  if (entry.correctIndex < 0 || entry.correctIndex >= entry.options.length) return [];
  return [entry.correctIndex];
}

function buildSolutions(column7?: LpsColumn3Row[]) {
  return buildRows(column7).map((_, idx) => ({
    col7: extractColumn7Solution(column7?.[idx]),
  }));
}

export const LPS_PAGE5_ROWS_B: LpsPage5Row[] = buildRows(LPS_PAGE5_COLUMN7_B);

export const LPS_PAGE5_SOLUTIONS_B: LpsPage5Solution[] = buildSolutions(LPS_PAGE5_COLUMN7_B);

export function getLpsPage5Dataset(testName?: string): LpsPage5Dataset {
  if (testName === 'LPS-B') {
    return { rows: LPS_PAGE5_ROWS_B, solutions: LPS_PAGE5_SOLUTIONS_B };
  }

  return { rows: [], solutions: [] };
}
