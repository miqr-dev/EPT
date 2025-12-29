export type LpsPage9SvgMeta = { width: number; height: number; viewBox?: string };

export type LpsPage9SvgPrompt = {
  svg: string;
  svgMeta?: LpsPage9SvgMeta;
  /**
   * Five unique letter options for this SVG (e.g., ['T', 'H', 'R', 'V', 'W']).
   */
  options: string[];
  /**
   * The letter that represents the correct answer for this SVG. The dataset builder
   * will resolve this to a zero-based index when possible.
   */
  correctOption?: string;
};

export type LpsPage9SvgRow = {
  prompts: LpsPage9SvgPrompt[];
};

const PLACEHOLDER_META: LpsPage9SvgMeta = { width: 148, height: 146, viewBox: '0 0 148 146' };

export const LPS_PAGE9_PLACEHOLDER_SVG = `
<svg xmlns="http://www.w3.org/2000/svg" width="148" height="146" viewBox="0 0 148 146" aria-hidden="true">
  <rect x="4" y="4" width="140" height="138" rx="16" fill="#f8fafc" stroke="#e2e8f0" stroke-width="4" />
  <path d="M26 98h96" stroke="#cbd5e1" stroke-width="8" stroke-linecap="round" stroke-dasharray="18 12" />
  <circle cx="46" cy="50" r="18" fill="#cbd5e1" />
  <rect x="78" y="32" width="38" height="38" rx="8" fill="#e2e8f0" />
</svg>`;

const BADGE_META: LpsPage9SvgMeta = { width: 148, height: 146, viewBox: '0 0 148 146' };

function badge(label: string, hue: number): string {
  return `
<svg xmlns="http://www.w3.org/2000/svg" width="148" height="146" viewBox="0 0 148 146" aria-hidden="true">
  <defs>
    <linearGradient id="grad-${label}" x1="0" x2="1" y1="0" y2="1">
      <stop offset="0%" stop-color="hsl(${hue}, 75%, 72%)" />
      <stop offset="100%" stop-color="hsl(${(hue + 40) % 360}, 78%, 64%)" />
    </linearGradient>
  </defs>
  <rect x="10" y="10" width="128" height="126" rx="16" fill="url(#grad-${label})" />
  <circle cx="74" cy="73" r="34" fill="rgba(255,255,255,0.86)" />
  <text x="74" y="80" text-anchor="middle" font-family="'Inter', 'Arial', sans-serif" font-size="36" font-weight="800" fill="hsl(${hue}, 68%, 32%)">
    ${label}
  </text>
</svg>`;
}

export const LPS_PAGE9_SHAPES_B: LpsPage9SvgRow[] = [
  {
    prompts: [
      {
        svg: badge('A', 210),
        svgMeta: BADGE_META,
        options: ['T', 'H', 'R', 'V', 'W'],
        correctOption: 'H',
      },
      {
        svg: badge('B', 165),
        svgMeta: BADGE_META,
        options: ['L', 'N', 'S', 'T', 'A'],
        correctOption: 'A',
      },
      {
        svg: badge('C', 32),
        svgMeta: BADGE_META,
        options: ['M', 'N', 'O', 'T', 'F'],
        correctOption: 'M',
      },
    ],
  },
];

export const LPS_PAGE9_PLACEHOLDER_META = PLACEHOLDER_META;
