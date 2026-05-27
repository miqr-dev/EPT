<script setup lang="ts">
import { Input } from '@/components/ui/input';
import axios from 'axios';
import { computed, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        testResultId?: number | null;
        manualScores?: Record<string, number | string | null>;
        results?: Record<string, any> | null;
        showAnswers?: boolean;
        forceOpenAnswers?: boolean;
    }>(),
    {
        manualScores: () => ({}),
        results: null,
        showAnswers: true,
        forceOpenAnswers: false,
    },
);

const emit = defineEmits<{
    'manual-score-updated': [{ key: string; value: number | null }];
}>();

const q2ManualScoreKey = 'bt_q2_manual_points';
const q2FirstWindowManualScoreKey = 'bt_q2_first_30_manual_points';
const q2AfterWindowManualScoreKey = 'bt_q2_after_30_manual_points';
const q5ManualScoreKey = 'bt_q5_manual_points';
const q5FirstWindowManualScoreKey = 'bt_q5_first_30_manual_points';
const q5AfterWindowManualScoreKey = 'bt_q5_after_30_manual_points';
const q2FirstWindowManualPointsInput = ref('');
const q2AfterWindowManualPointsInput = ref('');
const q5FirstWindowManualPointsInput = ref('');
const q5AfterWindowManualPointsInput = ref('');
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

const q2FirstWindowManualPoints = computed(() => parseManualPointsInput(q2FirstWindowManualPointsInput.value));
const q2AfterWindowManualPoints = computed(() => parseManualPointsInput(q2AfterWindowManualPointsInput.value));
const q2TotalManualPoints = computed(() => addManualParts(q2FirstWindowManualPoints.value, q2AfterWindowManualPoints.value));

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

const q5AfterAutoPoints = computed(() => Math.max(0, q5Score.value.points - firstWindowQ5Score.value.points));
const q5FirstWindowManualPoints = computed(() => parseManualPointsInput(q5FirstWindowManualPointsInput.value));
const q5AfterWindowManualPoints = computed(() => parseManualPointsInput(q5AfterWindowManualPointsInput.value));
const q5FirstWindowEffectivePoints = computed(() => q5FirstWindowManualPoints.value ?? firstWindowQ5Score.value.points);
const q5AfterWindowEffectivePoints = computed(() => q5AfterWindowManualPoints.value ?? q5AfterAutoPoints.value);
const q5EffectivePoints = computed(() => q5FirstWindowEffectivePoints.value + q5AfterWindowEffectivePoints.value);

function formatManualInputValue(value: unknown) {
    const parsed = toNumber(value);
    return parsed == null ? '' : `${parsed}`;
}

function refreshManualScoreInputs(next: Record<string, number | string | null> = props.manualScores) {
    const q2FirstRaw = next?.[q2FirstWindowManualScoreKey];
    const q2AfterRaw = next?.[q2AfterWindowManualScoreKey];
    const q2LegacyRaw = next?.[q2ManualScoreKey];

    q2FirstWindowManualPointsInput.value = formatManualInputValue(q2FirstRaw);
    q2AfterWindowManualPointsInput.value = formatManualInputValue(q2AfterRaw);

    if (q2AfterWindowManualPointsInput.value === '' && q2FirstWindowManualPointsInput.value === '') {
        q2AfterWindowManualPointsInput.value = formatManualInputValue(q2LegacyRaw);
    }

    const q5FirstRaw = next?.[q5FirstWindowManualScoreKey];
    const q5AfterRaw = next?.[q5AfterWindowManualScoreKey];
    const q5LegacyTotal = toNumber(next?.[q5ManualScoreKey]);

    q5FirstWindowManualPointsInput.value = formatManualInputValue(q5FirstRaw) || `${firstWindowQ5Score.value.points}`;
    q5AfterWindowManualPointsInput.value = formatManualInputValue(q5AfterRaw);

    if (q5AfterWindowManualPointsInput.value === '') {
        if (q5LegacyTotal != null && (q5FirstRaw == null || q5FirstRaw === '')) {
            q5AfterWindowManualPointsInput.value = `${Math.max(0, q5LegacyTotal - firstWindowQ5Score.value.points)}`;
        } else {
            q5AfterWindowManualPointsInput.value = `${q5AfterAutoPoints.value}`;
        }
    }
}

watch(
    () => [props.manualScores, firstWindowQ5Score.value.points, q5AfterAutoPoints.value] as const,
    ([next]) => refreshManualScoreInputs(next),
    { immediate: true, deep: true },
);

function buildQ6Score(scoring: any) {
    const points = toNumber(scoring?.points) ?? 0;
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
    explanation.push(`Vergabtes Ergebnis: ${points}/${max}`);

    return { points, max, explanation };
}

const q6Score = computed(() => buildQ6Score(q6Scoring.value));
const firstWindowQ6Score = computed(() => buildQ6Score(firstWindowQ6Scoring.value ?? q6Scoring.value));

const totalPoints = computed(
    () =>
        q1Stats.value.points +
        (q2TotalManualPoints.value ?? 0) +
        q3Score.value.points +
        q4Score.value.points +
        q5EffectivePoints.value +
        q6Score.value.points,
);
const firstWindowTotalPoints = computed(
    () =>
        firstWindowQ1Stats.value.points +
        (q2FirstWindowManualPoints.value ?? 0) +
        firstWindowQ3Score.value.points +
        firstWindowQ4Score.value.points +
        q5FirstWindowEffectivePoints.value +
        firstWindowQ6Score.value.points,
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

function sanitizeQ2FirstWindowManualPoints(event: Event) {
    sanitizeManualPointsInput(event, (value) => {
        q2FirstWindowManualPointsInput.value = value;
    });
}

function sanitizeQ2AfterWindowManualPoints(event: Event) {
    sanitizeManualPointsInput(event, (value) => {
        q2AfterWindowManualPointsInput.value = value;
    });
}

function sanitizeQ5FirstWindowManualPoints(event: Event) {
    sanitizeManualPointsInput(event, (value) => {
        q5FirstWindowManualPointsInput.value = value;
    });
}

function sanitizeQ5AfterWindowManualPoints(event: Event) {
    sanitizeManualPointsInput(event, (value) => {
        q5AfterWindowManualPointsInput.value = value;
    });
}

async function persistQ2FirstWindowManualPoints() {
    await persistManualPoints(q2FirstWindowManualScoreKey, q2FirstWindowManualPoints.value);
}

async function persistQ2AfterWindowManualPoints() {
    await persistManualPoints(q2AfterWindowManualScoreKey, q2AfterWindowManualPoints.value);
}

async function persistQ5FirstWindowManualPoints() {
    await persistManualPoints(q5FirstWindowManualScoreKey, q5FirstWindowManualPoints.value);
}

async function persistQ5AfterWindowManualPoints() {
    await persistManualPoints(q5AfterWindowManualScoreKey, q5AfterWindowManualPoints.value);
}
</script>

<template>
    <div :class="['w-full space-y-3', !forceOpenAnswers ? 'lg:w-1/2' : '']">
        <div class="rounded-lg border-2 border-black bg-white p-3 text-sm">
            <div class="mb-3 flex items-center justify-between border-b border-black pb-2">
                <div class="font-semibold">BT – Auswertung Form A</div>
                <div class="text-right">
                    <div class="text-sm">
                        <span class="font-semibold">Rohwert bis 30 Min.:</span> <span class="text-xl font-bold">{{ firstWindowTotalPoints }}</span>
                        <span class="font-semibold">/ 50</span>
                    </div>
                    <div class="text-lg">
                        <span class="font-semibold">Rohwert gesamt:</span> <span class="text-2xl font-bold">{{ totalPoints }}</span>
                        <span class="font-semibold">/ 50</span>
                    </div>
                    <div><span class="font-semibold">Benötigte Zeit:</span> {{ totalTime }}</div>
                    <div class="text-xs text-blue-700">Blau = nach 30 Minuten eingetragen</div>
                </div>
            </div>

            <table class="w-full border-collapse border border-black">
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
                            <div>Frühdienst: zweisilbig {{ q1Stats.earlyTwoSyllable }}/9, dreisilbig {{ q1Stats.earlyThreeSyllable }}/1</div>
                            <div>Spätdienst: einsilbig {{ q1Stats.lateOneSyllable }}/8, dreisilbig {{ q1Stats.lateThreeSyllable }}/7</div>
                            <div class="mt-1 text-muted-foreground">Abzüge gemäß Soll-Verteilung.</div>
                        </td>
                        <td class="border border-black p-2 text-center font-semibold">{{ firstWindowQ1Stats.points }}</td>
                        <td class="border border-black p-2 text-center font-semibold">{{ q1Stats.points }}</td>
                    </tr>

                    <tr>
                        <td class="border border-black p-2 align-top">
                            <div class="mb-2 font-semibold">Aufgabe 2</div>
                            <div class="mb-2">Manuelle Bewertung durch Prüfer:in.</div>
                            <div class="flex flex-wrap gap-3">
                                <label class="flex flex-col gap-1 text-xs text-muted-foreground">
                                    bis 30 Min.
                                    <Input
                                        :model-value="q2FirstWindowManualPointsInput"
                                        class="w-24 text-center"
                                        inputmode="numeric"
                                        @input="sanitizeQ2FirstWindowManualPoints"
                                        @blur="persistQ2FirstWindowManualPoints"
                                    />
                                </label>
                                <label class="flex flex-col gap-1 text-xs text-blue-700">
                                    nach 30 Min.
                                    <Input
                                        :model-value="q2AfterWindowManualPointsInput"
                                        class="w-24 text-center text-foreground"
                                        inputmode="numeric"
                                        @input="sanitizeQ2AfterWindowManualPoints"
                                        @blur="persistQ2AfterWindowManualPoints"
                                    />
                                </label>
                            </div>
                        </td>
                        <td class="border border-black p-2 text-center font-semibold">{{ q2FirstWindowManualPoints ?? '—' }}</td>
                        <td class="border border-black p-2 text-center font-semibold">{{ q2TotalManualPoints ?? '—' }}</td>
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
                            <div class="mt-1 text-muted-foreground">
                                Abzüge: {{ q3Score.mistakes.length ? q3Score.mistakes.join(' | ') : 'keine' }}.
                            </div>
                        </td>
                        <td class="border border-black p-2 text-center font-semibold">{{ firstWindowQ3Score.points }}</td>
                        <td class="border border-black p-2 text-center font-semibold">{{ q3Score.points }}</td>
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
                            <div class="mt-1 text-muted-foreground">
                                Abzüge: {{ q4Score.mistakes.length ? q4Score.mistakes.join(' | ') : 'keine' }}.
                            </div>
                        </td>
                        <td class="border border-black p-2 text-center font-semibold">{{ firstWindowQ4Score.points }}</td>
                        <td class="border border-black p-2 text-center font-semibold">{{ q4Score.points }}</td>
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
                            <div class="mt-1 text-muted-foreground">
                                Abzüge: {{ q5Score.mistakes.length ? q5Score.mistakes.join(' | ') : 'keine' }}.
                            </div>
                            <div class="mt-3 flex flex-wrap gap-3">
                                <label class="flex flex-col gap-1 text-xs text-muted-foreground">
                                    bis 30 Min.
                                    <Input
                                        :model-value="q5FirstWindowManualPointsInput"
                                        class="w-24 text-center"
                                        inputmode="numeric"
                                        @input="sanitizeQ5FirstWindowManualPoints"
                                        @blur="persistQ5FirstWindowManualPoints"
                                    />
                                </label>
                                <label class="flex flex-col gap-1 text-xs text-blue-700">
                                    nach 30 Min.
                                    <Input
                                        :model-value="q5AfterWindowManualPointsInput"
                                        class="w-24 text-center text-foreground"
                                        inputmode="numeric"
                                        @input="sanitizeQ5AfterWindowManualPoints"
                                        @blur="persistQ5AfterWindowManualPoints"
                                    />
                                </label>
                            </div>
                        </td>
                        <td class="border border-black p-2 text-center font-semibold">{{ q5FirstWindowEffectivePoints }}</td>
                        <td class="border border-black p-2 text-center font-semibold">{{ q5EffectivePoints }}</td>
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
                            <div class="mt-1 text-muted-foreground">Punkte-Herleitung: {{ q6Score.explanation.join(' | ') }}</div>
                        </td>
                        <td class="border border-black p-2 text-center font-semibold">{{ firstWindowQ6Score.points }}</td>
                        <td class="border border-black p-2 text-center font-semibold">{{ q6Score.points }}</td>
                    </tr>

                    <tr>
                        <td class="border border-black p-2 text-right text-lg font-bold">Rohwert</td>
                        <td class="border border-black p-2 text-center text-xl font-extrabold">{{ firstWindowTotalPoints }}</td>
                        <td class="border border-black p-2 text-center text-xl font-extrabold">{{ totalPoints }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
