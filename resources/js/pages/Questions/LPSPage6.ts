import { LPS_PAGE6_COLUMN8_B } from './lpsPage6SvgShapes';

export type LpsPage6Option = { label: string };

export type LpsPage6Row = {
  id: number;
  column8Options: LpsPage6Option[];
  column8Svg?: string;
  column8SvgMeta?: { width: number; height: number; viewBox?: string };
  correctIndices?: number[];
};

export type LpsPage6Solution = {
  col8?: number[];
};

export type LpsPage6Dataset = {
  rows: LpsPage6Row[];
  solutions: LpsPage6Solution[];
};

function buildRow(rowIdx: number, column8?: typeof LPS_PAGE6_COLUMN8_B): LpsPage6Row {
  const entry = column8?.[rowIdx];
  const fallbackId = rowIdx + 1;

  return {
    id: fallbackId,
    column8Options: (entry?.options ?? []).map((label) => ({ label })),
    column8Svg: entry?.svg,
    column8SvgMeta: entry?.svgMeta,
    correctIndices: entry?.correctIndices,
  };
}

function buildRows(column8?: typeof LPS_PAGE6_COLUMN8_B) {
  const rowCount = column8?.length ?? 0;
  return Array.from({ length: rowCount }, (_, idx) => buildRow(idx, column8));
}

function extractColumn8Solution(entry?: (typeof LPS_PAGE6_COLUMN8_B)[number]) {
  if (!entry?.correctIndices?.length) return [] as number[];
  return entry.correctIndices;
}

function buildSolutions(column8?: typeof LPS_PAGE6_COLUMN8_B) {
  return buildRows(column8).map((_, idx) => ({
    col8: extractColumn8Solution(column8?.[idx]),
  }));
}

export const LPS_PAGE6_ROWS_B: LpsPage6Row[] = buildRows(LPS_PAGE6_COLUMN8_B);

export const LPS_PAGE6_SOLUTIONS_B: LpsPage6Solution[] = buildSolutions(LPS_PAGE6_COLUMN8_B);

export function getLpsPage6Dataset(testName?: string): LpsPage6Dataset {
  if (testName === 'LPS-B') {
    return { rows: LPS_PAGE6_ROWS_B, solutions: LPS_PAGE6_SOLUTIONS_B };
  }

  return { rows: [], solutions: [] };
}
