<script setup lang="ts">
import { Input } from '@/components/ui/input';
import axios from 'axios';
import { computed, ref, watch } from 'vue';

const props = withDefaults(
  defineProps<{
    testResultId?: number | null;
    manualScores?: Record<string, number | string | null>;
    results?: Record<string, any> | null;
  }>(),
  {
    manualScores: () => ({}),
    results: null,
  },
);

const emit = defineEmits<{
  'manual-score-updated': [{ key: string; value: number | null }];
}>();

const q2ManualScoreKey = 'bt_q2_manual_points';
const q2ManualPoints = ref('');
const days = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag'];

const q1Assignments = computed<Record<string, string | null>>(() => props.results?.assignments ?? {});
const q3CashAnswers = computed<Record<string, string>>(() => props.results?.cash_answers ?? {});
const q4FolderAnswers = computed<Record<number, string>>(() => props.results?.folder_answers ?? {});
const q5StampDays = computed<Record<number, boolean>>(() => props.results?.stamp_answer_days ?? {});
const q6RouteAssignments = computed<Record<string, string | null>>(() => props.results?.route_assignments ?? {});
const q6RouteTimes = computed<Record<string, string>>(() => props.results?.route_times ?? {});
const q6RouteTotals = computed<Record<string, string>>(() => props.results?.route_totals ?? {});

watch(
  () => props.manualScores,
  (next) => {
    const raw = next?.[q2ManualScoreKey];
    q2ManualPoints.value = raw == null || raw === '' ? '' : `${raw}`;
  },
  { immediate: true, deep: true },
);

function buildCellKey(shift: 'early' | 'late', slot: number, day: string) {
  return `${shift}-${slot}-${day}`;
}

function sanitizeManualPoints(event: Event) {
  const target = event.target as HTMLInputElement;
  const sanitized = target.value.replace(/\D/g, '').slice(0, 2);
  q2ManualPoints.value = sanitized;
  target.value = sanitized;
}

async function persistQ2ManualPoints() {
  const value = q2ManualPoints.value === '' ? null : Number(q2ManualPoints.value);
  emit('manual-score-updated', { key: q2ManualScoreKey, value });
  if (!props.testResultId) return;

  await axios.put(route('test-results.manual-scores.update', { testResult: props.testResultId }), {
    key: q2ManualScoreKey,
    value,
  });
}
</script>

<template>
  <div class="space-y-8">
    <section class="space-y-3">
      <h3 class="text-lg font-semibold">BT – Aufgabe 1</h3>
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
    </section>

    <section class="space-y-3">
      <h3 class="text-lg font-semibold">BT – Aufgabe 2</h3>
      <p class="text-sm text-muted-foreground">Punkteingabe durch Prüfer:in (manuell).</p>
      <Input :model-value="q2ManualPoints" class="w-24" inputmode="numeric" @input="sanitizeManualPoints" @blur="persistQ2ManualPoints" />
    </section>

    <section class="space-y-3">
      <h3 class="text-lg font-semibold">BT – Aufgabe 3</h3>
      <table class="min-w-full rounded-lg border text-sm shadow">
        <thead><tr><th class="border p-1 text-left">Geldsorte</th><th class="border p-1 text-left">Anzahl</th></tr></thead>
        <tbody>
          <tr v-for="(label, key) in { '100': '100€', '50': '50€', '20': '20€', '10': '10€', '5': '5€', '2': '2€', '1': '1€', '0.5': '0,50€' }" :key="key">
            <td class="border p-1">{{ label }}</td><td class="border p-1">{{ q3CashAnswers[key] || '—' }}</td>
          </tr>
        </tbody>
      </table>
    </section>

    <section class="space-y-3">
      <h3 class="text-lg font-semibold">BT – Aufgabe 4</h3>
      <table class="min-w-full rounded-lg border text-sm shadow">
        <thead><tr><th class="border p-1 text-left">Ordner-Nr.</th><th v-for="i in 10" :key="`h-${i}`" class="border p-1">{{ i }}</th></tr></thead>
        <tbody>
          <tr><td class="border p-1">Buchstaben</td><td v-for="i in 10" :key="`f-${i}`" class="border p-1">{{ q4FolderAnswers[i] || '—' }}</td></tr>
        </tbody>
      </table>
    </section>

    <section class="space-y-3">
      <h3 class="text-lg font-semibold">BT – Aufgabe 5</h3>
      <table class="min-w-full rounded-lg border text-sm shadow">
        <thead><tr><th class="border p-1 text-left">Tag</th><th class="border p-1 text-left">Markiert</th></tr></thead>
        <tbody><tr v-for="day in 10" :key="day"><td class="border p-1">{{ day }}.AT</td><td class="border p-1">{{ q5StampDays[day] ? 'X' : '—' }}</td></tr></tbody>
      </table>
    </section>

    <section class="space-y-3">
      <h3 class="text-lg font-semibold">BT – Aufgabe 6</h3>
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
    </section>
  </div>
</template>
