export type KonzPage2SvgRow = {
    questionSvg: string;
    answerSvg: string;
};

const ROW_1_QUESTION_SVG = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 980 190" preserveAspectRatio="xMidYMid meet">
    <rect width="980" height="190" fill="white"/>
    <g transform="translate(4 10)">
        <rect x="0" y="0" width="178" height="170" fill="white" stroke="#111827" stroke-width="4"/>
        <ellipse cx="88" cy="85" rx="74" ry="72" fill="white" stroke="#111827" stroke-width="1.5"/>
        <line x1="12" y1="85" x2="164" y2="85" stroke="#111827" stroke-width="2"/>
    </g>
    <g transform="translate(254 10)">
        <rect x="0" y="0" width="178" height="170" fill="white" stroke="#111827" stroke-width="4"/>
        <circle cx="89" cy="72" r="46" fill="white" stroke="#111827" stroke-width="1.5"/>
        <line x1="43" y1="72" x2="135" y2="72" stroke="#111827" stroke-width="2"/>
    </g>
    <g transform="translate(512 10)">
        <rect x="0" y="0" width="178" height="170" fill="white" stroke="#111827" stroke-width="4"/>
        <circle cx="89" cy="70" r="50" fill="white" stroke="#111827" stroke-width="1.5"/>
        <line x1="89" y1="20" x2="89" y2="120" stroke="#111827" stroke-width="2"/>
    </g>
    <g transform="translate(770 10)">
        <rect x="0" y="0" width="178" height="170" fill="white" stroke="#111827" stroke-width="4"/>
        <text x="89" y="88" text-anchor="middle" dominant-baseline="middle" font-size="62" font-family="Arial, sans-serif" fill="#111827">?</text>
    </g>
</svg>`;

const ROW_1_ANSWER_SVG = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1240 190" preserveAspectRatio="xMidYMid meet">
    <rect width="1240" height="190" fill="white"/>
    <g>
        <text x="44" y="50" text-anchor="end" font-size="46" font-family="Arial, sans-serif" fill="#111827">[1]</text>
        <g transform="translate(54 16)">
            <rect x="0" y="0" width="182" height="158" fill="white" stroke="#111827" stroke-width="4"/>
            <path d="M18 79 A73 73 0 0 1 164 79 L164 79 L18 79 Z" fill="#8b8b8b"/>
            <circle cx="91" cy="79" r="73" fill="none" stroke="#111827" stroke-width="1.5"/>
            <line x1="18" y1="79" x2="164" y2="79" stroke="#111827" stroke-width="2"/>
        </g>
    </g>
    <g>
        <text x="390" y="50" text-anchor="end" font-size="46" font-family="Arial, sans-serif" fill="#111827">[2]</text>
        <g transform="translate(404 16)">
            <rect x="0" y="0" width="182" height="158" fill="white" stroke="#111827" stroke-width="4"/>
            <circle cx="91" cy="79" r="46" fill="white" stroke="#111827" stroke-width="1.5"/>
            <line x1="91" y1="33" x2="91" y2="125" stroke="#111827" stroke-width="2"/>
        </g>
    </g>
    <g>
        <text x="742" y="50" text-anchor="end" font-size="46" font-family="Arial, sans-serif" fill="#111827">[3]</text>
        <g transform="translate(756 16)">
            <rect x="0" y="0" width="182" height="158" fill="white" stroke="#111827" stroke-width="4"/>
            <circle cx="91" cy="79" r="73" fill="white" stroke="#111827" stroke-width="1.5"/>
            <line x1="91" y1="6" x2="91" y2="152" stroke="#111827" stroke-width="2"/>
        </g>
    </g>
    <g>
        <text x="1094" y="50" text-anchor="end" font-size="46" font-family="Arial, sans-serif" fill="#111827">[4]</text>
        <g transform="translate(1108 16)">
            <rect x="0" y="0" width="182" height="158" fill="white" stroke="#111827" stroke-width="4"/>
            <circle cx="91" cy="79" r="72" fill="white" stroke="#111827" stroke-width="1.5"/>
            <line x1="19" y1="79" x2="163" y2="79" stroke="#111827" stroke-width="2"/>
        </g>
    </g>
</svg>`;

function buildRow(questionSvg: string, answerSvg: string): KonzPage2SvgRow {
    return { questionSvg, answerSvg };
}

export const KONZ_PAGE2_SVG_ROWS: KonzPage2SvgRow[] = Array.from({ length: 10 }, () => buildRow(ROW_1_QUESTION_SVG, ROW_1_ANSWER_SVG));
