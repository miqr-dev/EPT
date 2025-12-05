<script setup lang="ts">
import { computed } from 'vue';
import { getLpsDataset } from '@/pages/Questions/LPSPage1';

const props = defineProps<{ results: Record<string, any> | null; testName?: string }>();

const page1 = computed(() => props.results?.page1 ?? []);
const lpsRows = computed(() => getLpsDataset(props.testName).rows);
const displayRows = computed(() =>
  lpsRows.value.map((row, idx) => ({
    row,
    response:
      page1.value[idx] ?? {
        col1: [],
        col2: [],
        col3: [],
        col4: [],
        col5: [],
      },
  })),
);
const totalTime = computed(() => props.results?.total_time_seconds ?? null);
const page1Score = computed(() => props.results?.page1_score ?? null);
const page1MaxScore = computed(() => props.results?.page1_max_score ?? null);
const testLabel = computed(() => props.testName ?? 'LPS');

function formatTime(seconds?: number | null) {
  if (seconds == null) return '–';
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins}:${secs.toString().padStart(2, '0')}`;
}
</script>

<template>
  <div class="space-y-4">
    <div class="rounded-lg border bg-background">
      <div class="border-b px-4 py-3 text-base font-semibold">{{ testLabel }} – Seite 1</div>
      <div class="overflow-x-auto">
        <table class="min-w-[960px] w-full border-collapse text-sm">
          <thead>
            <tr class="bg-muted/40 text-left">
              <th colspan="2" class="px-4 py-2 text-center font-semibold">1 + 2</th>
              <th class="px-4 py-2 text-center font-semibold">3</th>
              <th class="px-4 py-2 text-center font-semibold">4</th>
              <th class="px-4 py-2 text-center font-semibold">5</th>
            </tr>
            <tr class="bg-muted/20 text-left">
              <th class="px-4 py-2 text-center font-semibold">1</th>
              <th class="px-4 py-2 text-center font-semibold">2</th>
              <th class="px-4 py-2 text-center font-semibold">3</th>
              <th class="px-4 py-2 text-center font-semibold">4</th>
              <th class="px-4 py-2 text-center font-semibold">5</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in displayRows" :key="row.row.id" class="border-t align-top">
              <td class="px-3 py-3">
                <div class="flex flex-wrap gap-2">
                  <div
                    v-for="(char, charIdx) in row.row.column1.split('')"
                    :key="`${row.row.id}-1-${charIdx}`"
                    class="relative flex h-8 w-8 items-center justify-center rounded-full border bg-muted/20 font-semibold"
                  >
                    <span class="text-sm leading-none">{{ char }}</span>
                    <span v-if="row.response.col1?.[charIdx]" class="absolute text-lg font-bold leading-none text-destructive">
                      /
                    </span>
                  </div>
                </div>
              </td>
              <td class="px-3 py-3">
                <div class="flex flex-wrap gap-2">
                  <div
                    v-for="(char, charIdx) in row.row.column2.split('')"
                    :key="`${row.row.id}-2-${charIdx}`"
                    class="relative flex h-8 w-8 items-center justify-center rounded-full border bg-muted/20 font-semibold"
                  >
                    <span class="text-sm leading-none">{{ char }}</span>
                    <span v-if="row.response.col2?.[charIdx]" class="absolute text-lg font-bold leading-none text-destructive">
                      /
                    </span>
                  </div>
                </div>
              </td>
              <td class="px-3 py-3">
                <div v-if="row.row.column3?.length" class="grid grid-cols-4 gap-2">
                  <div
                    v-for="(option, optionIdx) in row.row.column3"
                    :key="option.id"
                    class="relative flex h-12 w-12 items-center justify-center rounded-md border bg-muted/20"
                  >
                    <img :src="option.src" class="h-10 w-10" alt="" />
                    <span
                      v-if="row.response.col3?.[optionIdx]"
                      class="absolute text-lg font-bold leading-none text-destructive"
                    >
                      /
                    </span>
                  </div>
                </div>
                <div v-else class="text-center text-muted-foreground">–</div>
              </td>
              <td class="px-3 py-3 text-center text-muted-foreground">–</td>
              <td class="px-3 py-3 text-center text-muted-foreground">–</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
      <div>Benötigte Zeit: {{ formatTime(totalTime) }}</div>
      <div>
        Punkte: <span class="text-foreground font-semibold">{{ page1MaxScore ? `${page1Score} / ${page1MaxScore}` : '–' }}</span>
      </div>
    </div>
  </div>
</template>
