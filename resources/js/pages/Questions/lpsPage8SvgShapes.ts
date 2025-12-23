type LpsPage8SvgMeta = { width: number; height: number; viewBox?: string };

export type LpsPage8SvgOption = {
  id: string;
  svg: string;
  svgMeta?: LpsPage8SvgMeta;
};

export type LpsPage8SvgPrompt = {
  svg: string;
  svgMeta?: LpsPage8SvgMeta;
  /**
   * Identifier of the matching option. The dataset builder will resolve this to a zero-based index.
   */
  correctOptionId?: string;
};

export type LpsPage8SvgRow = {
  prompts: LpsPage8SvgPrompt[];
};

const PLACEHOLDER_META: LpsPage8SvgMeta = { width: 148, height: 146, viewBox: '0 0 148 146' };

export const LPS_PAGE8_PLACEHOLDER_SVG = `
<svg xmlns="http://www.w3.org/2000/svg" width="148" height="146" viewBox="0 0 148 146" aria-hidden="true">
  <rect x="4" y="4" width="140" height="138" rx="16" fill="#f8fafc" stroke="#e2e8f0" stroke-width="4" />
  <path d="M26 98h96" stroke="#cbd5e1" stroke-width="8" stroke-linecap="round" stroke-dasharray="18 12" />
  <circle cx="46" cy="50" r="18" fill="#cbd5e1" />
  <rect x="78" y="32" width="38" height="38" rx="8" fill="#e2e8f0" />
</svg>`;

const FIRST_QUESTION_SVG =
  '<svg xmlns="http://www.w3.org/2000/svg" width="148" height="146" viewBox="0 0 148 146"><path fill="#202325" d="M12.6 131.64q-.51-1.66-.35-3.89 1.15-16.35.72-29c-.31-9.11.5-14.97.17-23.33q-.09-2.35.07-9.34.59-26.41-.34-54.12a1.86 1.84-14.8 0 1 1-1.7q1.31-.69 3.39-.71c20.38-.28 38.99.02 60.42-.15q8.67-.07 25.15.03 20.29.12 25.14-.13 5.12-.26 4.81 4.68-.31 5.1-.3 6.52.36 28.99.04 38.75-.2 6.02-.05 8.25.37 5.44-.13 7.97-.34 1.73.02 5.36c.39 3.99-.04 8.41.13 12.69q.17 4.51.11 7.36-.37 17-.06 28.07a2.06 2.05-9.1 0 1-1.47 2.02c-5.05 1.52-10.76.45-15.69.99a10.08 9.67 42.8 0 1-1.83.03c-7.75-.62-15.73-.02-23.39-.16q-13.94-.24-24.16.25c-3.65.18-7.5-.34-10.99.27a6.61 6.52 43.8 0 1-2.13.03q-3.15-.47-10.67-.2-2.44.09-10.42.04-8.92-.06-18.54-.1a.68.68 0 0 1-.65-.48M128.424 18.407a3.02 3.02 0 0 0-3.025-3.015l-66.34.116a3.02 3.02 0 0 0-3.015 3.025l.112 63.96a3.02 3.02 0 0 0 3.025 3.015l66.34-.116a3.02 3.02 0 0 0 3.015-3.025zM18.12 16.77q-.86 16.21-.53 31.52c.12 6.08-.23 11.19-.07 16.87q.1 3.8.09 12.64-.03 15.08-.27 46.57a.97.97 0 0 0 .82.97q4.14.62 8.59.39.57-.03 16 .13 20.09.21 32.25.02 2.27-.04 4.49-.12 3.25-.11 10.5.02 4.73.08 10.51-.02 8.77-.17 25.96-.11a1.53 1.52-81.7 0 0 1.48-1.11q.62-2.19.24-5.17c-.34-2.61.43-5.16.34-7.79q-.18-5.34-.13-14.33.02-2.6-.49-4.35a1.9 1.89-7.8 0 0-1.81-1.37q-31.67-.1-47.34.14-6.88.11-9.53-1.05a1.52 1.51 48.2 0 0-1.04-.06l-2.66.79a4.89 4.82 35.9 0 1-1.31.2q-5.07.1-10.48-.12-2.38-.1-2.33-2.5c.63-27.89 1.2-48.07.02-71.44a1.65 1.64 5.8 0 0-1.24-1.51c-1.13-.29-2.95-.59-3.93-.58q-15.49.19-26.88.19a1.25 1.25 0 0 0-1.25 1.18"/></svg>';

export const LPS_PAGE8_OPTION_SVGS: LpsPage8SvgOption[] = [
  {
    id: 'triangle',
    svg: '<svg xmlns="http://www.w3.org/2000/svg" width="52" height="54" viewBox="0 0 52 54"><path fill="#222324" d="m30.64 9.39 18.02 36.09a2.65 2.65 0 0 1-2.41 3.83l-34.68-.55a2.65 2.65 0 0 1-2.36-3.77L25.87 9.45a2.65 2.65 0 0 1 4.77-.06M16.63 40.75a1.54 1.54 0 0 0 1.39 2.21l21.33-.07a1.54 1.54 0 0 0 1.37-2.23L29.98 19.02a1.54 1.54 0 0 0-2.76.01z"/></svg>',
    svgMeta: { width: 52, height: 54, viewBox: '0 0 52 54' },
  },
  {
    id: 'square',
    svg: '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="56" viewBox="0 0 48 56"><path fill="#191b1c" d="M22.2 48.8 2.13 28.73a2.56 2.56 0 0 1 0-3.62L22.66 4.59a2.56 2.56 0 0 1 3.69.07l18.58 20.01a2.56 2.56 0 0 1 0 3.48L25.89 48.73a2.56 2.56 0 0 1-3.69.07m2.16-38.25L9.45 25.46a1.39 1.39 0 0 0-.1 1.85q6.6 8.24 13.84 14.51a1.84 1.84 0 0 0 2.56-.14l13.22-14.29a1.14 1.13-45.2 0 0 0-1.55L24.79 10.55a.3.3 0 0 0-.43 0"/></svg>',
    svgMeta: { width: 48, height: 56, viewBox: '0 0 48 56' },
  },
  {
    id: 't-shape',
    svg: '<svg xmlns="http://www.w3.org/2000/svg" width="51" height="51" viewBox="0 0 51 51"><path fill="#1f2021" d="M45.36 11.54a2.13 2.12 1.2 0 1-2.22 2.12q-6.29-.28-6.62-.3-2.71-.15-5.6.66a2.01 2-7.7 0 0-1.46 1.91q-.11 8.79-.46 13.38c-.36 4.6.63 9.59.16 14.68a1.99 1.99 0 0 1-1.98 1.82h-.91a1.77 1.76-86.5 0 1-1.75-1.98q.54-4.46.22-9.23c-.47-7.08.43-12.53-.42-19.3a2 1.99-3.6 0 0-1.98-1.74H10.57a2.71 2.71 0 0 1-2.62-2.02L7.54 10a1.81 1.8 81 0 1 1.65-2.28q15.9-.8 21.45-.7c4.18.08 7.73-.6 12.24-.03a2.84 2.83 3.7 0 1 2.48 2.81z"/></svg>',
    svgMeta: { width: 51, height: 51, viewBox: '0 0 51 51' },
  },
  {
    id: 'u-shape',
    svg: '<svg xmlns="http://www.w3.org/2000/svg" width="44" height="53" viewBox="0 0 44 53"><path fill="#060908" d="M33.75 8.45a1.47 1.47 0 0 1 1.97-1.35l.05.02a2.85 2.84-79.5 0 1 1.82 2.66l-.11 34.26a2.31 2.31 0 0 1-2.37 2.3q-12.13-.3-23.36.11-2.31.09-4.3-.84a1.81 1.8 12.7 0 1-1.05-1.65l.27-35.1a1.83 1.82 1.1 0 1 1.89-1.81l.15.01a2.04 2.04 0 0 1 1.97 2.04l-.05 30.65a1.19 1.19 0 0 0 1.19 1.19q7.14.01 14.93.11.54.01 5.92-.94a.77.77 0 0 0 .63-.66q.45-3.56.45-3.7-.33-14.16 0-27.3"/></svg>',
    svgMeta: { width: 44, height: 53, viewBox: '0 0 44 53' },
  },
  {
    id: 'l-shape',
    svg: '<svg xmlns="http://www.w3.org/2000/svg" width="44" height="59" viewBox="0 0 44 59"><path fill="#1d1e21" d="M9.71 10.09a1.88 1.88 0 0 1 2.17-1.88h.03a2.53 2.52 5 0 1 2.14 2.54q-.21 12.48-.24 25.73c-.01 2.51.41 4.58.57 7a1.19 1.17 87.7 0 0 1.18 1.1q9.81-.07 18.23-.11 3.43-.01 2.73 4.19a2.3 2.29.5 0 1-1.94 1.9c-3.7.54-6.57-.48-10.08-.11q-3.16.32-12.52-.13-2.86-.14-2.78-2.76.66-21.52.51-37.47"/></svg>',
    svgMeta: { width: 44, height: 59, viewBox: '0 0 44 59' },
  },
];

// To mark the correct answer for a prompt, set `correctOptionId` on the
// corresponding entry in BASE_ROWS (or future rows). The value must match one of
// the option ids above (triangle, square, t-shape, u-shape, l-shape).

function buildPlaceholderRow(): LpsPage8SvgRow {
  return {
    prompts: [
      { svg: LPS_PAGE8_PLACEHOLDER_SVG, svgMeta: PLACEHOLDER_META },
      { svg: LPS_PAGE8_PLACEHOLDER_SVG, svgMeta: PLACEHOLDER_META },
    ],
  };
}

const BASE_ROWS: LpsPage8SvgRow[] = [
  {
    prompts: [
      { svg: FIRST_QUESTION_SVG, svgMeta: PLACEHOLDER_META, correctOptionId: 'square' },
      { svg: LPS_PAGE8_PLACEHOLDER_SVG, svgMeta: PLACEHOLDER_META },
    ],
  },
  {
    prompts: [
      { svg: LPS_PAGE8_OPTION_SVGS[0].svg, svgMeta: LPS_PAGE8_OPTION_SVGS[0].svgMeta, correctOptionId: 'triangle' },
      { svg: LPS_PAGE8_OPTION_SVGS[1].svg, svgMeta: LPS_PAGE8_OPTION_SVGS[1].svgMeta, correctOptionId: 'square' },
    ],
  },
];

export const LPS_PAGE8_SHAPES_B: LpsPage8SvgRow[] = (() => {
  const rows: LpsPage8SvgRow[] = [...BASE_ROWS];

  while (rows.length < 21) {
    rows.push(buildPlaceholderRow());
  }

  return rows;
})();
