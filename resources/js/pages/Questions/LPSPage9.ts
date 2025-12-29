import {
  LPS_PAGE9_PLACEHOLDER_META,
  LPS_PAGE9_PLACEHOLDER_SVG,
  LPS_PAGE9_SHAPES_B,
  type LpsPage9SvgRow,
} from './lpsPage9SvgShapes';

export type LpsPage9Prompt = {
  id: string;
  svg: string;
  svgMeta?: { width: number; height: number; viewBox?: string };
  options: string[];
  correctIndex?: number | null;
};

export type LpsPage9Row = {
  id: number;
  prompts: LpsPage9Prompt[];
};

export type LpsPage9Solution = {
  correctOptionIndices: (number | null)[];
};

export type LpsPage9Dataset = {
  rows: LpsPage9Row[];
  solutions: LpsPage9Solution[];
};

const LETTER_BANK = 'ABCDEFGHJKLMNPQRSTUVWXYZ';

function buildFallbackOptions(seed: number) {
  const options: string[] = [];
  let offset = (seed * 5) % LETTER_BANK.length;
  while (options.length < 5) {
    const next = LETTER_BANK[offset % LETTER_BANK.length];
    if (!options.includes(next)) options.push(next);
    offset += 3;
  }
  return options;
}

function resolveCorrectIndex(options: string[], correctOption?: string) {
  if (!correctOption) return null;
  const idx = options.findIndex((option) => option === correctOption);
  return idx === -1 ? null : idx;
}

function buildPrompt(rowId: number, promptIdx: number, prompt?: LpsPage9SvgRow['prompts'][number]): LpsPage9Prompt {
  const fallbackOptions = buildFallbackOptions(rowId * 7 + promptIdx * 11);
  const options = prompt?.options?.length ? prompt.options : fallbackOptions;
  return {
    id: `lps-b-r${rowId}-p${promptIdx + 1}`,
    svg: prompt?.svg ?? LPS_PAGE9_PLACEHOLDER_SVG,
    svgMeta: prompt?.svgMeta ?? LPS_PAGE9_PLACEHOLDER_META,
    options,
    correctIndex: resolveCorrectIndex(options, prompt?.correctOption),
  };
}

function buildRow(rowIdx: number, svgRow?: LpsPage9SvgRow): LpsPage9Row {
  const prompts = Array.from({ length: 3 }, (_, promptIdx) => buildPrompt(rowIdx + 1, promptIdx, svgRow?.prompts?.[promptIdx]));
  return {
    id: rowIdx + 1,
    prompts,
  };
}

function buildRows(svgRows?: LpsPage9SvgRow[]) {
  const rows = Array.from({ length: svgRows?.length ?? 0 }, (_, idx) => buildRow(idx, svgRows?.[idx]));
  while (rows.length < 14) {
    rows.push(buildRow(rows.length));
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

export const LPS_PAGE9_ROWS_B = buildRows(LPS_PAGE9_SHAPES_B);
export const LPS_PAGE9_SOLUTIONS_B = buildSolutions(LPS_PAGE9_ROWS_B);

export function getLpsPage9Dataset(testName?: string): LpsPage9Dataset {
  if (testName === 'LPS-B') {
    return { rows: LPS_PAGE9_ROWS_B, solutions: LPS_PAGE9_SOLUTIONS_B };
  }

  return { rows: [], solutions: [] };
}
