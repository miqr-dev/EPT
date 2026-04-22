<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Head } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const emit = defineEmits(['started', 'complete', 'update:answers']);

type Restriction = 'F' | 'S' | null;

type Apprentice = {
  id: number;
  name: string;
  restriction: Restriction;
};

type DragPayload = {
  name: string;
  from: 'pool' | 'cell';
  key?: string;
};

const days = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag'];
const earlySlots = [1, 2];
const lateSlots = [1, 2, 3];

const apprentices = ref<Apprentice[]>([
  { id: 1, name: 'Angermann', restriction: null },
  { id: 2, name: 'Basten', restriction: 'S' },
  { id: 3, name: 'Bauer', restriction: 'S' },
  { id: 4, name: 'Berkelhahn', restriction: null },
  { id: 5, name: 'Bertram', restriction: 'S' },
  { id: 6, name: 'Bolte', restriction: 'S' },
  { id: 7, name: 'Diesterweg', restriction: null },
  { id: 8, name: 'Eschweiler', restriction: null },
  { id: 9, name: 'Fuchs', restriction: 'F' },
  { id: 10, name: 'Hamburger', restriction: null },
  { id: 11, name: 'Hamelring', restriction: null },
  { id: 12, name: 'Hans', restriction: 'F' },
  { id: 13, name: 'Kleininger', restriction: null },
  { id: 14, name: 'Krämer', restriction: 'S' },
  { id: 15, name: 'Kühne', restriction: 'S' },
  { id: 16, name: 'Kurz', restriction: 'F' },
  { id: 17, name: 'Mann', restriction: 'F' },
  { id: 18, name: 'Müller', restriction: 'S' },
  { id: 19, name: 'Pahl', restriction: 'F' },
  { id: 20, name: 'Paul', restriction: 'F' },
  { id: 21, name: 'Pees', restriction: 'F' },
  { id: 22, name: 'Petersen', restriction: null },
  { id: 23, name: 'Roth', restriction: 'F' },
  { id: 24, name: 'Schneider', restriction: 'S' },
  { id: 25, name: 'Schuster', restriction: 'S' },
]);

const assignments = ref<Record<string, string | null>>(
  Object.fromEntries(
    days.flatMap((day) => [
      ...earlySlots.map((slot) => [`early-${slot}-${day}`, null]),
      ...lateSlots.map((slot) => [`late-${slot}-${day}`, null]),
    ]),
  ),
);

const leftNames = computed(() => apprentices.value.slice(0, 13));
const rightNames = computed(() => apprentices.value.slice(13));
const maxPage = 6;
const page = ref(1);
const showTest = ref(false);
const cashDenominations = [
  { label: '100€', key: '100' },
  { label: '50€', key: '50' },
  { label: '20€', key: '20' },
  { label: '10€', key: '10' },
  { label: '5€', key: '5' },
  { label: '2€', key: '2' },
  { label: '1€', key: '1' },
  { label: '0,50€', key: '0.5' },
];
const cashAnswers = ref<Record<string, string>>(
  Object.fromEntries(cashDenominations.map((denomination) => [denomination.key, ''])),
);
const folderAnswers = ref<Record<number, string>>(
  Object.fromEntries(Array.from({ length: 10 }, (_, index) => [index + 1, ''])),
);
const stampUsage = [
  { day: 1, values: ['—', '18', '10', '22', '—'] },
  { day: 2, values: ['2', '16', '—', '32', '—'] },
  { day: 3, values: ['4', '16', '—', '10', '—'] },
  { day: 4, values: ['10', '12', '—', '—', '30'] },
  { day: 5, values: ['4', '25', '—', '32', '—'] },
  { day: 6, values: ['10', '18', '12', '—', '—'] },
  { day: 7, values: ['—', '24', '15', '7', '—'] },
  { day: 8, values: ['5', '10', '—', '—', '—'] },
  { day: 9, values: ['—', '—', '—', '12', '50'] },
  { day: 10, values: ['1', '3', '3', '15', '—'] },
];
const stampAnswerDays = ref<Record<number, boolean>>(
  Object.fromEntries(Array.from({ length: 10 }, (_, index) => [index + 1, false])),
);
const routeRows = Array.from({ length: 6 }, (_, index) => index + 1);
const routeAssignments = ref<Record<string, string | null>>(
  Object.fromEntries(
    routeRows.flatMap((row) => [
      [`route-from-${row}`, row === 1 ? 'Eigene Wohnung' : null],
      [`route-to-${row}`, null],
    ]),
  ),
);
const routeTimes = ref<Record<string, string>>(
  Object.fromEntries(
    routeRows.flatMap((row) => [
      [`route-time-${row}`, ''],
      [`route-msg-${row}`, ''],
    ]),
  ),
);
const routeTotals = ref({ totalWay: '', totalMsg: '', returnWay: '' });
const q3ExpectedCashCounts: Record<string, number> = {
  '100': 64,
  '50': 3,
  '20': 6,
  '10': 3,
  '5': 3,
  '2': 4,
  '1': 5,
  '0.5': 4,
};
const btAlphabet = 'ABCDEFGHJKLMNOPQRSTUVWXYZ';
const q4LetterWeights: Record<string, number> = {
  A: 15, F: 15, W: 15, B: 15, G: 15, J: 15, X: 15, Y: 15, C: 15, H: 15, Z: 15, D: 15, // 2.5%
  R: 20, P: 20, Q: 20, // 3.33%
  N: 30, K: 30, S: 30, V: 30, T: 30, U: 30, O: 30, L: 30, // 5%
  M: 60, E: 60, // 10%
};
const q4TargetFolderWeight = 60; // 10%
const q5ExpectedBuyDays = new Set([5, 7, 10]);
const q5DayPoints: Record<number, number> = {
  5: 2,
  7: 2,
  10: 4,
};
const oneSyllableNames = new Set(['Fuchs', 'Hans', 'Kurz', 'Mann', 'Pahl', 'Paul', 'Pees', 'Roth']);
const twoSyllableNames = new Set([
  'Basten',
  'Bauer',
  'Bertram',
  'Bolte',
  'Krämer',
  'Kühne',
  'Müller',
  'Schneider',
  'Schuster',
]);
const threeSyllableNames = new Set([
  'Angermann',
  'Berkelhahn',
  'Diesterweg',
  'Eschweiler',
  'Hamburger',
  'Hamelring',
  'Kleininger',
  'Petersen',
]);

function buildCellKey(shift: 'early' | 'late', slot: number, day: string) {
  return `${shift}-${slot}-${day}`;
}

function handleDragStart(event: DragEvent, payload: DragPayload) {
  event.dataTransfer?.setData('text/plain', JSON.stringify(payload));
  event.dataTransfer?.setDragImage((event.target as HTMLElement) ?? document.body, 0, 0);
}

function handleDropOnCell(event: DragEvent, key: string) {
  event.preventDefault();
  const payloadText = event.dataTransfer?.getData('text/plain');
  if (!payloadText) return;

  const payload = JSON.parse(payloadText) as DragPayload;
  if (!payload?.name) return;
  const targetValue = assignments.value[key];
  if (targetValue && payload.key !== key) return;

  if (payload.from === 'cell' && payload.key) {
    assignments.value[payload.key] = null;
  }

  assignments.value[key] = payload.name;
}

function handleDropOnPool(event: DragEvent) {
  event.preventDefault();
  const payloadText = event.dataTransfer?.getData('text/plain');
  if (!payloadText) return;

  const payload = JSON.parse(payloadText) as DragPayload;
  if (!payload?.name) return;

  if (payload.from === 'cell' && payload.key) {
    assignments.value[payload.key] = null;
  }
}

function allowDrop(event: DragEvent) {
  event.preventDefault();
}

function clearCell(key: string) {
  assignments.value[key] = null;
}

function handleCashInput(key: string, event: Event) {
  const target = event.target as HTMLInputElement;
  const cleaned = target.value.replace(/\D/g, '').slice(0, 2);
  cashAnswers.value[key] = cleaned;
  target.value = cleaned;
}

function handleFolderInput(index: number, event: Event) {
  const target = event.target as HTMLInputElement;
  const cleaned = target.value.toUpperCase().replace(/[^A-Z]/g, '').slice(0, 5);
  folderAnswers.value[index] = cleaned;
  target.value = cleaned;
}

function toggleStampAnswer(day: number) {
  stampAnswerDays.value[day] = !stampAnswerDays.value[day];
}

function handleRouteDropOnCell(event: DragEvent, key: string) {
  if (key === 'route-from-1' && routeAssignments.value[key] === 'Eigene Wohnung') return;

  event.preventDefault();
  const payloadText = event.dataTransfer?.getData('text/plain');
  if (!payloadText) return;

  const payload = JSON.parse(payloadText) as DragPayload;
  if (!payload?.name) return;

  if (payload.from === 'cell' && payload.key) {
    routeAssignments.value[payload.key] = null;
  }

  routeAssignments.value[key] = payload.name;
}

function handleRouteTimeInput(key: string, event: Event) {
  const target = event.target as HTMLInputElement;
  const cleaned = target.value.replace(/\D/g, '').slice(0, 3);
  routeTimes.value[key] = cleaned;
  target.value = cleaned;
}

function handleRouteTotalInput(key: 'totalWay' | 'totalMsg' | 'returnWay', event: Event) {
  const target = event.target as HTMLInputElement;
  const cleaned = target.value.replace(/\D/g, '').slice(0, 3);
  routeTotals.value[key] = cleaned;
  target.value = cleaned;
}

function nextPage() {
  if (page.value < maxPage) {
    page.value += 1;
  }
}

function prevPage() {
  if (page.value > 1) {
    page.value -= 1;
  }
}

function startTest() {
  emit('started');
  showTest.value = true;
  page.value = 1;
}

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

function evaluateQ4() {
  const answers = Array.from({ length: 10 }, (_, index) => normalizeFolderLetters(folderAnswers.value[index + 1]));
  const seenLetters = new Set<string>();
  const folderWeights = answers.map((chars) =>
    chars.reduce((sum, char) => {
      if (seenLetters.has(char)) return sum;
      seenLetters.add(char);
      return sum + (q4LetterWeights[char] ?? 0);
    }, 0),
  );
  const correctFolderCount = folderWeights.filter((weight) => weight === q4TargetFolderWeight).length;

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

  if (correctFolderCount === 10 && alphabeticalOrderMet) {
    points = folderOrderMet ? 8 : 7;
  }

  return { points, correctFolderCount, alphabeticalOrderMet, folderOrderMet, folderWeights };
}

const debugScores = computed(() => {
  const q6TravelTimes: Record<string, number> = {
    'Eigene Wohnung|Frey': 5,
    'Frey|Eigene Wohnung': 5,
    'Eigene Wohnung|Fuchs': 15,
    'Fuchs|Eigene Wohnung': 15,
    'Eigene Wohnung|Hermann': 8,
    'Hermann|Eigene Wohnung': 8,
    'Eigene Wohnung|Schneider': 5,
    'Schneider|Eigene Wohnung': 5,
    'Müller|Frey': 5,
    'Frey|Müller': 5,
    'Müller|Fuchs': 20,
    'Fuchs|Müller': 20,
    'Frey|Bär': 5,
    'Bär|Frey': 5,
    'Frey|Hermann': 5,
    'Hermann|Frey': 5,
    'Frey|Schneider': 12,
    'Schneider|Frey': 12,
    'Bär|Hermann': 5,
    'Hermann|Bär': 5,
    'Hermann|Schneider': 5,
    'Schneider|Hermann': 5,
    'Fuchs|Schneider': 15,
    'Schneider|Fuchs': 15,
  };
  const q6PhoneLocations = new Set(['Müller', 'Bär', 'Hermann', 'Schneider']);
  const q6ExpectedPeople = new Set(['Müller', 'Frey', 'Bär', 'Hermann', 'Schneider', 'Fuchs']);

  let rowCorrectCount = 0;
  let hasPhoneUsage = false;
  let hasAnyCorrectRow = false;
  let hasAnyInput = false;
  let lastNotifiedPerson: string | null = null;
  const notifiedPeople = new Set<string>();

  routeRows.forEach((row) => {
    const fromRaw = (routeAssignments.value[`route-from-${row}`] ?? '').trim();
    const toRaw = (routeAssignments.value[`route-to-${row}`] ?? '').trim();
    const effectiveFrom = fromRaw;
    const wayRaw = routeTimes.value[`route-time-${row}`];
    const msgRaw = routeTimes.value[`route-msg-${row}`];
    const hasRowInput = fromRaw !== '' || toRaw !== '' || wayRaw !== '' || msgRaw !== '';
    if (hasRowInput) {
      hasAnyInput = true;
    }

    if (!fromRaw || !toRaw) return;

    const msgOk = Number(msgRaw) === 3;
    const wayIsZeroOrEmpty = wayRaw === '' || Number(wayRaw) === 0;
    const fromHasPhone = q6PhoneLocations.has(effectiveFrom);
    const toHasPhone = q6PhoneLocations.has(toRaw);
    const isPhoneRow = wayIsZeroOrEmpty && fromHasPhone && toHasPhone;
    let rowOk = false;

    if (isPhoneRow) {
      hasPhoneUsage = true;
      rowOk = msgOk;
      if (rowOk) {
        notifiedPeople.add(toRaw);
        lastNotifiedPerson = toRaw;
      }
    } else {
      const expectedTravelTime = q6TravelTimes[`${effectiveFrom}|${toRaw}`];
      const wayMinutes = Number(wayRaw);
      const wayOk = Number.isFinite(wayMinutes) && wayMinutes > 0;
      rowOk = Number.isFinite(expectedTravelTime) && wayOk && msgOk;
      if (rowOk) {
        notifiedPeople.add(toRaw);
        lastNotifiedPerson = toRaw;
      }
    }

    if (rowOk) {
      rowCorrectCount += 1;
      hasAnyCorrectRow = true;
    }
  });

  const allPeopleNotified = [...q6ExpectedPeople].every((name) => notifiedPeople.has(name));
  const fullyCorrectCompletion = allPeopleNotified && rowCorrectCount >= 6;
  const totalWay = Number(routeTotals.value.totalWay);
  const totalMsg = Number(routeTotals.value.totalMsg);
  const returnWay = Number(routeTotals.value.returnWay);
  if (routeTotals.value.totalWay !== '' || routeTotals.value.totalMsg !== '' || routeTotals.value.returnWay !== '') {
    hasAnyInput = true;
  }

  const getReturnOptions = (from: string | null) => {
    if (!from) return new Set<number>();
    const options = new Set<number>();
    const direct = q6TravelTimes[`${from}|Eigene Wohnung`];
    if (Number.isFinite(direct)) {
      options.add(direct);
    }

    Object.entries(q6TravelTimes).forEach(([path, leg1]) => {
      const [start, via] = path.split('|');
      if (start !== from || via === 'Eigene Wohnung') return;
      const leg2 = q6TravelTimes[`${via}|Eigene Wohnung`];
      if (Number.isFinite(leg2)) {
        options.add(leg1 + leg2);
      }
    });

    return options;
  };

  const returnOptions = getReturnOptions(lastNotifiedPerson);
  const returnWayCorrect = returnOptions.has(returnWay);
  const isBestTime = fullyCorrectCompletion && hasPhoneUsage && totalWay === 30 && totalMsg === 18 && returnWay === 15;

  let q6Points = 0;
  if (isBestTime) {
    q6Points = 8;
  } else if (fullyCorrectCompletion) {
    if (hasPhoneUsage) {
      q6Points = returnWayCorrect ? 7 : 4;
    } else {
      q6Points = 6;
    }
  } else if (hasAnyInput || hasAnyCorrectRow) {
    q6Points = hasPhoneUsage ? 4 : 2;
  }

  const firstAppearanceNames = new Set<string>();
  let earlyTwoSyllable = 0;
  let earlyThreeSyllable = 0;
  let lateOneSyllable = 0;
  let lateThreeSyllable = 0;

  const orderedSlots: Array<{ shift: 'early' | 'late'; slot: number }> = [
    { shift: 'early', slot: 1 },
    { shift: 'early', slot: 2 },
    { shift: 'late', slot: 1 },
    { shift: 'late', slot: 2 },
    { shift: 'late', slot: 3 },
  ];

  orderedSlots.forEach(({ shift, slot }) => {
    days.forEach((day) => {
      const key = buildCellKey(shift, slot, day);
      const name = assignments.value[key];
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

  const q1Points =
    (earlyTwoSyllable === 9 ? 3 : 0) +
    (earlyThreeSyllable === 1 ? 1 : 0) +
    (lateOneSyllable === 8 ? 3 : 0) +
    (lateThreeSyllable === 7 ? 2 : 0);

  const q3CorrectFields = Object.entries(q3ExpectedCashCounts).reduce((sum, [key, expected]) => {
    const actual = Number(cashAnswers.value[key] || 0);
    return sum + (actual === expected ? 1 : 0);
  }, 0);

  const selectedDays = Object.entries(stampAnswerDays.value)
    .filter(([, checked]) => checked)
    .map(([day]) => Number(day));
  const q5CorrectDays = selectedDays.filter((day) => q5ExpectedBuyDays.has(day)).length;
  const q5Points = selectedDays.reduce((total, day) => total + (q5DayPoints[day] ?? 0), 0);

  const q4 = evaluateQ4();

  return {
    q1: {
      points: q1Points,
      earlyTwoSyllable,
      earlyThreeSyllable,
      lateOneSyllable,
      lateThreeSyllable,
    },
    q2: { note: 'Keine Auto-Auswertung (Freitextaufgabe).' },
    q3: { points: q3CorrectFields, max: 8 },
    q4: {
      points: q4.points,
      max: 8,
      correctFolders: q4.correctFolderCount,
      alphabeticalOrderMet: q4.alphabeticalOrderMet,
      folderOrderMet: q4.folderOrderMet,
      folderWeights: q4.folderWeights,
    },
    q5: {
      points: q5Points,
      max: 8,
      correctDays: q5CorrectDays,
    },
    q6: {
      points: q6Points,
      max: 8,
      hasPhoneUsage,
      hasAnyInput,
      hasAnyCorrectRow,
      rowCorrectCount,
      allPeopleNotified,
      fullyCorrectCompletion,
      lastNotifiedPerson,
      returnWayCorrect,
      returnOptions: Array.from(returnOptions).sort((a, b) => a - b),
      isBestTime,
    },
  };
});


const btResultPayload = computed(() => ({
  assignments: { ...assignments.value },
  cash_answers: { ...cashAnswers.value },
  folder_answers: { ...folderAnswers.value },
  stamp_answer_days: { ...stampAnswerDays.value },
  route_assignments: { ...routeAssignments.value },
  route_times: { ...routeTimes.value },
  route_totals: { ...routeTotals.value },
  scoring: debugScores.value,
}));

watch(
  btResultPayload,
  (payload) => {
    emit('update:answers', payload);
  },
  { immediate: true, deep: true },
);

function completeBtTest() {
  emit('complete', btResultPayload.value);
}
if (import.meta.env.DEV) {
  (globalThis as { __btDebugScores?: unknown }).__btDebugScores = debugScores;
}
</script>

<template>

  <Head title="BT" />
  <div class="relative h-screen overflow-hidden bg-background text-black">
    <div v-if="showTest" class="absolute right-6 top-4 flex items-center gap-2">
      <Button variant="outline" @click="prevPage" :disabled="page === 1">Zurück</Button>
      <Button v-if="page < maxPage" @click="nextPage">Weiter</Button>
      <Button v-else @click="completeBtTest">Test beenden</Button>
    </div>
    <div v-if="showTest"
      class="absolute bottom-4 left-4 z-20 w-[900px] max-w-[calc(100%-2rem)] rounded-lg border border-black bg-white/95 p-3 font-sans text-xs shadow-lg">
      <div class="mb-2 font-semibold">DEV: Dynamische Punkte (temporär, horizontal)</div>
      <div class="grid grid-cols-6 gap-2">
        <div class="rounded border border-black/20 px-2 py-1">
          <div class="font-semibold">A1</div>
          <div class="font-bold">{{ debugScores.q1.points }}/9</div>
          <div class="text-[10px] text-muted-foreground">
            F2 {{ debugScores.q1.earlyTwoSyllable }}/9 · F3 {{ debugScores.q1.earlyThreeSyllable }}/1
          </div>
          <div class="text-[10px] text-muted-foreground">
            S1 {{ debugScores.q1.lateOneSyllable }}/8 · S3 {{ debugScores.q1.lateThreeSyllable }}/7
          </div>
        </div>
        <div class="rounded border border-black/20 px-2 py-1">
          <div class="font-semibold">A2</div>
          <div class="font-bold">—</div>
          <div class="text-[10px] text-muted-foreground">{{ debugScores.q2.note }}</div>
        </div>
        <div class="rounded border border-black/20 px-2 py-1">
          <div class="font-semibold">A3</div>
          <div class="font-bold">{{ debugScores.q3.points }}/{{ debugScores.q3.max }}</div>
          <div class="text-[10px] text-muted-foreground">richtige Felder</div>
        </div>
        <div class="rounded border border-black/20 px-2 py-1">
          <div class="font-semibold">A4</div>
          <div class="font-bold">{{ debugScores.q4.points }}/{{ debugScores.q4.max }}</div>
          <div class="text-[10px] text-muted-foreground">
            Ordner mit 10%: {{ debugScores.q4.correctFolders }}/10
          </div>
          <div class="text-[10px] text-muted-foreground">
            Alpha {{ debugScores.q4.alphabeticalOrderMet ? '✓' : '✗' }} · Reihenfolge {{ debugScores.q4.folderOrderMet ?
            '✓' : '✗' }}
          </div>
          <div class="text-[10px] text-muted-foreground">
            Gewichte: {{ debugScores.q4.folderWeights.join(' / ') }}
          </div>
        </div>
        <div class="rounded border border-black/20 px-2 py-1">
          <div class="font-semibold">A5</div>
          <div class="font-bold">{{ debugScores.q5.points }}/{{ debugScores.q5.max }}</div>
          <div class="text-[10px] text-muted-foreground">5.AT=2 · 7.AT=2 · 10.AT=4</div>
        </div>
        <div class="rounded border border-black/20 px-2 py-1">
          <div class="font-semibold">A6</div>
          <div class="font-bold">{{ debugScores.q6.points }}/{{ debugScores.q6.max }}</div>
          <div class="text-[10px] text-muted-foreground">
            Reihen korrekt: {{ debugScores.q6.rowCorrectCount }} · Alle 6: {{ debugScores.q6.allPeopleNotified ? '✓' :
            '✗' }}
          </div>
          <div class="text-[10px] text-muted-foreground">
            Tel: {{ debugScores.q6.hasPhoneUsage ? '✓' : '✗' }} · Bestzeit: {{ debugScores.q6.isBestTime ? '✓' : '✗' }}
          </div>
        </div>
      </div>
    </div>
    <div v-if="!showTest" class="flex h-full items-center justify-center px-6">
      <div
        class="max-w-5xl space-y-6 rounded-2xl border border-black/20 bg-white p-8 font-serif text-base leading-relaxed shadow-sm">
        <h1 class="text-center text-2xl font-semibold tracking-[0.2em]">EINFÜHRUNG</h1>
        <p class="text-lg">
          Die Aufgaben auf den folgenden Seiten geben Ihnen Gelegenheit, Ihr Verständnis für einfache Zusammenhänge
          verwaltungs-büromäßiger Art zu zeigen. Sie brauchen dazu keine kaufmännischen Fachkenntnisse, sondern müssen
          nur etwas geschickt und anstellig sein.
        </p>
        <p class="text-lg">
          Jede Aufgabe wird genau beschrieben. Lesen Sie sich bitte diese Schilderungen Wort für Wort langsam durch und
          vergleichen Sie das Gelesene bitte mit den vorgegebenen Antwort-„Feldern“. Dann stellen Sie sich in Gedanken
          vor, wie Sie diese Arbeit in der Praxis durchführen würden und fügen Sie erst dann die Lösungen ein.
        </p>
        <p class="text-lg">
          Bitte schreiben Sie etwaige Nebenrechnungen auf ein extra Blatt und versehen dieses mit Ihrem Namen und dem
          Datum. Von der Aufgabe 3 ab wird keiner ohne Nebenrechnungen auskommen. Jeder im Büro Beschäftigte macht sich
          derartige Notizen. Es ist daher auch bei diesen Aufgaben ratsam, sich Notizen zu machen. Bei der Aufgabe 5
          müssen unbedingt die Nebenrechnungen angegeben werden.
        </p>
        <p class="text-lg">
          Sollten Sie einmal bei einer Aufgabe nicht zurechtkommen, so versuchen Sie wenigstens, einen Ansatz zu finden
          und einzutragen. Versäumen Sie bei den leichten Aufgaben am Anfang nicht zu viel Zeit, da Ihnen dann nachher
          bei den umfangreicheren Aufgaben 4-6 Zeit fehlen muss. Halten Sie sich nicht zu lange bei einer Aufgabe auf.
          Sie haben 60 Minuten Zeit für sechs Aufgaben.
        </p>
        <div class="pt-2 text-center">
          <Button class="px-8" @click="startTest">Test starten</Button>
        </div>
      </div>
    </div>
    <div v-else-if="page === 1" class="flex h-full flex-col">
      <div class="flex-[0.6] overflow-hidden px-6 pt-3 font-serif text-base">
        <div class="flex h-full flex-col gap-4">
          <div class="px-4 py-2 text-center">
            <h1 class="text-xl font-semibold tracking-[0.4em]">Aufgabe 1</h1>
          </div>
          <div class="flex flex-1 gap-4">
            <div class="flex-1 border border-black/20 px-6 py-4">
              <div class="space-y-4 text-center text-xl leading-relaxed">
                <p>
                  Unser Betrieb beschäftigt 25 Lehrlinge. Von diesen sollen jeweils zwei Lehrlinge für den
                  Post-Frühdienst und drei Lehrlinge für den Post-Spätdienst eingeteilt werden, so dass in jeder Woche
                  jeder Lehrling einmal Postdienst hat. Aus verkehrstechnischen Gründen können acht
                  Lehrlinge keinen Frühdienst und neun Lehrlinge keinen Spätdienst machen.
                </p>
                <p class="text-lg">(Siehe Vermerke in der Liste)</p>
                <p>
                  Stellen Sie bitte einen Wochenplan auf (Montag bis Freitag), in dem Sie alle Namen der Lehrlinge für
                  den Postdienst (Früh oder Spät) unten in die Tabelle eintragen. Gehen Sie hierfür mit der Maus auf den
                  jeweiligen Namen und ziehen diesen an die passende Stelle.
                </p>
              </div>
              <!-- <div class="mt-4 text-center text-base">_____</div> -->
            </div>

            <div class="flex-1 border border-black/20 px-4 py-2">
              <div class="flex h-full items-start justify-center gap-8 overflow-hidden">
                <div class="border-2 border-black px-4 py-2 text-xl leading-tight w-[420px] flex-none"
                  @dragover="allowDrop" @drop="handleDropOnPool">
                  <div class="grid grid-cols-2 gap-x-8">
                    <div class="space-y-0">
                      <div v-for="apprentice in leftNames" :key="apprentice.id" class="flex items-center gap-2">
                        <span class="w-6 text-right flex-none">{{ apprentice.id }}</span>
                        <span class="flex-1 text-left text-xl" draggable="true"
                          @dragstart="(event) => handleDragStart(event, { name: apprentice.name, from: 'pool' })">
                          {{ apprentice.name }}
                        </span>
                        <span class="w-4 text-center flex-none">{{ apprentice.restriction ?? '' }}</span>
                      </div>
                    </div>
                    <div class="space-y-0">
                      <div v-for="apprentice in rightNames" :key="apprentice.id" class="flex items-center gap-2">
                        <span class="w-6 text-right flex-none">{{ apprentice.id }}</span>
                        <span class="flex-1 text-left" draggable="true"
                          @dragstart="(event) => handleDragStart(event, { name: apprentice.name, from: 'pool' })">
                          {{ apprentice.name }}
                        </span>
                        <span class="w-4 text-center flex-none">{{ apprentice.restriction ?? '' }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="space-y-6 text-xl text-left leading-tight min-w-[200px]">
                  <div class="text-center">
                    <p class="text-xl mb-1" style="letter-spacing: 0.4em;">L i s t e</p>
                    <p>der Namen</p>
                  </div>
                  <div class="space-y-0">
                    <p>F = kann<span class="ml-1" style="letter-spacing: 0.2em;"> k e i n e n</span></p>
                    <p class="pl-8">Frühdienst</p>
                    <p class="pl-8">machen</p>
                  </div>
                  <div class="space-y-0">
                    <p>S = kann <span class="ml-1" style="letter-spacing: 0.2em;"> k e i n e n</span></p>
                    <p class="pl-8">Spätdienst</p>
                    <p class="pl-8">machen</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex-[0.4] overflow-hidden px-6 pb-4 pt-2 font-serif">
        <div class="flex h-full flex-col">
          <div class="border-t border-black" />
          <div class="flex-1">
            <table class="mt-3 w-full table-fixed border border-black text-base">
              <thead>
                <tr>
                  <th class="w-24 border border-black p-1 text-left">Aufgabe 1</th>
                  <th v-for="day in days" :key="day" class="border border-black p-1 text-center">
                    {{ day }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <th class="border border-black p-1 text-left" rowspan="2">Frühdienst</th>
                  <td v-for="day in days" :key="`early-1-${day}`" class="border border-black p-1 align-middle"
                    @dragover="allowDrop" @drop="(event) => handleDropOnCell(event, buildCellKey('early', 1, day))">
                    <div class="flex items-center gap-2">
                      <span class="w-4 text-right">1.</span>
                      <span v-if="assignments[buildCellKey('early', 1, day)]" class="flex-1 min-w-0 cursor-move"
                        draggable="true"
                        @dragstart="(event) => handleDragStart(event, { name: assignments[buildCellKey('early', 1, day)] ?? '', from: 'cell', key: buildCellKey('early', 1, day) })">
                        {{ assignments[buildCellKey('early', 1, day)] }}
                      </span>
                      <button v-if="assignments[buildCellKey('early', 1, day)]" type="button"
                        class="flex h-4 w-4 items-center justify-center rounded-full border border-black text-[10px] leading-none"
                        aria-label="Remove assignment" @click.stop="clearCell(buildCellKey('early', 1, day))">
                        ×
                      </button>
                      <span v-else class="flex-1">&nbsp;</span>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td v-for="day in days" :key="`early-2-${day}`" class="border border-black p-1 align-middle"
                    @dragover="allowDrop" @drop="(event) => handleDropOnCell(event, buildCellKey('early', 2, day))">
                    <div class="flex items-center gap-2">
                      <span class="w-4 text-right">2.</span>
                      <span v-if="assignments[buildCellKey('early', 2, day)]" class="flex-1 min-w-0 cursor-move"
                        draggable="true"
                        @dragstart="(event) => handleDragStart(event, { name: assignments[buildCellKey('early', 2, day)] ?? '', from: 'cell', key: buildCellKey('early', 2, day) })">
                        {{ assignments[buildCellKey('early', 2, day)] }}
                      </span>
                      <button v-if="assignments[buildCellKey('early', 2, day)]" type="button"
                        class="flex h-4 w-4 items-center justify-center rounded-full border border-black text-[10px] leading-none"
                        aria-label="Remove assignment" @click.stop="clearCell(buildCellKey('early', 2, day))">
                        ×
                      </button>
                      <span v-else class="flex-1">&nbsp;</span>
                    </div>
                  </td>
                </tr>
                <tr>
                  <th class="border border-black p-1 text-left" rowspan="3">Spätdienst</th>
                  <td v-for="day in days" :key="`late-1-${day}`" class="border border-black p-1 align-middle"
                    @dragover="allowDrop" @drop="(event) => handleDropOnCell(event, buildCellKey('late', 1, day))">
                    <div class="flex items-center gap-2">
                      <span class="w-4 text-right">1.</span>
                      <span v-if="assignments[buildCellKey('late', 1, day)]" class="flex-1 min-w-0 cursor-move"
                        draggable="true"
                        @dragstart="(event) => handleDragStart(event, { name: assignments[buildCellKey('late', 1, day)] ?? '', from: 'cell', key: buildCellKey('late', 1, day) })">
                        {{ assignments[buildCellKey('late', 1, day)] }}
                      </span>
                      <button v-if="assignments[buildCellKey('late', 1, day)]" type="button"
                        class="flex h-4 w-4 items-center justify-center rounded-full border border-black text-[10px] leading-none"
                        aria-label="Remove assignment" @click.stop="clearCell(buildCellKey('late', 1, day))">
                        ×
                      </button>
                      <span v-else class="flex-1">&nbsp;</span>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td v-for="day in days" :key="`late-2-${day}`" class="border border-black p-1 align-middle"
                    @dragover="allowDrop" @drop="(event) => handleDropOnCell(event, buildCellKey('late', 2, day))">
                    <div class="flex items-center gap-2">
                      <span class="w-4 text-right">2.</span>
                      <span v-if="assignments[buildCellKey('late', 2, day)]" class="flex-1 min-w-0 cursor-move"
                        draggable="true"
                        @dragstart="(event) => handleDragStart(event, { name: assignments[buildCellKey('late', 2, day)] ?? '', from: 'cell', key: buildCellKey('late', 2, day) })">
                        {{ assignments[buildCellKey('late', 2, day)] }}
                      </span>
                      <button v-if="assignments[buildCellKey('late', 2, day)]" type="button"
                        class="flex h-4 w-4 items-center justify-center rounded-full border border-black text-[10px] leading-none"
                        aria-label="Remove assignment" @click.stop="clearCell(buildCellKey('late', 2, day))">
                        ×
                      </button>
                      <span v-else class="flex-1">&nbsp;</span>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td v-for="day in days" :key="`late-3-${day}`" class="border border-black p-1 align-middle"
                    @dragover="allowDrop" @drop="(event) => handleDropOnCell(event, buildCellKey('late', 3, day))">
                    <div class="flex items-center gap-2">
                      <span class="w-4 text-right">3.</span>
                      <span v-if="assignments[buildCellKey('late', 3, day)]" class="flex-1 min-w-0 cursor-move"
                        draggable="true"
                        @dragstart="(event) => handleDragStart(event, { name: assignments[buildCellKey('late', 3, day)] ?? '', from: 'cell', key: buildCellKey('late', 3, day) })">
                        {{ assignments[buildCellKey('late', 3, day)] }}
                      </span>
                      <button v-if="assignments[buildCellKey('late', 3, day)]" type="button"
                        class="flex h-4 w-4 items-center justify-center rounded-full border border-black text-[10px] leading-none"
                        aria-label="Remove assignment" @click.stop="clearCell(buildCellKey('late', 3, day))">
                        ×
                      </button>
                      <span v-else class="flex-1">&nbsp;</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="page === 2" class="flex h-full flex-col px-12 py-8 font-serif text-base">
      <div class="text-center">
        <h1 class="text-xl font-semibold tracking-[0.4em]">Aufgabe 2</h1>
      </div>
      <div class="mt-10 flex flex-1 flex-col items-center">
        <div class="max-w-2xl text-center space-y-4 leading-relaxed">
          <p>
            Sie erhalten ungeordnet 220 Kopien von Rechnungen aus den Orten Essen, Düsseldorf und Köln.
          </p>
          <p>Sie sollen möglichst schnell feststellen:</p>
          <div class="text-center">_____</div>
        </div>
        <div class="mt-10 w-full max-w-2xl space-y-4 text-left">
          <p>a) wie viele dieser Rechnungen aus jedem der drei Orte kommen</p>
          <div class="space-y-2">
            <p>b) welche bereits bezahlt und welche noch unbezahlt sind</p>
            <div class="flex flex-wrap items-center gap-4 pl-6">
              <p class="text-base">Bezahlte Rechnungen haben entsprechenden Stempelaufdruck</p>
              <div class="border border-black px-4 py-1 text-sm tracking-[0.5em]">BEZAHLT</div>
            </div>
          </div>
          <p>c) welche Höhe die unbezahlten Rechnungen insgesamt haben.</p>
        </div>
        <p class="mt-10 max-w-2xl text-center">
          Geben Sie bitte auf dem Lösungsblatt in Stichworten an, wie Sie diese Aufgabe anpacken würden.
        </p>
      </div>
    </div>
    <div v-else-if="page === 3" class="flex h-full flex-col">
      <div class="flex-[0.6] overflow-hidden px-6 pt-3 font-serif text-base">
        <div class="flex h-full flex-col gap-4">
          <div class="px-4 py-2 text-center">
            <h1 class="text-xl font-semibold tracking-[0.4em]">Aufgabe 3</h1>
          </div>
          <div class="flex flex-1 gap-4">
            <div class="flex-1 border border-black/20 px-6 py-4">
              <div class="space-y-4 text-center text-base leading-relaxed">
                <p class="text-lg">
                  Sie sollen jeden der acht Beträge auf nebenstehender Liste gegen Quittung in bar auszahlen.
                </p>
                <p class="text-lg">
                  Rechnen Sie bitte aus, welche Geldsorten Sie benötigen. Sie sollen in keinem Falle wechseln
                  und möglichst wenig Geldstücke bzw. Scheine brauchen.
                </p>
                <p class="text-lg">
                  Tragen Sie unten ein, welche Anzahl Sie hierzu von jeder Geldsorte benötigen!
                </p>
              </div>
              <!-- <div class="mt-4 text-center text-base">_____</div>
              <p class="mt-6 text-center text-base">In diesem Heft bitte keine Notizen machen!</p> -->
            </div>

            <div class="flex-1 border border-black/20 px-4 py-2">
              <div class="flex h-full items-stretch justify-center">
                <div class="h-full w-[240px] border-2 border-black px-10 py-6 text-xl">
                  <table class="h-full w-full border-collapse">
                    <tbody>
                      <tr>
                        <td class="w-8 align-middle text-left">€</td>
                        <td class="align-middle text-right">2 854,50</td>
                      </tr>
                      <tr>
                        <td class="align-middle text-left">“</td>
                        <td class="align-middle text-right">26,00</td>
                      </tr>
                      <tr>
                        <td class="align-middle text-left">“</td>
                        <td class="align-middle text-right">144,00</td>
                      </tr>
                      <tr>
                        <td class="align-middle text-left">“</td>
                        <td class="align-middle text-right">211,50</td>
                      </tr>
                      <tr>
                        <td class="align-middle text-left">“</td>
                        <td class="align-middle text-right">86,00</td>
                      </tr>
                      <tr>
                        <td class="align-middle text-left">“</td>
                        <td class="align-middle text-right">3 221,50</td>
                      </tr>
                      <tr>
                        <td class="align-middle text-left">“</td>
                        <td class="align-middle text-right">175,50</td>
                      </tr>
                      <tr>
                        <td class="align-middle text-left">“</td>
                        <td class="align-middle text-right">11,00</td>
                      </tr>
                      <tr class="border-t border-black">
                        <td class="pt-2 align-middle text-left">€</td>
                        <td class="pt-2 align-middle text-right">6 730,00</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex-[0.4] overflow-hidden px-6 pb-4 pt-2 font-serif">
        <div class="flex h-full flex-col">
          <div class="border-t border-black" />
          <div class="flex-1">
            <table class="mt-3 w-full table-fixed border border-black text-base">
              <thead>
                <tr>
                  <th class="w-28 border border-black p-1 text-left">Geldsorten</th>
                  <th v-for="denomination in cashDenominations" :key="denomination.key"
                    class="border border-black p-1 text-center">
                    {{ denomination.label }}
                  </th>
                  <th class="w-10 border border-black p-1" />
                </tr>
              </thead>
              <tbody>
                <tr>
                  <th class="border border-black p-1 text-left">Anzahl</th>
                  <td v-for="denomination in cashDenominations" :key="`input-${denomination.key}`"
                    class="border border-black p-1 text-center">
                    <input :value="cashAnswers[denomination.key]" type="text" inputmode="numeric" maxlength="2"
                      class="h-6 w-10 border border-black text-center text-base"
                      @input="(event) => handleCashInput(denomination.key, event)" />
                  </td>
                  <td class="border border-black p-1" />
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div v-else-if="page === 4" class="flex h-full flex-col">
      <div class="flex-[0.7] overflow-hidden px-6 pt-3 font-serif text-base">
        <div class="flex h-full flex-col gap-4">
          <div class="px-4 py-2 text-center">
            <h1 class="text-xl font-semibold tracking-[0.4em]">Aufgabe 4</h1>
          </div>
          <div class="flex flex-1 gap-4">
            <div class="flex-1 border border-black/20 px-6 py-4">
              <div class="space-y-4 text-center text-base leading-relaxed">
                <p class="text-lg">
                  Sie erhalten 10 Ordner und eine Menge Briefe. Diese sollen nach den Anfangsbuchstaben der
                  Absender geordnet in den Ordnern abgelegt werden. Beim Sortieren der Briefe haben Sie
                  festgestellt, dass die einzelnen Buchstaben des Alphabets prozentual unterschiedlich oft
                  vorkommen (vgl. nebenstehende Zusammenstellung). Zum Schluss soll jeder Ordner die gleiche
                  Menge Briefe enthalten.
                </p>
                <div class="text-center text-lg">Wie müssen die 25 Buchstaben des Alphabets</div>
                <div class="mx-auto w-fit border border-black px-4 py-1 text-lg tracking-[0.35em]">
                  A B C D E F G H J K L M N O P Q R S T U V W X Y Z
                </div>
                <div class="text-center text-lg">auf die 10 Ordner aufgeteilt werden?</div>
                <p class="text-lg">
                  Schreiben Sie bitte die betreffenden Buchstaben unter die jeweilige Ordner-Nr. unten in die Tabelle!
                </p>
              </div>
              <!-- <div class="mt-4 text-center text-base">_____</div> -->
            </div>
            <div class="flex-1 border border-black/20">
              <div class="flex h-full w-full items-center justify-center border-2 border-black p-8">
                <table class="w-full border-collapse text-lg leading-tight">
                  <tbody>
                    <tr>
                      <td colspan="7" class="pb-8 text-center text-xl">Von allen Absendern beginnen</td>
                    </tr>
                    <tr>
                      <td class="w-[12%] py-1 text-left">jeweils</td>
                      <td class="w-[8%] py-1 text-right">2,5</td>
                      <td class="w-[4%] py-1 text-center">%</td>
                      <td class="py-1 pl-8 text-left">mit den Anfangsbuchstaben</td>
                      <td class="w-[15%] py-1"></td>
                      <td class="w-[4%] py-1"></td>
                      <td class="w-[25%] py-1"></td>
                    </tr>
                    <tr>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td class="py-1 pl-8 text-left tracking-[0.35em]">A F W B G J</td>
                      <td></td>
                      <td></td>
                      <td></td>
                    </tr>
                    <tr>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td class="py-1 pl-8 text-left tracking-[0.35em]">X Y C H Z D</td>
                      <td class="py-1 pl-4 text-left">zusammen</td>
                      <td class="py-1 text-center">=</td>
                      <td class="py-1 text-right">30 % aller Briefe</td>
                    </tr>
                    <tr class="h-4">
                      <td colspan="7"></td>
                    </tr>
                    <tr>
                      <td class="py-1 text-left">jeweils</td>
                      <td class="py-1 text-right">3,33</td>
                      <td class="py-1 text-center">%</td>
                      <td class="py-1 pl-8 text-left">mit den Anfangsbuchstaben</td>
                      <td></td>
                      <td></td>
                      <td></td>
                    </tr>
                    <tr>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td class="py-1 pl-8 text-left"><span class="tracking-[0.35em]">R P</span> und <span
                          class="tracking-[0.35em]">Q</span></td>
                      <td class="py-1 pl-4 text-left">zusammen</td>
                      <td class="py-1 text-center">=</td>
                      <td class="py-1 text-right">10 % aller Briefe</td>
                    </tr>
                    <tr class="h-4">
                      <td colspan="7"></td>
                    </tr>
                    <tr>
                      <td class="py-1 text-left">jeweils</td>
                      <td class="py-1 text-right">5,0</td>
                      <td class="py-1 text-center">%</td>
                      <td class="py-1 pl-8 text-left">mit den Anfangsbuchstaben</td>
                      <td></td>
                      <td></td>
                      <td></td>
                    </tr>
                    <tr>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td class="py-1 pl-8 text-left tracking-[0.35em]">N K S V T U</td>
                      <td></td>
                      <td></td>
                      <td></td>
                    </tr>
                    <tr>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td class="py-1 pl-8 text-left"><span class="tracking-[0.35em]">O</span> und <span
                          class="tracking-[0.35em]">L</span></td>
                      <td class="py-1 pl-4 text-left">zusammen</td>
                      <td class="py-1 text-center">=</td>
                      <td class="py-1 text-right">40 % aller Briefe</td>
                    </tr>
                    <tr class="h-4">
                      <td colspan="7"></td>
                    </tr>
                    <tr>
                      <td class="py-1 text-left">jeweils</td>
                      <td class="py-1 text-right">10,0</td>
                      <td class="py-1 text-center">%</td>
                      <td class="py-1 pl-8 text-left">mit den Anfangsbuchstaben</td>
                      <td></td>
                      <td></td>
                      <td></td>
                    </tr>
                    <tr>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td class="border-b border-black py-1 pl-8 text-left"><span class="tracking-[0.35em]">M</span> und
                        <span class="tracking-[0.35em]">E</span></td>
                      <td class="border-b border-black py-1 pl-4 text-left">zusammen</td>
                      <td class="border-b border-black py-1 text-center">=</td>
                      <td class="border-b border-black py-1 text-right">20 % aller Briefe</td>
                    </tr>
                    <tr>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td class="py-1 pl-4 text-left">zusammen</td>
                      <td class="py-1 text-center">=</td>
                      <td class="py-1 text-right">100 % aller Briefe</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="flex-[0.3] overflow-hidden px-6 pb-4 pt-2 font-serif">
        <div class="flex min-h-full flex-col">
          <div class="border-t border-black" />
          <div class="flex-1">
            <div class="mt-3 text-left text-base font-semibold">Aufgabe 4</div>
            <table class="mt-2 w-full table-fixed border border-black text-base">
              <tbody>
                <tr>
                  <th class="w-28 border border-black p-1 text-left">Ordner-Nr</th>
                  <td v-for="index in 10" :key="`ordner-${index}`" class="border border-black p-1 text-center">
                    {{ index }}
                  </td>
                  <td class="w-16 border border-black p-1" rowspan="2" />
                </tr>
                <tr>
                  <th class="border border-black p-1 text-left">Buchstaben</th>
                  <td v-for="index in 10" :key="`letters-${index}`" class="border border-black p-1 text-center">
                    <input :value="folderAnswers[index]" type="text" inputmode="text" maxlength="5"
                      class="h-6 w-16 border border-black text-center text-base uppercase"
                      @input="(event) => handleFolderInput(index, event)" />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div v-else-if="page === 5" class="flex h-full flex-col overflow-hidden">
      <div class="flex-[0.7] overflow-hidden px-6 pt-3 font-serif text-base">
        <div class="flex h-full flex-col gap-4">
          <div class="px-4 py-2 text-center">
            <h1 class="text-xl font-semibold tracking-[0.4em]">Aufgabe 5</h1>
          </div>
          <div class="flex flex-1 gap-4">
            <div class="flex-1 border border-black/20 px-6 py-4">
              <div class="space-y-4 text-center text-xl leading-relaxed">
                <p>
                  Am Beginn des 1. Arbeitstages im Monat enthielt Ihre Portokasse Briefmarken für € 50,00.
                </p>
                <p class="font-semibold">Die Anweisung lautet:</p>
                <p>
                  Es müssen abends auf der Post für € 20,00 neue Briefmarken gekauft werden, sobald der
                  Markenbestand unter € 12,00 gesunken ist.
                </p>
                <p>
                  An welchen Arbeitstagen müssen Sie neue Briefmarken kaufen, wenn für die Postsendungen die
                  in der Aufstellung genannten Briefmarken gebraucht worden sind?
                </p>
                <p>
                  Bitte auf einem extra Blatt Nebenrechnungen vornehmen und dann die betreffenden Tage unten in der
                  Tabelle anklicken (sodass ein „X“ erscheint).
                </p>
                <!-- <p>In dieses Heft bitte keine Notizen machen!</p> -->
              </div>
              <!-- <div class="mt-4 text-center text-base">_____</div> -->
            </div>
            <div class="flex-1 border border-black/20">
              <div class="flex h-full w-full flex-col items-center justify-center gap-3 border-2 border-black p-6">
                <div class="text-center text-xl">Aufstellung der gebrauchten Briefmarken</div>
                <table class="w-full border-collapse text-xl leading-tight">
                  <thead>
                    <tr>
                      <th class="w-24 border border-black p-1 text-left" rowspan="2">Arbeitstag (abends)</th>
                      <th class="border border-black p-1 text-center" colspan="5">Briefmarken (Werte in €)</th>
                    </tr>
                    <tr>
                      <th class="border border-black p-1 text-center">0,70</th>
                      <th class="border border-black p-1 text-center">0,20</th>
                      <th class="border border-black p-1 text-center">0,15</th>
                      <th class="border border-black p-1 text-center">0,10</th>
                      <th class="border border-black p-1 text-center">0,07</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="row in stampUsage" :key="`day-${row.day}`">
                      <td class="border border-black p-1 text-center">{{ row.day }}</td>
                      <td v-for="(value, index) in row.values" :key="`value-${row.day}-${index}`"
                        class="border border-black p-1 text-center">
                        {{ value }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="flex-[0.4] overflow-hidden px-6 pb-4 pt-2 font-serif">
        <div class="flex min-h-full flex-col">
          <div class="border-t border-black" />
          <div class="flex-1">
            <table class="mt-3 w-full table-fixed border border-black text-base">
              <tbody>
                <tr>
                  <th class="w-48 border border-black p-2 text-left" rowspan="2">
                    <div class="text-base font-semibold">Aufgabe 5</div>
                    <div class="mt-1 text-sm font-normal">
                      Neue Briefmarken gekauft am (AT = Arbeitstag)
                    </div>
                  </th>
                  <th v-for="day in 10" :key="`stamp-day-${day}`" class="border border-black p-1 text-center">
                    {{ day }}.AT
                  </th>
                  <th class="w-16 border border-black p-1" rowspan="2" />
                </tr>
                <tr>
                  <td v-for="day in 10" :key="`stamp-answer-${day}`" class="border border-black p-1 text-center">
                    <button type="button" class="h-10 w-full border border-black text-base font-semibold"
                      :aria-pressed="stampAnswerDays[day]" @click="() => toggleStampAnswer(day)">
                      {{ stampAnswerDays[day] ? 'X' : '' }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div v-else-if="page === 6" class="flex h-full flex-col overflow-hidden">
      <div class="flex-[0.6] px-6 pt-3 font-serif text-base overflow-hidden">
        <div class="flex h-full flex-col gap-2">
          <div class="px-4 py-1 text-center">
            <h1 class="text-xl font-semibold tracking-[0.4em]">Aufgabe 6</h1>
          </div>
          <div class="flex flex-1 gap-4 items-stretch overflow-hidden">
            <div class="flex-[0.45] border border-black/20 px-6 py-4 overflow-hidden">
              <div class="flex h-full flex-col justify-between space-y-4 text-center text-xl leading-relaxed">
                <p>
                  Sie sollen Ihre Bekannten möglichst rasch benachrichtigen, dass die Feier zu Ihrem
                  Geburtstag aus dienstlichen Gründen um einen Tag verlegt werden muss. Sie wissen, dass Sie
                  alle jetzt zu Hause antreffen werden und sollen mit allen selbst sprechen, d.h. keinen
                  anderen als Boten beauftragen. Einige haben Telefon, einige können Sie nur mit dem Fahrrad
                  erreichen. Andere Beförderungsmittel sollen nicht benutzt werden. Die eigene Wohnung besitzt
                  kein Telefon.
                </p>
                <p>
                  Geben Sie bitte unten in der Tabelle an, in welcher Reihenfolge Sie Ihre Bekannten
                  <span style="letter-spacing: 0.25em; white-space: pre;">b e n a c h r i c h t i g e n</span> (Namen mit der Maus in das
                  jeweilige Feld hineinziehen) und wie viel Zeit Sie dafür jeweils im Einzelnen und insgesamt (ohne
                  Rückweg) brauchen (Zeiten eintragen).
                </p>
                <p>
                  Für Benachrichtigung sind jeweils (persönlich oder per Telefon) 3 Minuten zu berechnen und in der
                  Tabelle unter „Nachricht“ einzutragen.
                </p>
                <!-- <p class="font-semibold">In dieses Heft bitte keine Notizen machen!</p> -->
                <!-- <div class="text-center text-lg">_____</div> -->
              </div>
            </div>
            <div class="flex-[0.55] border-2 border-black p-4 flex flex-col overflow-hidden">
              <div class="mb-2 text-center text-xl font-bold underline underline-offset-4"
                style="letter-spacing: 0.4em;">P l a n</div>
              <div class="relative h-full w-full min-h-0 flex-1 select-none">
                <svg class="absolute inset-0 h-full w-full" viewBox="0 0 800 400">
                  <!-- Lines -->
                  <line x1="120" y1="180" x2="400" y2="80" stroke="black" stroke-width="1" /> <!-- M-Fr -->
                  <line x1="120" y1="180" x2="400" y2="350" stroke="black" stroke-width="1" /> <!-- M-Fu -->
                  <line x1="400" y1="80" x2="680" y2="50" stroke="black" stroke-width="1" /> <!-- Fr-B -->
                  <line x1="400" y1="80" x2="680" y2="160" stroke="black" stroke-width="1" /> <!-- Fr-H -->
                  <line x1="400" y1="80" x2="680" y2="300" stroke="black" stroke-width="1" /> <!-- Fr-S -->
                  <line x1="400" y1="80" x2="400" y2="220" stroke="black" stroke-width="1" /> <!-- Fr-Ho -->
                  <line x1="680" y1="50" x2="680" y2="160" stroke="black" stroke-width="1" /> <!-- B-H -->
                  <line x1="680" y1="160" x2="680" y2="300" stroke="black" stroke-width="1" /> <!-- H-S -->
                  <line x1="680" y1="160" x2="400" y2="220" stroke="black" stroke-width="1" /> <!-- H-Ho -->
                  <line x1="680" y1="300" x2="400" y2="220" stroke="black" stroke-width="1" /> <!-- S-Ho -->
                  <line x1="680" y1="300" x2="400" y2="350" stroke="black" stroke-width="1" /> <!-- S-Fu -->
                  <line x1="400" y1="350" x2="400" y2="220" stroke="black" stroke-width="1" /> <!-- Fu-Ho -->

                  <!-- Numbers -->
                  <text x="240" y="125" font-size="14" class="font-serif">5</text> <!-- M-Fr -->
                  <text x="240" y="280" font-size="14" class="font-serif">20</text> <!-- M-Fu -->
                  <text x="540" y="60" font-size="14" class="font-serif">5</text> <!-- Fr-B -->
                  <text x="540" y="115" font-size="14" class="font-serif">5</text> <!-- Fr-H -->
                  <text x="580" y="250" font-size="14" class="font-serif">12</text> <!-- Fr-S -->
                  <text x="410" y="150" font-size="14" class="font-serif">5</text> <!-- Fr-Ho -->
                  <text x="690" y="110" font-size="14" class="font-serif">5</text> <!-- B-H -->
                  <text x="690" y="240" font-size="14" class="font-serif">5</text> <!-- H-S -->
                  <text x="580" y="175" font-size="14" class="font-serif">8</text> <!-- H-Ho -->
                  <text x="540" y="275" font-size="14" class="font-serif">5</text> <!-- S-Ho -->
                  <text x="540" y="340" font-size="14" class="font-serif">15</text> <!-- S-Fu -->
                  <text x="410" y="290" font-size="14" class="font-serif">15</text> <!-- Fu-Ho -->
                </svg>

                <!-- Draggable Names -->
                <div
                  class="absolute cursor-move border border-black bg-white px-4 py-1 text-xl font-serif whitespace-nowrap"
                  style="left: calc(120/800 * 100%); top: calc(180/400 * 100%); transform: translate(-50%, -50%);"
                  draggable="true" @dragstart="(event) => handleDragStart(event, { name: 'Müller', from: 'pool' })">
                  Müller (Tel.)
                </div>
                <div class="absolute cursor-move border border-black bg-white px-4 py-1 text-xl font-serif"
                  style="left: calc(400/800 * 100%); top: calc(80/400 * 100%); transform: translate(-50%, -50%);"
                  draggable="true" @dragstart="(event) => handleDragStart(event, { name: 'Frey', from: 'pool' })">
                  Frey
                </div>
                <div
                  class="absolute cursor-move border border-black bg-white px-4 py-1 text-xl font-serif whitespace-nowrap"
                  style="left: calc(680/800 * 100%); top: calc(50/400 * 100%); transform: translate(-50%, -50%);"
                  draggable="true" @dragstart="(event) => handleDragStart(event, { name: 'Bär', from: 'pool' })">
                  Bär (Tel.)
                </div>
                <div
                  class="absolute cursor-move border border-black bg-white px-4 py-1 text-xl font-serif whitespace-nowrap"
                  style="left: calc(680/800 * 100%); top: calc(160/400 * 100%); transform: translate(-50%, -50%);"
                  draggable="true" @dragstart="(event) => handleDragStart(event, { name: 'Hermann', from: 'pool' })">
                  Hermann (Tel.)
                </div>
                <div
                  class="absolute cursor-move border border-black bg-white px-4 py-1 text-xl font-serif whitespace-nowrap"
                  style="left: calc(680/800 * 100%); top: calc(300/400 * 100%); transform: translate(-50%, -50%);"
                  draggable="true" @dragstart="(event) => handleDragStart(event, { name: 'Schneider', from: 'pool' })">
                  Schneider (Tel.)
                </div>
                <div class="absolute cursor-move border border-black bg-white px-4 py-1 text-xl font-serif"
                  style="left: calc(400/800 * 100%); top: calc(350/400 * 100%); transform: translate(-50%, -50%);"
                  draggable="true" @dragstart="(event) => handleDragStart(event, { name: 'Fuchs', from: 'pool' })">
                  Fuchs
                </div>

                <!-- EIGENE WOHNUNG -->
                <div
                  class="absolute border-[3px] border-double border-black bg-white px-6 py-1 text-xl font-bold font-serif whitespace-nowrap"
                  style="left: calc(400/800 * 100%); top: calc(220/400 * 100%); transform: translate(-50%, -50%);">
                  EIGENE WOHNUNG
                </div>
                <div
                  class="absolute bottom-0 left-0 w-fit max-w-[80%] border border-black bg-white/95 px-3 py-2 text-[20px] leading-tight">
                  <div class="flex gap-3">
                    <span class="w-14">(Tel.)</span>
                    <span>= hat Telefonanschluss</span>
                  </div>
                  <div class="flex gap-3">
                    <span class="w-14"> Zahlen </span>
                    <span>= Wegminuten mit Fahrrad</span>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex-[0.4] px-6 pb-4 pt-1 font-serif overflow-hidden">
        <div class="flex h-full flex-col">
          <div class="border-t border-black" />
          <div class="flex-1 overflow-hidden">
            <div class="mt-0.5 text-left text-sm font-bold">Aufgabe 6</div>
            <table class="mt-0.5 w-full table-fixed border-2 border-black text-sm">
              <thead>
                <tr class="border-b border-black">
                  <th class="border-r border-black p-1 text-center font-normal">von</th>
                  <th class="border-r border-black p-1 text-center font-normal">nach</th>
                  <th class="border-r border-black p-1 text-center font-normal">Zeit (Weg)</th>
                  <th class="p-1 text-center font-normal">Zeit (Nachr.)</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in routeRows" :key="`route-row-${row}`" class="border-b border-black h-9">
                  <td class="border-r border-black px-2" @dragover="allowDrop"
                    @drop="(event) => handleRouteDropOnCell(event, `route-from-${row}`)">
                    <div class="flex items-center gap-2">
                      <span v-if="routeAssignments[`route-from-${row}`]" class="flex-1 min-w-0"
                        :class="{ 'cursor-move': row !== 1 || routeAssignments[`route-from-${row}`] !== 'Eigene Wohnung' }"
                        :draggable="row !== 1 || routeAssignments[`route-from-${row}`] !== 'Eigene Wohnung'"
                        @dragstart="(event) => handleDragStart(event, { name: routeAssignments[`route-from-${row}`] ?? '', from: 'cell', key: `route-from-${row}` })">
                        {{ routeAssignments[`route-from-${row}`] }}
                      </span>
                      <span v-else class="flex-1">&nbsp;</span>
                      <button
                        v-if="routeAssignments[`route-from-${row}`] && (row !== 1 || routeAssignments[`route-from-${row}`] !== 'Eigene Wohnung')"
                        type="button"
                        class="flex h-4 w-4 items-center justify-center rounded-full border border-black text-[10px] leading-none"
                        @click="routeAssignments[`route-from-${row}`] = null">
                        ×
                      </button>
                    </div>
                  </td>
                  <td class="border-r border-black px-2" @dragover="allowDrop"
                    @drop="(event) => handleRouteDropOnCell(event, `route-to-${row}`)">
                    <div class="flex items-center gap-2">
                      <span v-if="routeAssignments[`route-to-${row}`]" class="flex-1 min-w-0 cursor-move"
                        draggable="true"
                        @dragstart="(event) => handleDragStart(event, { name: routeAssignments[`route-to-${row}`] ?? '', from: 'cell', key: `route-to-${row}` })">
                        {{ routeAssignments[`route-to-${row}`] }}
                      </span>
                      <span v-else class="flex-1">&nbsp;</span>
                      <button v-if="routeAssignments[`route-to-${row}`]" type="button"
                        class="flex h-4 w-4 items-center justify-center rounded-full border border-black text-[10px] leading-none"
                        @click="routeAssignments[`route-to-${row}`] = null">
                        ×
                      </button>
                    </div>
                  </td>
                  <td class="border-r border-black px-2">
                    <div class="flex items-center justify-end gap-3 pr-4">
                      <input :value="routeTimes[`route-time-${row}`]" type="text" inputmode="numeric" maxlength="3"
                        class="h-6 w-16 border border-black text-center text-base"
                        @input="(event) => handleRouteTimeInput(`route-time-${row}`, event)" />
                      <span class="w-8">Min.</span>
                    </div>
                  </td>
                  <td class="px-2">
                    <div class="flex items-center justify-end gap-2 pr-8">
                      <input :value="routeTimes[`route-msg-${row}`]" type="text" inputmode="numeric" maxlength="3"
                        class="h-6 w-16 border border-black text-center text-base"
                        @input="(event) => handleRouteTimeInput(`route-msg-${row}`, event)" />
                      <span class="w-8">Min.</span>
                    </div>
                  </td>
                </tr>
                <tr class="border-b border-black h-9">
                  <td class="border-r border-black px-2 text-right pr-4" colspan="2">Gesamtzeit:</td>
                  <td class="border-r border-black px-2">
                    <div class="flex items-center justify-end gap-3 pr-4">
                      <input :value="routeTotals.totalWay" type="text" inputmode="numeric" maxlength="3"
                        class="h-6 w-16 border border-black text-center text-base"
                        @input="(event) => handleRouteTotalInput('totalWay', event)" />
                      <span class="w-8">Min.</span>
                    </div>
                  </td>
                  <td class="px-2">
                    <div class="flex items-center justify-end gap-2 pr-8">
                      <input :value="routeTotals.totalMsg" type="text" inputmode="numeric" maxlength="3"
                        class="h-6 w-16 border border-black text-center text-base"
                        @input="(event) => handleRouteTotalInput('totalMsg', event)" />
                      <span class="w-8">Min.</span>
                    </div>
                  </td>
                </tr>
                <tr class="h-9">
                  <td class="border-r border-black px-2 text-right pr-4" colspan="2">
                    anschließend zurück zur eigenen Wohnung:
                  </td>
                  <td class="border-r border-black px-2">
                    <div class="flex items-center justify-end gap-3 pr-4">
                      <input :value="routeTotals.returnWay" type="text" inputmode="numeric" maxlength="3"
                        class="h-6 w-16 border border-black text-center text-base"
                        @input="(event) => handleRouteTotalInput('returnWay', event)" />
                      <span class="w-8">Min.</span>
                    </div>
                  </td>
                  <td class="px-2" />
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
