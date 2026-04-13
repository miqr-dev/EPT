<script setup lang="ts">
import { Input } from '@/components/ui/input';
import axios from 'axios';
import { computed, ref, watch } from 'vue';

const props = withDefaults(
  defineProps<{
    testResultId?: number | null;
    manualScores?: Record<string, number | string | null>;
  }>(),
  {
    manualScores: () => ({}),
  },
);

const emit = defineEmits<{
  'manual-score-updated': [{ key: string; value: number | null }];
}>();

const scoreKeys = {
  earlyTwoSyllableCount: 'bt_q1_early_two_syllable_count',
  earlyThreeSyllableCount: 'bt_q1_early_three_syllable_count',
  lateOneSyllableCount: 'bt_q1_late_one_syllable_count',
  lateThreeSyllableCount: 'bt_q1_late_three_syllable_count',
  q3Count100: 'bt_q3_count_100',
  q3Count50: 'bt_q3_count_50',
  q3Count20: 'bt_q3_count_20',
  q3Count10: 'bt_q3_count_10',
  q3Count5: 'bt_q3_count_5',
  q3Count2: 'bt_q3_count_2',
  q3Count1: 'bt_q3_count_1',
  q3Count050: 'bt_q3_count_050',
  q4CorrectFolderCount: 'bt_q4_correct_folder_count',
  q4AlphabeticalOrderMet: 'bt_q4_alphabetical_order_met',
  q4FolderOrderMet: 'bt_q4_folder_order_met',
} as const;

const questionThreeCriteria = [
  { label: '100,-', key: scoreKeys.q3Count100, expected: 64 },
  { label: '50,-', key: scoreKeys.q3Count50, expected: 3 },
  { label: '20,-', key: scoreKeys.q3Count20, expected: 6 },
  { label: '10,-', key: scoreKeys.q3Count10, expected: 3 },
  { label: '5,-', key: scoreKeys.q3Count5, expected: 3 },
  { label: '2,-', key: scoreKeys.q3Count2, expected: 4 },
  { label: '1,-', key: scoreKeys.q3Count1, expected: 5 },
  { label: '0,50', key: scoreKeys.q3Count050, expected: 4 },
] as const;

const values = ref<Record<string, string>>({
  [scoreKeys.earlyTwoSyllableCount]: '',
  [scoreKeys.earlyThreeSyllableCount]: '',
  [scoreKeys.lateOneSyllableCount]: '',
  [scoreKeys.lateThreeSyllableCount]: '',
  [scoreKeys.q3Count100]: '',
  [scoreKeys.q3Count50]: '',
  [scoreKeys.q3Count20]: '',
  [scoreKeys.q3Count10]: '',
  [scoreKeys.q3Count5]: '',
  [scoreKeys.q3Count2]: '',
  [scoreKeys.q3Count1]: '',
  [scoreKeys.q3Count050]: '',
  [scoreKeys.q4CorrectFolderCount]: '',
});
const q4AlphabeticalOrderMet = ref(false);
const q4FolderOrderMet = ref(false);

watch(
  () => props.manualScores,
  (next) => {
    values.value[scoreKeys.earlyTwoSyllableCount] = toInput(next?.[scoreKeys.earlyTwoSyllableCount]);
    values.value[scoreKeys.earlyThreeSyllableCount] = toInput(next?.[scoreKeys.earlyThreeSyllableCount]);
    values.value[scoreKeys.lateOneSyllableCount] = toInput(next?.[scoreKeys.lateOneSyllableCount]);
    values.value[scoreKeys.lateThreeSyllableCount] = toInput(next?.[scoreKeys.lateThreeSyllableCount]);
    values.value[scoreKeys.q3Count100] = toInput(next?.[scoreKeys.q3Count100]);
    values.value[scoreKeys.q3Count50] = toInput(next?.[scoreKeys.q3Count50]);
    values.value[scoreKeys.q3Count20] = toInput(next?.[scoreKeys.q3Count20]);
    values.value[scoreKeys.q3Count10] = toInput(next?.[scoreKeys.q3Count10]);
    values.value[scoreKeys.q3Count5] = toInput(next?.[scoreKeys.q3Count5]);
    values.value[scoreKeys.q3Count2] = toInput(next?.[scoreKeys.q3Count2]);
    values.value[scoreKeys.q3Count1] = toInput(next?.[scoreKeys.q3Count1]);
    values.value[scoreKeys.q3Count050] = toInput(next?.[scoreKeys.q3Count050]);
    values.value[scoreKeys.q4CorrectFolderCount] = toInput(next?.[scoreKeys.q4CorrectFolderCount]);
    q4AlphabeticalOrderMet.value = toBoolean(next?.[scoreKeys.q4AlphabeticalOrderMet]);
    q4FolderOrderMet.value = toBoolean(next?.[scoreKeys.q4FolderOrderMet]);
  },
  { immediate: true, deep: true },
);

const earlyTwoSyllablePoints = computed(() => (toNumber(values.value[scoreKeys.earlyTwoSyllableCount]) === 9 ? 3 : 0));
const earlyThreeSyllablePoints = computed(() => (toNumber(values.value[scoreKeys.earlyThreeSyllableCount]) === 1 ? 1 : 0));
const lateOneSyllablePoints = computed(() => (toNumber(values.value[scoreKeys.lateOneSyllableCount]) === 8 ? 3 : 0));
const lateThreeSyllablePoints = computed(() => (toNumber(values.value[scoreKeys.lateThreeSyllableCount]) === 7 ? 2 : 0));

const questionOneTotal = computed(
  () =>
    earlyTwoSyllablePoints.value +
    earlyThreeSyllablePoints.value +
    lateOneSyllablePoints.value +
    lateThreeSyllablePoints.value,
);

const questionThreeTotal = computed(() =>
  questionThreeCriteria.reduce((sum, criterion) => {
    const value = toNumber(values.value[criterion.key]);
    return sum + (value === criterion.expected ? 1 : 0);
  }, 0),
);
const questionFourBasePoints = computed(() => {
  const correctFolders = toClampedFolderCount(values.value[scoreKeys.q4CorrectFolderCount]);
  if (correctFolders == null || correctFolders < 1) return 0;
  if (correctFolders <= 4) return 2;
  if (correctFolders <= 9) return 4;
  if (correctFolders === 10) return 6;
  return 0;
});
const questionFourAlphabeticalPoints = computed(() => (questionFourBasePoints.value === 6 && q4AlphabeticalOrderMet.value ? 1 : 0));
const questionFourFolderOrderPoints = computed(() =>
  questionFourBasePoints.value === 6 && q4AlphabeticalOrderMet.value && q4FolderOrderMet.value ? 1 : 0,
);
const questionFourTotal = computed(() => {
  return questionFourBasePoints.value + questionFourAlphabeticalPoints.value + questionFourFolderOrderPoints.value;
});

function toInput(value: number | string | null | undefined) {
  if (value == null || value === '') return '';
  return `${value}`;
}

function toNumber(value: string): number | null {
  if (!value) return null;
  const parsed = Number(value);
  return Number.isNaN(parsed) ? null : parsed;
}

function toBoolean(value: unknown): boolean {
  if (value === true || value === 1 || value === '1' || value === 'true') return true;
  return false;
}

function toClampedFolderCount(value: string): number | null {
  const parsed = toNumber(value);
  if (parsed == null) return null;
  return Math.max(0, Math.min(10, parsed));
}

function sanitizeAndSet(key: string, event: Event) {
  const target = event.target as HTMLInputElement;
  const sanitized = target.value.replace(/\D/g, '').slice(0, 2);
  if (key === scoreKeys.q4CorrectFolderCount) {
    const numeric = sanitized === '' ? '' : `${Math.min(10, Number(sanitized))}`;
    values.value[key] = numeric;
    target.value = numeric;
    return;
  }
  values.value[key] = sanitized;
  target.value = sanitized;
}

async function persistValue(key: string) {
  const numericValue = toNumber(values.value[key]);
  emit('manual-score-updated', { key, value: numericValue });

  if (!props.testResultId) return;

  await axios.put(route('test-results.manual-scores.update', { testResult: props.testResultId }), {
    key,
    value: numericValue,
  });
}

async function persistBooleanValue(key: string, value: boolean) {
  const numericValue = value ? 1 : 0;
  emit('manual-score-updated', { key, value: numericValue });

  if (!props.testResultId) return;

  await axios.put(route('test-results.manual-scores.update', { testResult: props.testResultId }), {
    key,
    value: numericValue,
  });
}
</script>

<template>
  <div class="space-y-4">
    <h3 class="text-lg font-semibold">BT – Aufgabe 1 (Scoring)</h3>
    <p class="text-sm text-muted-foreground">
      Punktvergabe: Frühdienst (9 zweisilbige Namen = 3 P.; 1 dreisilbiger Name = 1 P.),
      Spätdienst (8 einsilbige Namen = 3 P.; 7 dreisilbige Namen = 2 P.)
    </p>

    <div class="overflow-x-auto">
      <table class="min-w-full rounded-lg border text-sm shadow">
        <thead class="bg-muted/40">
          <tr>
            <th class="px-3 py-2 text-left">Kriterium</th>
            <th class="px-3 py-2 text-left">Eingabe</th>
            <th class="px-3 py-2 text-left">Punkte</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="px-3 py-2">Frühdienst: zweisilbige Namen (Soll: 9)</td>
            <td class="px-3 py-2">
              <Input
                :model-value="values[scoreKeys.earlyTwoSyllableCount]"
                class="w-20"
                inputmode="numeric"
                @input="(event) => sanitizeAndSet(scoreKeys.earlyTwoSyllableCount, event)"
                @blur="persistValue(scoreKeys.earlyTwoSyllableCount)"
              />
            </td>
            <td class="px-3 py-2 font-semibold">{{ earlyTwoSyllablePoints }} / 3</td>
          </tr>
          <tr>
            <td class="px-3 py-2">Frühdienst: dreisilbige Namen (Soll: 1)</td>
            <td class="px-3 py-2">
              <Input
                :model-value="values[scoreKeys.earlyThreeSyllableCount]"
                class="w-20"
                inputmode="numeric"
                @input="(event) => sanitizeAndSet(scoreKeys.earlyThreeSyllableCount, event)"
                @blur="persistValue(scoreKeys.earlyThreeSyllableCount)"
              />
            </td>
            <td class="px-3 py-2 font-semibold">{{ earlyThreeSyllablePoints }} / 1</td>
          </tr>
          <tr>
            <td class="px-3 py-2">Spätdienst: einsilbige Namen (Soll: 8)</td>
            <td class="px-3 py-2">
              <Input
                :model-value="values[scoreKeys.lateOneSyllableCount]"
                class="w-20"
                inputmode="numeric"
                @input="(event) => sanitizeAndSet(scoreKeys.lateOneSyllableCount, event)"
                @blur="persistValue(scoreKeys.lateOneSyllableCount)"
              />
            </td>
            <td class="px-3 py-2 font-semibold">{{ lateOneSyllablePoints }} / 3</td>
          </tr>
          <tr>
            <td class="px-3 py-2">Spätdienst: dreisilbige Namen (Soll: 7)</td>
            <td class="px-3 py-2">
              <Input
                :model-value="values[scoreKeys.lateThreeSyllableCount]"
                class="w-20"
                inputmode="numeric"
                @input="(event) => sanitizeAndSet(scoreKeys.lateThreeSyllableCount, event)"
                @blur="persistValue(scoreKeys.lateThreeSyllableCount)"
              />
            </td>
            <td class="px-3 py-2 font-semibold">{{ lateThreeSyllablePoints }} / 2</td>
          </tr>
        </tbody>
        <tfoot>
          <tr class="bg-muted/30">
            <td class="px-3 py-2 font-semibold" colspan="2">Aufgabe 1 Gesamt</td>
            <td class="px-3 py-2 font-bold">{{ questionOneTotal }} / 9</td>
          </tr>
        </tfoot>
      </table>
    </div>

    <h3 class="text-lg font-semibold">BT – Aufgabe 3 (Scoring)</h3>
    <p class="text-sm text-muted-foreground">Punktvergabe: Jede richtige Angabe = 1 Punkt.</p>

    <div class="overflow-x-auto">
      <table class="min-w-full rounded-lg border text-sm shadow">
        <thead class="bg-muted/40">
          <tr>
            <th class="px-3 py-2 text-left">Geldsorte</th>
            <th class="px-3 py-2 text-left">Soll</th>
            <th class="px-3 py-2 text-left">Eingabe</th>
            <th class="px-3 py-2 text-left">Punkte</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="criterion in questionThreeCriteria" :key="criterion.key">
            <td class="px-3 py-2">{{ criterion.label }}</td>
            <td class="px-3 py-2">{{ criterion.expected }}</td>
            <td class="px-3 py-2">
              <Input
                :model-value="values[criterion.key]"
                class="w-20"
                inputmode="numeric"
                @input="(event) => sanitizeAndSet(criterion.key, event)"
                @blur="persistValue(criterion.key)"
              />
            </td>
            <td class="px-3 py-2 font-semibold">{{ toNumber(values[criterion.key]) === criterion.expected ? 1 : 0 }} / 1</td>
          </tr>
        </tbody>
        <tfoot>
          <tr class="bg-muted/30">
            <td class="px-3 py-2 font-semibold" colspan="3">Aufgabe 3 Gesamt</td>
            <td class="px-3 py-2 font-bold">{{ questionThreeTotal }} / 8</td>
          </tr>
        </tfoot>
      </table>
    </div>

    <h3 class="text-lg font-semibold">BT – Aufgabe 4 (Scoring)</h3>
    <p class="text-sm text-muted-foreground">
      Punktvergabe: 1–4 richtige Ordner = 2 P., 5–9 = 4 P., 10 = 6 P.; bei 10 Ordnern zusätzlich
      alphabetische Reihenfolge eingehalten = +1 P., Ordner-Reihenfolge eingehalten = +1 P.
    </p>
    <div class="overflow-x-auto">
      <table class="min-w-full rounded-lg border text-sm shadow">
        <thead class="bg-muted/40">
          <tr>
            <th class="px-3 py-2 text-left">Kriterium</th>
            <th class="px-3 py-2 text-left">Eingabe</th>
            <th class="px-3 py-2 text-left">Punkte</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="px-3 py-2">Richtig verteilte Ordner (0–10)</td>
            <td class="px-3 py-2">
              <Input
                :model-value="values[scoreKeys.q4CorrectFolderCount]"
                class="w-20"
                inputmode="numeric"
                @input="(event) => sanitizeAndSet(scoreKeys.q4CorrectFolderCount, event)"
                @blur="persistValue(scoreKeys.q4CorrectFolderCount)"
              />
            </td>
            <td class="px-3 py-2 font-semibold">{{ questionFourBasePoints }} / 6</td>
          </tr>
          <tr>
            <td class="px-3 py-2">Alphabetische Reihenfolge eingehalten</td>
            <td class="px-3 py-2">
              <input
                v-model="q4AlphabeticalOrderMet"
                type="checkbox"
                class="h-4 w-4 align-middle"
                @change="persistBooleanValue(scoreKeys.q4AlphabeticalOrderMet, q4AlphabeticalOrderMet)"
              />
            </td>
            <td class="px-3 py-2 font-semibold">{{ questionFourAlphabeticalPoints }} / 1</td>
          </tr>
          <tr>
            <td class="px-3 py-2">Ordner-Reihenfolge eingehalten</td>
            <td class="px-3 py-2">
              <input
                v-model="q4FolderOrderMet"
                type="checkbox"
                class="h-4 w-4 align-middle"
                @change="persistBooleanValue(scoreKeys.q4FolderOrderMet, q4FolderOrderMet)"
              />
            </td>
            <td class="px-3 py-2 font-semibold">{{ questionFourFolderOrderPoints }} / 1</td>
          </tr>
        </tbody>
        <tfoot>
          <tr class="bg-muted/30">
            <td class="px-3 py-2 font-semibold" colspan="2">Aufgabe 4 Gesamt</td>
            <td class="px-3 py-2 font-bold">{{ questionFourTotal }} / 8</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>
