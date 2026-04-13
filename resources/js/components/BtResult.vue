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
} as const;

const values = ref<Record<string, string>>({
  [scoreKeys.earlyTwoSyllableCount]: '',
  [scoreKeys.earlyThreeSyllableCount]: '',
  [scoreKeys.lateOneSyllableCount]: '',
  [scoreKeys.lateThreeSyllableCount]: '',
});

watch(
  () => props.manualScores,
  (next) => {
    values.value[scoreKeys.earlyTwoSyllableCount] = toInput(next?.[scoreKeys.earlyTwoSyllableCount]);
    values.value[scoreKeys.earlyThreeSyllableCount] = toInput(next?.[scoreKeys.earlyThreeSyllableCount]);
    values.value[scoreKeys.lateOneSyllableCount] = toInput(next?.[scoreKeys.lateOneSyllableCount]);
    values.value[scoreKeys.lateThreeSyllableCount] = toInput(next?.[scoreKeys.lateThreeSyllableCount]);
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

function toInput(value: number | string | null | undefined) {
  if (value == null || value === '') return '';
  return `${value}`;
}

function toNumber(value: string): number | null {
  if (!value) return null;
  const parsed = Number(value);
  return Number.isNaN(parsed) ? null : parsed;
}

function sanitizeAndSet(key: string, event: Event) {
  const target = event.target as HTMLInputElement;
  const sanitized = target.value.replace(/\D/g, '').slice(0, 2);
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
  </div>
</template>
