import { LPS_PAGE7_PLACEHOLDER_SVG, LPS_PAGE7_SHAPES_B, type LpsPage7SvgRow } from './lpsPage7SvgShapes';

const DEFAULT_PAGE7_NUMBER_OPTIONS = [3, 4, 5, 6, 7] as const;

export type LpsPage7Prompt = {
  id: string;
  svg: string;
  svgMeta?: { width: number; height: number; viewBox?: string };
  options: number[];
  /**
   * Zero-based index of the correct option. When undefined, the prompt will not contribute to scoring.
   */
  correctIndex?: number;
};

export type LpsPage7Row = {
  id: number;
  prompts: LpsPage7Prompt[];
};

export type LpsPage7Solution = {
  /**
   * Zero-based indices for the correct option of each prompt. Use null to indicate no scoring entry.
   */
  correctOptionIndices: (number | null)[];
};

export type LpsPage7Dataset = {
  rows: LpsPage7Row[];
  solutions: LpsPage7Solution[];
};

function buildPrompt(rowId: number, promptIdx: number, prompt?: LpsPage7SvgRow['prompts'][number]): LpsPage7Prompt {
  const options = prompt?.options ? [...prompt.options] : [...DEFAULT_PAGE7_NUMBER_OPTIONS];
  const resolvedIndex =
    typeof prompt?.correctIndex === 'number'
      ? prompt.correctIndex
      : prompt?.correctAnswer !== undefined
        ? options.indexOf(prompt.correctAnswer)
        : undefined;

  return {
    id: `lps-b-r${rowId}-p${promptIdx + 1}`,
    svg: prompt?.svg ?? LPS_PAGE7_PLACEHOLDER_SVG,
    svgMeta: prompt?.svgMeta,
    options,
    correctIndex: resolvedIndex !== -1 ? resolvedIndex : undefined,
  };
}

function buildRow(rowIdx: number, promptRow?: LpsPage7SvgRow): LpsPage7Row {
  const promptCount = 3;
  const prompts = Array.from({ length: promptCount }, (_, promptIdx) =>
    buildPrompt(rowIdx, promptIdx, promptRow?.prompts?.[promptIdx]),
  );

  return {
    id: rowIdx,
    prompts,
  };
}

function buildRows(promptRows?: LpsPage7SvgRow[]) {
  const rows = Array.from({ length: promptRows?.length ?? 0 }, (_, idx) => buildRow(idx + 1, promptRows?.[idx]));

  while (rows.length < 14) {
    rows.push(buildRow(rows.length + 1));
  }

  return rows;
}

function buildSolutions(rows: LpsPage7Row[]): LpsPage7Solution[] {
  return rows.map((row) => ({
    correctOptionIndices: row.prompts.map((prompt) =>
      typeof prompt.correctIndex === 'number' ? prompt.correctIndex : null,
    ),
  }));
}

export const LPS_PAGE7_ROWS_B: LpsPage7Row[] = buildRows(LPS_PAGE7_SHAPES_B);
export const LPS_PAGE7_SOLUTIONS_B: LpsPage7Solution[] = buildSolutions(LPS_PAGE7_ROWS_B);

export function getLpsPage7Dataset(testName?: string): LpsPage7Dataset {
  if (testName === 'LPS-B') {
    return { rows: LPS_PAGE7_ROWS_B, solutions: LPS_PAGE7_SOLUTIONS_B };
  }

  return { rows: [], solutions: [] };
}
