import { LPS_PAGE7_COLUMN9_B } from './lpsPage7SvgShapes';

export type LpsPage7Shape = {
  id: string;
  svgContent: string;
  svgMeta: { viewBox: string; width: number; height: number; translateX?: number; translateY?: number };
  isExample?: boolean;
  correctOption?: number;
};

export type LpsPage7Row = {
  id: number;
  shapes: LpsPage7Shape[];
  options: number[];
};

export type LpsPage7Solution = {
  shapes?: Array<number | undefined>;
};

export type LpsPage7Dataset = {
  rows: LpsPage7Row[];
  solutions: LpsPage7Solution[];
};

function buildRow(rowIdx: number, column9?: LpsPage7Row[]): LpsPage7Row {
  const entry = column9?.[rowIdx];
  const fallbackId = rowIdx + 1;
  return {
    id: entry?.id ?? fallbackId,
    shapes: entry?.shapes ?? [],
    options: entry?.options ?? [],
  };
}

function buildRows(column9?: LpsPage7Row[]) {
  const rowCount = column9?.length ?? 0;
  return Array.from({ length: rowCount }, (_, idx) => buildRow(idx, column9));
}

function extractRowSolution(entry?: LpsPage7Row): LpsPage7Solution {
  const correct = entry?.shapes.map((shape) => {
    if (typeof shape.correctOption !== 'number') return undefined;
    const optionIndex = entry.options.findIndex((value) => value === shape.correctOption);
    return optionIndex === -1 ? undefined : optionIndex;
  });
  return { shapes: correct };
}

function buildSolutions(column9?: LpsPage7Row[]): LpsPage7Solution[] {
  return buildRows(column9).map((_, idx) => extractRowSolution(column9?.[idx]));
}

export const LPS_PAGE7_ROWS_B: LpsPage7Row[] = buildRows(LPS_PAGE7_COLUMN9_B);
export const LPS_PAGE7_SOLUTIONS_B: LpsPage7Solution[] = buildSolutions(LPS_PAGE7_COLUMN9_B);

export function getLpsPage7Dataset(testName?: string): LpsPage7Dataset {
  if (testName === 'LPS-B') {
    return { rows: LPS_PAGE7_ROWS_B, solutions: LPS_PAGE7_SOLUTIONS_B };
  }
  return { rows: [], solutions: [] };
}
