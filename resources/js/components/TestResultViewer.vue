<script setup lang="ts">
defineOptions({
  inheritAttrs: false,
});
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';
import MrtAResult from '@/components/MrtAResult.vue';

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
  participantProfile?: { age: number } | null;
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
</script>

<template>
  <div v-if="local" v-bind="$attrs">
    <MrtAResult v-if="test.name === 'MRT-A'" :results="local" />
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
