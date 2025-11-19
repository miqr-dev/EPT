<script setup lang="ts">
defineOptions({
  inheritAttrs: false,
});
import LmtResult from '@/components/LmtResult.vue';
import MrtAResult from '@/components/MrtAResult.vue';
import MrtBResult from '@/components/MrtBResult.vue';
import KonzentrationstestResult from '@/components/KonzentrationstestResult.vue';
import AvemResult from '@/components/AvemResult.vue';
import { Input } from '@/components/ui/input';
import FpiResult from '@/pages/Scores/FPI/FPIResult.vue';
import { norms_female_16_24 } from '@/pages/Scores/FPI/norms_female_16_24';
import { norms_female_25_44 } from '@/pages/Scores/FPI/norms_female_25_44';
import { norms_female_45_59 } from '@/pages/Scores/FPI/norms_female_45_59';
import { norms_female_60up } from '@/pages/Scores/FPI/norms_female_60up';
import { norms_male_16_24 } from '@/pages/Scores/FPI/norms_male_16_24';
import { norms_male_25_44 } from '@/pages/Scores/FPI/norms_male_25_44';
import { norms_male_45_59 } from '@/pages/Scores/FPI/norms_male_45_59';
import { norms_male_60up } from '@/pages/Scores/FPI/norms_male_60up';
import { computed, ref, watch } from 'vue';
import BIT2Result from '@/components/BIT2Result.vue';

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

const props = withDefaults(
  defineProps<{
    modelValue: ResultJson | null;
    test: { name: string };
    participantProfile?: { age: number; sex?: string } | null;
    showAnswers?: boolean;
  }>(),
  {
    showAnswers: true,
  },
);

const emit = defineEmits(['update:modelValue']);

const local = ref<ResultJson | null>(null);

watch(
  () => props.modelValue,
  (val) => {
    local.value = val ? JSON.parse(JSON.stringify(val)) : null;
  },
  { immediate: true, deep: true },
);

watch(
  local,
  (val) => {
    emit('update:modelValue', val as any);
  },
  { deep: true },
);

function formatTime(seconds?: number | null) {
  if (seconds == null) return '–';
  const m = Math.floor(seconds / 60);
  const s = seconds % 60;
  return `${m}:${s.toString().padStart(2, '0')}`;
}

function getFpiNormTable(sex?: string, age?: number) {
  if (!sex || !age) return null;
  const s = sex.toLowerCase();
  const isFemale = s === 'f' || s === 'female' || s === 'w' || s === 'weiblich';
  if (age >= 16 && age <= 24) {
    return isFemale ? norms_female_16_24 : norms_male_16_24;
  }
  if (age >= 25 && age <= 44) {
    return isFemale ? norms_female_25_44 : norms_male_25_44;
  }
  if (age >= 45 && age <= 59) {
    return isFemale ? norms_female_45_59 : norms_male_45_59;
  }
  return isFemale ? norms_female_60up : norms_male_60up;
}

const fpiStanines = computed(() => {
  if (!local.value) return [];
  const stanineKeys = ['LEB', 'SOZ', 'LEI', 'GEH', 'ERR', 'AGGR', 'BEAN', 'KORP', 'GES', 'OFF', 'EXTR', 'EMOT'];
  const scoreKeys: (number | string)[] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 'E', 'N'];
  const src: any = local.value;

  // Direct arrays
  const direct = src.stanines || src.categoryStanines;
  if (Array.isArray(direct) && direct.some((v: any) => v != null)) {
    return direct.map((v: any) => (v != null ? Number(v) : null));
  }

  // Object with keys
  const obj = src.category_stanines || src.categoryStanines || src.stanines;
  if (obj && typeof obj === 'object' && !Array.isArray(obj)) {
    const arr = stanineKeys.map((k) => (obj[k] != null ? Number(obj[k]) : null));
    if (arr.some((v) => v != null)) return arr;
  }

  // Derive from raw scores
  const scores = src.category_scores || src.categoryScores;
  if (scores) {
    const table = getFpiNormTable(props.participantProfile?.sex, props.participantProfile?.age);
    if (!table) return [];
    return stanineKeys.map((key, idx) => {
      const raw = scores[scoreKeys[idx]];
      if (raw == null) return null;
      const ranges: [number, number][] = (table as any)[key] ?? [];
      for (let i = 0; i < ranges.length; i++) {
        const [min, max] = ranges[i];
        if (raw >= min && raw <= max) return i + 1;
      }
      return null;
    });
  }
  return [];
});

const fpiRohwerte = computed(() => {
  if (!local.value) return [];
  const src: any = local.value;
  if (Array.isArray(src.rohwerte)) return src.rohwerte;
  if (Array.isArray(src.categoryScores)) return src.categoryScores;
  const scores = src.category_scores || src.categoryScores;
  if (scores) {
    const keys: (string | number)[] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 'E', 'N'];
    return keys.map((k) => scores[k] ?? null);
  }
  return [];
});
</script>

<template>
  <div v-if="local" v-bind="$attrs">
    <MrtAResult v-if="test.name === 'MRT-A'" :results="local" />
    <MrtBResult v-else-if="test.name === 'MRT-B'" :results="local" />
    <KonzentrationstestResult v-else-if="test.name === 'Konzentrationstest'" :results="local" />
        <LmtResult v-else-if="test.name === 'LMT'" :results="local" :show-answers="showAnswers" />
        <FpiResult v-else-if="test.name === 'FPI-R'" :stanines="fpiStanines" :rohwerte="fpiRohwerte" :answers="local.answers" :show-answers="showAnswers" />
        <BIT2Result v-else-if="test.name === 'BIT-2'" :results="local" :participantProfile="participantProfile" :show-answers="showAnswers" />
        <AvemResult v-else-if="test.name === 'AVEM'" :results="local" />
    <template v-else>
      <table class="mb-4 w-full overflow-hidden rounded-lg border text-sm shadow">
        <tbody>
          <tr class="bg-muted/40 dark:bg-gray-700">
            <td class="w-1/2 px-3 py-2 font-semibold">Rohwert</td>
            <td class="px-3 py-2">{{ local.rohwert }}</td>
          </tr>
          <tr class="dark:bg-gray-800">
            <td class="px-3 py-2 font-semibold">Prozentrang (PR)</td>
            <td class="px-3 py-2">{{ local.prozentrang }}</td>
          </tr>
          <tr class="bg-muted/40 dark:bg-gray-700">
            <td class="px-3 py-2 font-semibold">T-Wert (Normwert)</td>
            <td class="px-3 py-2">{{ local.twert }}</td>
          </tr>
          <tr class="dark:bg-gray-800">
            <td class="px-3 py-2 font-semibold">Benötigte Zeit</td>
            <td class="px-3 py-2">{{ formatTime(local.total_time_seconds) }}</td>
          </tr>
        </tbody>
      </table>

      <template v-if="test.name === 'BRT-A' || test.name === 'BRT-B'">
        <h3 class="mb-2 font-bold">Antworten</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full rounded-lg border text-sm shadow">
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
              <tr v-for="(ans, idx) in local.answers" :key="idx"
                :class="ans.is_correct ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20'">
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
                <td class="min-w-[60px] px-2 py-1 text-right font-mono">{{ formatTime(ans.time_seconds) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </template>
  </div>
</template>
