<script setup lang="ts">
import { computed } from 'vue';
import { LPS_PAGE1_ROWS } from '@/pages/Questions/LPSPage1';

const props = defineProps<{ results: Record<string, any> | null }>();

const page1 = computed(() => props.results?.page1 ?? []);
const totalTime = computed(() => props.results?.total_time_seconds ?? null);

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
      <div class="border-b px-4 py-3 text-base font-semibold">LPS – Seite 1</div>
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
            <tr v-for="(row, idx) in page1" :key="idx" class="border-t align-top">
              <td class="px-3 py-3">
                <div class="flex items-center justify-between gap-3">
                  <span class="font-mono text-lg leading-none">{{ LPS_PAGE1_ROWS[idx]?.column12[0] }}</span>
                  <span class="w-32 rounded border bg-muted/30 px-2 py-1 font-mono text-sm">
                    {{ row.col1 || '–' }}
                  </span>
                </div>
              </td>
              <td class="px-3 py-3">
                <div class="flex items-center justify-between gap-3">
                  <span class="font-mono text-lg leading-none">{{ LPS_PAGE1_ROWS[idx]?.column12[1] }}</span>
                  <span class="w-32 rounded border bg-muted/30 px-2 py-1 font-mono text-sm">
                    {{ row.col2 || '–' }}
                  </span>
                </div>
              </td>
              <td class="px-3 py-3">
                <div class="flex items-center justify-between gap-3">
                  <span class="font-mono text-lg leading-none">{{ LPS_PAGE1_ROWS[idx]?.column3 }}</span>
                  <span class="w-32 rounded border bg-muted/30 px-2 py-1 font-mono text-sm">
                    {{ row.col3 || '–' }}
                  </span>
                </div>
              </td>
              <td class="px-3 py-3">
                <div class="flex items-center justify-between gap-3">
                  <span class="font-mono text-lg leading-none">{{ LPS_PAGE1_ROWS[idx]?.column4 }}</span>
                  <span class="w-32 rounded border bg-muted/30 px-2 py-1 font-mono text-sm">
                    {{ row.col4 || '–' }}
                  </span>
                </div>
              </td>
              <td class="px-3 py-3">
                <div class="flex items-center justify-between gap-3">
                  <span class="font-mono text-lg leading-none">{{ LPS_PAGE1_ROWS[idx]?.column5 }}</span>
                  <span class="w-32 rounded border bg-muted/30 px-2 py-1 font-mono text-sm">
                    {{ row.col5 || '–' }}
                  </span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="text-sm text-muted-foreground">Benötigte Zeit: {{ formatTime(totalTime) }}</div>
  </div>
</template>
