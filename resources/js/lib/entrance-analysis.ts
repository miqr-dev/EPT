import { getLpsDataset } from '@/pages/Questions/LPSPage1';
import { getLpsPage10Dataset } from '@/pages/Questions/LPSPage10';
import { getLpsPage11Dataset } from '@/pages/Questions/LPSPage11';
import { getLpsPage5Dataset } from '@/pages/Questions/LPSPage5';
import { getLpsPage6Dataset } from '@/pages/Questions/LPSPage6';
import { getLpsPage7Dataset } from '@/pages/Questions/LPSPage7';
import { getLpsPage8Dataset } from '@/pages/Questions/LPSPage8';
import { getLpsPage9Dataset } from '@/pages/Questions/LPSPage9';
import {
    LPS_B_IQ_BY_T_RANGES,
    LPS_B_SCORE_TABLES,
    type LpsBAgeGroupKey,
    type LpsBScoreKey,
    type LpsBScoreRangeRow,
    type LpsBScoreRow,
    type LpsBTotalScoreRow,
} from '@/pages/Scores/LPS/lpsbScoreTables';

export type ObservationFields = {
    instruction_understanding: string;
    work_method: string;
    work_speed: string;
    group_behavior: string;
    remarks: string;
};

type AssignmentResult = {
    result_json?: Record<string, any> | null;
    manual_scores?: Array<{ key: string; value: number | string | null }> | Record<string, number | string | null>;
    created_at?: string | null;
};

type Assignment = {
    test?: { name?: string | null; code?: string | null };
    results?: AssignmentResult[];
};

const FPI_ROWS = [
    'Lebenszufriedenheit',
    'Soziale Orientierung',
    'Leistungsorientierung',
    'Gehemmtheit',
    'Erregbarkeit',
    'Aggressivität',
    'Beanspruchung',
    'Körperliche Beschwerden',
    'Gesundheitssorgen',
    'Offenheit',
    'Extraversion',
    'Emotionalität',
];

const AVEM_ROWS = [
    'subjektive Bedeutsamkeit der Arbeit',
    'beruflicher Ehrgeiz',
    'Verausgabungsbereitschaft',
    'Perfektionsstreben',
    'Distanzierungsfähigkeit',
    'Resignationstendenz (bei Misserfolg)',
    'offensive Problembewältigung',
    'innere Ruhe/Ausgeglichenheit',
    'Erfolgserleben im Beruf',
    'Lebenszufriedenheit',
    'Erleben sozialer Unterstützung',
];

const BIT_ROWS = [
    ['TH', 'technisches Handwerk'],
    ['GH', 'gestaltendes Handwerk'],
    ['TN', 'technisch-naturwissenschaftlich'],
    ['EH', 'Ernährungshandwerk'],
    ['LF', 'land- und forstwirtschaftlich'],
    ['KB', 'kaufmännischer Bereich'],
    ['VB', 'verwaltender Bereich'],
    ['LG', 'literarisch-geisteswissenschaftlich'],
    ['SE', 'sozial-erzieherisch'],
] as const;

function normalizedIdentifiers(assignment: Assignment) {
    return [assignment?.test?.name, assignment?.test?.code].filter(Boolean).map((value) => String(value).trim().toUpperCase());
}

function latestResult(assignments: Assignment[], aliases: string[]) {
    const names = new Set(aliases.map((alias) => alias.toUpperCase()));
    const candidates = assignments.flatMap((assignment) => {
        if (!normalizedIdentifiers(assignment).some((identifier) => names.has(identifier))) return [];
        return (assignment.results ?? []).map((result) => ({ assignment, result }));
    });

    return (
        candidates.sort((a, b) => {
            const aTime = a.result.created_at ? Date.parse(a.result.created_at) : 0;
            const bTime = b.result.created_at ? Date.parse(b.result.created_at) : 0;
            return bTime - aTime;
        })[0] ?? null
    );
}

function manualScoreMap(result?: AssignmentResult | null) {
    const scores = result?.manual_scores ?? [];
    if (!Array.isArray(scores)) return scores;

    return scores.reduce<Record<string, number | string | null>>((map, entry) => {
        if (entry?.key) map[entry.key] = entry.value ?? null;
        return map;
    }, {});
}

function numberValue(value: unknown): number | null {
    if (value === null || value === undefined || value === '') return null;
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : null;
}

function ageGroup(age?: number | null): LpsBAgeGroupKey | null {
    if (age === null || age === undefined || Number.isNaN(Number(age))) return null;
    if (age <= 18) return 'under_18';
    if (age <= 20) return '19_20';
    if (age <= 29) return '21_29';
    if (age <= 39) return '30_39';
    if (age <= 49) return '40_49';
    return '50_plus';
}

function matchesScore(entry: LpsBScoreRow | LpsBScoreRangeRow, raw: number) {
    return 'raw' in entry ? entry.raw === raw : raw >= entry.min && raw <= entry.max;
}

function lookupLpsColumn(ageKey: LpsBAgeGroupKey, key: LpsBScoreKey, raw: number) {
    const rows = LPS_B_SCORE_TABLES[ageKey]?.columns?.[key] ?? [];
    const direct = rows.find((entry) => matchesScore(entry, raw));
    if (direct) return direct.t;

    const sorted = [...rows].sort((a, b) => ('raw' in a ? a.raw : a.min) - ('raw' in b ? b.raw : b.min));
    return (
        sorted.reduce<LpsBScoreRow | LpsBScoreRangeRow | null>((closest, entry) => {
            const maximum = 'raw' in entry ? entry.raw : entry.max;
            return maximum <= raw ? entry : closest;
        }, null)?.t ??
        sorted[0]?.t ??
        null
    );
}

function lpsAnalysis(entry: ReturnType<typeof latestResult>, age?: number | null) {
    const result = entry?.result?.result_json;
    const ageKey = ageGroup(age);
    if (!result || !ageKey) return null;

    const { solutions: page1Solutions } = getLpsDataset('LPS-B');
    const { solutions: page5Solutions } = getLpsPage5Dataset('LPS-B');
    const { solutions: page6Solutions } = getLpsPage6Dataset('LPS-B');
    const { solutions: page7Solutions } = getLpsPage7Dataset('LPS-B');
    const { solutions: page8Solutions } = getLpsPage8Dataset('LPS-B');
    const { solutions: page9Solutions } = getLpsPage9Dataset('LPS-B');
    const { solutions: page10Solutions } = getLpsPage10Dataset('LPS-B');
    const { rows: page11Rows, solutions: page11Solutions } = getLpsPage11Dataset('LPS-B');

    const scoreSelections = (responses: any[], solutions: any[], responseKey: string, solutionKey: string) =>
        (responses ?? []).reduce((total, response, index) => {
            const correct = solutions[index]?.[solutionKey] ?? [];
            const picks = response?.[responseKey] ?? [];
            return total + correct.filter((correctIndex: number) => picks[correctIndex]).length;
        }, 0);

    const scorePrompts = (responses: any[], solutions: any[], isExample: (row: number, prompt: number) => boolean) =>
        (responses ?? []).reduce((total, response, rowIndex) => {
            return (
                total +
                (solutions[rowIndex]?.correctOptionIndices ?? []).reduce((rowTotal: number, correctIndex: number | null, promptIndex: number) => {
                    if (isExample(rowIndex, promptIndex) || correctIndex === null || correctIndex === undefined) return rowTotal;
                    return rowTotal + (response?.prompts?.[promptIndex]?.[correctIndex] ? 1 : 0);
                }, 0)
            );
        }, 0);

    const sequenceStats = (key: 'col13' | 'col14') =>
        (result.page11 ?? []).reduce(
            (totals: { positive: number; negative: number }, response: any, index: number) => {
                const correctIndices = page11Solutions[index]?.[key] ?? [];
                const selected = (response?.[key] ?? []).flatMap((isSelected: boolean, selectedIndex: number) => (isSelected ? [selectedIndex] : []));
                const tokens = String(page11Rows[index]?.[key === 'col13' ? 'column13' : 'column14'] ?? '')
                    .trim()
                    .split(/\s+/);
                const remaining = correctIndices.map((correctIndex: number) => tokens[correctIndex]).filter(Boolean);

                selected.forEach((selectedIndex: number) => {
                    const match = remaining.indexOf(tokens[selectedIndex]);
                    if (match >= 0) {
                        totals.positive += 1;
                        remaining.splice(match, 1);
                    } else {
                        totals.negative += 1;
                    }
                });

                return totals;
            },
            { positive: 0, negative: 0 },
        );

    const col13 = sequenceStats('col13');
    const col14 = sequenceStats('col14');
    const manualScores = manualScoreMap(entry.result);
    const columnScores = {
        col1: scoreSelections(result.page1, page1Solutions, 'col1', 'col1'),
        col2: scoreSelections(result.page1, page1Solutions, 'col2', 'col2'),
        col3: scoreSelections(result.page1, page1Solutions, 'col3', 'col3'),
        col4: scoreSelections(result.page1, page1Solutions, 'col4', 'col4'),
        col5: scoreSelections(result.page1, page1Solutions, 'col5', 'col5'),
        col6: numberValue(manualScores.column6_score) ?? 0,
        col7: scoreSelections(result.page5, page5Solutions, 'col7', 'col7'),
        col8: scoreSelections(result.page6, page6Solutions, 'col8', 'col8'),
        col9: scorePrompts(result.page7, page7Solutions, (row, prompt) => row === 0 && prompt < 2),
        col10: scorePrompts(result.page8, page8Solutions, (row, prompt) => row < 2 && prompt === 0),
        col11: scorePrompts(result.page9, page9Solutions, (row, prompt) => row === 0 && prompt < 2),
        col12: (result.page10 ?? []).reduce((total: number, response: any, rowIndex: number) => {
            if (rowIndex < 2) return total;
            const correctIndex = page10Solutions[rowIndex]?.correctIndex;
            return total + (correctIndex !== null && correctIndex !== undefined && response?.paths?.[correctIndex] ? 1 : 0);
        }, 0),
        col13: col13.positive,
        col14: col14.positive,
        col13Wrong: numberValue(result.column13_wrong ?? result.col13_wrong ?? result.page11_negative_score) ?? col13.negative,
    };

    const raw: Record<string, number> = {
        test_1_2: columnScores.col1 + columnScores.col2,
        test_3_4: columnScores.col3 + columnScores.col4,
        test_5_6: columnScores.col5 + columnScores.col6,
        test_7_10: columnScores.col7 + columnScores.col8 + columnScores.col9 + columnScores.col10,
        test_11_12: columnScores.col11 + columnScores.col12,
        test_13: columnScores.col13,
        test_14: columnScores.col14,
        test_13_14: columnScores.col13 + columnScores.col14,
        test_14_wrong: columnScores.col13Wrong,
    };
    const totalRaw = raw.test_1_2 + raw.test_3_4 + raw.test_5_6 + raw.test_7_10 + raw.test_11_12 + raw.test_13_14;
    const total = (LPS_B_SCORE_TABLES[ageKey].total as LpsBTotalScoreRow[]).find((score) => matchesScore(score, totalRaw)) ?? null;
    const iq =
        total?.t === null || total?.t === undefined
            ? null
            : (LPS_B_IQ_BY_T_RANGES.find((range) => total.t >= range.minT && total.t <= range.maxT)?.iq ?? null);

    const rows: Array<{ key: LpsBScoreKey; label: string }> = [
        { key: 'test_1_2', label: '1-2 Allgemeinbildung' },
        { key: 'test_3_4', label: '3-4 logisch-schlussfolgerndes Denken' },
        { key: 'test_5_6', label: '5-6 Sprachproduktion/-verständnis' },
        { key: 'test_7_10', label: '7-10 technische Begabung' },
        { key: 'test_11_12', label: '11-12 grafisch-gestalterische Fähigkeit' },
        { key: 'test_13', label: '13 kurzzeitige Konzentration' },
        { key: 'test_14', label: '14 Arbeitstempo' },
        { key: 'test_14_wrong', label: '−13 Arbeitssorgfalt' },
    ];

    return {
        rows: rows.map((row) => ({ ...row, value: lookupLpsColumn(ageKey, row.key, raw[row.key]) })),
        totalRaw,
        totalT: total?.t ?? null,
        percentile: total?.pr ?? null,
        iq,
    };
}

function btTotal(entry: ReturnType<typeof latestResult>) {
    const result = entry?.result?.result_json;
    if (!result) return null;

    const scoring = result.scoring?.total ?? result.scoring ?? {};
    const firstWindowScoring = result.scoring?.first_30_minutes ?? {};
    const manual = manualScoreMap(entry.result);
    const hasStoredManualScore = (key: string) => {
        const raw = manual[key];
        return raw !== null && raw !== undefined && String(raw).trim() !== '';
    };
    const hasAnyBtManualScore = [1, 2, 3, 4, 5, 6].some((task) =>
        [`bt_q${task}_manual_points`, `bt_q${task}_first_30_manual_points`, `bt_q${task}_after_30_manual_points`].some(hasStoredManualScore),
    );
    const hasBtScoring = ['q1', 'q3', 'q4', 'q5', 'q6'].some((key) => numberValue(scoring[key]?.points) !== null);
    const directTotal = numberValue(result.total_score);

    if ((!hasAnyBtManualScore || !hasBtScoring) && directTotal !== null) return directTotal;

    const taskTotal = (task: number, scoringKey: string, useAutoFallback = true) => {
        const firstKey = `bt_q${task}_first_30_manual_points`;
        const afterKey = `bt_q${task}_after_30_manual_points`;
        const legacyKey = `bt_q${task}_manual_points`;
        const hasFirst = hasStoredManualScore(firstKey);
        const hasAfter = hasStoredManualScore(afterKey);
        const firstManual = numberValue(manual[firstKey]);
        const afterManual = numberValue(manual[afterKey]);
        const legacyManual = numberValue(manual[legacyKey]);

        if (!useAutoFallback) {
            return hasFirst || hasAfter ? (firstManual ?? 0) + (afterManual ?? 0) : (legacyManual ?? 0);
        }

        const totalAuto = numberValue(scoring[scoringKey]?.points) ?? 0;
        const firstAuto = numberValue(firstWindowScoring[scoringKey]?.points) ?? 0;
        const afterAuto = Math.max(0, totalAuto - firstAuto);

        if (hasFirst || hasAfter) {
            return (hasFirst ? (firstManual ?? 0) : firstAuto) + (hasAfter ? (afterManual ?? 0) : afterAuto);
        }

        return legacyManual ?? totalAuto;
    };

    return taskTotal(1, 'q1') + taskTotal(2, 'q2', false) + taskTotal(3, 'q3') + taskTotal(4, 'q4') + taskTotal(5, 'q5') + taskTotal(6, 'q6');
}

function lmtInterpretation(group: string, value: number | null) {
    if (value === null) return '';
    if (group === 'L1') {
        if (value <= 30) return 'nicht motiviert, unwillig';
        if (value <= 39) return 'wenig motiviert, kaum leistungsbereit';
        if (value <= 60) return 'Ø motiviert, leistungsorientiert';
        if (value <= 65) return 'motiviert, leistungsbereit';
        return '(sehr) motiviert, leistungswillig';
    }
    if (group === 'L2') {
        if (value <= 39) return 'interessenabhängig';
        if (value <= 44) return 'tendenziell interessenabhängig';
        if (value <= 55) return 'Ø ehrgeizig und ausdauernd';
        if (value <= 60) return 'tendenziell interessenunabhängig';
        return 'interessenunabhängig';
    }
    if (group === 'F-') {
        if (value <= 39) return 'keine prüfungsängstliche Neigung';
        if (value <= 44) return 'geringe prüfungsängstliche Neigung';
        if (value <= 55) return 'Ø';
        if (value <= 60) return 'tendenziell prüfungsängstlich';
        return 'Misserfolgsfurcht, Prüfungsangst führt zu Leistungseinschränkung';
    }
    if (value <= 39) return 'keine prüfungsförderlichen Strategien';
    if (value <= 44) return 'einige prüfungsförderliche Strategien';
    if (value <= 55) return 'Ø';
    if (value <= 60) return 'tendenziell guter Einsatz vorhandener Strategien';
    return 'prüfungsförderliche Strategien führen zu Leistungssteigerung';
}

const AVEM_INTERVALS = [
    ['6-7', '8-9', '10-12', '13-14', '15-17', '18-19', '20-22', '23-24', '25-30'],
    ['6-10', '11-12', '13-14', '15-17', '18-19', '20-22', '23-25', '26-27', '28-30'],
    ['6-10', '11-13', '14-15', '16-17', '18-19', '20-22', '23-24', '25-27', '28-30'],
    ['6-14', '15-17', '18-19', '20-21', '22-23', '24-25', '26-27', '28-29', '30'],
    ['6-8', '9-11', '12-14', '15-17', '18-19', '20-21', '22-24', '25-26', '27-30'],
    ['6-7', '8-9', '10-11', '12-13', '14-16', '17-18', '19-20', '21-23', '24-30'],
    ['6-16', '17-18', '19', '20-21', '22-23', '24-25', '26-27', '28-29', '30'],
    ['6-12', '13-14', '15-17', '18-19', '20-21', '22-23', '24-25', '26-27', '28-30'],
    ['6-15', '16-17', '18-19', '20-21', '22-23', '24-25', '26-27', '28-29', '30'],
    ['6-14', '15-17', '18-19', '20-21', '22-23', '24-25', '26-27', '28', '29-30'],
    ['6-14', '15-17', '18-19', '20-22', '23-24', '25-26', '27-28', '29', '30'],
];
const AVEM_REVERSED = new Set([23, 56, 13, 16, 49, 60, 19, 30, 31, 54, 22, 33, 55]);

function avemStanines(result?: Record<string, any> | null) {
    const direct = Array.isArray(result?.stanines) ? result.stanines : Array.isArray(result?.group_stanines) ? result.group_stanines : null;
    if (direct?.length === 11) return direct.map(numberValue);

    const answers = new Map<number, number>();
    (result?.answers ?? []).forEach((answer: any) => {
        const number = Number(answer.number);
        const value = Number(answer.answer ?? answer.user_answer);
        if (number >= 1 && number <= 66 && value >= 1 && value <= 5) answers.set(number, value);
    });

    return Array.from({ length: 11 }, (_, scaleIndex) => {
        const items = Array.from({ length: 6 }, (_, itemIndex) => scaleIndex + 1 + itemIndex * 11);
        const values = items.flatMap((item) => {
            const value = answers.get(item);
            return value ? [AVEM_REVERSED.has(item) ? 6 - value : value] : [];
        });
        if (!values.length) return null;
        const normalized = Math.round((values.reduce((total, value) => total + value, 0) / values.length) * 6);
        const index = AVEM_INTERVALS[scaleIndex].findIndex((range) => {
            const [minimum, maximum = minimum] = range.split('-').map(Number);
            return normalized >= minimum && normalized <= maximum;
        });
        return index >= 0 ? index + 1 : null;
    });
}

const KONZ_CORRECT = [
    ['13', '26', '133', '39', '125', '50', '480', '210', '11,25', '242'],
    ['3', '1', '3', '2', '2', '1', '2', '4', '1', '3'],
    ['1', '4', '3', '1', '3', '3', '0', '2', '2', '5', '2', '3', '2', '2', '4', '3', '3', '1', '3', '3', '0', '2'],
    ['12', '11', '13', '10', '11', '9', '16', '14', '13', '10', '15', '15', '12', '13'],
    ['7', '4', '8', '5', '7', '9', '7', '10', '10', '9'],
];

function concentrationValues(result?: Record<string, any> | null) {
    if (!result) return null;
    const wrong = KONZ_CORRECT.map((correct, pageIndex) => {
        const answers = result[`page${pageIndex + 1}`] ?? [];
        return correct.reduce((total, answer, answerIndex) => total + (String(answers[answerIndex] ?? '').trim() === answer ? 0 : 1), 0);
    });
    const logic = wrong[0] + wrong[1];
    const concentration = wrong[2] + wrong[3] + wrong[4];
    const total = logic + concentration;
    return {
        columns: [wrong[0], wrong[1], wrong[2], wrong[3] + wrong[4]],
        logic,
        concentration,
        total,
        performancePercent: Math.round(((66 - total) / 66) * 100),
    };
}

export function buildEntranceAnalysis(assignments: Assignment[] = [], profile?: { age?: number | null } | null) {
    const lpsEntry = latestResult(assignments, ['LPS-B']);
    const brtEntry = latestResult(assignments, ['BRT-A', 'BRT-B']);
    const mrtEntry = latestResult(assignments, ['MRT-A', 'MRT-B']);
    const btEntry = latestResult(assignments, ['BT']);
    const fpiEntry = latestResult(assignments, ['FPI-R']);
    const lmtEntry = latestResult(assignments, ['LMT']);
    const bitEntry = latestResult(assignments, ['BIT-2']);
    const concentrationEntry = latestResult(assignments, ['628', '628 TEST', 'KONZENTRATIONSTEST', '628 08.03']);
    const avemEntry = latestResult(assignments, ['AVEM']);
    const lmtResult = lmtEntry?.result?.result_json;
    const lmtRows = [
        ['L1', 'Leistungsstreben: Motivation/-wille'],
        ['L2', 'Ausdauer/Fleiß'],
        ['F-', 'Misserfolgsfurcht: leistungshemmend'],
        ['F+', 'Misserfolgsfurcht: leistungsfördernd'],
    ] as const;

    return {
        lps: lpsAnalysis(lpsEntry, profile?.age),
        brtT: numberValue(brtEntry?.result?.result_json?.twert),
        mrtPercentile: numberValue(mrtEntry?.result?.result_json?.prozentrang),
        btRaw: btTotal(btEntry),
        fpi: FPI_ROWS.map((label, index) => ({
            label,
            value: numberValue(fpiEntry?.result?.result_json?.stanines?.[index]),
        })),
        lmt: lmtRows.map(([key, label]) => {
            const value = numberValue(lmtResult?.group_t_values?.[key]);
            return { key, label, value, interpretation: lmtInterpretation(key, value) };
        }),
        bit: BIT_ROWS.map(([key, label]) => ({
            key,
            label,
            value: numberValue(bitEntry?.result?.result_json?.group_percentiles?.[key]),
        })),
        concentration: concentrationValues(concentrationEntry?.result?.result_json),
        avem: AVEM_ROWS.map((label, index) => ({
            label,
            value: avemStanines(avemEntry?.result?.result_json)?.[index] ?? null,
        })),
    };
}

export function emptyObservations(): ObservationFields {
    return {
        instruction_understanding: '',
        work_method: '',
        work_speed: '',
        group_behavior: '',
        remarks: '',
    };
}
