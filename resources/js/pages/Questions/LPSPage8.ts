import { LPS_PAGE8_OPTION_SVGS, LPS_PAGE8_PLACEHOLDER_SVG, LPS_PAGE8_SHAPES_B, type LpsPage8SvgRow } from './lpsPage8SvgShapes';

export type LpsPage8Option = { id: string; svg: string; svgMeta?: { width: number; height: number; viewBox?: string } };

export type LpsPage8Prompt = {
  id: string;
  svg: string;
  svgMeta?: { width: number; height: number; viewBox?: string };
  options: LpsPage8Option[];
  correctIndex?: number;
};

export type LpsPage8Row = {
  id: number;
  prompts: LpsPage8Prompt[];
};

export type LpsPage8Solution = {
  correctOptionIndices: (number | null)[];
};

export type LpsPage8Dataset = {
  rows: LpsPage8Row[];
  solutions: LpsPage8Solution[];
};

function buildOptions(rowId: number, promptIdx: number) {
  return LPS_PAGE8_OPTION_SVGS.map((option, optionIdx) => ({
    ...option,
    id: `${option.id}-r${rowId}-p${promptIdx + 1}-o${optionIdx + 1}`,
  }));
}

function resolveCorrectIndex(prompt?: LpsPage8SvgRow['prompts'][number]) {
  if (!prompt?.correctOptionId) return undefined;
  const idx = LPS_PAGE8_OPTION_SVGS.findIndex((option) => option.id === prompt.correctOptionId);
  return idx !== -1 ? idx : undefined;
}

function buildPrompt(rowId: number, promptIdx: number, prompt?: LpsPage8SvgRow['prompts'][number]): LpsPage8Prompt {
  return {
    id: `lps-b-r${rowId}-p${promptIdx + 1}`,
    svg: prompt?.svg ?? LPS_PAGE8_PLACEHOLDER_SVG,
    svgMeta: prompt?.svgMeta,
    options: buildOptions(rowId, promptIdx),
    correctIndex: resolveCorrectIndex(prompt),
  };
}

function buildRow(rowIdx: number, svgRow?: LpsPage8SvgRow): LpsPage8Row {
  const promptCount = 2;
  const prompts = Array.from({ length: promptCount }, (_, promptIdx) =>
    buildPrompt(rowIdx + 1, promptIdx, svgRow?.prompts?.[promptIdx]),
  );

  return {
    id: rowIdx + 1,
    prompts,
  };
}

function buildRows(svgRows?: LpsPage8SvgRow[]) {
  const rows = Array.from({ length: svgRows?.length ?? 0 }, (_, idx) => buildRow(idx, svgRows?.[idx]));

  while (rows.length < 21) {
    rows.push(buildRow(rows.length + 1));
  }

  return rows;
}

function buildSolutions(rows: LpsPage8Row[]): LpsPage8Solution[] {
  return rows.map((row) => ({
    correctOptionIndices: row.prompts.map((prompt) =>
      typeof prompt.correctIndex === 'number' ? prompt.correctIndex : null,
    ),
  }));
}

export const LPS_PAGE8_ROWS_B: LpsPage8Row[] = buildRows(LPS_PAGE8_SHAPES_B);
export const LPS_PAGE8_SOLUTIONS_B: LpsPage8Solution[] = buildSolutions(LPS_PAGE8_ROWS_B);

export function getLpsPage8Dataset(testName?: string): LpsPage8Dataset {
  if (testName === 'LPS-B') {
    return { rows: LPS_PAGE8_ROWS_B, solutions: LPS_PAGE8_SOLUTIONS_B };
  }

  return { rows: [], solutions: [] };
}
