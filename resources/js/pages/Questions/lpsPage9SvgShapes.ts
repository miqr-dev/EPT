export type LpsPage9SvgPrompt = {
  svg: string;
  svgMeta?: { width: number; height: number; viewBox?: string };
  options?: string[];
  /**
   * Preferred way to mark the correct option; this is resolved to a zero-based index in the dataset builder.
   */
  correctLetter?: string;
  correctIndex?: number;
};

export type LpsPage9SvgRow = {
  prompts: LpsPage9SvgPrompt[];
};

export const LPS_PAGE9_PLACEHOLDER_SVG = `
<svg viewBox="0 0 140 120" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <rect x="16" y="18" width="108" height="84" rx="8" fill="#e2e8f0" stroke="#cbd5e1" stroke-width="2" />
  <path d="M16 78 L50 46 L86 78 L104 62 L124 82" fill="none" stroke="#94a3b8" stroke-width="3" />
  <circle cx="52" cy="44" r="6" fill="#0f172a" opacity="0.1" />
</svg>`;

export const LPS_PAGE9_SHAPES_B: LpsPage9SvgRow[] = [];
