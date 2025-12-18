const DEFAULT_OPTIONS = [3, 4, 5, 6, 7, 8];

const PLACEHOLDER_ROW1_CONTENT = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 355 118" width="355" height="118"><text x="8" y="16" font-size="12" fill="#222326">Row 1 SVG missing</text></svg>`;
const PLACEHOLDER_ROW2_CONTENT = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 355 123" width="355" height="123"><text x="8" y="16" font-size="12" fill="#242529">Row 2 SVG missing</text></svg>`;

function sliceRow(
  baseContent: string,
  baseWidth: number,
  baseHeight: number,
  slices: Array<{ id: string; x: number; isExample?: boolean; correctOption?: number }>,
) {
  const sliceWidth = baseWidth / 3;
  return slices.map((slice) => ({
    id: slice.id,
    isExample: slice.isExample,
    correctOption: slice.correctOption,
    svgContent: baseContent,
    svgMeta: {
      viewBox: `0 0 ${sliceWidth} ${baseHeight}`,
      width: Math.round(sliceWidth),
      height: baseHeight,
      translateX: slice.x,
      translateY: 0,
    },
  }));
}

const LPS_PAGE7_ROW1_SHAPES = sliceRow(
  PLACEHOLDER_ROW1_CONTENT,
  355,
  118,
  [
    { id: 'p7r1-s1', x: 0, isExample: true },
    { id: 'p7r1-s2', x: 355 / 3, isExample: true },
    { id: 'p7r1-s3', x: (355 / 3) * 2, correctOption: 6 },
  ],
);

const LPS_PAGE7_ROW2_SHAPES = sliceRow(
  PLACEHOLDER_ROW2_CONTENT,
  355,
  123,
  [
    { id: 'p7r2-s1', x: 0 },
    { id: 'p7r2-s2', x: 355 / 3 },
    { id: 'p7r2-s3', x: (355 / 3) * 2 },
  ],
);

export const LPS_PAGE7_COLUMN9_B = [
  { id: 1, shapes: LPS_PAGE7_ROW1_SHAPES, options: DEFAULT_OPTIONS },
  { id: 2, shapes: LPS_PAGE7_ROW2_SHAPES, options: DEFAULT_OPTIONS },
];

export const LPS_PAGE7_COLUMN9_B_SHAPE_COUNT = LPS_PAGE7_COLUMN9_B.reduce(
  (total, row) => total + row.shapes.length,
  0,
);
