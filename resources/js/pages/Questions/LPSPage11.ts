import { LPS_PAGE11_PLACEHOLDER_SVG, LPS_PAGE11_SHAPES_B, type LpsPage11SvgRow } from './lpsPage11SvgShapes';

export type LpsPage11Prompt = {
  id: string;
  svg: string;
  svgMeta?: { width: number; height: number; viewBox?: string };
  options: string[];
  /**
   * Zero-based index of the correct option. When undefined, the prompt will not contribute to scoring.
   */
  correctIndex?: number;
};

export type LpsPage11Row = {
  id: number;
  prompts: LpsPage11Prompt[];
};

export type LpsPage11Solution = {
  /**
   * Zero-based indices for the correct option of each prompt. Use null to indicate no scoring entry.
   */
  correctOptionIndices: (number | null)[];
};

export type LpsPage11Dataset = {
  rows: LpsPage11Row[];
  solutions: LpsPage11Solution[];
};

function resolveCorrectIndex(prompt?: LpsPage11SvgRow['prompts'][number]) {
  if (!prompt?.options?.length) return undefined;
  if (typeof prompt.correctIndex === 'number') return prompt.correctIndex;
  if (prompt.correctOption !== undefined) {
    const idx = prompt.options.indexOf(prompt.correctOption);
    return idx !== -1 ? idx : undefined;
  }
  return undefined;
}

function buildPrompt(rowId: number, promptIdx: number, prompt?: LpsPage11SvgRow['prompts'][number]): LpsPage11Prompt {
  const options = prompt?.options ? [...prompt.options] : [];
  return {
    id: `lps-b-c11-r${rowId}-p${promptIdx + 1}`,
    svg: prompt?.svg ?? LPS_PAGE11_PLACEHOLDER_SVG,
    svgMeta: prompt?.svgMeta,
    options,
    correctIndex: resolveCorrectIndex(prompt),
  };
}

function buildRow(rowIdx: number, promptRow?: LpsPage11SvgRow): LpsPage11Row {
  const promptCount = 3;
  const prompts = Array.from({ length: promptCount }, (_, promptIdx) =>
    buildPrompt(rowIdx, promptIdx, promptRow?.prompts?.[promptIdx]),
  );

  return {
    id: rowIdx,
    prompts,
  };
}

function buildRows(promptRows?: LpsPage11SvgRow[]) {
  const totalRows = 14;
  const rows: LpsPage11Row[] = [];

  for (let idx = 0; idx < totalRows; idx += 1) {
    rows.push(buildRow(idx + 1, promptRows?.[idx]));
  }

  return rows;
}

function buildSolutions(rows: LpsPage11Row[]): LpsPage11Solution[] {
  return rows.map((row) => ({
    correctOptionIndices: row.prompts.map((prompt) =>
      typeof prompt.correctIndex === 'number' ? prompt.correctIndex : null,
    ),
  }));
}

export const LPS_PAGE11_ROWS_B: LpsPage11Row[] = buildRows(LPS_PAGE11_SHAPES_B);
export const LPS_PAGE11_SOLUTIONS_B: LpsPage11Solution[] = buildSolutions(LPS_PAGE11_ROWS_B);

export function getLpsPage11Dataset(testName?: string): LpsPage11Dataset {
  if (testName === 'LPS-B') {
    return { rows: LPS_PAGE11_ROWS_B, solutions: LPS_PAGE11_SOLUTIONS_B };
  }

  return { rows: [], solutions: [] };
}
