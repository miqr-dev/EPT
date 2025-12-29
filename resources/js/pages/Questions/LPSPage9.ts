import { LPS_PAGE9_PLACEHOLDER_SVG, LPS_PAGE9_SHAPES_B, type LpsPage9SvgRow } from './lpsPage9SvgShapes';

const DEFAULT_PAGE9_LETTER_OPTIONS = ['A', 'B', 'C', 'D', 'E'] as const;

export type LpsPage9Prompt = {
  id: string;
  svg: string;
  svgMeta?: { width: number; height: number; viewBox?: string };
  options: string[];
  /**
   * Zero-based index of the correct option. When undefined, the prompt will not contribute to scoring.
  */
  correctIndex?: number;
};

export type LpsPage9Row = {
  id: number;
  prompts: LpsPage9Prompt[];
};

export type LpsPage9Solution = {
  /**
   * Zero-based indices for the correct option of each prompt. Use null to indicate no scoring entry.
   */
  correctOptionIndices: (number | null)[];
};

export type LpsPage9Dataset = {
  rows: LpsPage9Row[];
  solutions: LpsPage9Solution[];
};

function buildPrompt(rowId: number, promptIdx: number, prompt?: LpsPage9SvgRow['prompts'][number]): LpsPage9Prompt {
  const options = prompt?.options ? [...prompt.options] : [...DEFAULT_PAGE9_LETTER_OPTIONS];
  const resolvedIndex =
    typeof prompt?.correctIndex === 'number'
      ? prompt.correctIndex
      : prompt?.correctLetter
        ? options.findIndex((option) => option === prompt.correctLetter)
        : undefined;

  return {
    id: `lps-b-col11-r${rowId}-p${promptIdx + 1}`,
    svg: prompt?.svg ?? LPS_PAGE9_PLACEHOLDER_SVG,
    svgMeta: prompt?.svgMeta,
    options,
    correctIndex: resolvedIndex !== -1 ? resolvedIndex : undefined,
  };
}

function buildRow(rowIdx: number, promptRow?: LpsPage9SvgRow): LpsPage9Row {
  const promptCount = 3;
  const prompts = Array.from({ length: promptCount }, (_, promptIdx) =>
    buildPrompt(rowIdx, promptIdx, promptRow?.prompts?.[promptIdx]),
  );

  return {
    id: rowIdx,
    prompts,
  };
}

function buildRows(promptRows?: LpsPage9SvgRow[]) {
  const rows = Array.from({ length: promptRows?.length ?? 0 }, (_, idx) => buildRow(idx + 1, promptRows?.[idx]));

  while (rows.length < 14) {
    rows.push(buildRow(rows.length + 1));
  }

  return rows;
}

function buildSolutions(rows: LpsPage9Row[]): LpsPage9Solution[] {
  return rows.map((row) => ({
    correctOptionIndices: row.prompts.map((prompt) =>
      typeof prompt.correctIndex === 'number' ? prompt.correctIndex : null,
    ),
  }));
}

export const LPS_PAGE9_ROWS_B: LpsPage9Row[] = buildRows(LPS_PAGE9_SHAPES_B);
export const LPS_PAGE9_SOLUTIONS_B: LpsPage9Solution[] = buildSolutions(LPS_PAGE9_ROWS_B);

export function getLpsPage9Dataset(testName?: string): LpsPage9Dataset {
  if (testName === 'LPS-B') {
    return { rows: LPS_PAGE9_ROWS_B, solutions: LPS_PAGE9_SOLUTIONS_B };
  }

  return { rows: [], solutions: [] };
}
