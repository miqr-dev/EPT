import { computed } from 'vue';

export type LpsPage9Prompt = {
  id: string;
  svg: string;
  svgMeta?: { width: number; height: number; viewBox?: string };
  options: string[];
  correctIndex: number | null;
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

type LpsPage9SvgPrompt = {
  svg?: string;
  svgMeta?: { width: number; height: number; viewBox?: string };
  options?: string[];
  correctOption?: string;
};

type LpsPage9SvgRow = {
  prompts: LpsPage9SvgPrompt[];
};

const DEFAULT_META = { width: 150, height: 130, viewBox: '0 0 150 130' } as const;
const LETTER_BANK = 'ABCDEFGHJKLMNPQRSTUVWXYZ';

const PRESET_ROWS_B: LpsPage9SvgRow[] = [
  {
    prompts: [
      { svg: buildBadgeSvg('A'), options: ['T', 'H', 'R', 'V', 'W'], correctOption: 'H' },
      { svg: buildBadgeSvg('B'), options: ['L', 'N', 'S', 'T', 'A'], correctOption: 'A' },
      { svg: buildBadgeSvg('C'), options: ['M', 'N', 'O', 'T', 'F'], correctOption: 'M' },
    ],
  },
];

function buildBadgeSvg(label: string) {
  const hue = (label.charCodeAt(0) * 17) % 360;
  return `
<svg xmlns="http://www.w3.org/2000/svg" width="${DEFAULT_META.width}" height="${DEFAULT_META.height}" viewBox="${DEFAULT_META.viewBox}" aria-hidden="true">
  <defs>
    <linearGradient id="grad-${label}" x1="0" x2="1" y1="0" y2="1">
      <stop offset="0%" stop-color="hsl(${hue}, 75%, 72%)" />
      <stop offset="100%" stop-color="hsl(${(hue + 40) % 360}, 78%, 64%)" />
    </linearGradient>
  </defs>
  <rect x="10" y="10" width="130" height="110" rx="16" fill="url(#grad-${label})" />
  <circle cx="75" cy="65" r="34" fill="rgba(255,255,255,0.85)" />
  <text x="75" y="72" text-anchor="middle" font-family="'Inter', 'Arial', sans-serif" font-size="36" font-weight="800" fill="hsl(${hue}, 68%, 32%)">
    ${label}
  </text>
</svg>`;
}

function generateOptions(seed: number) {
  const options: string[] = [];
  let offset = (seed * 3) % LETTER_BANK.length;
  while (options.length < 5) {
    const next = LETTER_BANK[offset % LETTER_BANK.length];
    if (!options.includes(next)) options.push(next);
    offset += 2;
  }
  const correctIndex = seed % options.length;
  return { options, correctIndex };
}

function resolveCorrectIndex(options: string[], correctOption?: string | null) {
  if (!correctOption) return null;
  const idx = options.findIndex((opt) => opt === correctOption);
  return idx === -1 ? null : idx;
}

function buildPrompt(rowId: number, promptIdx: number, prompt?: LpsPage9SvgPrompt): LpsPage9Prompt {
  const seed = rowId * 7 + promptIdx * 3;
  const { options, correctIndex: defaultCorrect } = generateOptions(seed);
  const mergedOptions = prompt?.options?.length ? prompt.options : options;
  const correctIndex = resolveCorrectIndex(mergedOptions, prompt?.correctOption) ?? defaultCorrect;

  return {
    id: `lps-b-r${rowId}-c11-p${promptIdx + 1}`,
    svg: prompt?.svg ?? buildBadgeSvg(`${rowId}-${promptIdx + 1}`),
    svgMeta: prompt?.svgMeta ?? DEFAULT_META,
    options: mergedOptions,
    correctIndex,
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
  const rows = (svgRows ?? []).map((row, idx) => buildRow(idx, row));
  while (rows.length < 14) {
    rows.push(buildRow(rows.length, undefined));
  }
  return rows;
}

function buildSolutions(rows: LpsPage9Row[]): LpsPage9Solution[] {
  return rows.map((row) => ({
    correctOptionIndices: row.prompts.map((prompt) => prompt.correctIndex ?? null),
  }));
}

const rowsB = buildRows(PRESET_ROWS_B);
const solutionsB = buildSolutions(rowsB);

export function getLpsPage9Dataset(testName?: string): LpsPage9Dataset {
  if (testName === 'LPS-B') {
    return { rows: rowsB, solutions: solutionsB };
  }

  return { rows: [], solutions: [] };
}

export const LPS_PAGE9_ROWS_B = computed(() => rowsB);
export const LPS_PAGE9_SOLUTIONS_B = computed(() => solutionsB);
