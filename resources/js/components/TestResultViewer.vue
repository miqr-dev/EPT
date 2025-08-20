<script setup lang="ts">
defineOptions({
  inheritAttrs: false,
});
import { ref, watch, computed } from 'vue';
import { Input } from '@/components/ui/input';
import MrtAResult from '@/components/MrtAResult.vue';
const bit2Groups = ['TH', 'GH', 'TN', 'EH', 'LF', 'KB', 'VB', 'LG', 'SE'];

interface Answer {
  question: string;
  user_answer: string;
  correct_answers?: string[] | string;
  time_seconds?: number;
  is_correct?: boolean;
}

interface ResultJson {
  rohwert?: number;
  prozentrang?: number;
  twert?: number;
  total_time_seconds?: number;
  answers: Answer[];
  [key: string]: any;
}

const props = defineProps<{
  modelValue: ResultJson | null;
  test: { name: string };
  participantProfile?: { age: number; sex?: string } | null;
}>();

const emit = defineEmits(['update:modelValue']);

const local = ref<ResultJson | null>(null);

watch(
  () => props.modelValue,
  (val) => {
    local.value = val ? JSON.parse(JSON.stringify(val)) : null;
  },
  { immediate: true, deep: true }
);

watch(
  local,
  (val) => {
    emit('update:modelValue', val as any);
  },
  { deep: true }
);

function formatTime(seconds?: number | null) {
  if (seconds == null) return '–';
  const m = Math.floor(seconds / 60);
  const s = seconds % 60;
  return `${m}:${s.toString().padStart(2, '0')}`;
}

// --- BIT‑2 percentile table (placeholder linear values) ---
const bit2Percentiles = [
  100, 95, 90, 85, 80, 75, 70, 65, 60, 55, 50, 45, 40, 35, 30, 25, 20, 15, 10, 5, 0,
];

function buildNormTable() {
  return bit2Percentiles.map((p) => {
    const row: Record<string, number | string> = { percentile: p };
    for (const code of bit2Groups) {
      row[code] = Math.floor(9 + (36 * p) / 100);
    }
    return row;
  });
}

const normTable = buildNormTable();

const highlighted = computed(() => {
  const map: Record<string, number | null> = {};
  if (!local.value) return map;
  for (const code of bit2Groups) {
    const raw = local.value.group_totals?.[code] ?? 0;
    let idx: number | null = null;
    for (let i = 0; i < normTable.length; i++) {
      const row = normTable[i];
      if (raw >= (row[code] as number)) {
        idx = i;
        break;
      }
    }
    map[code] = idx;
  }
  return map;
});
</script>

<template>
  <div v-if="local" v-bind="$attrs">
    <MrtAResult v-if="test.name === 'MRT-A'" :results="local" />
    <div v-else-if="test.name === 'BIT-2'" class="overflow-x-auto">
      <table class="w-full text-sm border rounded-lg overflow-hidden shadow mb-4">
        <thead class="bg-muted/40 dark:bg-gray-700">
          <tr>
            <th
              v-for="(code, idx) in bit2Groups"
              :key="code"
              class="px-3 py-2 font-semibold text-center"
            >
              {{ idx + 1 }} {{ code }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr class="dark:bg-gray-800">
            <td v-for="code in bit2Groups" :key="code" class="px-3 py-2 text-center">
              {{ local.group_totals?.[code] ?? 0 }}
            </td>
          </tr>
        </tbody>
      </table>
      <!-- Norm table -->
      <table class="min-w-full text-xs border">
        <thead class="bg-muted/40 dark:bg-gray-700">
          <tr>
            <th class="px-2 py-1 font-semibold text-center">{{
              props.participantProfile?.sex === 'f' ? '♀' : '♂'
            }}</th>
            <th
              v-for="code in bit2Groups"
              :key="code"
              class="px-2 py-1 font-semibold"
            >
              {{ code }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(row, rIdx) in normTable"
            :key="rIdx"
            :class="{
              'border-b-2 border-blue-500': [85, 60, 35, 20].includes(
                row.percentile as number
              ),
            }"
          >
            <td class="px-2 py-1 text-center">{{ row.percentile }}-</td>
            <td
              v-for="code in bit2Groups"
              :key="code"
              class="px-2 py-1 text-center"
              :class="{
                'text-red-600 underline decoration-2': highlighted[code] === rIdx,
              }"
            >
              {{ row[code] }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <template v-else>
      <table class="w-full text-sm border rounded-lg overflow-hidden shadow mb-4">
        <tbody>
          <tr class="bg-muted/40 dark:bg-gray-700">
            <td class="font-semibold px-3 py-2 w-1/2">Rohwert</td>
            <td class="px-3 py-2">{{ local.rohwert }}</td>
          </tr>
          <tr class="dark:bg-gray-800">
            <td class="font-semibold px-3 py-2">Prozentrang (PR)</td>
            <td class="px-3 py-2">{{ local.prozentrang }}</td>
          </tr>
          <tr class="bg-muted/40 dark:bg-gray-700">
            <td class="font-semibold px-3 py-2">T-Wert (Normwert)</td>
            <td class="px-3 py-2">{{ local.twert }}</td>
          </tr>
          <tr class="dark:bg-gray-800">
            <td class="font-semibold px-3 py-2">Benötigte Zeit</td>
            <td class="px-3 py-2">{{ formatTime(local.total_time_seconds) }}</td>
          </tr>
        </tbody>
      </table>

      <h3 class="font-bold mb-2">Antworten</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm border rounded-lg shadow">
          <thead class="bg-muted/40 dark:bg-gray-700">
            <tr>
              <th class="px-2 py-1 text-left font-semibold">#</th>
              <th class="px-2 py-1 text-left font-semibold">Frage</th>
              <th class="px-2 py-1 text-left font-semibold">Ihre Antwort</th>
              <th class="px-2 py-1 text-left font-semibold">Richtige Antwort</th>
              <th class="px-2 py-1 text-left font-semibold">Bearbeitungszeit</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(ans, idx) in local.answers"
              :key="idx"
              :class="ans.is_correct ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20'"
            >
              <td class="px-2 py-1 font-medium text-muted-foreground">{{ idx + 1 }}</td>
              <td class="px-2 py-1 align-top">{{ ans.question }}</td>
              <td class="px-2 py-1">
                <Input v-model="ans.user_answer" class="w-24" />
              </td>
              <td class="px-2 py-1">
                <span class="font-mono">{{
                  Array.isArray(ans.correct_answers) ? ans.correct_answers.join(', ') : ans.correct_answers
                }}</span>
              </td>
              <td class="px-2 py-1 text-right font-mono min-w-[60px]">{{ formatTime(ans.time_seconds) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </div>
</template>
