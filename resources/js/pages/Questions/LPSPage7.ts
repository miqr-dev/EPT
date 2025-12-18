
export type LpsPage7Solution = {
  col9: number[];
};

export type LpsPage7Row = {
  id: string;
  column9: Array<{
    svg: string;
    options: number[];
  }>;
};

// Placeholder SVG for now. The user will replace it later.
const placeholderSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="150" height="150" viewBox="0 0 100 100" class="h-auto w-full"><rect width="100" height="100" rx="8" fill="#e2e8f0" /></svg>`;

const LPS_PAGE7_ROWS: LpsPage7Row[] = [
  {
    id: 'p7-row-1',
    column9: [
      { svg: placeholderSvg, options: [3, 4, 5, 6, 7] },
      { svg: placeholderSvg, options: [3, 4, 5, 6, 7] },
      { svg: placeholderSvg, options: [3, 4, 5, 6, 8] },
    ],
  },
  {
    id: 'p7-row-2',
    column9: [
      { svg: placeholderSvg, options: [3, 4, 5, 6, 7] },
      { svg: placeholderSvg, options: [3, 4, 5, 6, 7] },
      { svg: placeholderSvg, options: [3, 4, 5, 6, 8] },
    ],
  },
  // Add 12 more rows here later
];

// Each number in col9 is the index of the correct answer in the options array for that image.
const LPS_PAGE7_SOLUTIONS: LpsPage7Solution[] = [
  // Row 1: Correct answers are 5 (index 2), 4 (index 1), 6 (index 3)
  { col9: [2, 1, 3] },
  // Row 2: Correct answers are 6 (index 3), 5 (index 2), 5 (index 2)
  { col9: [3, 2, 2] },
];

export function getLpsPage7Dataset() {
  // Can be extended later to support different test variations if needed
  return {
    rows: LPS_PAGE7_ROWS,
    solutions: LPS_PAGE7_SOLUTIONS,
  };
}
