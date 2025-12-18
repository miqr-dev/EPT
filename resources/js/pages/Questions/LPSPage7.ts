export type LpsPage7Prompt = {
  id: string;
  svg: string;
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

const SAMPLE_CUBE = `
<svg viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <g fill="#e2e8f0" stroke="#0f172a" stroke-linejoin="round" stroke-width="2">
    <polygon points="30,30 70,20 100,40 60,50" fill="#cbd5e1" />
    <polygon points="60,50 100,40 100,80 60,90" fill="#e5e7eb" />
    <polygon points="30,30 60,50 60,90 30,70" fill="#94a3b8" />
  </g>
</svg>`;

const SAMPLE_PRISM = `
<svg viewBox="0 0 140 120" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <g fill="#e2e8f0" stroke="#0f172a" stroke-linejoin="round" stroke-width="2">
    <polygon points="30,70 80,50 120,70 70,90" fill="#cbd5e1" />
    <polygon points="30,70 70,90 70,110 30,90" fill="#94a3b8" />
    <polygon points="70,90 120,70 120,92 70,110" fill="#e5e7eb" />
    <polygon points="30,70 55,40 105,60 80,90" fill="#cbd5e1" />
    <polygon points="105,60 120,70 80,90" fill="#e5e7eb" />
  </g>
</svg>`;

const SAMPLE_CYLINDER = `
<svg viewBox="0 0 140 120" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <defs>
    <linearGradient id="cyl" x1="0%" x2="0%" y1="0%" y2="100%">
      <stop offset="0%" stop-color="#e2e8f0" />
      <stop offset="100%" stop-color="#cbd5e1" />
    </linearGradient>
  </defs>
  <g stroke="#0f172a" stroke-width="2">
    <ellipse cx="70" cy="28" rx="40" ry="12" fill="#e5e7eb" />
    <path d="M30 28v64c0 8 18 14 40 14s40-6 40-14V28" fill="url(#cyl)" />
    <ellipse cx="70" cy="92" rx="40" ry="12" fill="#cbd5e1" />
  </g>
</svg>`;

const SAMPLE_CONE = `
<svg viewBox="0 0 140 120" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <g stroke="#0f172a" stroke-width="2" fill="#e2e8f0" stroke-linejoin="round">
    <path d="M70 12 L120 96 Q70 108 20 96 Z" fill="#e5e7eb" />
    <path d="M70 12 L70 96" stroke="#94a3b8" />
    <ellipse cx="70" cy="96" rx="50" ry="12" fill="#cbd5e1" />
  </g>
</svg>`;

const SAMPLE_PYRAMID = `
<svg viewBox="0 0 140 120" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <g stroke="#0f172a" stroke-width="2" fill="#e2e8f0" stroke-linejoin="round">
    <polygon points="70,12 120,88 70,108 20,88" fill="#e5e7eb" />
    <polygon points="70,12 120,88 70,108" fill="#cbd5e1" />
    <polygon points="70,12 20,88 70,108" fill="#94a3b8" />
    <polygon points="20,88 70,108 120,88 70,108" fill="#cbd5e1" />
  </g>
</svg>`;

const SAMPLE_HALF_CYLINDER = `
<svg viewBox="0 0 140 120" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <g stroke="#0f172a" stroke-width="2">
    <path d="M30 42v52c0 8 18 14 40 14s40-6 40-14V42" fill="#e2e8f0" />
    <path d="M30 42c0-12 18-22 40-22s40 10 40 22" fill="#cbd5e1" />
    <ellipse cx="70" cy="94" rx="40" ry="12" fill="#94a3b8" />
  </g>
</svg>`;

const PLACEHOLDER_SHAPE = `
<svg viewBox="0 0 140 120" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <rect x="16" y="18" width="108" height="84" rx="8" fill="#e2e8f0" stroke="#cbd5e1" stroke-width="2" />
  <path d="M16 78 L50 46 L86 78 L104 62 L124 82" fill="none" stroke="#94a3b8" stroke-width="3" />
  <circle cx="52" cy="44" r="6" fill="#0f172a" opacity="0.1" />
</svg>`;

function buildPlaceholderRow(rowId: number): LpsPage7Row {
  return {
    id: rowId,
    prompts: Array.from({ length: 3 }, (_, promptIdx) => ({
      id: `lps-b-r${rowId}-p${promptIdx + 1}`,
      svg: PLACEHOLDER_SHAPE,
      options: [1, 2, 3, 4, 5],
    })),
  };
}

const baseRows: LpsPage7Row[] = [
  {
    id: 1,
    prompts: [
      { id: 'lps-b-r1-p1', svg: SAMPLE_CUBE, options: [3, 4, 5, 6, 7], correctIndex: 3 },
      { id: 'lps-b-r1-p2', svg: SAMPLE_PRISM, options: [4, 5, 6, 7, 8], correctIndex: 1 },
      { id: 'lps-b-r1-p3', svg: SAMPLE_CYLINDER, options: [2, 3, 4, 5, 6], correctIndex: 1 },
    ],
  },
  {
    id: 2,
    prompts: [
      { id: 'lps-b-r2-p1', svg: SAMPLE_CONE, options: [1, 2, 3, 4, 5], correctIndex: 2 },
      { id: 'lps-b-r2-p2', svg: SAMPLE_PYRAMID, options: [3, 4, 5, 6, 7], correctIndex: 2 },
      { id: 'lps-b-r2-p3', svg: SAMPLE_HALF_CYLINDER, options: [2, 3, 4, 5, 6], correctIndex: 2 },
    ],
  },
];

while (baseRows.length < 14) {
  baseRows.push(buildPlaceholderRow(baseRows.length + 1));
}

function buildSolutions(rows: LpsPage7Row[]): LpsPage7Solution[] {
  return rows.map((row) => ({
    correctOptionIndices: row.prompts.map((prompt) =>
      typeof prompt.correctIndex === 'number' ? prompt.correctIndex : null,
    ),
  }));
}

export const LPS_PAGE7_ROWS_B: LpsPage7Row[] = baseRows;
export const LPS_PAGE7_SOLUTIONS_B: LpsPage7Solution[] = buildSolutions(baseRows);

export function getLpsPage7Dataset(testName?: string): LpsPage7Dataset {
  if (testName === 'LPS-B') {
    return { rows: LPS_PAGE7_ROWS_B, solutions: LPS_PAGE7_SOLUTIONS_B };
  }

  return { rows: [], solutions: [] };
}
