export type LpsPage6Option = { label: string };

export type LpsPage6Row = {
  id: number;
  column8Options: LpsPage6Option[];
  column8Svg?: string;
  column8SvgMeta?: { width: number; height: number };
  correctIndex?: number;
};

export type LpsPage6Solution = {
  col8?: number[];
};

export type LpsPage6Dataset = {
  rows: LpsPage6Row[];
  solutions: LpsPage6Solution[];
};

const DEFAULT_OPTIONS: LpsPage6Option[] = [
  { label: 'A' },
  { label: 'B' },
  { label: 'C' },
  { label: 'D' },
  { label: 'E' },
];

const LPS_PAGE6_COLUMN8_B: Array<{
  svg?: string;
  correctIndex?: number;
}> = [
  {
    correctIndex: 2,
    svg: `
      <svg viewBox="0 0 420 220" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Diagram with numbered shapes">
        <rect x="80" y="90" width="240" height="60" fill="none" stroke="#111" stroke-width="7" rx="6" />
        <circle cx="300" cy="70" r="35" fill="none" stroke="#111" stroke-width="7" />
        <circle cx="200" cy="180" r="35" fill="none" stroke="#111" stroke-width="7" />
        <text x="292" y="78" font-family="Inter, sans-serif" font-size="32" font-weight="700">1</text>
        <text x="70" y="120" font-family="Inter, sans-serif" font-size="32" font-weight="700">2</text>
        <text x="70" y="160" font-family="Inter, sans-serif" font-size="32" font-weight="700">3</text>
        <path d="M330 120c0-25 18-45 40-45s40 20 40 45v60c0 25-18 45-40 45s-40-20-40-45z" fill="none" stroke="#111" stroke-width="7" />
        <path d="M370 120v75" stroke="#111" stroke-width="7" />
        <path d="M330 120h80" stroke="#111" stroke-width="7" />
        <text x="362" y="110" font-family="Inter, sans-serif" font-size="28" font-weight="700">C</text>
        <text x="395" y="140" font-family="Inter, sans-serif" font-size="28" font-weight="700">A</text>
        <text x="334" y="140" font-family="Inter, sans-serif" font-size="28" font-weight="700">D</text>
        <text x="363" y="170" font-family="Inter, sans-serif" font-size="28" font-weight="700">E</text>
        <text x="395" y="200" font-family="Inter, sans-serif" font-size="28" font-weight="700">B</text>
      </svg>
    `,
  },
  {},
  {},
  {},
  {},
  {},
  {},
  {},
];

function buildRow(rowIdx: number, column8?: typeof LPS_PAGE6_COLUMN8_B): LpsPage6Row {
  const entry = column8?.[rowIdx];
  const fallbackId = rowIdx + 1;

  return {
    id: fallbackId,
    column8Options: DEFAULT_OPTIONS,
    column8Svg: entry?.svg,
    column8SvgMeta: entry?.svg ? { width: 420, height: 220 } : undefined,
    correctIndex: entry?.correctIndex,
  };
}

function buildRows(column8?: typeof LPS_PAGE6_COLUMN8_B) {
  const rowCount = column8?.length ?? 0;
  return Array.from({ length: rowCount }, (_, idx) => buildRow(idx, column8));
}

function extractColumn8Solution(entry?: (typeof LPS_PAGE6_COLUMN8_B)[number]) {
  if (!entry || typeof entry.correctIndex !== 'number') return [] as number[];
  if (entry.correctIndex < 0 || entry.correctIndex >= DEFAULT_OPTIONS.length) return [] as number[];
  return [entry.correctIndex];
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
