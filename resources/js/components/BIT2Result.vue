<script setup lang="ts">
import { computed } from 'vue';
import { BIT2_QUESTIONS } from '@/pages/Questions/BIT2Questions';

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
  results: ResultJson | null;
  participantProfile?: { age: number; sex?: string } | null;
}>();

// --- BIT‑2 percentile table (real values) ---
interface NormRow {
  percentile: string;
  [key: string]: string | number | null;
}

const bit2NormTables: Record<'m' | 'f', NormRow[]> = {
  m: [
    { percentile: '100', TH: 41, GH: 44, TN: 45, EH: 44, LF: 45, KB: 43, VB: 43, LG: 44, SE: 45 },
    { percentile: '95-', TH: 35, GH: 38, TN: 42, EH: 37, LF: 41, KB: 36, VB: 32, LG: 37, SE: 40 },
    { percentile: '90-', TH: 32, GH: 35, TN: 40, EH: 35, LF: 38, KB: 33, VB: 30, LG: 34, SE: 36 },
    { percentile: '85-', TH: 31, GH: 33, TN: 38, EH: 33, LF: 36, KB: 31, VB: 28, LG: 32, SE: 35 },
    { percentile: '80-', TH: 30, GH: 31, TN: 37, EH: 32, LF: 34, KB: 29, VB: 27, LG: 29, SE: 33 },
    { percentile: '75-', TH: 29, GH: 30, TN: 36, EH: 31, LF: 32, KB: 28, VB: 26, LG: 27, SE: 31 },
    { percentile: '70-', TH: 28, GH: 28, TN: 35, EH: 28, LF: 31, KB: 27, VB: 25, LG: 26, SE: 30 },
    { percentile: '65-', TH: 26, GH: 27, TN: 34, EH: 27, LF: 29, KB: 26, VB: 24, LG: 24, SE: 28 },
    { percentile: '60-', TH: 25, GH: 26, TN: 32, EH: 26, LF: 28, KB: 25, VB: 23, LG: 23, SE: 27 },
    { percentile: '55-', TH: 24, GH: 25, TN: 31, EH: 25, LF: 27, KB: 24, VB: 22, LG: null, SE: 26 },
    { percentile: '50-', TH: 23, GH: 23, TN: 29, EH: null, LF: 26, KB: 23, VB: null, LG: 22, SE: 24 },
    { percentile: '45-', TH: 21, GH: 22, TN: 28, EH: 24, LF: 25, KB: 22, VB: 21, LG: 20, SE: 23 },
    { percentile: '40-', TH: 20, GH: 21, TN: 27, EH: 23, LF: 23, KB: 21, VB: 19, LG: null, SE: 21 },
    { percentile: '35-', TH: 19, GH: 20, TN: 25, EH: 21, LF: 22, KB: 20, VB: 18, LG: 19, SE: 20 },
    { percentile: '30-', TH: 18, GH: 19, TN: 23, EH: 20, LF: 20, KB: 19, VB: 16, LG: 18, SE: 19 },
    { percentile: '25-', TH: 16, GH: 18, TN: 21, EH: 19, LF: 19, KB: 17, VB: 15, LG: 17, SE: 18 },
    { percentile: '20-', TH: 14, GH: 17, TN: 19, EH: 18, LF: 18, KB: 16, VB: 14, LG: 16, SE: 17 },
    { percentile: '15-', TH: 13, GH: 15, TN: 18, EH: 17, LF: 17, KB: 15, VB: 12, LG: 15, SE: 15 },
    { percentile: '10-', TH: 10, GH: 14, TN: 16, EH: 15, LF: 15, KB: 13, VB: 11, LG: 14, SE: 14 },
    { percentile: '5-', TH: 9, GH: 13, TN: 13, EH: 13, LF: 13, KB: 11, VB: 9, LG: 11, SE: 12 },
    { percentile: '>0-', TH: null, GH: 9, TN: 9, EH: 9, LF: 9, KB: 9, VB: null, LG: 9, SE: 9 },
  ],
  f: [
    { percentile: '100', TH: 43, GH: 45, TN: 45, EH: 44, LF: 45, KB: 42, VB: 45, LG: 44, SE: 45 },
    { percentile: '95', TH: 27, GH: 41, TN: 36, EH: 38, LF: 39, KB: 34, VB: 34, LG: 37, SE: 42 },
    { percentile: '90', TH: 24, GH: 38, TN: 34, EH: 35, LF: 36, KB: 31, VB: 31, LG: 35, SE: 41 },
    { percentile: '85', TH: 22, GH: 37, TN: 31, EH: 34, LF: 34, KB: 29, VB: 29, LG: 32, SE: 39 },
    { percentile: '80', TH: 20, GH: 36, TN: 28, EH: 32, LF: 32, KB: 28, VB: 27, LG: 31, SE: 38 },
    { percentile: '75', TH: null, GH: 35, TN: 26, EH: 30, LF: 31, KB: 27, VB: 26, LG: 29, SE: 37 },
    { percentile: '70', TH: 19, GH: 33, TN: 25, EH: 29, LF: 29, KB: 26, VB: 25, LG: 27, SE: 36 },
    { percentile: '65', TH: 18, GH: 32, TN: 24, EH: 28, LF: 28, KB: 25, VB: null, LG: 26, SE: null },
    { percentile: '60', TH: 17, GH: 31, TN: 23, EH: 27, LF: 27, KB: 24, VB: 24, LG: 25, SE: 35 },
    { percentile: '55', TH: 16, GH: 29, TN: 22, EH: 26, LF: 26, KB: null, VB: 23, LG: 24, SE: 34 },
    { percentile: '50', TH: 15, GH: 28, TN: 21, EH: null, LF: 25, KB: 23, VB: 22, LG: 23, SE: 33 },
    { percentile: '45', TH: 14, GH: 27, TN: 20, EH: 25, LF: 24, KB: null, VB: 21, LG: 22, SE: 32 },
    { percentile: '40', TH: 13, GH: null, TN: 19, EH: 24, LF: 23, KB: 22, VB: 20, LG: 21, SE: 31 },
    { percentile: '35', TH: 12, GH: 26, TN: 18, EH: 23, LF: 22, KB: 21, VB: 19, LG: 20, SE: 30 },
    { percentile: '30', TH: null, GH: 24, TN: 17, EH: 22, LF: 21, KB: 20, VB: 18, LG: 18, SE: 29 },
    { percentile: '25', TH: 11, GH: 23, TN: 16, EH: 21, LF: 20, KB: 19, VB: 16, LG: 17, SE: 27 },
    { percentile: '20', TH: 10, GH: 21, TN: 15, EH: 20, LF: 18, KB: 18, VB: 15, LG: 16, SE: 26 },
    { percentile: '15', TH: 9, GH: 20, TN: 14, EH: 19, LF: 17, KB: 17, VB: 13, LG: 15, SE: 24 },
    { percentile: '10', TH: null, GH: 17, TN: 12, EH: 17, LF: 16, KB: 15, VB: 11, LG: 14, SE: 22 },
    { percentile: '5', TH: null, GH: 15, TN: 11, EH: 14, LF: 14, KB: 13, VB: 9, LG: 12, SE: 18 },
    { percentile: '>0', TH: null, GH: 9, TN: 9, EH: 9, LF: 9, KB: 9, VB: null, LG: 9, SE: 10 },
  ],
};

const normTable = computed(() => bit2NormTables[props.participantProfile?.sex === 'f' ? 'f' : 'm']);

const highlightPercentilePrefixes = ['85', '20'];

function shouldHighlightRow(percentile: NormRow['percentile']) {
  const normalized = `${percentile ?? ''}`.trim();
  return highlightPercentilePrefixes.some((prefix) => normalized.startsWith(prefix));
}


const highlighted = computed(() => {
  const map: Record<string, number | null> = {};
  if (!props.results) return map;
  for (const code of bit2Groups) {
    const raw = props.results.group_totals?.[code] ?? 0;
    let idx: number | null = null;
    const rows = normTable.value;
    for (let i = 0; i < rows.length; i++) {
      const row = rows[i];
      const threshold = row[code] as number | null;
      if (threshold != null && raw >= threshold) {
        idx = i;
        break;
      }
    }
    map[code] = idx;
  }
  return map;
});

const answerMap: Record<number, string> = {
  5: 'sehr gern',
  4: 'gern',
  3: 'weder gern noch ungern',
  2: 'ungern',
  1: 'sehr ungern',
};

const normalizedAnswers = computed(() => {
  if (!props.results?.answers) return [];
  return props.results.answers.map((ans: any, index: number) => {
    const question = BIT2_QUESTIONS[index];
    const userAnswer = ans.user_answer ?? ans.answer;
    return {
      number: ans.number ?? index + 1,
      question: question?.text ?? ans.question,
      user_answer: userAnswer !== null ? answerMap[userAnswer] ?? userAnswer : '–',
      time_seconds: ans.time_seconds,
    };
  });
});
</script>
<template>
  <div class="overflow-x-auto">
    <!-- <table class="mb-4 w-full overflow-hidden rounded-lg border text-sm shadow">
      <thead class="bg-muted/40 dark:bg-gray-700">
        <tr>
          <th v-for="(code, idx) in bit2Groups" :key="code" class="px-3 py-2 text-center font-semibold">{{ idx + 1 }} {{
            code }}</th>
        </tr>
      </thead>
      <tbody>
        <tr class="dark:bg-gray-800">
          <td v-for="code in bit2Groups" :key="code" class="px-3 py-2 text-center">
            {{ results.group_totals?.[code] ?? 0 }}
          </td>
        </tr>
      </tbody>
    </table> -->
    <!-- Norm table -->
    <table class="w-auto mx-auto border text-sm min-w-[760px]">
      <thead class="bg-muted/40 dark:bg-gray-700">
        <tr>
          <th class="px-2 py-1 text-center font-semibold">{{ props.participantProfile?.sex === 'f' ? '♀' : '♂' }}</th>
          <th v-for="code in bit2Groups" :key="code" class="px-2 py-1 font-semibold">
            {{ code }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, rIdx) in normTable" :key="rIdx" :class="{
          'border-b-2 border-blue-500': shouldHighlightRow(row.percentile),
        }">
          <td class="px-2 py-1 text-center">{{ row.percentile }}</td>
          <td v-for="code in bit2Groups" :key="code" class="px-2 py-1 text-center" :class="{
            'text-red-600 underline decoration-2': highlighted[code] === rIdx,
          }">
            {{ row[code] }}
          </td>
        </tr>
        <!-- Footer: single left cell (% over SC) spanning two rows -->
        <tr class="border-t-2">
          <!-- Left merged cell -->
          <td class="px-2 py-1 text-center align-top" rowspan="2">
            <div class="leading-tight">
              <div>%</div>
              <div class="mt-1">SC</div>
            </div>
          </td>

          <!-- Row for % (empty cells, like in the scan) -->
          <td v-for="code in bit2Groups" :key="'pct-' + code" class="px-2 py-1 text-center"></td>
        </tr>

        <!-- Row for SC values (scores at the very bottom) -->
        <tr>
          <td v-for="code in bit2Groups" :key="'sc-' + code" class="px-2 py-1 text-center font-semibold">
            {{ results.group_totals?.[code] ?? '' }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
    <details class="mt-4">
      <summary class="cursor-pointer">Antworten anzeigen</summary>
      <table class="w-full text-sm border-collapse border border-gray-300">
        <thead>
          <tr class="bg-gray-100">
            <th class="border border-gray-300 p-2">#</th>
            <th class="border border-gray-300 p-2">Frage</th>
            <th class="border border-gray-300 p-2">Ihre Antwort</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(answer, index) in normalizedAnswers" :key="index">
            <td class="border border-gray-300 p-2">{{ answer.number }}</td>
            <td class="border border-gray-300 p-2">{{ answer.question }}</td>
            <td class="border border-gray-300 p-2">{{ answer.user_answer }}</td>
          </tr>
        </tbody>
      </table>
    </details>
</template>
