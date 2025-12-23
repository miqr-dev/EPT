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

const SVG_META = { width: 180, height: 140, viewBox: '0 0 140 120' } as const;
const OPTION_META = { width: 76, height: 76, viewBox: '0 0 140 120' } as const;

type ShapeTemplate = { id: string; path: string; viewBox: string };

const SHAPE_TEMPLATES: ShapeTemplate[] = [
    {
        id: 'triangle',
        viewBox: '0 0 140 120',
        path: 'M70 12 L122 108 H18 Z',
    },
    {
        id: 'square',
        viewBox: '0 0 140 120',
        path: 'M24 18 H116 V102 H24 Z',
    },
    {
        id: 'diamond',
        viewBox: '0 0 140 120',
        path: 'M70 8 L128 60 L70 112 L12 60 Z',
    },
    {
        id: 'chevron',
        viewBox: '0 0 140 120',
        path: 'M22 74 L70 20 L118 74 L102 74 L70 42 L38 74 Z',
    },
    {
        id: 'arch',
        viewBox: '0 0 140 120',
        path: 'M26 18 H114 V94 H90 V46 H50 V94 H26 Z',
    },
];

const BASE_BACKGROUND = `<rect x="4" y="4" width="132" height="112" rx="18" fill="#f8fafc" stroke="#e2e8f0" stroke-width="4" />`;

function buildShapeSvg(templateId: string, options: { fill: string; stroke?: string; strokeWidth?: number }): string {
    const template = SHAPE_TEMPLATES.find((shape) => shape.id === templateId);
    if (!template) return LPS_PAGE8_PLACEHOLDER_SVG;

    const { fill, stroke = fill, strokeWidth = 8 } = options;

    return `
<svg viewBox="${template.viewBox}" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  ${BASE_BACKGROUND}
  <path d="${template.path}" fill="${fill}" stroke="${stroke}" stroke-width="${strokeWidth}" stroke-linejoin="round" />
</svg>`;
}

export const LPS_PAGE8_PLACEHOLDER_SVG = `
<svg viewBox="0 0 140 120" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  ${BASE_BACKGROUND}
  <path d="M28 86 H112" stroke="#cbd5e1" stroke-width="6" stroke-linecap="round" stroke-dasharray="16 10" />
  <circle cx="46" cy="46" r="16" fill="#cbd5e1" />
  <rect x="70" y="30" width="34" height="34" rx="8" fill="#e2e8f0" />
</svg>`;

export const LPS_PAGE8_OPTION_SVGS: LpsPage8SvgOption[] = SHAPE_TEMPLATES.map((shape, idx) => ({
    id: shape.id,
    svg: buildShapeSvg(shape.id, {
        fill: ['#0ea5e9', '#f97316', '#22c55e', '#6366f1', '#f43f5e'][idx % 5],
        stroke: '#0f172a',
        strokeWidth: 6,
    }),
    svgMeta: OPTION_META,
}));

const PROMPT_SHAPE_IDS: [string, string][] = [
    ['triangle', 'square'],
    ...Array.from({ length: 20 }, (_, idx) => {
        const shapes = SHAPE_TEMPLATES.map((shape) => shape.id);
        const first = shapes[(idx * 2) % shapes.length];
        const second = shapes[(idx * 2 + 1) % shapes.length];
        return [first, second];
    }),
];

export const LPS_PAGE8_SHAPES_B: LpsPage8SvgRow[] = PROMPT_SHAPE_IDS.map((shapePair) => ({
    prompts: shapePair.map((shapeId) => ({
        svg: buildShapeSvg(shapeId, { fill: '#0f172a', stroke: '#0f172a', strokeWidth: 10 }),
        svgMeta: SVG_META,
        correctOptionId: shapeId,
    })),
}));
