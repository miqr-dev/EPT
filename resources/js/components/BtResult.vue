<script setup lang="ts">
import { Input } from '@/components/ui/input';
import axios from 'axios';
import { computed, ref, watch, type ComputedRef, type Ref } from 'vue';

const props = withDefaults(
    defineProps<{
        testResultId?: number | null;
        manualScores?: Record<string, number | string | null>;
        results?: Record<string, any> | null;
        showAnswers?: boolean;
        forceOpenAnswers?: boolean;
        pdfMode?: boolean;
        answersOnly?: boolean;
    }>(),
    {
        manualScores: () => ({}),
        results: null,
        showAnswers: true,
        forceOpenAnswers: false,
        pdfMode: false,
        answersOnly: false,
    },
);

const emit = defineEmits<{
    'manual-score-updated': [{ key: string; value: number | null }];
}>();

type ManualScorePdfEntry = {
    label: string;
    value: string;
    tone: 'regular' | 'late';
};

type ManualScoreControl = {
    taskNumber: number;
    firstInput: Ref<string>;
    afterInput: Ref<string>;
    firstManualPoints: ComputedRef<number | null>;
    afterManualPoints: ComputedRef<number | null>;
    totalManualPoints: ComputedRef<number | null>;
    firstAutoPoints: ComputedRef<number>;
    afterAutoPoints: ComputedRef<number>;
    firstEffectivePoints: ComputedRef<number>;
    totalEffectivePoints: ComputedRef<number>;
    pdfEntries: ComputedRef<ManualScorePdfEntry[]>;
    refresh: (next?: Record<string, number | string | null>) => void;
    sanitizeFirst: (event: Event) => void;
    sanitizeAfter: (event: Event) => void;
    persistFirst: () => Promise<void>;
    persistAfter: () => Promise<void>;
};

const days = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag'];
const q3Denominations = [
    { key: '100', label: '100€', expected: 64 },
    { key: '50', label: '50€', expected: 3 },
    { key: '20', label: '20€', expected: 6 },
    { key: '10', label: '10€', expected: 3 },
    { key: '5', label: '5€', expected: 3 },
    { key: '2', label: '2€', expected: 4 },
    { key: '1', label: '1€', expected: 5 },
    { key: '0.5', label: '0,50€', expected: 4 },
] as const;

const q1Assignments = computed<Record<string, string | null>>(() => props.results?.assignments ?? {});
const q3CashAnswers = computed<Record<string, string>>(() => props.results?.cash_answers ?? {});
const q4FolderAnswers = computed<Record<number, string>>(() => props.results?.folder_answers ?? {});
const q5StampDays = computed<Record<number, boolean>>(() => props.results?.stamp_answer_days ?? {});
const q6RouteAssignments = computed<Record<string, string | null>>(() => props.results?.route_assignments ?? {});
const q6RouteTimes = computed<Record<string, string>>(() => props.results?.route_times ?? {});
const q6RouteTotals = computed<Record<string, string>>(() => props.results?.route_totals ?? {});
const q6Scoring = computed(() => props.results?.scoring?.q6 ?? null);
const firstWindowQ6Scoring = computed(() => props.results?.scoring?.first_30_minutes?.q6 ?? null);
const firstWindowResults = computed<Record<string, any>>(() => props.results?.first_30_minute_answers ?? props.results ?? {});
const answerTimes = computed<Record<string, Record<string, number>>>(() => props.results?.answer_times ?? {});

function parseManualPointsInput(value: string) {
    if (value === '') return null;
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : null;
}

function addManualParts(first: number | null, after: number | null) {
    if (first == null && after == null) return null;
    return (first ?? 0) + (after ?? 0);
}

const oneSyllableNames = new Set(['Fuchs', 'Hans', 'Kurz', 'Mann', 'Pahl', 'Paul', 'Pees', 'Roth']);
const twoSyllableNames = new Set(['Basten', 'Bauer', 'Bertram', 'Bolte', 'Krämer', 'Kühne', 'Müller', 'Schneider', 'Schuster']);
const threeSyllableNames = new Set(['Angermann', 'Berkelhahn', 'Diesterweg', 'Eschweiler', 'Hamburger', 'Hamelring', 'Kleininger', 'Petersen']);

function buildCellKey(shift: 'early' | 'late', slot: number, day: string) {
    return `${shift}-${slot}-${day}`;
}

function toNumber(value: unknown): number | null {
    if (value == null || value === '') return null;
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : null;
}

function formatTime(seconds?: number | null) {
    if (seconds == null || !Number.isFinite(seconds)) return '–';
    const total = Math.max(0, Math.floor(seconds));
    const m = Math.floor(total / 60);
    const s = total % 60;
    return `${m}:${s.toString().padStart(2, '0')}`;
}

function answerWasAfter30(section: string, key: string | number) {
    const timestamp = Number(answerTimes.value?.[section]?.[String(key)]);
    return Number.isFinite(timestamp) && timestamp >= 30 * 60;
}

function answerClass(section: string, key: string | number) {
    return answerWasAfter30(section, key) ? 'text-blue-700 font-semibold' : '';
}

function calculateQ1Stats(source: Record<string, any> | null | undefined) {
    const sourceAssignments = (source?.assignments ?? {}) as Record<string, string | null>;
    const orderedSlots: Array<{ shift: 'early' | 'late'; slot: number }> = [
        { shift: 'early', slot: 1 },
        { shift: 'early', slot: 2 },
        { shift: 'late', slot: 1 },
        { shift: 'late', slot: 2 },
        { shift: 'late', slot: 3 },
    ];
    const firstAppearanceNames = new Set<string>();
    let earlyTwoSyllable = 0;
    let earlyThreeSyllable = 0;
    let lateOneSyllable = 0;
    let lateThreeSyllable = 0;

    orderedSlots.forEach(({ shift, slot }) => {
        days.forEach((day) => {
            const name = sourceAssignments[buildCellKey(shift, slot, day)];
            if (!name || firstAppearanceNames.has(name)) return;
            firstAppearanceNames.add(name);

            if (shift === 'early') {
                if (twoSyllableNames.has(name)) earlyTwoSyllable += 1;
                if (threeSyllableNames.has(name)) earlyThreeSyllable += 1;
            } else {
                if (oneSyllableNames.has(name)) lateOneSyllable += 1;
                if (threeSyllableNames.has(name)) lateThreeSyllable += 1;
            }
        });
    });

    const points =
        (earlyTwoSyllable === 9 ? 3 : 0) + (earlyThreeSyllable === 1 ? 1 : 0) + (lateOneSyllable === 8 ? 3 : 0) + (lateThreeSyllable === 7 ? 2 : 0);

    return { earlyTwoSyllable, earlyThreeSyllable, lateOneSyllable, lateThreeSyllable, points, max: 9 };
}

const q1Stats = computed(() => calculateQ1Stats(props.results));
const firstWindowQ1Stats = computed(() => calculateQ1Stats(firstWindowResults.value));

function calculateQ3Score(source: Record<string, any> | null | undefined) {
    const cashAnswers = (source?.cash_answers ?? {}) as Record<string, string>;
    const mistakes = q3Denominations
        .map((denomination) => {
            const actual = toNumber(cashAnswers[denomination.key]);
            if (actual === denomination.expected) return null;
            return `${denomination.label}: ${actual ?? 'leer'} statt ${denomination.expected}`;
        })
        .filter((value): value is string => value !== null);

    return { points: q3Denominations.length - mistakes.length, max: q3Denominations.length, mistakes };
}

const q3Score = computed(() => calculateQ3Score(props.results));
const firstWindowQ3Score = computed(() => calculateQ3Score(firstWindowResults.value));

const btAlphabet = 'ABCDEFGHJKLMNOPQRSTUVWXYZ';
const q4LetterWeights: Record<string, number> = {
    A: 15,
    F: 15,
    W: 15,
    B: 15,
    G: 15,
    J: 15,
    X: 15,
    Y: 15,
    C: 15,
    H: 15,
    Z: 15,
    D: 15,
    R: 20,
    P: 20,
    Q: 20,
    N: 30,
    K: 30,
    S: 30,
    V: 30,
    T: 30,
    U: 30,
    O: 30,
    L: 30,
    M: 60,
    E: 60,
};

function normalizeFolderLetters(value: string) {
    return value
        .toUpperCase()
        .replace(/[^A-Z]/g, '')
        .split('')
        .filter((char, index, arr) => btAlphabet.includes(char) && arr.indexOf(char) === index);
}

function isAlphabetical(chars: string[]) {
    return chars.every((char, index) => index === 0 || btAlphabet.indexOf(chars[index - 1]) <= btAlphabet.indexOf(char));
}

function calculateQ4Score(source: Record<string, any> | null | undefined) {
    const folderAnswers = (source?.folder_answers ?? {}) as Record<string, string>;
    const answers = Array.from({ length: 10 }, (_, index) => normalizeFolderLetters(String(folderAnswers[String(index + 1)] ?? '')));
    const seenLetters = new Set<string>();
    const folderWeights = answers.map((chars) =>
        chars.reduce((sum, char) => {
            if (seenLetters.has(char)) return sum;
            seenLetters.add(char);
            return sum + (q4LetterWeights[char] ?? 0);
        }, 0),
    );

    const correctFolderCount = folderWeights.filter((weight) => weight === 60).length;
    const alphabeticalOrderMet = answers.every((chars) => isAlphabetical(chars));
    const folderOrderMet = answers.every((chars, index) => {
        if (index === answers.length - 1) return true;
        const currentIndices = chars.map((char) => btAlphabet.indexOf(char));
        const nextIndices = answers[index + 1].map((char) => btAlphabet.indexOf(char));
        if (!currentIndices.length || !nextIndices.length) return false;
        return Math.max(...currentIndices) < Math.min(...nextIndices);
    });

    let points = 0;
    if (correctFolderCount >= 1 && correctFolderCount <= 4) points = 2;
    else if (correctFolderCount >= 5 && correctFolderCount <= 9) points = 4;
    else if (correctFolderCount === 10) points = 6;
    if (correctFolderCount === 10 && alphabeticalOrderMet) points = folderOrderMet ? 8 : 7;

    const mistakes: string[] = [];
    if (correctFolderCount < 10) mistakes.push(`${10 - correctFolderCount} Ordner nicht korrekt auf 10% verteilt`);
    if (correctFolderCount === 10 && !alphabeticalOrderMet) mistakes.push('alphabetische Reihenfolge in mindestens einem Ordner nicht eingehalten');
    if (correctFolderCount === 10 && alphabeticalOrderMet && !folderOrderMet) mistakes.push('Ordner-Reihenfolge untereinander nicht eingehalten');

    return { points, max: 8, mistakes };
}

const q4Score = computed(() => calculateQ4Score(props.results));
const firstWindowQ4Score = computed(() => calculateQ4Score(firstWindowResults.value));

const q5ExpectedDays = new Set([5, 7, 10]);
const q5DayPoints: Record<number, number> = { 5: 2, 7: 2, 10: 4 };
function calculateQ5Score(source: Record<string, any> | null | undefined) {
    const stampDays = (source?.stamp_answer_days ?? {}) as Record<string, boolean>;
    const selectedDays = Array.from({ length: 10 }, (_, idx) => idx + 1).filter((day) => stampDays[String(day)] === true);
    const points = selectedDays.reduce((total, day) => total + (q5ExpectedDays.has(day) ? q5DayPoints[day] : 0), 0);
    const missing = [5, 7, 10].filter((day) => !selectedDays.includes(day));
    const wrong = selectedDays.filter((day) => !q5ExpectedDays.has(day));
    const mistakes: string[] = [];
    if (missing.length) mistakes.push(`fehlende korrekte Tage: ${missing.join(', ')}`);
    if (wrong.length) mistakes.push(`falsch zusätzlich markiert: ${wrong.join(', ')}`);
    return { points, max: 8, mistakes };
}

const q5Score = computed(() => calculateQ5Score(props.results));
const firstWindowQ5Score = computed(() => calculateQ5Score(firstWindowResults.value));

function hasStoredManualScore(key: string) {
    const raw = props.manualScores?.[key];
    return raw !== null && raw !== undefined && String(raw).trim() !== '';
}

function manualEntry(label: string, value: string, tone: 'regular' | 'late' = 'regular'): ManualScorePdfEntry {
    return { label, value, tone };
}

function hasQ6TableInput(source: Record<string, any> | null | undefined) {
    const routeAssignments = (source?.route_assignments ?? {}) as Record<string, string | null>;
    const routeTimes = (source?.route_times ?? {}) as Record<string, string>;
    const routeTotals = (source?.route_totals ?? {}) as Record<string, string>;

    const hasRouteRowInput = Array.from({ length: 6 }, (_, index) => index + 1).some((row) => {
        const fromRaw = String(routeAssignments[`route-from-${row}`] ?? '').trim();
        const toRaw = String(routeAssignments[`route-to-${row}`] ?? '').trim();
        const wayRaw = String(routeTimes[`route-time-${row}`] ?? '').trim();
        const msgRaw = String(routeTimes[`route-msg-${row}`] ?? '').trim();
        const hasEditableFromInput = row !== 1 && fromRaw !== '';
        return hasEditableFromInput || toRaw !== '' || wayRaw !== '' || msgRaw !== '';
    });

    return hasRouteRowInput || ['totalWay', 'totalMsg', 'returnWay'].some((key) => String(routeTotals[key] ?? '').trim() !== '');
}

function formatManualInputValue(value: unknown) {
    const parsed = toNumber(value);
    return parsed == null ? '' : `${parsed}`;
}

function buildQ6Score(scoring: any, hasTableInput = true) {
    let points = toNumber(scoring?.points) ?? 0;
    const max = toNumber(scoring?.max) ?? 8;
    const explanation: string[] = [];

    if (!scoring) {
        explanation.push('Keine automatische Detailwertung vorhanden.');
        return { points, max, explanation };
    }

    explanation.push(`Korrekte Reihen: ${scoring.rowCorrectCount ?? 0}`);
    explanation.push(`Alle 6 Personen benachrichtigt: ${scoring.allPeopleNotified ? 'ja' : 'nein'}`);
    explanation.push(`Telefon sinnvoll eingesetzt: ${scoring.hasPhoneUsage ? 'ja' : 'nein'}`);
    explanation.push(`Bestzeit erreicht: ${scoring.isBestTime ? 'ja' : 'nein'}`);
    explanation.push(`Eintrag vorhanden: ${hasTableInput ? 'ja' : 'nein'}`);

    if (!hasTableInput) {
        points = 0;
    }

    explanation.push(`Vergabtes Ergebnis: ${points}/${max}`);

    return { points, max, explanation };
}

const q6Score = computed(() => buildQ6Score(q6Scoring.value, hasQ6TableInput(props.results)));
const firstWindowQ6Score = computed(() => buildQ6Score(firstWindowQ6Scoring.value ?? q6Scoring.value, hasQ6TableInput(firstWindowResults.value)));

function buildManualScoreTask(
    taskNumber: number,
    firstAutoPointsValue: () => number,
    totalAutoPointsValue: () => number,
    useAutoFallback = true,
): ManualScoreControl {
    const totalKey = `bt_q${taskNumber}_manual_points`;
    const firstKey = `bt_q${taskNumber}_first_30_manual_points`;
    const afterKey = `bt_q${taskNumber}_after_30_manual_points`;
    const firstInput = ref('');
    const afterInput = ref('');
    const firstManualPoints = computed(() => parseManualPointsInput(firstInput.value));
    const afterManualPoints = computed(() => parseManualPointsInput(afterInput.value));
    const totalManualPoints = computed(() => addManualParts(firstManualPoints.value, afterManualPoints.value));
    const firstAutoPoints = computed(() => Math.max(0, firstAutoPointsValue()));
    const afterAutoPoints = computed(() => Math.max(0, totalAutoPointsValue() - firstAutoPoints.value));
    const firstEffectivePoints = computed(() =>
        useAutoFallback ? (firstManualPoints.value ?? firstAutoPoints.value) : (firstManualPoints.value ?? 0),
    );
    const afterEffectivePoints = computed(() =>
        useAutoFallback ? (afterManualPoints.value ?? afterAutoPoints.value) : (afterManualPoints.value ?? 0),
    );
    const totalEffectivePoints = computed(() => firstEffectivePoints.value + afterEffectivePoints.value);
    const pdfEntries = computed(() => {
        const entries: ManualScorePdfEntry[] = [];

        if (hasStoredManualScore(firstKey)) {
            entries.push(manualEntry('bis 30 Min.', firstInput.value));
        }

        if (hasStoredManualScore(afterKey)) {
            entries.push(manualEntry('nach 30 Min.', afterInput.value, 'late'));
        }

        if (entries.length === 0 && hasStoredManualScore(totalKey)) {
            const totalValue = useAutoFallback ? String(totalEffectivePoints.value) : String(totalManualPoints.value ?? '');
            entries.push(manualEntry('gesamt', totalValue, 'late'));
        }

        return entries.filter((entry) => entry.value.trim() !== '');
    });

    function refresh(next: Record<string, number | string | null> = props.manualScores) {
        const firstRaw = next?.[firstKey];
        const afterRaw = next?.[afterKey];
        const legacyTotal = toNumber(next?.[totalKey]);

        firstInput.value = formatManualInputValue(firstRaw);
        afterInput.value = formatManualInputValue(afterRaw);

        if (!useAutoFallback) {
            if (afterInput.value === '' && firstInput.value === '') {
                afterInput.value = formatManualInputValue(legacyTotal);
            }
            return;
        }

        if (firstInput.value === '') {
            firstInput.value = `${firstAutoPoints.value}`;
        }

        if (afterInput.value === '') {
            afterInput.value =
                legacyTotal != null && (firstRaw == null || firstRaw === '')
                    ? `${Math.max(0, legacyTotal - firstAutoPoints.value)}`
                    : `${afterAutoPoints.value}`;
        }
    }

    function sanitizeFirst(event: Event) {
        sanitizeManualPointsInput(event, (value) => {
            firstInput.value = value;
        });
    }

    function sanitizeAfter(event: Event) {
        sanitizeManualPointsInput(event, (value) => {
            afterInput.value = value;
        });
    }

    async function persistFirst() {
        await persistManualPoints(firstKey, firstManualPoints.value);
    }

    async function persistAfter() {
        await persistManualPoints(afterKey, afterManualPoints.value);
    }

    return {
        taskNumber,
        firstInput,
        afterInput,
        firstManualPoints,
        afterManualPoints,
        totalManualPoints,
        firstAutoPoints,
        afterAutoPoints,
        firstEffectivePoints,
        totalEffectivePoints,
        pdfEntries,
        refresh,
        sanitizeFirst,
        sanitizeAfter,
        persistFirst,
        persistAfter,
    };
}

const q1ManualScore = buildManualScoreTask(
    1,
    () => firstWindowQ1Stats.value.points,
    () => q1Stats.value.points,
);
const q2ManualScore = buildManualScoreTask(
    2,
    () => 0,
    () => 0,
    false,
);
const q3ManualScore = buildManualScoreTask(
    3,
    () => firstWindowQ3Score.value.points,
    () => q3Score.value.points,
);
const q4ManualScore = buildManualScoreTask(
    4,
    () => firstWindowQ4Score.value.points,
    () => q4Score.value.points,
);
const q5ManualScore = buildManualScoreTask(
    5,
    () => firstWindowQ5Score.value.points,
    () => q5Score.value.points,
);
const q6ManualScore = buildManualScoreTask(
    6,
    () => firstWindowQ6Score.value.points,
    () => q6Score.value.points,
);
const manualScoreTasks: ManualScoreControl[] = [q1ManualScore, q2ManualScore, q3ManualScore, q4ManualScore, q5ManualScore, q6ManualScore];

watch(
    () => [props.manualScores, ...manualScoreTasks.flatMap((task) => [task.firstAutoPoints.value, task.afterAutoPoints.value])] as const,
    ([next]) => {
        const manualScores = next as Record<string, number | string | null>;
        manualScoreTasks.forEach((task) => task.refresh(manualScores));
    },
    { immediate: true, deep: true },
);

const totalPoints = computed(
    () =>
        q1ManualScore.totalEffectivePoints.value +
        (q2ManualScore.totalManualPoints.value ?? 0) +
        q3ManualScore.totalEffectivePoints.value +
        q4ManualScore.totalEffectivePoints.value +
        q5ManualScore.totalEffectivePoints.value +
        q6ManualScore.totalEffectivePoints.value,
);
const firstWindowTotalPoints = computed(
    () =>
        q1ManualScore.firstEffectivePoints.value +
        (q2ManualScore.firstManualPoints.value ?? 0) +
        q3ManualScore.firstEffectivePoints.value +
        q4ManualScore.firstEffectivePoints.value +
        q5ManualScore.firstEffectivePoints.value +
        q6ManualScore.firstEffectivePoints.value,
);
const totalTime = computed(() => formatTime(toNumber(props.results?.total_time_seconds)));

function sanitizeManualPointsInput(event: Event, setValue: (value: string) => void) {
    const target = event.target as HTMLInputElement;
    const sanitized = target.value.replace(/\D/g, '').slice(0, 2);
    setValue(sanitized);
    target.value = sanitized;
}

async function persistManualPoints(key: string, value: number | null) {
    emit('manual-score-updated', { key, value });
    if (!props.testResultId) return;

    await axios.put(route('test-results.manual-scores.update', { testResult: props.testResultId }), {
        key,
        value,
    });
}
</script>

<template>
    <div :class="['bt-result w-full space-y-3', { 'bt-result--pdf': pdfMode }, !forceOpenAnswers && !pdfMode ? 'lg:w-1/2' : '']">
        <div class="bt-evaluation-card rounded-lg border-2 border-black bg-white p-3 text-sm">
            <div v-if="!answersOnly" class="bt-summary-header mb-3 flex items-center justify-between border-b border-black pb-2">
                <div class="font-semibold">BT – Auswertung Form A</div>
                <div class="text-right">
                    <div class="text-sm">
                        <span class="font-semibold">Rohwert bis 30 Min.:</span>
                        <span class="bt-window-score text-xl font-bold">{{ firstWindowTotalPoints }}</span>
                        <span class="font-semibold">/ 50</span>
                    </div>
                    <div class="text-lg">
                        <span class="font-semibold">Rohwert gesamt:</span>
                        <span class="bt-total-score text-2xl font-bold">{{ totalPoints }}</span>
                        <span class="font-semibold">/ 50</span>
                    </div>
                    <div><span class="font-semibold">Benötigte Zeit:</span> {{ totalTime }}</div>
                    <div class="text-xs text-blue-700">Blau = nach 30 Minuten eingetragen</div>
                </div>
            </div>

            <table class="bt-score-table w-full border-collapse border border-black">
                <thead>
                    <tr>
                        <th class="border border-black p-2 text-left">Aufgabe</th>
                        <th class="w-24 border border-black p-2 text-center">bis 30 Min.</th>
                        <th class="w-24 border border-black p-2 text-center">gesamt</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-black p-2 align-top">
                            <div class="mb-2 font-semibold">Aufgabe 1</div>
                            <table class="mb-2 w-full border-collapse border border-black text-xs">
                                <thead>
                                    <tr>
                                        <th class="w-20 border border-black p-1 text-left">Dienst</th>
                                        <th v-for="day in days" :key="`q1-head-${day}`" class="border border-black p-1 text-center">{{ day }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th class="border border-black p-1 text-left">Früh 1</th>
                                        <td
                                            v-for="day in days"
                                            :key="`q1-e1-${day}`"
                                            :class="[
                                                'border border-black p-1 text-center',
                                                answerClass('assignments', buildCellKey('early', 1, day)),
                                            ]"
                                        >
                                            {{ q1Assignments[buildCellKey('early', 1, day)] ?? '—' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="border border-black p-1 text-left">Früh 2</th>
                                        <td
                                            v-for="day in days"
                                            :key="`q1-e2-${day}`"
                                            :class="[
                                                'border border-black p-1 text-center',
                                                answerClass('assignments', buildCellKey('early', 2, day)),
                                            ]"
                                        >
                                            {{ q1Assignments[buildCellKey('early', 2, day)] ?? '—' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="border border-black p-1 text-left">Spät 1</th>
                                        <td
                                            v-for="day in days"
                                            :key="`q1-l1-${day}`"
                                            :class="['border border-black p-1 text-center', answerClass('assignments', buildCellKey('late', 1, day))]"
                                        >
                                            {{ q1Assignments[buildCellKey('late', 1, day)] ?? '—' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="border border-black p-1 text-left">Spät 2</th>
                                        <td
                                            v-for="day in days"
                                            :key="`q1-l2-${day}`"
                                            :class="['border border-black p-1 text-center', answerClass('assignments', buildCellKey('late', 2, day))]"
                                        >
                                            {{ q1Assignments[buildCellKey('late', 2, day)] ?? '—' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="border border-black p-1 text-left">Spät 3</th>
                                        <td
                                            v-for="day in days"
                                            :key="`q1-l3-${day}`"
                                            :class="['border border-black p-1 text-center', answerClass('assignments', buildCellKey('late', 3, day))]"
                                        >
                                            {{ q1Assignments[buildCellKey('late', 3, day)] ?? '—' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <template v-if="!pdfMode">
                                <div>Frühdienst: zweisilbig {{ q1Stats.earlyTwoSyllable }}/9, dreisilbig {{ q1Stats.earlyThreeSyllable }}/1</div>
                                <div>Spätdienst: einsilbig {{ q1Stats.lateOneSyllable }}/8, dreisilbig {{ q1Stats.lateThreeSyllable }}/7</div>
                                <div class="mt-1 text-muted-foreground">Abzüge gemäß Soll-Verteilung.</div>
                            </template>
                            <div v-if="!pdfMode" class="bt-manual-controls mt-3 flex flex-wrap gap-3">
                                <label class="bt-manual-label flex flex-col gap-1 text-xs text-muted-foreground">
                                    bis 30 Min.
                                    <Input
                                        :model-value="q1ManualScore.firstInput.value"
                                        class="w-24 text-center"
                                        inputmode="numeric"
                                        @input="q1ManualScore.sanitizeFirst"
                                        @blur="q1ManualScore.persistFirst"
                                    />
                                </label>
                                <label class="bt-manual-label flex flex-col gap-1 text-xs text-blue-700">
                                    nach 30 Min.
                                    <Input
                                        :model-value="q1ManualScore.afterInput.value"
                                        class="w-24 text-center text-foreground"
                                        inputmode="numeric"
                                        @input="q1ManualScore.sanitizeAfter"
                                        @blur="q1ManualScore.persistAfter"
                                    />
                                </label>
                            </div>
                            <div v-else-if="q1ManualScore.pdfEntries.value.length" class="bt-pdf-manual-values">
                                <div
                                    v-for="entry in q1ManualScore.pdfEntries.value"
                                    :key="`q1-${entry.label}`"
                                    :class="['bt-pdf-manual-value', { 'bt-pdf-manual-value--late': entry.tone === 'late' }]"
                                >
                                    <span>{{ entry.label }}</span>
                                    <strong>{{ entry.value }}</strong>
                                </div>
                            </div>
                        </td>
                        <td class="border border-black p-2 text-center font-semibold">{{ q1ManualScore.firstEffectivePoints.value }}</td>
                        <td class="border border-black p-2 text-center font-semibold">{{ q1ManualScore.totalEffectivePoints.value }}</td>
                    </tr>

                    <tr>
                        <td class="border border-black p-2 align-top">
                            <div class="mb-2 font-semibold">Aufgabe 2</div>
                            <div v-if="!pdfMode" class="mb-2">Manuelle Bewertung durch Prüfer:in.</div>
                            <div v-if="!pdfMode" class="bt-manual-controls flex flex-wrap gap-3">
                                <label class="bt-manual-label flex flex-col gap-1 text-xs text-muted-foreground">
                                    bis 30 Min.
                                    <Input
                                        :model-value="q2ManualScore.firstInput.value"
                                        class="w-24 text-center"
                                        inputmode="numeric"
                                        @input="q2ManualScore.sanitizeFirst"
                                        @blur="q2ManualScore.persistFirst"
                                    />
                                </label>
                                <label class="bt-manual-label flex flex-col gap-1 text-xs text-blue-700">
                                    nach 30 Min.
                                    <Input
                                        :model-value="q2ManualScore.afterInput.value"
                                        class="w-24 text-center text-foreground"
                                        inputmode="numeric"
                                        @input="q2ManualScore.sanitizeAfter"
                                        @blur="q2ManualScore.persistAfter"
                                    />
                                </label>
                            </div>
                            <div v-else-if="q2ManualScore.pdfEntries.value.length" class="bt-pdf-manual-values">
                                <div
                                    v-for="entry in q2ManualScore.pdfEntries.value"
                                    :key="`q2-${entry.label}`"
                                    :class="['bt-pdf-manual-value', { 'bt-pdf-manual-value--late': entry.tone === 'late' }]"
                                >
                                    <span>{{ entry.label }}</span>
                                    <strong>{{ entry.value }}</strong>
                                </div>
                            </div>
                        </td>
                        <td class="border border-black p-2 text-center font-semibold">{{ q2ManualScore.firstManualPoints.value ?? '—' }}</td>
                        <td class="border border-black p-2 text-center font-semibold">{{ q2ManualScore.totalManualPoints.value ?? '—' }}</td>
                    </tr>

                    <tr>
                        <td class="border border-black p-2 align-top">
                            <div class="mb-2 font-semibold">Aufgabe 3</div>
                            <table class="w-full border-collapse border border-black text-xs">
                                <thead>
                                    <tr>
                                        <th class="border border-black p-1 text-left">Geldsorten</th>
                                        <th v-for="entry in q3Denominations" :key="entry.key" class="border border-black p-1 text-center">
                                            {{ entry.label }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th class="border border-black p-1 text-left">Anzahl</th>
                                        <td
                                            v-for="entry in q3Denominations"
                                            :key="`q3-${entry.key}`"
                                            :class="['border border-black p-1 text-center', answerClass('cash_answers', entry.key)]"
                                        >
                                            {{ q3CashAnswers[entry.key] || '—' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-if="!pdfMode" class="mt-1 text-muted-foreground">
                                Abzüge: {{ q3Score.mistakes.length ? q3Score.mistakes.join(' | ') : 'keine' }}.
                            </div>
                            <div v-if="!pdfMode" class="bt-manual-controls mt-3 flex flex-wrap gap-3">
                                <label class="bt-manual-label flex flex-col gap-1 text-xs text-muted-foreground">
                                    bis 30 Min.
                                    <Input
                                        :model-value="q3ManualScore.firstInput.value"
                                        class="w-24 text-center"
                                        inputmode="numeric"
                                        @input="q3ManualScore.sanitizeFirst"
                                        @blur="q3ManualScore.persistFirst"
                                    />
                                </label>
                                <label class="bt-manual-label flex flex-col gap-1 text-xs text-blue-700">
                                    nach 30 Min.
                                    <Input
                                        :model-value="q3ManualScore.afterInput.value"
                                        class="w-24 text-center text-foreground"
                                        inputmode="numeric"
                                        @input="q3ManualScore.sanitizeAfter"
                                        @blur="q3ManualScore.persistAfter"
                                    />
                                </label>
                            </div>
                            <div v-else-if="q3ManualScore.pdfEntries.value.length" class="bt-pdf-manual-values">
                                <div
                                    v-for="entry in q3ManualScore.pdfEntries.value"
                                    :key="`q3-${entry.label}`"
                                    :class="['bt-pdf-manual-value', { 'bt-pdf-manual-value--late': entry.tone === 'late' }]"
                                >
                                    <span>{{ entry.label }}</span>
                                    <strong>{{ entry.value }}</strong>
                                </div>
                            </div>
                        </td>
                        <td class="border border-black p-2 text-center font-semibold">{{ q3ManualScore.firstEffectivePoints.value }}</td>
                        <td class="border border-black p-2 text-center font-semibold">{{ q3ManualScore.totalEffectivePoints.value }}</td>
                    </tr>

                    <tr>
                        <td class="border border-black p-2 align-top">
                            <div class="mb-2 font-semibold">Aufgabe 4</div>
                            <table class="w-full border-collapse border border-black text-xs">
                                <thead>
                                    <tr>
                                        <th class="border border-black p-1 text-left">Ordner-Nr.</th>
                                        <th v-for="i in 10" :key="`h-${i}`" class="border border-black p-1 text-center">{{ i }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th class="border border-black p-1 text-left">Form A</th>
                                        <td
                                            v-for="i in 10"
                                            :key="`f-${i}`"
                                            :class="['border border-black p-1 text-center', answerClass('folder_answers', i)]"
                                        >
                                            {{ q4FolderAnswers[i] || '—' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-if="!pdfMode" class="mt-1 text-muted-foreground">
                                Abzüge: {{ q4Score.mistakes.length ? q4Score.mistakes.join(' | ') : 'keine' }}.
                            </div>
                            <div v-if="!pdfMode" class="bt-manual-controls mt-3 flex flex-wrap gap-3">
                                <label class="bt-manual-label flex flex-col gap-1 text-xs text-muted-foreground">
                                    bis 30 Min.
                                    <Input
                                        :model-value="q4ManualScore.firstInput.value"
                                        class="w-24 text-center"
                                        inputmode="numeric"
                                        @input="q4ManualScore.sanitizeFirst"
                                        @blur="q4ManualScore.persistFirst"
                                    />
                                </label>
                                <label class="bt-manual-label flex flex-col gap-1 text-xs text-blue-700">
                                    nach 30 Min.
                                    <Input
                                        :model-value="q4ManualScore.afterInput.value"
                                        class="w-24 text-center text-foreground"
                                        inputmode="numeric"
                                        @input="q4ManualScore.sanitizeAfter"
                                        @blur="q4ManualScore.persistAfter"
                                    />
                                </label>
                            </div>
                            <div v-else-if="q4ManualScore.pdfEntries.value.length" class="bt-pdf-manual-values">
                                <div
                                    v-for="entry in q4ManualScore.pdfEntries.value"
                                    :key="`q4-${entry.label}`"
                                    :class="['bt-pdf-manual-value', { 'bt-pdf-manual-value--late': entry.tone === 'late' }]"
                                >
                                    <span>{{ entry.label }}</span>
                                    <strong>{{ entry.value }}</strong>
                                </div>
                            </div>
                        </td>
                        <td class="border border-black p-2 text-center font-semibold">{{ q4ManualScore.firstEffectivePoints.value }}</td>
                        <td class="border border-black p-2 text-center font-semibold">{{ q4ManualScore.totalEffectivePoints.value }}</td>
                    </tr>

                    <tr>
                        <td class="border border-black p-2 align-top">
                            <div class="mb-2 font-semibold">Aufgabe 5</div>
                            <table class="w-full border-collapse border border-black text-xs">
                                <thead>
                                    <tr>
                                        <th class="border border-black p-1 text-left">Neue Briefmarken gekauft am</th>
                                        <th v-for="day in 10" :key="`d-${day}`" class="border border-black p-1 text-center">{{ day }}.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th class="border border-black p-1 text-left">Form A</th>
                                        <td
                                            v-for="day in 10"
                                            :key="`m-${day}`"
                                            :class="['border border-black p-1 text-center', answerClass('stamp_answer_days', day)]"
                                        >
                                            {{ q5StampDays[day] ? 'X' : '' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-if="!pdfMode" class="mt-1 text-muted-foreground">
                                Abzüge: {{ q5Score.mistakes.length ? q5Score.mistakes.join(' | ') : 'keine' }}.
                            </div>
                            <div v-if="!pdfMode" class="bt-manual-controls mt-3 flex flex-wrap gap-3">
                                <label class="bt-manual-label flex flex-col gap-1 text-xs text-muted-foreground">
                                    bis 30 Min.
                                    <Input
                                        :model-value="q5ManualScore.firstInput.value"
                                        class="w-24 text-center"
                                        inputmode="numeric"
                                        @input="q5ManualScore.sanitizeFirst"
                                        @blur="q5ManualScore.persistFirst"
                                    />
                                </label>
                                <label class="bt-manual-label flex flex-col gap-1 text-xs text-blue-700">
                                    nach 30 Min.
                                    <Input
                                        :model-value="q5ManualScore.afterInput.value"
                                        class="w-24 text-center text-foreground"
                                        inputmode="numeric"
                                        @input="q5ManualScore.sanitizeAfter"
                                        @blur="q5ManualScore.persistAfter"
                                    />
                                </label>
                            </div>
                            <div v-else-if="q5ManualScore.pdfEntries.value.length" class="bt-pdf-manual-values mt-3">
                                <div
                                    v-for="entry in q5ManualScore.pdfEntries.value"
                                    :key="`q5-${entry.label}`"
                                    :class="['bt-pdf-manual-value', { 'bt-pdf-manual-value--late': entry.tone === 'late' }]"
                                >
                                    <span>{{ entry.label }}</span>
                                    <strong>{{ entry.value }}</strong>
                                </div>
                            </div>
                        </td>
                        <td class="border border-black p-2 text-center font-semibold">{{ q5ManualScore.firstEffectivePoints.value }}</td>
                        <td class="border border-black p-2 text-center font-semibold">{{ q5ManualScore.totalEffectivePoints.value }}</td>
                    </tr>

                    <tr>
                        <td class="border border-black p-2 align-top">
                            <div class="mb-2 font-semibold">Aufgabe 6</div>
                            <table class="w-full border-collapse border border-black text-xs">
                                <thead>
                                    <tr>
                                        <th class="border border-black p-1 text-left">von</th>
                                        <th class="border border-black p-1 text-left">nach</th>
                                        <th class="border border-black p-1 text-center">Zeit (Weg)</th>
                                        <th class="border border-black p-1 text-center">Zeit (Nachr.)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in 6" :key="`r-${row}`">
                                        <td :class="['border border-black p-1', answerClass('route_assignments', `route-from-${row}`)]">
                                            {{ q6RouteAssignments[`route-from-${row}`] || '—' }}
                                        </td>
                                        <td :class="['border border-black p-1', answerClass('route_assignments', `route-to-${row}`)]">
                                            {{ q6RouteAssignments[`route-to-${row}`] || '—' }}
                                        </td>
                                        <td :class="['border border-black p-1 text-center', answerClass('route_times', `route-time-${row}`)]">
                                            {{ q6RouteTimes[`route-time-${row}`] || '—' }}
                                        </td>
                                        <td :class="['border border-black p-1 text-center', answerClass('route_times', `route-msg-${row}`)]">
                                            {{ q6RouteTimes[`route-msg-${row}`] || '—' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border border-black p-1 text-right" colspan="2">Gesamtzeit</td>
                                        <td :class="['border border-black p-1 text-center', answerClass('route_totals', 'totalWay')]">
                                            {{ q6RouteTotals.totalWay || '—' }}
                                        </td>
                                        <td :class="['border border-black p-1 text-center', answerClass('route_totals', 'totalMsg')]">
                                            {{ q6RouteTotals.totalMsg || '—' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border border-black p-1 text-right" colspan="2">anschl. zurück z. eig. Wohnung</td>
                                        <td :class="['border border-black p-1 text-center', answerClass('route_totals', 'returnWay')]">
                                            {{ q6RouteTotals.returnWay || '—' }}
                                        </td>
                                        <td class="border border-black p-1 text-center">—</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-if="!pdfMode" class="mt-1 text-muted-foreground">Punkte-Herleitung: {{ q6Score.explanation.join(' | ') }}</div>
                            <div v-if="!pdfMode" class="bt-manual-controls mt-3 flex flex-wrap gap-3">
                                <label class="bt-manual-label flex flex-col gap-1 text-xs text-muted-foreground">
                                    bis 30 Min.
                                    <Input
                                        :model-value="q6ManualScore.firstInput.value"
                                        class="w-24 text-center"
                                        inputmode="numeric"
                                        @input="q6ManualScore.sanitizeFirst"
                                        @blur="q6ManualScore.persistFirst"
                                    />
                                </label>
                                <label class="bt-manual-label flex flex-col gap-1 text-xs text-blue-700">
                                    nach 30 Min.
                                    <Input
                                        :model-value="q6ManualScore.afterInput.value"
                                        class="w-24 text-center text-foreground"
                                        inputmode="numeric"
                                        @input="q6ManualScore.sanitizeAfter"
                                        @blur="q6ManualScore.persistAfter"
                                    />
                                </label>
                            </div>
                            <div v-else-if="q6ManualScore.pdfEntries.value.length" class="bt-pdf-manual-values">
                                <div
                                    v-for="entry in q6ManualScore.pdfEntries.value"
                                    :key="`q6-${entry.label}`"
                                    :class="['bt-pdf-manual-value', { 'bt-pdf-manual-value--late': entry.tone === 'late' }]"
                                >
                                    <span>{{ entry.label }}</span>
                                    <strong>{{ entry.value }}</strong>
                                </div>
                            </div>
                        </td>
                        <td class="border border-black p-2 text-center font-semibold">{{ q6ManualScore.firstEffectivePoints.value }}</td>
                        <td class="border border-black p-2 text-center font-semibold">{{ q6ManualScore.totalEffectivePoints.value }}</td>
                    </tr>

                    <tr>
                        <td class="border border-black p-2 text-right text-lg font-bold">Rohwert</td>
                        <td class="bt-window-score border border-black p-2 text-center text-xl font-extrabold">{{ firstWindowTotalPoints }}</td>
                        <td class="bt-total-score border border-black p-2 text-center text-xl font-extrabold">{{ totalPoints }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
.bt-result {
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
}

.bt-window-score {
    color: #111827;
}

.bt-total-score {
    color: #1d4ed8;
}

.bt-result--pdf {
    font-size: 14px;
    line-height: 1.4;
}

.bt-result--pdf .bt-evaluation-card {
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 14px;
    box-shadow: none;
}

.bt-result--pdf .bt-summary-header {
    align-items: flex-start;
    gap: 18px;
    border-color: #d1d5db;
    margin-bottom: 12px;
    padding-bottom: 10px;
}

.bt-result--pdf .bt-summary-header > :first-child {
    font-size: 15px;
    line-height: 1.3;
}

.bt-result--pdf .bt-summary-header .text-right {
    min-width: 270px;
    color: #111827;
}

.bt-result--pdf .bt-summary-header .text-sm {
    font-size: 13.5px;
    line-height: 1.4;
}

.bt-result--pdf .bt-summary-header .text-lg {
    font-size: 15px;
    line-height: 1.45;
}

.bt-result--pdf .bt-summary-header .text-xs {
    margin-top: 2px;
    font-size: 12px;
    line-height: 1.35;
}

.bt-result--pdf .bt-score-table,
.bt-result--pdf .bt-score-table table {
    border-color: #9ca3af;
}

.bt-result--pdf .bt-score-table th,
.bt-result--pdf .bt-score-table td {
    border-color: #9ca3af;
}

.bt-result--pdf .bt-score-table > thead > tr > th {
    background: #f8fafc;
    padding: 7px 8px;
    font-size: 13px;
    line-height: 1.25;
}

.bt-result--pdf .bt-score-table > tbody > tr > td {
    padding: 7px 8px;
    vertical-align: top;
}

.bt-result--pdf .bt-score-table > tbody > tr > td:first-child {
    font-size: 13px;
}

.bt-result--pdf .bt-score-table > tbody > tr > td:nth-child(2),
.bt-result--pdf .bt-score-table > tbody > tr > td:nth-child(3) {
    width: 86px;
    font-size: 15px;
}

.bt-result--pdf .bt-score-table table {
    margin-top: 4px;
    margin-bottom: 6px;
    font-size: 12px;
    line-height: 1.25;
}

.bt-result--pdf .bt-score-table table th,
.bt-result--pdf .bt-score-table table td {
    padding: 4px 5px;
}

.bt-result--pdf .bt-score-table .text-xs {
    font-size: 12px;
    line-height: 1.3;
}

.bt-result--pdf .bt-score-table .text-muted-foreground {
    color: #4b5563;
}

.bt-result--pdf .bt-pdf-manual-values {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
}

.bt-result--pdf .bt-pdf-manual-value {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: #f8fafc;
    padding: 5px 8px;
    color: #111827;
    font-size: 12.5px;
    line-height: 1.2;
}

.bt-result--pdf .bt-pdf-manual-value--late {
    border-color: #bfdbfe;
    background: #eff6ff;
}

.bt-result--pdf .bt-pdf-manual-value span {
    color: #4b5563;
}

.bt-result--pdf .bt-pdf-manual-value strong {
    min-width: 18px;
    text-align: center;
    font-size: 14px;
}

@media print {
    .bt-result--pdf {
        break-inside: avoid;
    }
}
</style>
