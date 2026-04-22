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
  }>(),
  {
    manualScores: () => ({}),
    results: null,
    showAnswers: true,
  },
);

const emit = defineEmits<{
  'manual-score-updated': [{ key: string; value: number | null }];
}>();

const q2ManualScoreKey = 'bt_q2_manual_points';
const q2ManualPointsInput = ref('');
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

watch(
  () => props.manualScores,
  (next) => {
    const raw = next?.[q2ManualScoreKey];
    q2ManualPointsInput.value = raw == null || raw === '' ? '' : `${raw}`;
  },
  { immediate: true, deep: true },
);

const q2ManualPoints = computed(() => {
  if (q2ManualPointsInput.value === '') return null;
  const parsed = Number(q2ManualPointsInput.value);
  return Number.isFinite(parsed) ? parsed : null;
});

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

const q1Stats = computed(() => {
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
      const name = q1Assignments.value[buildCellKey(shift, slot, day)];
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

  const points = (earlyTwoSyllable === 9 ? 3 : 0) + (earlyThreeSyllable === 1 ? 1 : 0) + (lateOneSyllable === 8 ? 3 : 0) + (lateThreeSyllable === 7 ? 2 : 0);

  return { earlyTwoSyllable, earlyThreeSyllable, lateOneSyllable, lateThreeSyllable, points, max: 9 };
});

const q3Score = computed(() => {
  const mistakes = q3Denominations
    .map((denomination) => {
      const actual = toNumber(q3CashAnswers.value[denomination.key]);
      if (actual === denomination.expected) return null;
      return `${denomination.label}: ${actual ?? 'leer'} statt ${denomination.expected}`;
    })
    .filter((value): value is string => value !== null);

  return { points: q3Denominations.length - mistakes.length, max: q3Denominations.length, mistakes };
});

const btAlphabet = 'ABCDEFGHJKLMNOPQRSTUVWXYZ';
const q4LetterWeights: Record<string, number> = {
  A: 15, F: 15, W: 15, B: 15, G: 15, J: 15, X: 15, Y: 15, C: 15, H: 15, Z: 15, D: 15,
  R: 20, P: 20, Q: 20,
  N: 30, K: 30, S: 30, V: 30, T: 30, U: 30, O: 30, L: 30,
  M: 60, E: 60,
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

const q4Score = computed(() => {
  const answers = Array.from({ length: 10 }, (_, index) => normalizeFolderLetters(String(q4FolderAnswers.value[index + 1] ?? '')));
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
});

const q5ExpectedDays = new Set([5, 7, 10]);
const q5DayPoints: Record<number, number> = { 5: 2, 7: 2, 10: 4 };
const q5Score = computed(() => {
  const selectedDays = Array.from({ length: 10 }, (_, idx) => idx + 1).filter((day) => q5StampDays.value[day] === true);
  const points = selectedDays.reduce((total, day) => total + (q5ExpectedDays.has(day) ? q5DayPoints[day] : 0), 0);
  const missing = [5, 7, 10].filter((day) => !selectedDays.includes(day));
  const wrong = selectedDays.filter((day) => !q5ExpectedDays.has(day));
  const mistakes: string[] = [];
  if (missing.length) mistakes.push(`fehlende korrekte Tage: ${missing.join(', ')}`);
  if (wrong.length) mistakes.push(`falsch zusätzlich markiert: ${wrong.join(', ')}`);
  return { points, max: 8, mistakes };
});

const q6Score = computed(() => {
  const points = toNumber(q6Scoring.value?.points) ?? 0;
  const max = toNumber(q6Scoring.value?.max) ?? 8;
  const explanation: string[] = [];

  if (!q6Scoring.value) {
    explanation.push('Keine automatische Detailwertung vorhanden.');
    return { points, max, explanation };
  }

  explanation.push(`Korrekte Reihen: ${q6Scoring.value.rowCorrectCount ?? 0}`);
  explanation.push(`Alle 6 Personen benachrichtigt: ${q6Scoring.value.allPeopleNotified ? 'ja' : 'nein'}`);
  explanation.push(`Telefon sinnvoll eingesetzt: ${q6Scoring.value.hasPhoneUsage ? 'ja' : 'nein'}`);
  explanation.push(`Bestzeit erreicht: ${q6Scoring.value.isBestTime ? 'ja' : 'nein'}`);
  explanation.push(`Vergabtes Ergebnis: ${points}/${max}`);

  return { points, max, explanation };
});

const totalPoints = computed(() => q1Stats.value.points + (q2ManualPoints.value ?? 0) + q3Score.value.points + q4Score.value.points + q5Score.value.points + q6Score.value.points);
const totalTime = computed(() => formatTime(toNumber(props.results?.total_time_seconds)));

function sanitizeManualPoints(event: Event) {
  const target = event.target as HTMLInputElement;
  const sanitized = target.value.replace(/\D/g, '').slice(0, 2);
  q2ManualPointsInput.value = sanitized;
  target.value = sanitized;
}

async function persistQ2ManualPoints() {
  const value = q2ManualPoints.value;
  emit('manual-score-updated', { key: q2ManualScoreKey, value });
  if (!props.testResultId) return;

  await axios.put(route('test-results.manual-scores.update', { testResult: props.testResultId }), {
    key: q2ManualScoreKey,
    value,
  });
}
</script>

<template>
  <div class="space-y-4">
    <table class="mb-4 w-full overflow-hidden rounded-lg border text-sm shadow">
      <tbody>
        <tr class="bg-muted/40">
          <td class="w-1/2 px-3 py-2 font-semibold">Gesamtpunkte</td>
          <td class="px-3 py-2">{{ totalPoints }}</td>
        </tr>
        <tr>
          <td class="px-3 py-2 font-semibold">Benötigte Zeit</td>
          <td class="px-3 py-2">{{ totalTime }}</td>
        </tr>
      </tbody>
    </table>

    <details v-if="showAnswers" class="mt-4">
      <summary class="cursor-pointer font-medium">Antworten anzeigen</summary>

      <div class="mt-4 space-y-8">
        <section class="space-y-3">
          <h3 class="text-lg font-semibold">BT – Aufgabe 1 ({{ q1Stats.points }}/{{ q1Stats.max }})</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full table-fixed rounded-lg border text-sm shadow">
              <thead>
                <tr>
                  <th class="w-24 border p-1 text-left">Aufgabe 1</th>
                  <th v-for="day in days" :key="day" class="border p-1 text-center">{{ day }}</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <th class="border p-1 text-left" rowspan="2">Frühdienst</th>
                  <td v-for="day in days" :key="`e1-${day}`" class="border p-1">{{ q1Assignments[buildCellKey('early', 1, day)] ?? '—' }}</td>
                </tr>
                <tr>
                  <td v-for="day in days" :key="`e2-${day}`" class="border p-1">{{ q1Assignments[buildCellKey('early', 2, day)] ?? '—' }}</td>
                </tr>
                <tr>
                  <th class="border p-1 text-left" rowspan="3">Spätdienst</th>
                  <td v-for="day in days" :key="`l1-${day}`" class="border p-1">{{ q1Assignments[buildCellKey('late', 1, day)] ?? '—' }}</td>
                </tr>
                <tr>
                  <td v-for="day in days" :key="`l2-${day}`" class="border p-1">{{ q1Assignments[buildCellKey('late', 2, day)] ?? '—' }}</td>
                </tr>
                <tr>
                  <td v-for="day in days" :key="`l3-${day}`" class="border p-1">{{ q1Assignments[buildCellKey('late', 3, day)] ?? '—' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <p class="text-sm text-muted-foreground">
            Abzüge: 2-silbig Früh {{ q1Stats.earlyTwoSyllable }}/9, 3-silbig Früh {{ q1Stats.earlyThreeSyllable }}/1,
            1-silbig Spät {{ q1Stats.lateOneSyllable }}/8, 3-silbig Spät {{ q1Stats.lateThreeSyllable }}/7.
          </p>
        </section>

        <section class="space-y-3">
          <h3 class="text-lg font-semibold">BT – Aufgabe 2 ({{ q2ManualPoints ?? '—' }} P.)</h3>
          <p class="text-sm text-muted-foreground">Punkteingabe durch Prüfer:in (manuell).</p>
          <Input :model-value="q2ManualPointsInput" class="w-24" inputmode="numeric" @input="sanitizeManualPoints" @blur="persistQ2ManualPoints" />
        </section>

        <section class="space-y-3">
          <h3 class="text-lg font-semibold">BT – Aufgabe 3 ({{ q3Score.points }}/{{ q3Score.max }})</h3>
          <table class="min-w-full rounded-lg border text-sm shadow">
            <thead>
              <tr>
                <th class="border p-1 text-left">Geldsorten</th>
                <th v-for="entry in q3Denominations" :key="entry.key" class="border p-1 text-center">{{ entry.label }}</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th class="border p-1 text-left">Anzahl</th>
                <td v-for="entry in q3Denominations" :key="`val-${entry.key}`" class="border p-1 text-center">{{ q3CashAnswers[entry.key] || '—' }}</td>
              </tr>
            </tbody>
          </table>
          <p class="text-sm text-muted-foreground">Abzüge: {{ q3Score.mistakes.length ? q3Score.mistakes.join(' | ') : 'keine' }}.</p>
        </section>

        <section class="space-y-3">
          <h3 class="text-lg font-semibold">BT – Aufgabe 4 ({{ q4Score.points }}/{{ q4Score.max }})</h3>
          <table class="min-w-full rounded-lg border text-sm shadow">
            <thead><tr><th class="border p-1 text-left">Ordner-Nr.</th><th v-for="i in 10" :key="`h-${i}`" class="border p-1">{{ i }}</th></tr></thead>
            <tbody>
              <tr><td class="border p-1">Buchstaben</td><td v-for="i in 10" :key="`f-${i}`" class="border p-1">{{ q4FolderAnswers[i] || '—' }}</td></tr>
            </tbody>
          </table>
          <p class="text-sm text-muted-foreground">Abzüge: {{ q4Score.mistakes.length ? q4Score.mistakes.join(' | ') : 'keine' }}.</p>
        </section>

        <section class="space-y-3">
          <h3 class="text-lg font-semibold">BT – Aufgabe 5 ({{ q5Score.points }}/{{ q5Score.max }})</h3>
          <table class="min-w-full rounded-lg border text-sm shadow">
            <thead>
              <tr>
                <th class="border p-1 text-left">Arbeitstag</th>
                <th v-for="day in 10" :key="`head-${day}`" class="border p-1 text-center">{{ day }}.AT</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th class="border p-1 text-left">Markiert</th>
                <td v-for="day in 10" :key="`mark-${day}`" class="border p-1 text-center">{{ q5StampDays[day] ? 'X' : '—' }}</td>
              </tr>
            </tbody>
          </table>
          <p class="text-sm text-muted-foreground">Abzüge: {{ q5Score.mistakes.length ? q5Score.mistakes.join(' | ') : 'keine' }}.</p>
        </section>

        <section class="space-y-3">
          <h3 class="text-lg font-semibold">BT – Aufgabe 6 ({{ q6Score.points }}/{{ q6Score.max }})</h3>
          <table class="min-w-full rounded-lg border text-sm shadow">
            <thead><tr><th class="border p-1">von</th><th class="border p-1">nach</th><th class="border p-1">Zeit (Weg)</th><th class="border p-1">Zeit (Nachr.)</th></tr></thead>
            <tbody>
              <tr v-for="row in 6" :key="row">
                <td class="border p-1">{{ q6RouteAssignments[`route-from-${row}`] || '—' }}</td>
                <td class="border p-1">{{ q6RouteAssignments[`route-to-${row}`] || '—' }}</td>
                <td class="border p-1">{{ q6RouteTimes[`route-time-${row}`] || '—' }}</td>
                <td class="border p-1">{{ q6RouteTimes[`route-msg-${row}`] || '—' }}</td>
              </tr>
              <tr>
                <td class="border p-1 text-right" colspan="2">Gesamtzeit</td>
                <td class="border p-1">{{ q6RouteTotals.totalWay || '—' }}</td>
                <td class="border p-1">{{ q6RouteTotals.totalMsg || '—' }}</td>
              </tr>
              <tr>
                <td class="border p-1 text-right" colspan="2">Rückweg</td>
                <td class="border p-1">{{ q6RouteTotals.returnWay || '—' }}</td>
                <td class="border p-1">—</td>
              </tr>
            </tbody>
          </table>
          <p class="text-sm text-muted-foreground">Punkte-Herleitung: {{ q6Score.explanation.join(' | ') }}</p>
        </section>
      </div>
    </details>
  </div>
</template>
