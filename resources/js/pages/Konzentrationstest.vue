<!-- resources/js/Pages/Konzentrationstest.vue -->
<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { useTeacherForceFinish } from '@/composables/useTeacherForceFinish';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const startedAtMs = Date.now();

/* =========================================================
   NAV
========================================================= */
const page = ref(1);
const MAX_PAGE = 5;

const nextPage = () => {
    if (page.value < MAX_PAGE) page.value++;
};
const prevPage = () => {
    if (page.value > 1) page.value--;
};

const showUnansweredDialog = ref(false);
const unansweredNotes = ref<string[]>([]);
const hasCompleted = ref(false);

const { isForcedFinish, clearForcedFinish } = useTeacherForceFinish({
    isActive: () => !hasCompleted.value,
    onStart: () => {
        window.dispatchEvent(new Event('start-finish'));
    },
    onCountdownFinished: () => {
        forceFinish();
    },
    onCancel: () => {
        if (showUnansweredDialog.value) {
            showUnansweredDialog.value = false;
        }
        window.dispatchEvent(new Event('cancel-finish'));
    },
});

const buildResults = () => ({
    page1: page1Inputs.value,
    page2: page2Answers.value,
    page3: copyCounts.value,
    page4: page4Answers.value,
    page5: page5TickSums.value,
    wrong_count: wrongCount.value,
    performance_category: performanceCategory.value,
    total_time_seconds: Math.max(0, Math.round((Date.now() - startedAtMs) / 1000)),
});

const countEmpty = (answers: string[]) => answers.filter((a) => a.trim() === '').length;

const getUnansweredNotes = () => {
    const notes: string[] = [];

    const page1Missing = countEmpty(page1Inputs.value);
    if (page1Missing) notes.push(`${page1Missing} offene Antwort${page1Missing > 1 ? 'en' : ''} in Aufgabe 1 (Zahlenreihen)`);

    const page2Missing = countEmpty(page2Answers.value);
    if (page2Missing) notes.push(`${page2Missing} offene Antwort${page2Missing > 1 ? 'en' : ''} in Aufgabe 2 (Zuordnung)`);

    const page3Missing = countEmpty(copyCounts.value);
    if (page3Missing) notes.push(`${page3Missing} offene Antwort${page3Missing > 1 ? 'en' : ''} in Aufgabe 3 (Abschrift)`);

    const page4Missing = countEmpty(page4Answers.value);
    if (page4Missing) notes.push(`${page4Missing} offene Antwort${page4Missing > 1 ? 'en' : ''} in Aufgabe 4 (Ziffern zählen)`);

    const page5Missing = countEmpty(page5TickSums.value);
    if (page5Missing) notes.push(`${page5Missing} offene Antwort${page5Missing > 1 ? 'en' : ''} in Aufgabe 5 (Zeichen zählen)`);

    return notes;
};

const submitResults = () => {
    clearForcedFinish(false);
    hasCompleted.value = true;
    emit('complete', buildResults());
};

const forceFinish = () => {
    showUnansweredDialog.value = false;
    submitResults();
};

const finishTest = () => {
    const notes = getUnansweredNotes();
    if (notes.length) {
        unansweredNotes.value = notes;
        showUnansweredDialog.value = true;
        return;
    }

    submitResults();
};

const confirmFinishDespiteUnanswered = () => {
    showUnansweredDialog.value = false;
    submitResults();
};
const emit = defineEmits(['complete', 'started']);

onMounted(() => {
    emit('started');
});

const toggleMark = (marks: boolean[], i: number) => (marks[i] = !marks[i]);

/* =========================================================
   PAGE 1
========================================================= */
const page1Series: string[][] = [
    ['20', '10', '16', '8', '14', '7'],
    ['2', '7', '8', '13', '14', '19', '20', '25'],
    ['7', '9', '13', '21', '37', '69'],
    ['12', '11', '21', '20', '30', '29'],
    ['8', '24', '17', '51', '44', '132'],
    ['17', '20', '24', '29', '35', '42'],
    ['30', '10', '60', '20', '120', '40', '240', '80'],
    ['5', '15', '10', '30', '25', '75', '70'],
    ['30', '15', '20', '10', '15', '7,5', '12,5', '6,25'],
    ['25', '28', '56', '59', '118', '121'],
];
const page1Inputs = ref<string[]>(Array(page1Series.length).fill(''));
const page1Correct = ['13', '26', '133', '39', '125', '50', '480', '210', '11,25', '242'];

/* =========================================================
   PAGE 2 (row-based SVGs to keep both sides aligned while zooming)
========================================================= */
const page2Answers = ref<string[]>(Array(10).fill(''));

const page2BaseRows = [
    {
        question: { variant: 'circle-horizontal', accent: '#d1d5db' },
        answers: ['half-fill', 'circle-vertical-small', 'circle-vertical-large', 'circle-horizontal-large'],
    },
    {
        question: { variant: 'line-dot-bottom', accent: '#e5e7eb' },
        answers: ['line-dot-bottom-left', 'line-dot-bottom-right', 'line-dot-top-right', 'line-dot-top-double'],
    },
    { question: { variant: 'flower-basic', accent: '#f3f4f6' }, answers: ['flower-leaves', 'flower-petal-stem', 'flower-sun', 'flower-small'] },
    { question: { variant: 'bars-top', accent: '#f5f5f5' }, answers: ['bars-top', 'bars-middle', 'bars-bottom', 'bars-top'] },
    {
        question: { variant: 'triangle-diagonal', accent: '#e5e7eb' },
        answers: ['triangle-step-1', 'triangle-step-2', 'triangle-step-3', 'triangle-step-4'],
    },
    { question: { variant: 'diamond-stripe', accent: '#f3f4f6' }, answers: ['diamond-stripe', 'diamond-split', 'diamond-dot', 'diamond-wave'] },
    { question: { variant: 'arcs-quarter', accent: '#e5e7eb' }, answers: ['arcs-quarter', 'arcs-half', 'arcs-cross', 'arcs-ring'] },
    { question: { variant: 'stairs-rising', accent: '#f5f5f5' }, answers: ['stairs-rising', 'stairs-falling', 'stairs-block', 'stairs-corner'] },
    { question: { variant: 'cross-dots', accent: '#f3f4f6' }, answers: ['cross-dots', 'cross-bars', 'cross-diamond', 'cross-circle'] },
    { question: { variant: 'waves-stack', accent: '#e5e7eb' }, answers: ['waves-stack', 'waves-single', 'waves-grid', 'waves-dot'] },
] as const;

type Page2QuestionVariant = (typeof page2BaseRows)[number]['question']['variant'];
type Page2AnswerVariant = (typeof page2BaseRows)[number]['answers'][number];

const page2Rows = page2BaseRows.map((row, rowIndex) => ({
    id: rowIndex,
    questionSvg: buildQuestionSvg(row.question.variant, row.question.accent),
    answerSvg: buildAnswerSvgRow(row.answers, row.question.accent),
}));

function buildQuestionSvg(variant: Page2QuestionVariant, accent: string) {
    const cells = [0, 1, 2, 3].map((index) => buildQuestionCell(variant, index, accent)).join('');
    return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 440 84" preserveAspectRatio="xMidYMid meet">
    <rect width="440" height="84" fill="white"/>
    ${cells}
  </svg>`;
}

function buildAnswerSvgRow(variants: readonly Page2AnswerVariant[], accent: string) {
    const cells = variants.map((variant, index) => buildAnswerCell(variant, index, accent)).join('');
    return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 460 84" preserveAspectRatio="xMidYMid meet">
    <rect width="460" height="84" fill="white"/>
    ${cells}
  </svg>`;
}

function buildQuestionCell(variant: Page2QuestionVariant, index: number, accent: string) {
    const x = 12 + index * 104;
    const label = index === 3 ? '?' : '';
    return `<g transform="translate(${x} 8)">
    <rect x="0" y="0" width="80" height="68" fill="white" stroke="#111827" stroke-width="2.5"/>
    ${label ? `<text x="40" y="42" text-anchor="middle" font-size="26" font-family="Arial, sans-serif" fill="#1f2937">${label}</text>` : drawQuestionShape(variant, index, accent)}
  </g>`;
}

function buildAnswerCell(variant: Page2AnswerVariant, index: number, accent: string) {
    const x = 28 + index * 108;
    const labelX = x - 16;
    return `<g>
    <text x="${labelX}" y="26" text-anchor="end" font-size="18" font-family="Arial, sans-serif" fill="#1f2937">[${index + 1}]</text>
    <g transform="translate(${x} 8)">
      <rect x="0" y="0" width="80" height="68" fill="white" stroke="#111827" stroke-width="2.5"/>
      ${drawAnswerShape(variant, accent)}
    </g>
  </g>`;
}

function drawQuestionShape(variant: Page2QuestionVariant, index: number, accent: string) {
    switch (variant) {
        case 'circle-horizontal':
            return [
                `<circle cx="40" cy="34" r="26" fill="none" stroke="#111827" stroke-width="1.5"/>`,
                `<line x1="14" y1="34" x2="66" y2="34" stroke="#111827" stroke-width="1.5"/>`,
                index === 0 ? '' : `<circle cx="40" cy="34" r="18" fill="${accent}" opacity="0.45"/>`,
                index === 2 ? `<line x1="40" y1="8" x2="40" y2="60" stroke="#111827" stroke-width="1.5"/>` : '',
            ].join('');
        case 'line-dot-bottom': {
            const cy = index === 0 ? 50 : 22;
            return [
                `<line x1="40" y1="10" x2="40" y2="58" stroke="#9ca3af" stroke-width="2"/>`,
                `<circle cx="40" cy="${cy}" r="18" fill="white" stroke="#111827" stroke-width="1.5"/>`,
                `<line x1="40" y1="${cy - 18}" x2="40" y2="${cy + 18}" stroke="#111827" stroke-width="1.5"/>`,
                `<circle cx="${index === 1 ? 47 : 33}" cy="${cy}" r="2.5" fill="#111827"/>`,
            ].join('');
        }
        case 'flower-basic':
            return [
                drawPetals(40, 28, 3 + index, accent),
                `<circle cx="40" cy="28" r="8" fill="#111827"/>`,
                `<line x1="40" y1="36" x2="40" y2="58" stroke="#9ca3af" stroke-width="2"/>`,
                index > 0
                    ? `<path d="M40 52 C28 44, 20 44, 18 58 C28 58, 35 56, 40 52Z" fill="#111827"/><path d="M40 52 C52 44, 60 44, 62 58 C52 58, 45 56, 40 52Z" fill="#111827"/>`
                    : '',
            ].join('');
        case 'bars-top': {
            const y = [16, 32, 28][index] ?? 16;
            return `<g transform="translate(14 ${y})">
        <rect x="0" y="0" width="52" height="8" fill="#111827"/>
        <rect x="0" y="10" width="52" height="8" fill="${accent}" stroke="#111827" stroke-width="0.8"/>
        <rect x="0" y="20" width="52" height="8" fill="white" stroke="#111827" stroke-width="0.8"/>
      </g>`;
        }
        case 'triangle-diagonal':
            return [
                `<polygon points="16,56 40,12 64,56" fill="${accent}" stroke="#111827" stroke-width="1.5"/>`,
                index > 0 ? `<line x1="24" y1="46" x2="56" y2="22" stroke="#111827" stroke-width="1.5"/>` : '',
                index > 1 ? `<circle cx="40" cy="40" r="5" fill="#111827"/>` : '',
            ].join('');
        case 'diamond-stripe':
            return [
                `<polygon points="40,10 64,34 40,58 16,34" fill="white" stroke="#111827" stroke-width="1.5"/>`,
                `<path d="M24 34 H56" stroke="#111827" stroke-width="1.5"/>`,
                index > 0 ? `<path d="M40 18 V50" stroke="#111827" stroke-width="1.5"/>` : '',
                index > 1 ? `<path d="M28 22 L52 46" stroke="${accent}" stroke-width="4" opacity="0.8"/>` : '',
            ].join('');
        case 'arcs-quarter':
            return [
                `<path d="M18 50 A22 22 0 0 1 62 50" fill="none" stroke="#111827" stroke-width="1.5"/>`,
                `<path d="M24 50 A16 16 0 0 1 56 50" fill="none" stroke="${accent}" stroke-width="4" opacity="0.8"/>`,
                index > 0 ? `<line x1="40" y1="20" x2="40" y2="50" stroke="#111827" stroke-width="1.5"/>` : '',
                index > 1 ? `<circle cx="40" cy="50" r="4" fill="#111827"/>` : '',
            ].join('');
        case 'stairs-rising':
            return [
                `<path d="M18 54 H32 V42 H46 V30 H60 V18" fill="none" stroke="#111827" stroke-width="4" stroke-linecap="square" stroke-linejoin="miter"/>`,
                index > 0 ? `<rect x="18" y="54" width="12" height="4" fill="${accent}"/>` : '',
                index > 1 ? `<circle cx="60" cy="18" r="5" fill="#111827"/>` : '',
            ].join('');
        case 'cross-dots':
            return [
                `<line x1="40" y1="12" x2="40" y2="56" stroke="#111827" stroke-width="2"/>`,
                `<line x1="18" y1="34" x2="62" y2="34" stroke="#111827" stroke-width="2"/>`,
                `<circle cx="40" cy="34" r="6" fill="${accent}" stroke="#111827" stroke-width="1.5"/>`,
                index > 0 ? `<circle cx="28" cy="22" r="3" fill="#111827"/><circle cx="52" cy="46" r="3" fill="#111827"/>` : '',
            ].join('');
        case 'waves-stack':
            return [
                `<path d="M16 22 C24 14, 32 30, 40 22 S56 14, 64 22" fill="none" stroke="#111827" stroke-width="2"/>`,
                `<path d="M16 34 C24 26, 32 42, 40 34 S56 26, 64 34" fill="none" stroke="${accent}" stroke-width="4" opacity="0.8"/>`,
                index > 0 ? `<path d="M16 46 C24 38, 32 54, 40 46 S56 38, 64 46" fill="none" stroke="#111827" stroke-width="2"/>` : '',
                index > 1 ? `<circle cx="40" cy="56" r="4" fill="#111827"/>` : '',
            ].join('');
    }
}

function drawAnswerShape(variant: Page2AnswerVariant, accent: string) {
    switch (variant) {
        case 'half-fill':
            return `<path d="M14 34 A26 26 0 0 1 66 34 L66 34 L14 34 Z" fill="#8b8b8b"/><circle cx="40" cy="34" r="26" fill="none" stroke="#111827" stroke-width="1.5"/><line x1="14" y1="34" x2="66" y2="34" stroke="#111827" stroke-width="1.5"/>`;
        case 'circle-vertical-small':
            return `<circle cx="40" cy="34" r="18" fill="white" stroke="#111827" stroke-width="1.5"/><line x1="40" y1="16" x2="40" y2="52" stroke="#111827" stroke-width="1.5"/>`;
        case 'circle-vertical-large':
            return `<circle cx="40" cy="34" r="26" fill="white" stroke="#111827" stroke-width="1.5"/><line x1="40" y1="8" x2="40" y2="60" stroke="#111827" stroke-width="1.5"/>`;
        case 'circle-horizontal-large':
            return `<circle cx="40" cy="34" r="24" fill="white" stroke="#111827" stroke-width="1.5"/><line x1="16" y1="34" x2="64" y2="34" stroke="#111827" stroke-width="1.5"/>`;
        case 'line-dot-bottom-left':
            return `<line x1="40" y1="8" x2="40" y2="60" stroke="#9ca3af" stroke-width="2"/><circle cx="40" cy="50" r="18" fill="white" stroke="#111827" stroke-width="1.5"/><line x1="40" y1="32" x2="40" y2="68" stroke="#111827" stroke-width="1.5"/><circle cx="33" cy="50" r="2.5" fill="#111827"/>`;
        case 'line-dot-bottom-right':
            return `<line x1="40" y1="8" x2="40" y2="60" stroke="#9ca3af" stroke-width="2"/><circle cx="40" cy="50" r="18" fill="white" stroke="#111827" stroke-width="1.5"/><line x1="40" y1="32" x2="40" y2="68" stroke="#111827" stroke-width="1.5"/><circle cx="47" cy="50" r="2.5" fill="#111827"/>`;
        case 'line-dot-top-right':
            return `<line x1="40" y1="8" x2="40" y2="60" stroke="#9ca3af" stroke-width="2"/><circle cx="40" cy="22" r="18" fill="white" stroke="#111827" stroke-width="1.5"/><line x1="40" y1="4" x2="40" y2="40" stroke="#111827" stroke-width="1.5"/><circle cx="47" cy="22" r="2.5" fill="#111827"/>`;
        case 'line-dot-top-double':
            return `<line x1="40" y1="8" x2="40" y2="60" stroke="#9ca3af" stroke-width="2"/><circle cx="40" cy="22" r="18" fill="white" stroke="#111827" stroke-width="1.5"/><line x1="40" y1="4" x2="40" y2="40" stroke="#111827" stroke-width="1.5"/><circle cx="33" cy="22" r="2.5" fill="#111827"/><circle cx="47" cy="22" r="2.5" fill="#111827"/>`;
        case 'flower-leaves':
            return `${drawPetals(40, 28, 5, accent)}<circle cx="40" cy="28" r="8" fill="#111827"/><line x1="40" y1="36" x2="40" y2="58" stroke="#9ca3af" stroke-width="2"/><path d="M40 52 C28 44, 20 44, 18 58 C28 58, 35 56, 40 52Z" fill="#111827"/><path d="M40 52 C52 44, 60 44, 62 58 C52 58, 45 56, 40 52Z" fill="#111827"/>`;
        case 'flower-petal-stem':
            return `${drawPetals(40, 28, 4, accent)}<circle cx="40" cy="28" r="8" fill="#111827"/><line x1="40" y1="36" x2="40" y2="58" stroke="#111827" stroke-width="2"/><path d="M40 52 C28 44, 20 44, 18 58 C28 58, 35 56, 40 52Z" fill="#111827"/><path d="M40 52 C52 44, 60 44, 62 58 C52 58, 45 56, 40 52Z" fill="#111827"/>`;
        case 'flower-sun':
            return `${drawPetals(40, 28, 4, accent)}<circle cx="40" cy="28" r="8" fill="#111827"/><line x1="40" y1="36" x2="40" y2="58" stroke="#111827" stroke-width="2"/><path d="M40 52 C28 44, 20 44, 18 58 C28 58, 35 56, 40 52Z" fill="#111827"/><path d="M40 52 C52 44, 60 44, 62 58 C52 58, 45 56, 40 52Z" fill="#111827"/><g stroke="#9ca3af" stroke-width="1.5"><line x1="40" y1="2" x2="40" y2="12"/><line x1="24" y1="6" x2="30" y2="14"/><line x1="56" y1="6" x2="50" y2="14"/><line x1="16" y1="16" x2="26" y2="20"/><line x1="64" y1="16" x2="54" y2="20"/></g>`;
        case 'flower-small':
            return `${drawPetals(34, 28, 4, accent)}<circle cx="34" cy="28" r="7" fill="#111827"/><path d="M18 52 C24 50, 28 48, 34 52" stroke="#111827" stroke-width="2" fill="none"/><path d="M50 16 q10 8 4 18" stroke="#111827" stroke-width="2" fill="none"/>`;
        case 'bars-middle':
            return `<g transform="translate(14 22)"><rect x="0" y="0" width="52" height="8" fill="${accent}" stroke="#111827" stroke-width="0.8"/><rect x="0" y="10" width="52" height="8" fill="#111827"/><rect x="0" y="20" width="52" height="8" fill="white" stroke="#111827" stroke-width="0.8"/></g><line x1="66" y1="8" x2="66" y2="60" stroke="#111827" stroke-width="1"/>`;
        case 'bars-bottom':
            return `<g transform="translate(14 30)"><rect x="0" y="0" width="52" height="8" fill="${accent}" stroke="#111827" stroke-width="0.8"/><rect x="0" y="10" width="52" height="8" fill="#111827"/><rect x="0" y="20" width="52" height="8" fill="white" stroke="#111827" stroke-width="0.8"/></g><line x1="14" y1="8" x2="14" y2="60" stroke="#111827" stroke-width="1"/>`;
        case 'triangle-step-1':
            return `<polygon points="16,56 40,12 64,56" fill="${accent}" stroke="#111827" stroke-width="1.5"/>`;
        case 'triangle-step-2':
            return `<polygon points="16,56 40,12 64,56" fill="${accent}" stroke="#111827" stroke-width="1.5"/><line x1="24" y1="46" x2="56" y2="22" stroke="#111827" stroke-width="1.5"/>`;
        case 'triangle-step-3':
            return `<polygon points="16,56 40,12 64,56" fill="${accent}" stroke="#111827" stroke-width="1.5"/><line x1="24" y1="46" x2="56" y2="22" stroke="#111827" stroke-width="1.5"/><circle cx="40" cy="40" r="5" fill="#111827"/>`;
        case 'triangle-step-4':
            return `<polygon points="16,56 40,12 64,56" fill="${accent}" stroke="#111827" stroke-width="1.5"/><line x1="24" y1="46" x2="56" y2="22" stroke="#111827" stroke-width="1.5"/><circle cx="40" cy="40" r="5" fill="#111827"/><path d="M22 58 H58" stroke="#111827" stroke-width="2"/>`;
        case 'diamond-stripe':
            return `<polygon points="40,10 64,34 40,58 16,34" fill="white" stroke="#111827" stroke-width="1.5"/><path d="M24 34 H56" stroke="#111827" stroke-width="1.5"/>`;
        case 'diamond-split':
            return `<polygon points="40,10 64,34 40,58 16,34" fill="white" stroke="#111827" stroke-width="1.5"/><path d="M24 34 H56" stroke="#111827" stroke-width="1.5"/><path d="M40 18 V50" stroke="#111827" stroke-width="1.5"/>`;
        case 'diamond-dot':
            return `<polygon points="40,10 64,34 40,58 16,34" fill="white" stroke="#111827" stroke-width="1.5"/><path d="M24 34 H56" stroke="#111827" stroke-width="1.5"/><circle cx="40" cy="34" r="5" fill="#111827"/>`;
        case 'diamond-wave':
            return `<polygon points="40,10 64,34 40,58 16,34" fill="${accent}" opacity="0.4" stroke="#111827" stroke-width="1.5"/><path d="M22 34 C28 28, 34 40, 40 34 S52 28, 58 34" fill="none" stroke="#111827" stroke-width="1.5"/>`;
        case 'arcs-quarter':
            return `<path d="M18 50 A22 22 0 0 1 62 50" fill="none" stroke="#111827" stroke-width="1.5"/><path d="M24 50 A16 16 0 0 1 56 50" fill="none" stroke="${accent}" stroke-width="4" opacity="0.8"/>`;
        case 'arcs-half':
            return `<path d="M18 50 A22 22 0 0 1 62 50" fill="none" stroke="#111827" stroke-width="1.5"/><path d="M24 50 A16 16 0 0 1 56 50" fill="none" stroke="${accent}" stroke-width="4" opacity="0.8"/><line x1="40" y1="20" x2="40" y2="50" stroke="#111827" stroke-width="1.5"/>`;
        case 'arcs-cross':
            return `<path d="M18 50 A22 22 0 0 1 62 50" fill="none" stroke="#111827" stroke-width="1.5"/><line x1="40" y1="20" x2="40" y2="50" stroke="#111827" stroke-width="1.5"/><line x1="24" y1="36" x2="56" y2="36" stroke="#111827" stroke-width="1.5"/>`;
        case 'arcs-ring':
            return `<circle cx="40" cy="38" r="18" fill="none" stroke="#111827" stroke-width="1.5"/><circle cx="40" cy="38" r="8" fill="${accent}" opacity="0.5"/>`;
        case 'stairs-rising':
            return `<path d="M18 54 H32 V42 H46 V30 H60 V18" fill="none" stroke="#111827" stroke-width="4" stroke-linecap="square"/>`;
        case 'stairs-falling':
            return `<path d="M18 18 H32 V30 H46 V42 H60 V54" fill="none" stroke="#111827" stroke-width="4" stroke-linecap="square"/>`;
        case 'stairs-block':
            return `<path d="M18 54 H32 V42 H46 V30 H60 V18" fill="none" stroke="#111827" stroke-width="4" stroke-linecap="square"/><rect x="48" y="12" width="12" height="12" fill="${accent}" stroke="#111827" stroke-width="1"/>`;
        case 'stairs-corner':
            return `<path d="M18 54 H44 V28 H60" fill="none" stroke="#111827" stroke-width="4" stroke-linecap="square"/>`;
        case 'cross-dots':
            return `<line x1="40" y1="12" x2="40" y2="56" stroke="#111827" stroke-width="2"/><line x1="18" y1="34" x2="62" y2="34" stroke="#111827" stroke-width="2"/><circle cx="28" cy="22" r="3" fill="#111827"/><circle cx="52" cy="22" r="3" fill="#111827"/><circle cx="28" cy="46" r="3" fill="#111827"/><circle cx="52" cy="46" r="3" fill="#111827"/>`;
        case 'cross-bars':
            return `<line x1="40" y1="12" x2="40" y2="56" stroke="#111827" stroke-width="2"/><line x1="18" y1="34" x2="62" y2="34" stroke="#111827" stroke-width="2"/><rect x="24" y="18" width="32" height="32" fill="none" stroke="${accent}" stroke-width="4" opacity="0.8"/>`;
        case 'cross-diamond':
            return `<line x1="40" y1="12" x2="40" y2="56" stroke="#111827" stroke-width="2"/><line x1="18" y1="34" x2="62" y2="34" stroke="#111827" stroke-width="2"/><polygon points="40,18 56,34 40,50 24,34" fill="${accent}" opacity="0.5" stroke="#111827" stroke-width="1.5"/>`;
        case 'cross-circle':
            return `<line x1="40" y1="12" x2="40" y2="56" stroke="#111827" stroke-width="2"/><line x1="18" y1="34" x2="62" y2="34" stroke="#111827" stroke-width="2"/><circle cx="40" cy="34" r="16" fill="none" stroke="#111827" stroke-width="1.5"/>`;
        case 'waves-stack':
            return `<path d="M16 22 C24 14, 32 30, 40 22 S56 14, 64 22" fill="none" stroke="#111827" stroke-width="2"/><path d="M16 34 C24 26, 32 42, 40 34 S56 26, 64 34" fill="none" stroke="${accent}" stroke-width="4" opacity="0.8"/><path d="M16 46 C24 38, 32 54, 40 46 S56 38, 64 46" fill="none" stroke="#111827" stroke-width="2"/>`;
        case 'waves-single':
            return `<path d="M16 34 C24 26, 32 42, 40 34 S56 26, 64 34" fill="none" stroke="#111827" stroke-width="2"/>`;
        case 'waves-grid':
            return `<path d="M16 22 C24 14, 32 30, 40 22 S56 14, 64 22" fill="none" stroke="#111827" stroke-width="2"/><path d="M16 34 C24 26, 32 42, 40 34 S56 26, 64 34" fill="none" stroke="#111827" stroke-width="2"/><path d="M16 46 C24 38, 32 54, 40 46 S56 38, 64 46" fill="none" stroke="#111827" stroke-width="2"/><line x1="16" y1="14" x2="16" y2="54" stroke="${accent}" stroke-width="2"/><line x1="64" y1="14" x2="64" y2="54" stroke="${accent}" stroke-width="2"/>`;
        case 'waves-dot':
            return `<path d="M16 22 C24 14, 32 30, 40 22 S56 14, 64 22" fill="none" stroke="#111827" stroke-width="2"/><path d="M16 34 C24 26, 32 42, 40 34 S56 26, 64 34" fill="none" stroke="${accent}" stroke-width="4" opacity="0.8"/><circle cx="40" cy="52" r="4" fill="#111827"/>`;
    }
}

function drawPetals(cx: number, cy: number, petals: number, accent: string) {
    const ellipses = Array.from({ length: petals }, (_, index) => {
        const angle = (Math.PI * 2 * index) / petals;
        const px = cx + Math.cos(angle) * 14;
        const py = cy + Math.sin(angle) * 10;
        const rotation = (angle * 180) / Math.PI;
        return `<ellipse cx="${px.toFixed(2)}" cy="${py.toFixed(2)}" rx="8" ry="6" transform="rotate(${rotation.toFixed(2)} ${px.toFixed(2)} ${py.toFixed(2)})" fill="white" stroke="#111827" stroke-width="1.2"/>`;
    }).join('');
    return `<g fill="${accent}" opacity="0.9">${ellipses}</g>`;
}

// optional scoring keys
const page2Correct = ['3', '1', '3', '2', '2', '1', '2', '4', '1', '3'];
const page3Correct = ['1', '4', '3', '1', '3', '3', '0', '2', '2', '5', '2', '3', '2', '2', '4', '3', '3', '1', '3', '3', '0', '2'];
const page5Correct = ['7', '4', '8', '5', '7', '9', '7', '10', '10', '9'];

/* =========================================================
   PAGE 3 (Original LEFT, Abschrift RIGHT)  [was old page 4]
========================================================= */
type RowParts = [string, string, string, string];

const exampleOriginalRow: { parts: RowParts } = {
    parts: ['Sylvia Müller', '45678 Pasching', 'Leipziger Str. 4', 'Tel. 0212 7874624'],
};
const exampleCopyRow: { parts: RowParts } = {
    parts: ['Silvya Müller', '45678 Pa ching', 'Laibziger Str. 4', 'Tel. 0122 787462-'],
};
const exampleMarked: number[][] = [[1, 4], [8], [1], [6, 7, 16]];
const getExampleOriginalChars = (partIndex: number) => Array.from(exampleOriginalRow.parts[partIndex]);
const getExampleCopyChars = (partIndex: number) => Array.from(exampleCopyRow.parts[partIndex]);

const originalRows: Array<{ letter: string; parts: RowParts }> = [
    { letter: 'a', parts: ['Friedrich Berlenbeck', '45527 Hattingen', 'An der Hunsebeck 27', 'Tel. 0421 455773'] },
    { letter: 'b', parts: ['Friedel Feschner', '80768 Maienheim', 'Heissenstr. 4', 'Tel. 0943 557834'] },
    { letter: 'c', parts: ['Kordula Karlsferch', '47605 Kittenberg', 'Rellinghauser Str. 248', 'Tel. 0213 329823'] },
    { letter: 'd', parts: ['Dr. Mara Müller-Meerfeld', '20657 Bammenbach', 'Meracher Weg 3', 'Tel. 02592 2578-43'] },
    { letter: 'e', parts: ['Sybilla Vautenloh', '10678 Ickten', 'Banneken 4', 'Tel. 0553 365667'] },
    { letter: 'f', parts: ['Grazyna Rzymski', '76342 Waldfeldt', 'Mendelsche Gasse 3', 'Tel. 02435 450391'] },
    { letter: 'g', parts: ['Peter Waidtmanns', '98423 Schüttdorf', 'Karla-Wegemann-Str. 5', 'Tel. 07753 8942'] },
    { letter: 'h', parts: ['Maschinen Bortenbycks', '84932 Sahlfeeden', 'Bergerbeek 5-12', 'Tel. 0432 567409-0'] },
    { letter: 'i', parts: ['Dr. Sybille Schyrzyk', '87645 Mühlhausen', 'Bergerfeldstr. 5', 'Tel. 0876 786543'] },
    { letter: 'j', parts: ['Christoph Pehrmann', '56478 Esslingen', 'Mausegattstr. 5', 'Tel. 045 7874547'] },
    { letter: 'k', parts: ['Alicia Jablonski', '45632 Velbert', 'Johänntegesbrucher 8b', 'Tel. 0212 3840421'] },
    { letter: 'l', parts: ['Patrizia Iserloh-Konegen', '37273 Lethmate', 'Hacketäuerstr. 4', 'Tel. 0245 2248799'] },
    { letter: 'm', parts: ['Turkovic Elektro Fachhandel', '76321 Schöppingen', 'Obenrüdener Kotten 4', 'Tel. 03234 43973-3'] },
    { letter: 'n', parts: ['Slavica Kumarasamy', '54689 Meirhofen', 'Ernst-Woltmann-Str. 8', 'Tel. 0879 345345'] },
    { letter: 'o', parts: ['Hellmuth Spicker GmbH', '74839 Seeberg', 'Westerweider Str. 99', 'Tel. 02342 7734-3'] },
    { letter: 'p', parts: ['Kriskofski Umzüge', '93432 Strehlow', 'Richard-Wagner-Str. 3', 'Tel. 0217 8989-20'] },
    { letter: 'q', parts: ['Ruddeck Betonwaren', '80796 München', 'Entenpfhul 5', 'Tel. 0234 887745'] },
    { letter: 'r', parts: ['Kartonagenfabrik Großpietsch', '45363 Essen', 'Merowingerstr. 7', 'Tel. 0456 45638'] },
    { letter: 's', parts: ['Hermann Große-Naust', '13245 Rostock', 'Melanchthonstr. 4', 'Tel. 06534 73625'] },
    { letter: 't', parts: ['Autohaus Ayibogan', '33245 Duisburg', 'Am Kistner 3', 'Tel. 04567 43556'] },
    { letter: 'u', parts: ['Bauklempnerei Frickartz', '45637 Wesel', 'Am Röhr 40', 'Tel. 0443 876523'] },
    { letter: 'v', parts: ['Foto Forobosko', '23456 Lüneburg', 'Agnesstr. 14', 'Tel. 0442 275839'] },
];

const copyRows: Array<{ letter: string; parts: RowParts }> = [
    { letter: 'a', parts: ['Friedrich Berlenbeck', '45527 Hattingen', 'An der Hunsebeek 27', 'Tel. 0421 455773'] },
    { letter: 'b', parts: ['Friedel Fechner', '80768 Meienheim', 'Heisssenstr. 4', 'Tel. 0943 567834'] },
    { letter: 'c', parts: ['Kordula Kalsferch', '47605 Kittenberg', 'Relllinghauser Str. 248', 'Tel. 0213 32982'] },
    { letter: 'd', parts: ['Dr. Mara Müller-Mehrfeld', '20657 Bammenbach', 'Meracher Weg 3', 'Tel. 02592 2578-43'] },
    { letter: 'e', parts: ['Sibylla Vautenloh', '10678 Ickten', 'Bannecken 4', 'Tel. 0553 365667'] },
    { letter: 'f', parts: ['Gracyna Rzymski', '76342 Waldfeld', 'Mendelsche Gasse 3', 'Tel. 024355 450391'] },
    { letter: 'g', parts: ['Peter Waidtmanns', '98423 Schüttdorf', 'Karla-Wegemann-Str. 5', 'Tel. 07753 8942'] },
    { letter: 'h', parts: ['Maschienen Bortenbycks', '84932 Sahlfeeden', 'Bergerbek 5-12', 'Tel. 0432 567409-0'] },
    { letter: 'i', parts: ['Dr. Sybille Schyrzik', '87645 Mülhausen', 'Bergerfeldstr. 5', 'Tel. 0876 786543'] },
    { letter: 'j', parts: ['Christoph Peermann', '56478 Esßlingen', 'Mausegatstr. 5', 'Tel. 045 7875447'] },
    { letter: 'k', parts: ['Alizia Jablonski', '45633 Velbert', 'Johänntegesbrucher 8b', 'Tel. 0212 3840421'] },
    { letter: 'l', parts: ['Patrizia Iserloh-Kohnegen', '37273 Letmate', 'Hacketeuerstr. 4', 'Tel. 0245 2248799'] },
    { letter: 'm', parts: ['Turkovic Electro Fachhandel', '76321 Schöppingen', 'Oberrüdener Kotten 4', 'Tel. 03234 43973-3'] },
    { letter: 'n', parts: ['Slaviza Kumarasamy', '54689 Mairhofen', 'Ernst-Woltmann-Str. 8', 'Tel. 0879 345345'] },
    { letter: 'o', parts: ['Helmuth Spicker GmbH', '74839 Seeberg', 'Westerwaider Str. 99', 'Tel. 02341 7724-3'] },
    { letter: 'p', parts: ['Kriskofsky Umzüge', '93432 Strelow', 'Richard-Wagner-Str. 3', 'Tel. 0217 8999-20'] },
    { letter: 'q', parts: ['Ruddeck Betonwaren', '80796 München', 'Entenfuhl 5', 'Tel. 0234 887745'] },
    { letter: 'r', parts: ['Katonagenfabrik Großpietsch', '45363 Essen', 'Merowingerstr. 7', 'Tel. 0456 45638'] },
    { letter: 's', parts: ['Hermann Große-Naust', '13245 Rostock', 'Melanchthonstr. 4', 'Tel. 06543 73626'] },
    { letter: 't', parts: ['Autohaus Ayibogan', '33245 Dusiburg', 'Am Kistner 3', 'Tel. 04567 43566'] },
    { letter: 'u', parts: ['Bauklempnerei Frickartz', '45637 Wesel', 'Am Röhr 40', 'Tel. 0443 876523'] },
    { letter: 'v', parts: ['Foto Forrobosko', '23456 Lünenburg', 'Agnesstr. 14', 'Tel. 0442 275839'] },
];

// click-to-strike matrix & per-row error counts
const copyMarks = ref(copyRows.map((r) => r.parts.map((p) => Array.from(p).map(() => false))));
const copyCounts = ref<string[]>(Array(copyRows.length).fill(''));

const toggleCopyChar = (r: number, p: number, c: number) => {
    copyMarks.value[r][p][c] = !copyMarks.value[r][p][c];
};
const getChars = (r: number, p: number) => Array.from(copyRows[r].parts[p]);

/* =========================================================
   PAGES 4 + scoring  [were old 6–7]
========================================================= */
const page5Text = 'u n u u n u';
const page5Marks = ref<boolean[]>(page5Text.split(' ').map(() => false));
const page5Sum = ref('');

const page4Rows = [
    '8985689565966559886565689695865958655689565',
    '5986956896866596589668586896859858695895895',
    '6859598586965868656968568668695855689585565',
    '5689865898589658959589595866569895655668956',
    '5689658985685996859658995689586596556699965',
    '6898658695865965995895969565958958695899586',
    '6859686869588686665596956698695956598695866',
    '6898869665858586965589696568968958695865696',
    '8686986998566585869968959998659685966659659',
    '8699868959685965858968596886955565998695586',
    '6589686958656666698568958665968595986596859',
    '5689665898589868995659896666566596659658586',
    '6986988865988595556886656869565995568558566',
    '8695685588685585668989586566985869568956686',
];
const page4Answers = ref<string[]>(Array(page4Rows.length).fill(''));
const page4Correct = ['12', '11', '13', '10', '11', '9', '16', '14', '13', '10', '15', '15', '12', '13'];

const page4Marks = ref(page4Rows.map((row) => Array.from(row).map(() => false)));

const getPage4Chars = (row: number) => Array.from(page4Rows[row]);
const togglePage4Char = (row: number, idx: number) => {
    page4Marks.value[row][idx] = !page4Marks.value[row][idx];
};

const page5Chars = ['A', '%', 'B', '$', 'C', '&', 'D'];
const page5Marks2 = ref<boolean[]>(page5Chars.map(() => false));
const page5Total = ref('');

const toNum = (s: string) => {
    if (!s) return null;
    const n = parseFloat(s.replace(',', '.').trim());
    return Number.isFinite(n) ? n : null;
};

const wrongCount = computed(() => {
    let wrong = 0;

    // Page 1
    page1Correct.forEach((ans, i) => {
        const exp = toNum(ans);
        const got = toNum(page1Inputs.value[i] || '');
        if (got === null || got !== exp) wrong++;
    });

    // Page 2
    page2Correct.forEach((ans, i) => {
        if ((page2Answers.value[i] || '').trim() !== ans.trim()) wrong++;
    });

    // Page 3
    page3Correct.forEach((ans, i) => {
        if ((copyCounts.value[i] || '').trim() !== ans.trim()) wrong++;
    });

    // Page 4
    page4Correct.forEach((ans, i) => {
        if ((page4Answers.value[i] || '').trim() !== ans.trim()) wrong++;
    });

    // Page 5
    page5Correct.forEach((ans, i) => {
        if ((page5TickSums.value[i] || '').trim() !== ans.trim()) wrong++;
    });

    return wrong;
});

const performanceCategory = computed(() => {
    const wrong = wrongCount.value;
    if (wrong <= 5) return { range: '100-92%', category: 'Oberer Leistungsbereich' };
    if (wrong <= 13) return { range: '91-81%', category: 'Oberer Leistungsbereich' };
    if (wrong <= 22) return { range: '80-67%', category: 'Mittlerer Leistungsbereich' };
    if (wrong <= 33) return { range: '66-50%', category: 'Mittlerer Leistungsbereich' };
    if (wrong <= 46) return { range: '49-30%', category: 'Unterer Leistungsbereich' };
    return { range: '29-0%', category: 'Unterer Leistungsbereich' };
});

/* ======================== PAGE 5 — precise ticks via CSS ======================== */
type T = { ch: 'h' | 'u' | 'n' | 'm'; top?: 0 | 1 | 2 | 3; bot?: 0 | 1 | 2 | 3 };

/** Row a) — set exactly which letters get 1 or 2 ticks above/below.
 *  top: 0|1|2 (= none | single | double)
 *  bot: 0|1|2 (= none | single | double)
 *  👉 Change this array to match your scan 1:1.
 */
const rowA: T[] = [
    { ch: 'n', top: 2, bot: 1 }, // ← first char: double above + single below
    { ch: 'u', top: 1, bot: 2 },
    { ch: 'h', top: 1, bot: 2 }, // adjust these as needed to match the sheet
    { ch: 'u', bot: 2 },
    { ch: 'm', top: 1 },
    { ch: 'n' },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'u', bot: 3 },
    { ch: 'h', top: 1, bot: 1 },
    { ch: 'u', top: 2, bot: 2 },
    { ch: 'n', bot: 2 },
    { ch: 'h', top: 2 },
    { ch: 'n', bot: 2 },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'h', bot: 2 },
    { ch: 'n', top: 1 },
    { ch: 'u', bot: 2 },
    { ch: 'u', top: 2 },
    { ch: 'n', bot: 2 },
    { ch: 'n', top: 1, bot: 2 },
    { ch: 'u', top: 2 },
    { ch: 'n', bot: 2 },
    { ch: 'h', top: 1 },
    { ch: 'u', top: 2 },
    { ch: 'n', bot: 1 },
    { ch: 'n', top: 1 },
    { ch: 'u' },
    { ch: 'h', top: 1, bot: 1 },
];
const rowB: T[] = [
    { ch: 'h', top: 2, bot: 2 }, // ← first char: double above + single below
    { ch: 'h', top: 1, bot: 1 },
    { ch: 'u', top: 1, bot: 1 }, // adjust these as needed to match the sheet
    { ch: 'n', bot: 2 },
    { ch: 'u', top: 1 },
    { ch: 'h' },
    { ch: 'n', top: 2, bot: 2 },
    { ch: 'u', top: 2 },
    { ch: 'n', bot: 2 },
    { ch: 'n', top: 1 },
    { ch: 'n', bot: 2 },
    { ch: 'u' },
    { ch: 'n', top: 1, bot: 1 },
    { ch: 'n', bot: 2 },
    { ch: 'u', top: 1, bot: 3 },
    { ch: 'u', bot: 1 },
    { ch: 'n' },
    { ch: 'u', top: 2, bot: 1 },
    { ch: 'n', bot: 2 },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'n' },
    { ch: 'u', top: 2, bot: 1 },
    { ch: 'h' },
    { ch: 'u', top: 1, bot: 2 },
    { ch: 'u' },
    { ch: 'u', top: 2, bot: 1 },
    { ch: 'u', top: 1 },
];
const rowC: T[] = [
    { ch: 'n', top: 1, bot: 2 },
    { ch: 'n' },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'h', top: 1 },
    { ch: 'n', bot: 2 },

    { ch: 'u', top: 1, bot: 1 },
    { ch: 'h', bot: 1 },
    { ch: 'n', top: 2, bot: 2 },

    { ch: 'n', top: 1, bot: 2 },
    { ch: 'h', top: 2 },
    { ch: 'u', bot: 2 },
    { ch: 'u', top: 2 },
    { ch: 'n', top: 1, bot: 2 },
    { ch: 'n', top: 1 },
    { ch: 'h', bot: 2 },

    { ch: 'n', bot: 2 },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'u' },

    { ch: 'n', bot: 2 },
    { ch: 'h', top: 1 },
    { ch: 'n', top: 3, bot: 1 },

    { ch: 'n', bot: 1 },
    { ch: 'u', top: 2 },

    { ch: 'h', top: 1 },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'h' },
    { ch: 'n', top: 1, bot: 1 },
    { ch: 'u', top: 1, bot: 1 },
];
const rowD: T[] = [
    { ch: 'n', top: 1, bot: 2 },
    { ch: 'n', bot: 2 },
    { ch: 'u', top: 1 },
    { ch: 'n', bot: 2 },
    { ch: 'h', top: 3 },

    { ch: 'u', bot: 2 },
    { ch: 'h', top: 1 },
    { ch: 'n', bot: 2 },

    { ch: 'n' },
    { ch: 'u', bot: 2 },
    { ch: 'h', top: 1 },
    { ch: 'u', bot: 2 },
    { ch: 'n', top: 2 },
    { ch: 'n', bot: 2 },
    { ch: 'u', top: 1, bot: 1 },

    { ch: 'u', top: 1, bot: 2 },
    { ch: 'u' },
    { ch: 'h', top: 2, bot: 2 },

    { ch: 'n', top: 1 },
    { ch: 'h', top: 1, bot: 2 },
    { ch: 'u', top: 2, bot: 1 },

    { ch: 'n' },
    { ch: 'n', top: 1, bot: 2 },

    { ch: 'u', bot: 1 },
    { ch: 'n', top: 2 },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'h', top: 1 },
    { ch: 'n', bot: 1 },
];
const rowE: T[] = [
    { ch: 'u', top: 1 },
    { ch: 'n', bot: 2 },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'h', bot: 2 },
    { ch: 'n', top: 3 },

    { ch: 'n', bot: 2 },
    { ch: 'u', top: 2 },
    { ch: 'n', top: 1, bot: 2 },

    { ch: 'n', top: 1 },
    { ch: 'u', bot: 2 },
    { ch: 'n', top: 1 },
    { ch: 'n', bot: 2 },
    { ch: 'h', top: 2 },
    { ch: 'n', bot: 2 },
    { ch: 'h', top: 1 },

    { ch: 'u', top: 1, bot: 1 },
    { ch: 'h', top: 1 },
    { ch: 'n', bot: 3 },

    { ch: 'u', top: 2, bot: 1 },
    { ch: 'u', top: 2, bot: 1 },
    { ch: 'n', top: 1 },

    { ch: 'u', bot: 2 },
    { ch: 'n', top: 2, bot: 1 },

    { ch: 'h' },
    { ch: 'n', top: 1, bot: 2 },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'u', top: 2 },
    { ch: 'u', bot: 1 },
];
const rowF: T[] = [
    { ch: 'n', top: 2 },
    { ch: 'n', top: 1 },
    { ch: 'h', bot: 2 },
    { ch: 'n', top: 1 },
    { ch: 'u', bot: 2 },

    { ch: 'h', top: 1 },
    { ch: 'u', bot: 2 },
    { ch: 'u', top: 1, bot: 1 },

    { ch: 'n', top: 1, bot: 1 },
    { ch: 'n', top: 2, bot: 1 },
    { ch: 'u', bot: 2 },
    { ch: 'n', top: 1 },
    { ch: 'u', bot: 2 },
    { ch: 'n', top: 1 },
    { ch: 'n', bot: 2 },

    { ch: 'u', top: 2, bot: 1 },
    { ch: 'n', bot: 2 },
    { ch: 'h', top: 1, bot: 1 },

    { ch: 'u', top: 2, bot: 1 },
    { ch: 'n', top: 1, bot: 1 },
    { ch: 'u', top: 1, bot: 1 },

    { ch: 'h', bot: 1 },
    { ch: 'u', top: 3, bot: 1 },

    { ch: 'u', top: 1, bot: 1 },
    { ch: 'n', bot: 2 },
    { ch: 'u', bot: 2 },
    { ch: 'n', top: 2, bot: 1 },
    { ch: 'u', top: 1, bot: 1 },
];
const rowG: T[] = [
    { ch: 'n', top: 2, bot: 2 },
    { ch: 'n', top: 1, bot: 2 },
    { ch: 'u', top: 1 },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'n', top: 2 },

    { ch: 'n', bot: 2 },
    { ch: 'u', top: 2 },
    { ch: 'n', top: 1, bot: 2 },

    { ch: 'h' },
    { ch: 'n', bot: 2 },
    { ch: 'n', top: 1 },
    { ch: 'n', top: 1, bot: 2 },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'n', bot: 2 },
    { ch: 'n', top: 2 },

    { ch: 'h', bot: 2 },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'n', bot: 1 },

    { ch: 'n', top: 2 },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'n', bot: 2 },

    { ch: 'u', bot: 2 },
    { ch: 'h', top: 1 },

    { ch: 'h', bot: 2 },
    { ch: 'u', top: 1 },
    { ch: 'n', bot: 1 },
    { ch: 'u' },
    { ch: 'u', top: 1, bot: 1 },
];
const rowH: T[] = [
    { ch: 'n', top: 1 },
    { ch: 'u', top: 2 },
    { ch: 'n', bot: 2 },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'n', bot: 2 },

    { ch: 'n', top: 2, bot: 2 },
    { ch: 'h', bot: 2 },
    { ch: 'u', top: 1, bot: 1 },

    { ch: 'n', top: 1, bot: 2 },
    { ch: 'n', top: 2 },
    { ch: 'u', bot: 2 },
    { ch: 'n', top: 2 },
    { ch: 'u', top: 1, bot: 2 },
    { ch: 'u' },
    { ch: 'n', top: 2, bot: 2 },

    { ch: 'n', top: 1 },
    { ch: 'u', bot: 2 },
    { ch: 'h', bot: 1 },

    { ch: 'u', top: 2 },
    { ch: 'n', bot: 2 },
    { ch: 'u', top: 2, bot: 1 },

    { ch: 'n', bot: 2 },
    { ch: 'u', top: 2 },

    { ch: 'n', bot: 1 },
    { ch: 'u', top: 2 },
    { ch: 'n', top: 1, bot: 2 },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'u', top: 1, bot: 1 },
];
const rowI: T[] = [
    { ch: 'u', top: 1, bot: 2 },
    { ch: 'n', top: 2 },
    { ch: 'h', bot: 1 },
    { ch: 'u', top: 2 },
    { ch: 'n', bot: 2 },

    { ch: 'u', top: 1, bot: 1 },
    { ch: 'u', top: 2 },
    { ch: 'n', bot: 2 },

    { ch: 'u', top: 1, bot: 1 },
    { ch: 'n', top: 2 },
    { ch: 'h', bot: 2 },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'n', bot: 2 },
    { ch: 'n', top: 1 },
    { ch: 'h', bot: 2 },

    { ch: 'u', bot: 2 },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'n' },

    { ch: 'n', bot: 2 },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'n', top: 1 },

    { ch: 'n' },
    { ch: 'u', top: 1, bot: 1 },

    { ch: 'n' },
    { ch: 'h', top: 2, bot: 1 },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'n', top: 2 },
    { ch: 'u', bot: 1 },
];
const rowJ: T[] = [
    { ch: 'u', top: 1 },
    { ch: 'n', top: 1, bot: 2 },
    { ch: 'h', bot: 2 },
    { ch: 'u', top: 2, bot: 1 },
    { ch: 'n', bot: 2 },

    { ch: 'h', top: 1, bot: 1 },
    { ch: 'n', bot: 2 },
    { ch: 'u', top: 1, bot: 1 },

    { ch: 'u', bot: 2 },
    { ch: 'h', top: 1 },
    { ch: 'n', bot: 2 },
    { ch: 'n', top: 2, bot: 1 },
    { ch: 'u', bot: 2 },
    { ch: 'u', top: 1 },
    { ch: 'n', top: 1, bot: 2 },

    { ch: 'u', top: 2 },
    { ch: 'h', bot: 2 },
    { ch: 'u', top: 2 },

    { ch: 'u', top: 1, bot: 1 },
    { ch: 'u', top: 2, bot: 1 },
    { ch: 'u', bot: 2 },

    { ch: 'n', top: 1 },
    { ch: 'u', top: 1, bot: 2 },

    { ch: 'u', top: 1, bot: 2 },
    { ch: 'u', top: 1, bot: 1 },
    { ch: 'u', top: 1, bot: 2 },
    { ch: 'n', top: 1, bot: 1 },
    { ch: 'u', top: 1, bot: 1 },
];
/** Put all 10 rows here when ready; for now we render only a) to nail the look. */
const page5TickRows: T[][] = [rowA, rowB, rowC, rowD, rowE, rowF, rowG, rowH, rowI, rowJ];

/** click to mark */
const page5TickMarks = ref(page5TickRows.map((r) => r.map(() => false)));
const toggleTick = (r: number, i: number) => (page5TickMarks.value[r][i] = !page5TickMarks.value[r][i]);

/** per-row totals input */
const page5TickSums = ref<string[]>(Array(page5TickRows.length).fill(''));

const rowLabel = (i: number) => String.fromCharCode(97 + i); // a..j

// put near your other PAGE 5 helpers
const GAP_AFTER = [5, 8, 15, 18, 21, 23]; // 1-based
const hasGapAfter = (zeroBasedIndex: number) => GAP_AFTER.includes(zeroBasedIndex + 1);
</script>

<template>
    <Head title="628 Test" />

    <div class="mb-4"></div>

    <!-- FULL WIDTH PAGE WRAPPER -->
    <div class="w-full max-w-none px-3 md:px-8">
        <!-- ======================== PAGE 1 ======================== -->
        <div v-if="page === 1">
            <div class="flex items-baseline gap-3">
                <span class="text-3xl font-semibold">1</span>
                <span class="text-base">(je 1 Fehlerpunkt)</span>
            </div>
            <div class="my-2 h-[3px] bg-black"></div>

            <p class="text-[18px] leading-snug">Die folgenden Zahlenreihen sind nach einer bestimmten Gesetzmäßigkeit aufgebaut.</p>
            <p class="text-[18px] leading-snug">Durch welche Zahl wird die jeweilige Zahlenreihe logisch fortgesetzt?</p>
            <p class="mb-5 text-[18px] leading-snug">Tragen Sie bitte die entsprechende Zahl in das jeweils dafür vorgesehene Kästchen ein.</p>

            <ul class="list-disc space-y-6 pl-8">
                <li v-for="(row, i) in page1Series" :key="i" class="flex items-center gap-2">
                    <span class="text-[18px] whitespace-pre">{{ row.join(' . ') }} . ?</span>

                    <input v-model="page1Inputs[i]" class="answer-box h-8 w-12 text-[18px]" inputmode="numeric" />
                </li>
            </ul>
        </div>

        <!-- ======================== PAGE 2 (merged) ======================== -->
        <div v-else-if="page === 2">
            <div class="flex items-baseline gap-3">
                <span class="text-3xl font-semibold">2</span>
                <span class="text-base">(je 1 Fehlerpunkt)</span>
            </div>
            <div class="my-2 h-[3px] bg-black"></div>

            <p class="text-[18px] leading-snug">
                Aus den untenstehenden Bilderreihen lassen sich bestimmte Folgerungen und Gesetzmäßigkeiten ableiten. Wählen Sie
                <b>rechts</b> das Bild [1]–[4], das die Reihe sinnvoll vervollständigt, und tragen Sie links die Zahl in das Kästchen ein.
            </p>

            <div class="mt-6">
                <div class="grid grid-cols-1 items-start gap-x-8 gap-y-3 lg:grid-cols-[minmax(0,1fr)_2px_minmax(0,1fr)]">
                    <div class="text-2xl font-semibold text-red-600">➔ Bilderreihen</div>
                    <div class="hidden self-stretch bg-black/60 lg:block"></div>
                    <div class="text-2xl font-semibold text-red-600">➔ Antworttafeln [1]–[4]</div>

                    <template v-for="(row, i) in page2Rows" :key="`page2-row-${i}`">
                        <div class="page2-row page2-left-row">
                            <div class="page2-svg" v-html="row.questionSvg"></div>
                            <input v-model="page2Answers[i]" class="answer-box page2-answer-input text-[18px]" inputmode="numeric" maxlength="1" />
                        </div>
                        <div class="hidden self-stretch bg-black/60 lg:block"></div>
                        <div class="page2-row">
                            <div class="page2-svg" v-html="row.answerSvg"></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ======================== PAGE 3 (Original | Abschrift) ======================== -->
        <div v-else-if="page === 3">
            <div class="flex items-baseline gap-3">
                <span class="text-3xl font-semibold">3</span>
                <span class="text-base">(je 1 Fehlerpunkt)</span>
            </div>
            <div class="my-2 h-[3px] bg-black"></div>

            <p class="text-[18px] leading-snug">
                Links: Original – Rechts: Abschrift. Jede Position steht in <b>zwei Zeilen</b>; markieren Sie Fehler in der Abschrift und tragen Sie
                die Anzahl rechts ein.
            </p>

            <div class="mt-4 border-b border-black/50 pb-4">
                <div class="mb-2 text-[18px] font-semibold">Beispiel:</div>
                <div class="relative grid grid-cols-1 gap-10 font-mono text-[18px] lg:grid-cols-2">
                    <div class="pointer-events-none absolute inset-y-0 left-1/2 hidden w-[2px] bg-black/60 lg:block"></div>

                    <div class="pr-4">
                        <div class="mb-1 text-lg font-semibold text-red-600">➔ Original</div>
                        <div class="grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)] grid-rows-2 items-start gap-x-4 leading-[1.55] whitespace-nowrap">
                            <div>
                                <span v-for="(ch, cIdx) in getExampleOriginalChars(0)" :key="'ex-orig-0-' + cIdx" class="example-letter">
                                    {{ ch }}
                                </span>
                            </div>
                            <div>
                                <span v-for="(ch, cIdx) in getExampleOriginalChars(1)" :key="'ex-orig-1-' + cIdx" class="example-letter">
                                    {{ ch }}
                                </span>
                            </div>
                            <div class="mt-1">
                                <span v-for="(ch, cIdx) in getExampleOriginalChars(2)" :key="'ex-orig-2-' + cIdx" class="example-letter">
                                    {{ ch }}
                                </span>
                            </div>
                            <div class="mt-1">
                                <span v-for="(ch, cIdx) in getExampleOriginalChars(3)" :key="'ex-orig-3-' + cIdx" class="example-letter">
                                    {{ ch }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="pl-4">
                        <div class="mb-1 text-lg font-semibold text-red-600">➔ Abschrift</div>
                        <div
                            class="grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] grid-rows-2 items-start gap-x-4 leading-[1.55] whitespace-nowrap"
                        >
                            <div>
                                <template v-for="(ch, cIdx) in getExampleCopyChars(0)" :key="'ex-copy-0-' + cIdx">
                                    <span :class="['example-letter', { marked: exampleMarked[0].includes(cIdx) }]">
                                        {{ ch }}
                                    </span>
                                </template>
                            </div>
                            <div>
                                <template v-for="(ch, cIdx) in getExampleCopyChars(1)" :key="'ex-copy-1-' + cIdx">
                                    <span :class="['example-letter', { marked: exampleMarked[1].includes(cIdx) }]">
                                        {{ ch }}
                                    </span>
                                </template>
                            </div>
                            <input
                                value="7"
                                readonly
                                aria-label="Beispiel Fehleranzahl"
                                class="answer-box count-box row-span-2 self-center justify-self-center"
                            />
                            <div class="mt-1">
                                <template v-for="(ch, cIdx) in getExampleCopyChars(2)" :key="'ex-copy-2-' + cIdx">
                                    <span :class="['example-letter', { marked: exampleMarked[2].includes(cIdx) }]">
                                        {{ ch }}
                                    </span>
                                </template>
                            </div>
                            <div class="mt-1">
                                <template v-for="(ch, cIdx) in getExampleCopyChars(3)" :key="'ex-copy-3-' + cIdx">
                                    <span :class="['example-letter', { marked: exampleMarked[3].includes(cIdx) }]">
                                        {{ ch }}
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative mt-6 grid grid-cols-1 gap-10 lg:grid-cols-2">
                <div class="pointer-events-none absolute inset-y-0 left-1/2 hidden w-[2px] bg-black/60 lg:block"></div>

                <!-- LEFT: Original -->
                <section class="pr-4">
                    <div class="mb-3 text-2xl font-semibold text-red-600">➔ Original</div>
                    <div class="font-mono text-[18px]">
                        <div v-for="(row, i) in originalRows" :key="'orig-' + row.letter" class="border-b py-2">
                            <div
                                class="grid grid-cols-[22px_minmax(0,1fr)_minmax(0,1fr)] grid-rows-2 items-start gap-x-4 leading-[1.55] whitespace-nowrap"
                            >
                                <div class="row-span-2 pr-1 text-right font-semibold text-gray-700">{{ String.fromCharCode(97 + i) }})</div>
                                <div>{{ row.parts[0] }}</div>
                                <div>{{ row.parts[1] }}</div>
                                <div class="col-start-2 mt-1">{{ row.parts[2] }}</div>
                                <div class="col-start-3 mt-1">{{ row.parts[3] }}</div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- RIGHT: Abschrift -->
                <section class="pl-4">
                    <div class="mb-3 text-2xl font-semibold text-red-600">➔ Abschrift</div>
                    <div class="font-mono text-[18px]">
                        <div v-for="(row, rIdx) in copyRows" :key="'copy-' + row.letter" class="border-b py-2">
                            <div
                                class="grid grid-cols-[22px_minmax(0,1fr)_minmax(0,1fr)_auto] grid-rows-2 items-start gap-x-4 leading-[1.55] whitespace-nowrap"
                            >
                                <div class="row-span-2 pr-1 text-right font-semibold text-gray-700">{{ String.fromCharCode(97 + rIdx) }})</div>

                                <!-- Row 1 -->
                                <div>
                                    <span
                                        v-for="(ch, cIdx) in getChars(rIdx, 0)"
                                        :key="'r' + rIdx + 'p0c' + cIdx"
                                        @click="toggleCopyChar(rIdx, 0, cIdx)"
                                        :class="['letter', { marked: copyMarks[rIdx][0][cIdx] }]"
                                    >
                                        {{ ch }}
                                    </span>
                                </div>
                                <div>
                                    <span
                                        v-for="(ch, cIdx) in getChars(rIdx, 1)"
                                        :key="'r' + rIdx + 'p1c' + cIdx"
                                        @click="toggleCopyChar(rIdx, 1, cIdx)"
                                        :class="['letter', { marked: copyMarks[rIdx][1][cIdx] }]"
                                    >
                                        {{ ch }}
                                    </span>
                                </div>

                                <!-- Count -->
                                <input
                                    v-model="copyCounts[rIdx]"
                                    class="answer-box count-box row-span-2 self-center justify-self-center"
                                    inputmode="numeric"
                                    maxlength="2"
                                />

                                <!-- Row 2 -->
                                <div class="col-start-2 mt-1">
                                    <span
                                        v-for="(ch, cIdx) in getChars(rIdx, 2)"
                                        :key="'r' + rIdx + 'p2c' + cIdx"
                                        @click="toggleCopyChar(rIdx, 2, cIdx)"
                                        :class="['letter', { marked: copyMarks[rIdx][2][cIdx] }]"
                                    >
                                        {{ ch }}
                                    </span>
                                </div>
                                <div class="col-start-3 mt-1">
                                    <span
                                        v-for="(ch, cIdx) in getChars(rIdx, 3)"
                                        :key="'r' + rIdx + 'p3c' + cIdx"
                                        @click="toggleCopyChar(rIdx, 3, cIdx)"
                                        :class="['letter', { marked: copyMarks[rIdx][3][cIdx] }]"
                                    >
                                        {{ ch }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- ======================== PAGE 4 (6er zählen) ======================== -->
        <div v-else-if="page === 4">
            <div class="flex items-baseline gap-3">
                <span class="text-3xl font-semibold">4</span>
                <span class="text-base">(je 1 Fehlerpunkt)</span>
            </div>
            <div class="my-2 h-[3px] bg-black"></div>

            <p class="text-[18px] leading-snug">Bei den folgenden 14 Zahlenreihen müssen jeweils alle 6-en pro Zahlenreihe gezählt werden.</p>
            <p class="mb-10 text-[18px] leading-snug">Bitte notieren Sie in den Lösungskästchen die jeweilige Anzahl an 6-en pro Zeile!</p>
            <div v-for="(row, i) in page4Rows" :key="i" class="mb-3 flex items-center gap-3">
                <div class="font-mono text-[18px] tracking-wider whitespace-nowrap">
                    <span
                        v-for="(ch, idx) in getPage4Chars(i)"
                        :key="idx"
                        @click="togglePage4Char(i, idx)"
                        :class="['letter', { marked: page4Marks[i][idx] }]"
                        >{{ ch }}</span
                    >
                </div>
                <Input v-model="page4Answers[i]" class="inline h-9 w-16 text-[18px]" />
            </div>
        </div>

        <!-- ======================== PAGE 5 (Zeichen zählen) ======================== -->
        <div v-else-if="page === 5">
            <div class="flex items-baseline gap-3">
                <span class="text-3xl font-semibold">5</span>
                <span class="text-base">(je 1 Fehlerpunkt)</span>
            </div>
            <div class="my-2 h-[3px] bg-black"></div>

            <p class="mb-4 text-[18px] leading-snug">
                Bei den folgenden 10 Zeilen müssen alle Buchstaben „u“ erkannt werden, die zwei Striche besitzen.
            </p>

            <div class="mb-8 space-y-6">
                <p class="text-[18px] font-semibold">Beispiele:</p>
                <div class="tick-line text-[22px] leading-[1.35]">
                    <span class="tk example-tk" data-top="2">u</span>
                    <span class="tk example-tk" data-bot="2">u</span>
                    <span class="tk example-tk" data-top="1" data-bot="1">u</span>
                </div>
            </div>

            <p class="mb-10 text-[18px] leading-snug">
                Markieren und zählen Sie pro Reihe alle Buchstaben <b>„u“</b>, die <b>zwei Striche</b> besitzen und tragen Sie die jeweilige Summe
                (pro Reihe) in die Kästchen ein!
            </p>

            <div class="space-y-4">
                <div v-for="(row, r) in page5TickRows" :key="'p5r' + r" class="mb-12 flex flex-wrap items-start gap-3">
                    <!-- left label -->
                    <div class="row-label shrink-0 pr-1 text-[22px]">{{ rowLabel(r) }})</div>

                    <!-- the letters with ticks -->
                    <div class="tick-line inline-block text-[22px] leading-[1.35]">
                        <span
                            v-for="(t, i) in row"
                            :key="i"
                            class="tk inline-block cursor-pointer select-none"
                            :class="[{ marked: page5TickMarks[r][i] }, hasGapAfter(i) ? 'gap' : '']"
                            :data-top="t.top ?? 0"
                            :data-bot="t.bot ?? 0"
                            @click="toggleTick(r, i)"
                        >
                            {{ t.ch }}
                        </span>
                    </div>
                    <!-- right input -->
                    <Input v-model="page5TickSums[r]" class="inline h-9 w-16 border border-gray-400 bg-white text-[18px]" placeholder="" />
                </div>
            </div>
        </div>

        <!-- NAV -->
        <div class="my-8 flex gap-3">
            <Button @click="prevPage" v-if="page > 1">Zurück</Button>
            <Button @click="nextPage" v-if="page < MAX_PAGE">Weiter</Button>
            <Button variant="destructive" @click="finishTest" v-if="page === MAX_PAGE">Test beenden</Button>
        </div>

        <Dialog :open="showUnansweredDialog" @update:open="(val) => (showUnansweredDialog = val)">
            <DialogContent class="max-w-lg">
                <DialogHeader>
                    <DialogTitle>Nicht alle Antworten sind ausgefüllt</DialogTitle>
                    <DialogDescription> Bitte prüfen Sie die offenen Felder. Leere Eingaben werden als unbeantwortet gespeichert. </DialogDescription>
                </DialogHeader>
                <div class="space-y-3">
                    <p class="text-base">Offene Bereiche:</p>
                    <ul class="list-disc space-y-1 pl-5 text-base">
                        <li v-for="note in unansweredNotes" :key="note">{{ note }}</li>
                    </ul>
                </div>
                <DialogFooter class="gap-2">
                    <Button variant="outline" @click="showUnansweredDialog = false">Zurück zum Test</Button>
                    <Button variant="destructive" @click="confirmFinishDespiteUnanswered">Trotzdem beenden</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<style scoped>
.page2-row {
    min-height: 84px;
    display: flex;
    align-items: center;
}

.page2-left-row {
    gap: 1rem;
}

.page2-svg {
    flex: 1;
    width: 100%;
}

.page2-svg :deep(svg) {
    display: block;
    width: 100%;
    height: auto;
}

.page2-answer-input {
    width: 3rem;
    height: 3rem;
    flex: 0 0 auto;
    align-self: center;
}

/* small square inputs */
.answer-box {
    border: 1px solid #000;
    border-radius: 0;
    padding: 0;
    text-align: center;
    background: #fff;
    box-sizing: border-box;
}

/* page-1 inputs fixed size if used inside lists */
ul .answer-box {
    width: 2.5rem;
    height: 2rem;
}

/* Abschrift clickable letters */
.letter {
    cursor: pointer;
    padding: 0 0.25px;
}

.example-letter {
    cursor: default;
    pointer-events: none;
    padding: 0 0.25px;
    position: relative;
}

.example-tk {
    cursor: default;
    pointer-events: none;
}

.marked {
    color: #dc2626;
    text-decoration: line-through;
    text-decoration-thickness: 2px;
}

.count-box {
    width: 2.6ch;
    height: 1.6em;
    line-height: 1.6em;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
}

/* double underline used on page 5 */
.underline-double {
    text-decoration-line: underline;
    text-decoration-style: double;
    text-decoration-color: red;
}

/* ===== Page 5 tick geometry (triple tick support) ===== */
.tick-line {
    --top: -0.6em;
    /* raise/lower top ticks */
    --bot: 1.5em;
    /* move bottom ticks */
    --h: 0.23em;
    /* tick height */
    --w: 0.075em;
    /* tick thickness */
    /* try with px so it can’t collapse to the same pixel column */
    --sep1: 3px;
    /* gap between 1st and 2nd */
    --sep2: 7px;
    /* bigger gap to the 3rd */
    --word-gap: 0.6em;
    /* tweak until it matches the scan */

    line-height: 1;
    font-family: 'Noto Sans', 'DejaVu Sans', system-ui, sans-serif;
    white-space: pre-wrap;
    letter-spacing: 0.02em;
}

.tk {
    position: relative;
    display: inline-block;
    padding: 0 0.2em;
}

/* TOP ticks (anchored slightly left; adjust left: % if needed) */
.tk::before {
    content: none;
    position: absolute;
    top: var(--top);
    left: 30%;
    transform: translateX(-50%);
    width: var(--w);
    height: var(--h);
    background: currentColor;
    border-radius: 1px;
    z-index: 1;
}

/* single above */
.tk[data-top='1']::before {
    content: '';
}

/* double above */
.tk[data-top='2']::before {
    content: '';
    box-shadow: var(--sep1) 0 0 0 currentColor;
}

/* triple above: 1st at origin, 2nd at --sep1, 3rd further at --sep2 */
.tk[data-top='3']::before {
    content: '';
    box-shadow:
        var(--sep1) 0 0 0 currentColor,
        var(--sep2) 0 0 0 currentColor;
}

/* BOTTOM ticks */
.tk::after {
    content: none;
    position: absolute;
    top: var(--bot);
    left: 30%;
    transform: translateX(-50%);
    width: var(--w);
    height: var(--h);
    background: currentColor;
    border-radius: 1px;
    z-index: 1;
}

/* single below */
.tk[data-bot='1']::after {
    content: '';
}

/* double below */
.tk[data-bot='2']::after {
    content: '';
    box-shadow: var(--sep1) 0 0 0 currentColor;
}

/* triple below (bigger gap before the 3rd) */
.tk[data-bot='3']::after {
    content: '';
    box-shadow:
        var(--sep1) 0 0 0 currentColor,
        var(--sep2) 0 0 0 currentColor;
}

/* clicking mark styling */
.letter,
.tk {
    cursor: pointer;
}

.marked {
    color: #dc2626;
    text-decoration: line-through;
    text-decoration-thickness: 2px;
}

.tk.gap {
    margin-right: var(--word-gap);
}
</style>
